<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoomAvailabilityService
{
    /**
     * Check if a specific room is available, optionally excluding a booking.
     */
    public function isAvailable(Room $room, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        $query = $room->bookings()
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Pending / confirmed / inquiry: normal date-overlap check
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->whereIn('status', [
                            BookingStatus::Pending->value,
                            BookingStatus::Confirmed->value,
                            BookingStatus::Inquiry->value,
                        ])
                        ->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                // checked_in: guest is physically present.
                // Block if dates overlap OR checkout date has already passed (overdue stay).
                ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                    $today = now()->toDateString();
                    $q2->where('status', BookingStatus::CheckedIn->value)
                        ->where('check_in_date', '<', $checkOut)
                        ->where(function ($q3) use ($checkIn, $today) {
                            $q3->where('check_out_date', '>', $checkIn)
                               ->orWhere('check_out_date', '<=', $today);
                        });
                });
            });

        return ! $query->exists();
    }

    /**
     * Get all available rooms for a given room type and date range.
     * Uses lockForUpdate() when called inside a DB::transaction().
     */
    public function availableRooms(RoomType $roomType, string $checkIn, string $checkOut, ?int $excludeBookingId = null): Collection
    {
        $conflictingRoomIds = DB::table('bookings')
            ->whereNotIn('status', [
                BookingStatus::Cancelled->value,
                BookingStatus::CheckedOut->value,
                BookingStatus::NoShow->value,
            ])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->pluck('room_id');

        return Room::where('room_type_id', $roomType->id)
            ->whereNotIn('id', $conflictingRoomIds)
            ->whereNotIn('status', [RoomStatus::Maintenance->value, RoomStatus::Cleaning->value])
            ->get();
    }

    /**
     * Check availability with pessimistic lock (must be inside DB::transaction).
     */
    public function isAvailableLocked(Room $room, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        $today = now()->toDateString();

        $query = $room->bookings()
            ->lockForUpdate()
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($q) use ($checkIn, $checkOut, $today) {
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->whereIn('status', [
                            BookingStatus::Pending->value,
                            BookingStatus::Confirmed->value,
                            BookingStatus::Inquiry->value,
                        ])
                        ->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                ->orWhere(function ($q2) use ($checkIn, $checkOut, $today) {
                    $q2->where('status', BookingStatus::CheckedIn->value)
                        ->where('check_in_date', '<', $checkOut)
                        ->where(function ($q3) use ($checkIn, $today) {
                            $q3->where('check_out_date', '>', $checkIn)
                               ->orWhere('check_out_date', '<=', $today);
                        });
                });
            });

        return ! $query->exists();
    }
}
