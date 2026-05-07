<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\BookingGroup;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingTotalsService;
use App\Services\PricingService;
use App\Services\RoomAvailabilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GroupBookingController extends Controller
{
    public function __construct(
        private RoomAvailabilityService $availability,
        private PricingService          $pricing,
        private BookingTotalsService    $totals,
    ) {}

    public function create(): View
    {
        return view('group-bookings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['nullable', 'string', 'max:150'],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'room_ids'       => ['required', 'array', 'min:2'],
            'room_ids.*'     => ['integer', 'exists:rooms,id'],
            'guest_id'       => ['required', 'integer', 'exists:guests,id'],
            'adults'         => ['required', 'integer', 'min:1', 'max:50'],
            'children'       => ['nullable', 'integer', 'min:0', 'max:50'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $checkIn  = $data['check_in_date'];
        $checkOut = $data['check_out_date'];
        $nights   = Carbon::parse($checkIn)->diffInDays($checkOut);

        $group = DB::transaction(function () use ($data, $checkIn, $checkOut, $nights) {
            $group = BookingGroup::create([
                'name'       => $data['name'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['room_ids'] as $roomId) {
                $room = Room::with('roomType')->lockForUpdate()->findOrFail($roomId);

                if (! $this->availability->isAvailableLocked($room, $checkIn, $checkOut)) {
                    throw ValidationException::withMessages([
                        'room_ids' => "Номер {$room->number} уже занят на выбранные даты.",
                    ]);
                }

                $pricePerNight = $this->pricing->adjustedPrice($room->roomType, $checkIn, $checkOut);

                Booking::create([
                    'room_id'          => $room->id,
                    'guest_id'         => $data['guest_id'],
                    'check_in_date'    => $checkIn,
                    'check_out_date'   => $checkOut,
                    'adults'           => $data['adults'],
                    'children'         => $data['children'] ?? 0,
                    'status'           => BookingStatus::Pending->value,
                    'source'           => BookingSource::Staff->value,
                    'total_price'      => $nights * $pricePerNight,
                    'notes'            => $data['notes'] ?? null,
                    'created_by'       => Auth::id(),
                    'booking_group_id' => $group->id,
                ]);

                $room->update(['status' => RoomStatus::Occupied->value]);
            }

            return $group;
        });

        return redirect()->route('group-bookings.show', $group)
            ->with('success', 'Групповое бронирование создано.');
    }

    public function show(BookingGroup $group): View
    {
        $group->load([
            'bookings.room.roomType',
            'bookings.payments',
            'bookings.charges',
            'bookings.guest',
            'creator',
        ]);

        $totalsPerBooking = $group->bookings->map(fn(Booking $b) => [
            'id'          => $b->id,
            'grand_total' => $this->totals->grandTotal($b),
            'paid'        => $this->totals->paidAmount($b),
            'balance_due' => $this->totals->balanceDue($b),
        ])->keyBy('id');

        return view('group-bookings.show', compact('group', 'totalsPerBooking'));
    }

    public function checkInAll(BookingGroup $group): RedirectResponse
    {
        $group->load('bookings.room');
        $errors = [];

        foreach ($group->bookings as $booking) {
            if ($booking->status !== BookingStatus::Confirmed && $booking->status !== BookingStatus::Pending) {
                continue;
            }

            $room = $booking->room;
            if (! $room?->status->canCheckIn()) {
                $errors[] = "Номер {$room->number}: не готов к заселению.";
                continue;
            }

            $booking->status = BookingStatus::CheckedIn;
            $booking->save();
            $room->update(['status' => RoomStatus::Occupied->value]);
        }

        if ($errors) {
            return redirect()->route('group-bookings.show', $group)
                ->with('error', implode(' ', $errors));
        }

        return redirect()->route('group-bookings.show', $group)
            ->with('success', 'Все гости заселены.');
    }

    public function checkOutAll(BookingGroup $group): RedirectResponse
    {
        $group->load('bookings.room');

        foreach ($group->bookings as $booking) {
            if ($booking->status !== BookingStatus::CheckedIn) {
                continue;
            }

            $booking->status = BookingStatus::CheckedOut;
            $booking->save();
            $booking->room?->update(['status' => RoomStatus::Dirty->value]);
        }

        return redirect()->route('group-bookings.show', $group)
            ->with('success', 'Все гости выселены.');
    }

    public function invoice(BookingGroup $group): Response
    {
        $group->load([
            'bookings.room.roomType',
            'bookings.payments',
            'bookings.charges',
            'bookings.guest',
            'creator',
        ]);

        $totalsPerBooking = $group->bookings->map(fn(Booking $b) => [
            'id'          => $b->id,
            'room_cost'   => (float) $b->total_price,
            'charges'     => $this->totals->chargesTotal($b),
            'grand_total' => $this->totals->grandTotal($b),
            'paid'        => $this->totals->paidAmount($b),
            'balance_due' => $this->totals->balanceDue($b),
        ])->keyBy('id');

        $grandTotalAll  = $totalsPerBooking->sum('grand_total');
        $paidAll        = $totalsPerBooking->sum('paid');
        $balanceDueAll  = $totalsPerBooking->sum('balance_due');

        $hotel = config('hotel');

        $pdf = Pdf::loadView('group-bookings.pdf.invoice', compact(
            'group', 'totalsPerBooking', 'grandTotalAll', 'paidAll', 'balanceDueAll', 'hotel'
        ));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('group-invoice-' . $group->group_ref . '.pdf');
    }
}
