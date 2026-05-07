<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');

        $rooms = Room::with([
            'roomType',
            'assignedUser',
            'bookings' => fn($q) => $q->where('status', BookingStatus::CheckedIn->value)->with('guest'),
        ])
            ->when($statusFilter, fn($q, $s) => $q->where('status', $s))
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        // Rooms that have an incoming booking today (pending/confirmed) — these dirty rooms need priority
        $urgentRoomIds = \App\Models\Booking::whereIn('status', [
                BookingStatus::Confirmed->value,
                BookingStatus::Pending->value,
            ])
            ->whereDate('check_in_date', today())
            ->pluck('room_id')
            ->flip()
            ->toArray();

        // Within each floor, put dirty rooms with today's check-in at the top
        $rooms = $rooms->map(fn($floorRooms) =>
            $floorRooms->sortBy(fn($room) => match(true) {
                $room->status === RoomStatus::Dirty && isset($urgentRoomIds[$room->id]) => 0,
                $room->status === RoomStatus::Dirty   => 1,
                $room->status === RoomStatus::Cleaning => 2,
                default => 3,
            })->values()
        );

        $counts = [
            'all'         => Room::count(),
            'available'   => Room::where('status', RoomStatus::Available->value)->count(),
            'occupied'    => Room::where('status', RoomStatus::Occupied->value)->count(),
            'dirty'       => Room::where('status', RoomStatus::Dirty->value)->count(),
            'cleaning'    => Room::where('status', RoomStatus::Cleaning->value)->count(),
            'inspected'   => Room::where('status', RoomStatus::Inspected->value)->count(),
            'maintenance' => Room::where('status', RoomStatus::Maintenance->value)->count(),
        ];

        $staff = User::orderBy('name')->get();

        // Overdue checked-in bookings (checkout date has passed)
        $overdueBookings = \App\Models\Booking::with(['room', 'guest'])
            ->where('status', BookingStatus::CheckedIn->value)
            ->whereDate('check_out_date', '<', today())
            ->get();

        return view('housekeeping.index', compact('rooms', 'statusFilter', 'counts', 'staff', 'urgentRoomIds', 'overdueBookings'));
    }

    public function update(Request $request, Room $room)
    {
        // Handle assignment only
        if ($request->input('action') === 'assign') {
            $request->validate([
                'assigned_to' => ['nullable', 'exists:users,id'],
            ]);

            $room->assigned_to = $request->assigned_to ?: null;
            $room->save();

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', 'Сотрудник назначен');
        }

        $request->validate([
            'status' => ['required', Rule::in(array_column(RoomStatus::cases(), 'value'))],
        ]);

        $newStatus = RoomStatus::from($request->status);
        $currentStatus = $room->status;

        $allowed = [
            RoomStatus::Dirty->value       => [RoomStatus::Cleaning->value],
            RoomStatus::Cleaning->value    => [RoomStatus::Inspected->value, RoomStatus::Available->value],
            RoomStatus::Inspected->value   => [RoomStatus::Available->value],
            RoomStatus::Available->value   => [RoomStatus::Dirty->value, RoomStatus::Cleaning->value, RoomStatus::Maintenance->value],
            RoomStatus::Maintenance->value => [RoomStatus::Available->value, RoomStatus::Dirty->value],
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

    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'room_ids'   => ['required', 'array', 'min:1'],
            'room_ids.*' => ['integer', 'exists:rooms,id'],
            'status'     => ['required', Rule::in(array_column(RoomStatus::cases(), 'value'))],
        ]);

        $newStatus = RoomStatus::from($request->status);

        // Only allow dirty→cleaning in bulk
        if ($newStatus !== RoomStatus::Cleaning) {
            return response()->json(['error' => 'Массовое обновление доступно только для перехода "Грязный → Уборка".'], 422);
        }

        $updated = Room::whereIn('id', $request->room_ids)
            ->where('status', RoomStatus::Dirty->value)
            ->update(['status' => RoomStatus::Cleaning->value]);

        return response()->json(['updated' => $updated]);
    }
}
