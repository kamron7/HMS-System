<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingCalendarController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', today()->toDateString()))->startOfDay();
        $days = min(60, max(7, (int) $request->query('days', 30)));
        $to   = $from->copy()->addDays($days);

        $bookingsRaw = Booking::with(['guest', 'payments'])
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Confirmed->value,
                BookingStatus::CheckedIn->value,
                BookingStatus::CheckedOut->value,
                BookingStatus::Inquiry->value,
            ])
            ->where('check_in_date', '<', $to)
            ->where('check_out_date', '>', $from)
            ->get();

        $bookings = $bookingsRaw->map(fn(Booking $b) => [
            'id'         => $b->id,
            'room_id'    => $b->room_id,
            'guest'      => trim(optional($b->guest)->first_name . ' ' . optional($b->guest)->last_name),
            'phone'      => optional($b->guest)->phone ?? '',
            'check_in'   => $b->check_in_date->toDateString(),
            'check_out'  => $b->check_out_date->toDateString(),
            'nights'     => $b->check_in_date->diffInDays($b->check_out_date),
            'status'     => $b->status->value,
            'total'      => (float) $b->total_price,
            'paid'       => (float) $b->payments->sum('amount'),
            'url'        => route('bookings.show', $b),
            'status_url' => route('bookings.status', $b),
        ]);

        $blocks = RoomBlock::where('check_in_date', '<', $to)
            ->where('check_out_date', '>', $from)
            ->get()
            ->map(fn(RoomBlock $b) => [
                'id'           => $b->id,
                'room_id'      => $b->room_id,
                'check_in'     => $b->check_in_date->toDateString(),
                'check_out'    => $b->check_out_date->toDateString(),
                'reason'       => $b->reason,
                'reason_label' => RoomBlock::reasonLabel($b->reason),
                'notes'        => $b->notes,
                'delete_url'   => route('room-blocks.destroy', $b),
            ]);

        return response()->json(compact('bookings', 'blocks'));
    }

    public function index(Request $request): View
    {
        $fromStr = $request->query('from', today()->startOfMonth()->toDateString());
        $from    = Carbon::parse($fromStr)->startOfDay();
        $days    = 150;
        $to      = $from->copy()->addDays($days);

        $rooms = Room::with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $bookingsRaw = Booking::with(['guest', 'payments'])
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Confirmed->value,
                BookingStatus::CheckedIn->value,
                BookingStatus::CheckedOut->value,
                BookingStatus::Inquiry->value,
            ])
            ->where('check_in_date', '<', $to)
            ->where('check_out_date', '>', $from)
            ->get();

        $bookings = $bookingsRaw->map(fn(Booking $b) => [
            'id'         => $b->id,
            'room_id'    => $b->room_id,
            'guest'      => trim(optional($b->guest)->first_name . ' ' . optional($b->guest)->last_name),
            'phone'      => optional($b->guest)->phone ?? '',
            'check_in'   => $b->check_in_date->toDateString(),
            'check_out'  => $b->check_out_date->toDateString(),
            'nights'     => $b->check_in_date->diffInDays($b->check_out_date),
            'status'     => $b->status->value,
            'total'      => (float) $b->total_price,
            'paid'       => (float) $b->payments->sum('amount'),
            'url'        => route('bookings.show', $b),
            'status_url' => route('bookings.status', $b),
        ]);

        // Today's stats
        $todayStr          = today()->toDateString();
        $occupiedToday     = $bookingsRaw->filter(fn($b) =>
            in_array($b->status->value, [BookingStatus::CheckedIn->value, BookingStatus::Confirmed->value, BookingStatus::Pending->value]) &&
            $b->check_in_date->toDateString()  <= $todayStr &&
            $b->check_out_date->toDateString() >  $todayStr
        );
        $occupiedRoomIds   = $occupiedToday->pluck('room_id')->unique();
        $availableRooms    = $rooms->filter(fn($r) => !$occupiedRoomIds->contains($r->id));
        $checkingOutToday  = $bookingsRaw->filter(fn($b) => $b->check_out_date->toDateString() === $todayStr);
        $checkingInToday   = $bookingsRaw->filter(fn($b) => $b->check_in_date->toDateString()  === $todayStr);

        $todayStats = [
            'total'          => $rooms->count(),
            'available'      => $availableRooms->count(),
            'occupied'       => $occupiedToday->count(),
            'checking_out'   => $checkingOutToday->count(),
            'checking_in'    => $checkingInToday->count(),
            'available_rooms' => $availableRooms->sortBy('number')->map(fn($r) => ['id' => $r->id, 'number' => $r->number, 'type' => optional($r->roomType)->name ?? ''])->values()->all(),
            'checkout_nums'  => $checkingOutToday->map(fn($b) => optional($rooms->find($b->room_id))->number)->filter()->sort()->values()->all(),
        ];

        $roomsJson = $rooms->map(fn(Room $r) => [
            'id'         => $r->id,
            'number'     => $r->number,
            'floor'      => $r->floor,
            'type'       => optional($r->roomType)->name,
            'status'     => $r->status->value,
            'capacity'   => optional($r->roomType)->capacity ?? 1,
            'base_price' => (float) (optional($r->roomType)->base_price ?? 0),
        ]);

        $blocksRaw = RoomBlock::where('check_in_date', '<', $to)
            ->where('check_out_date', '>', $from)
            ->get();

        $blocksJson = $blocksRaw->map(fn(RoomBlock $b) => [
            'id'           => $b->id,
            'room_id'      => $b->room_id,
            'check_in'     => $b->check_in_date->toDateString(),
            'check_out'    => $b->check_out_date->toDateString(),
            'reason'       => $b->reason,
            'reason_label' => RoomBlock::reasonLabel($b->reason),
            'notes'        => $b->notes,
            'delete_url'   => route('room-blocks.destroy', $b),
        ]);

        return view('bookings.calendar', [
            'from'         => $from,
            'days'         => $days,
            'totalRooms'   => $rooms->count(),
            'roomsJson'    => $roomsJson,
            'bookingsJson' => $bookings,
            'blocksJson'   => $blocksJson,
            'todayStats'   => $todayStats,
        ]);
    }
}
