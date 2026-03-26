<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_available_returns_true_when_no_overlapping_bookings(): void
    {
        $room = Room::factory()->create();

        $this->assertTrue($room->isAvailable('2026-03-26', '2026-03-28'));
    }

    public function test_is_available_returns_false_when_booking_overlaps_check_in(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id'        => $room->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-27',
            'status'         => 'confirmed',
        ]);

        // Our requested period overlaps — check-in is before check-out of existing
        $this->assertFalse($room->isAvailable('2026-03-26', '2026-03-28'));
    }

    public function test_is_available_returns_false_when_booking_overlaps_check_out(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id'        => $room->id,
            'check_in_date'  => '2026-03-27',
            'check_out_date' => '2026-03-29',
            'status'         => 'confirmed',
        ]);

        // Our requested period overlaps — check-out is after check-in of existing
        $this->assertFalse($room->isAvailable('2026-03-26', '2026-03-28'));
    }

    public function test_is_available_returns_true_when_cancelled_booking_overlaps(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id'        => $room->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-27',
            'status'         => 'cancelled',
        ]);

        $this->assertTrue($room->isAvailable('2026-03-26', '2026-03-28'));
    }

    public function test_is_available_returns_true_when_checked_out_booking_overlaps(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id'        => $room->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-27',
            'status'         => 'checked_out',
        ]);

        $this->assertTrue($room->isAvailable('2026-03-26', '2026-03-28'));
    }
}
