<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        return view('rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        $statuses  = RoomStatus::cases();

        return view('rooms.create', compact('roomTypes', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number'       => ['required', 'string', 'max:10', 'unique:rooms,number'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'floor'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'status'       => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        Room::create($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Номер успешно добавлен.');
    }

    public function edit(Room $room): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        $statuses  = RoomStatus::cases();

        return view('rooms.edit', compact('room', 'roomTypes', 'statuses'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'number'       => ['required', 'string', 'max:10', 'unique:rooms,number,' . $room->id],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'floor'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'status'       => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Номер успешно обновлён.');
    }

    public function available(Request $request)
    {
        $checkIn  = $request->query('check_in');
        $checkOut = $request->query('check_out');

        $rooms = Room::with('roomType')
            ->where('status', RoomStatus::Available->value)
            ->get()
            ->filter(fn(Room $room) => $room->isAvailable($checkIn, $checkOut))
            ->values();

        return response()->json($rooms);
    }
}
