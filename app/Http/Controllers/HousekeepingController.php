<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');

        $rooms = Room::with([
            'roomType',
            'bookings' => fn($q) => $q->where('status', BookingStatus::CheckedIn->value)->with('guest'),
        ])
            ->when($statusFilter, fn($q, $s) => $q->where('status', $s))
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        return view('housekeeping.index', compact('rooms', 'statusFilter'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_column(RoomStatus::cases(), 'value'))],
        ]);

        $newStatus = RoomStatus::from($request->status);
        $currentStatus = $room->status;

        $allowed = [
            RoomStatus::Cleaning->value    => [RoomStatus::Available->value],
            RoomStatus::Available->value   => [RoomStatus::Cleaning->value, RoomStatus::Maintenance->value],
            RoomStatus::Maintenance->value => [RoomStatus::Available->value],
        ];

        $allowedTargets = $allowed[$currentStatus->value] ?? [];

        if (! in_array($newStatus->value, $allowedTargets)) {
            abort(422, 'Недопустимый переход статуса');
        }

        $room->status = $newStatus;
        $room->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $room->status->value,
                'label'  => $room->status->label(),
            ]);
        }

        return redirect()->back()->with('success', 'Статус номера обновлён');
    }
}
