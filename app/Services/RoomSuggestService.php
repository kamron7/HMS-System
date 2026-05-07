<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Support\Collection;

class RoomSuggestService
{
    public function suggest(
        int     $guestId,
        string  $checkIn,
        string  $checkOut,
        int     $adults,
        RoomAvailabilityService $availability
    ): Collection {
        $guest = Guest::find($guestId);
        $preferredFloor = null;
        $preferredTypeId = null;

        if ($guest) {
            $last = Booking::where('guest_id', $guestId)
                ->whereNotNull('room_id')
                ->latest('check_in_date')
                ->with('room.roomType')
                ->first();

            if ($last?->room) {
                $preferredFloor  = $last->room->floor;
                $preferredTypeId = $last->room->room_type_id;
            }
        }

        // Get all rooms that fit adults capacity
        $rooms = Room::with('roomType')
            ->whereHas('roomType', fn($q) => $q->where('capacity', '>=', $adults))
            ->get()
            ->filter(fn(Room $r) => $availability->isAvailable($r, $checkIn, $checkOut));

        if ($rooms->isEmpty()) {
            return collect();
        }

        // Score each room: inspected > preferred floor > preferred type > available
        $scored = $rooms->map(function (Room $r) use ($preferredFloor, $preferredTypeId) {
            $score = 0;
            if ($r->status === RoomStatus::Inspected) $score += 30;
            if ($r->status === RoomStatus::Available)  $score += 10;
            if ($preferredFloor !== null && $r->floor === $preferredFloor)   $score += 20;
            if ($preferredTypeId !== null && $r->room_type_id === $preferredTypeId) $score += 15;
            return ['room' => $r, 'score' => $score];
        })->sortByDesc('score')->take(3);

        return $scored->map(fn($item) => [
            'id'             => $item['room']->id,
            'number'         => $item['room']->number,
            'floor'          => $item['room']->floor,
            'status'         => $item['room']->status->value,
            'type_name'      => optional($item['room']->roomType)->name,
            'base_price'     => (float) optional($item['room']->roomType)->base_price,
            'capacity'       => optional($item['room']->roomType)->capacity,
            'reason'         => $this->buildReason($item['room'], $preferredFloor, $preferredTypeId),
        ])->values();
    }

    private function buildReason(Room $room, ?int $preferredFloor, ?int $preferredTypeId): string
    {
        $parts = [];
        if ($room->status === RoomStatus::Inspected) $parts[] = 'прошёл проверку';
        if ($preferredFloor !== null && $room->floor === $preferredFloor) $parts[] = 'предпочтительный этаж';
        if ($preferredTypeId !== null && $room->room_type_id === $preferredTypeId) $parts[] = 'знакомый тип';
        return implode(', ', $parts) ?: 'доступен';
    }
}
