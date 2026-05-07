<?php

namespace App\Http\Controllers;

use App\Models\RoomBlock;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomBlockController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date'  => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'reason'         => ['required', 'string', 'in:cleaning,maintenance,owner,admin,other'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        // Reject if active bookings overlap
        $conflict = \App\Models\Booking::where('room_id', $data['room_id'])
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in_date', '<', $data['check_out_date'])
            ->where('check_out_date', '>', $data['check_in_date'])
            ->exists();

        if ($conflict) {
            return response()->json(['error' => 'На эти даты есть активное бронирование.'], 422);
        }

        $block = RoomBlock::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        $room = Room::find($data['room_id']);

        return response()->json([
            'ok'    => true,
            'block' => [
                'id'         => $block->id,
                'room_id'    => $block->room_id,
                'check_in'   => $block->check_in_date->toDateString(),
                'check_out'  => $block->check_out_date->toDateString(),
                'reason'     => $block->reason,
                'reason_label' => RoomBlock::reasonLabel($block->reason),
                'notes'      => $block->notes,
            ],
        ]);
    }

    public function destroy(RoomBlock $roomBlock): JsonResponse
    {
        $roomBlock->delete();
        return response()->json(['ok' => true]);
    }
}
