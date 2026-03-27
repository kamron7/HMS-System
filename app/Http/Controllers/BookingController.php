<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::with(['guest', 'room.roomType'])
            ->orderBy('check_in_date', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('guest', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereRaw('first_name ILIKE ?', ["%{$search}%"])
                          ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                          ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($checkIn = $request->query('check_in')) {
            $query->where('check_in_date', '>=', $checkIn);
        }

        if ($checkOut = $request->query('check_out')) {
            $query->where('check_out_date', '<=', $checkOut);
        }

        $bookings = $query->paginate(20)->appends($request->query());
        $statuses = BookingStatus::cases();

        return view('bookings.index', [
            'bookings'  => $bookings,
            'statuses'  => $statuses,
            'search'    => $request->query('search', ''),
            'status'    => $request->query('status', ''),
            'check_in'  => $request->query('check_in', ''),
            'check_out' => $request->query('check_out', ''),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'guest',
            'room.roomType',
            'payments' => fn($q) => $q->orderBy('paid_at', 'asc'),
            'creator',
        ]);

        $paymentStatus = $booking->paymentStatus();

        return view('bookings.show', compact('booking', 'paymentStatus'));
    }

    public function create(): View
    {
        return view('bookings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'guest_id'       => ['required', 'integer', 'exists:guests,id'],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults'         => ['required', 'integer', 'min:1', 'max:20'],
            'children'       => ['nullable', 'integer', 'min:0', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if (! $room->isAvailable($validated['check_in_date'], $validated['check_out_date'])) {
            return back()
                ->withErrors(['room_id' => 'Этот номер уже занят на выбранные даты.'])
                ->withInput();
        }

        $room->load('roomType');

        $nights     = Carbon::parse($validated['check_in_date'])->diffInDays(Carbon::parse($validated['check_out_date']));
        $totalPrice = $nights * $room->roomType->base_price;

        $booking = Booking::create([
            ...$validated,
            'children'    => $validated['children'] ?? 0,
            'status'      => BookingStatus::Pending->value,
            'total_price' => $totalPrice,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Бронирование создано');
    }

    public function edit(Booking $booking)
    {
        abort(404);
    }

    public function update(Request $request, Booking $booking)
    {
        abort(404);
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'transition' => ['required', 'string', Rule::in(array_column(BookingStatus::cases(), 'value'))],
        ]);

        $target = BookingStatus::from($request->transition);

        if (!$booking->status->canTransitionTo($target)) {
            return back()->with('error', 'Недопустимый переход статуса.');
        }

        $booking->status = $target;
        $booking->save();

        $room = $booking->room;
        match($target) {
            BookingStatus::CheckedIn  => $room->update(['status' => RoomStatus::Occupied->value]),
            BookingStatus::CheckedOut => $room->update(['status' => RoomStatus::Cleaning->value]),
            BookingStatus::Cancelled  => $room->status === RoomStatus::Occupied
                ? $room->update(['status' => RoomStatus::Available->value])
                : null,
            default => null,
        };

        return back()->with('success', "Статус бронирования обновлён: {$target->label()}");
    }
}
