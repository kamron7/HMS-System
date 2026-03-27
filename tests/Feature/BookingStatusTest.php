<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStatusTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    private function makeBooking(BookingStatus $status, RoomStatus $roomStatus = RoomStatus::Available): Booking
    {
        $room = Room::factory()->create(['status' => $roomStatus->value]);
        return Booking::factory()->create([
            'room_id' => $room->id,
            'status'  => $status->value,
        ]);
    }

    // 1. pending → confirmed
    public function test_pending_can_transition_to_confirmed(): void
    {
        $booking = $this->makeBooking(BookingStatus::Pending);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'confirmed'])
            ->assertRedirect();

        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    // 2. pending → checked_in, room becomes occupied
    public function test_pending_can_transition_to_checked_in(): void
    {
        $booking = $this->makeBooking(BookingStatus::Pending);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'checked_in'])
            ->assertRedirect();

        $this->assertEquals(BookingStatus::CheckedIn, $booking->fresh()->status);
        $this->assertEquals(RoomStatus::Occupied, $booking->room->fresh()->status);
    }

    // 3. checked_in transition sets room to occupied
    public function test_checked_in_sets_room_to_occupied(): void
    {
        $booking = $this->makeBooking(BookingStatus::Confirmed);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'checked_in'])
            ->assertRedirect();

        $this->assertEquals(RoomStatus::Occupied, $booking->room->fresh()->status);
    }

    // 4. checked_out transition sets room to cleaning
    public function test_checked_out_sets_room_to_cleaning(): void
    {
        $booking = $this->makeBooking(BookingStatus::CheckedIn, RoomStatus::Occupied);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'checked_out'])
            ->assertRedirect();

        $this->assertEquals(BookingStatus::CheckedOut, $booking->fresh()->status);
        $this->assertEquals(RoomStatus::Cleaning, $booking->room->fresh()->status);
    }

    // 5. cancel on occupied room → room becomes available
    public function test_cancelled_sets_occupied_room_to_available(): void
    {
        $booking = $this->makeBooking(BookingStatus::CheckedIn, RoomStatus::Occupied);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'cancelled'])
            ->assertRedirect();

        $this->assertEquals(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertEquals(RoomStatus::Available, $booking->room->fresh()->status);
    }

    // 6. invalid transition (confirmed → pending) is rejected with error flash
    public function test_invalid_transition_rejected(): void
    {
        $booking = $this->makeBooking(BookingStatus::Confirmed);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'pending'])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Status unchanged
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    // 7. checked_out cannot transition to anything
    public function test_checked_out_cannot_transition(): void
    {
        $booking = $this->makeBooking(BookingStatus::CheckedOut);

        $this->actingAsReceptionist()
            ->post("/bookings/{$booking->id}/status", ['transition' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(BookingStatus::CheckedOut, $booking->fresh()->status);
    }

    // 8. unauthenticated user is redirected to login
    public function test_unauthenticated_redirected(): void
    {
        $booking = $this->makeBooking(BookingStatus::Pending);

        $this->post("/bookings/{$booking->id}/status", ['transition' => 'confirmed'])
            ->assertRedirect('/login');
    }
}
