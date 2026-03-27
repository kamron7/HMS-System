<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCreateTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. GET /bookings/create returns 200
    // -------------------------------------------------------------------------

    public function test_create_page_accessible(): void
    {
        $response = $this->actingAsReceptionist()->get('/bookings/create');

        $response->assertStatus(200);
        $response->assertViewIs('bookings.create');
    }

    // -------------------------------------------------------------------------
    // 2. Valid POST creates booking and redirects to show
    // -------------------------------------------------------------------------

    public function test_store_creates_booking(): void
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create(['base_price' => 100000]);
        $room     = Room::factory()->create(['room_type_id' => $roomType->id, 'status' => RoomStatus::Available->value]);
        $guest    = Guest::factory()->create();

        $checkIn  = today()->addDays(1)->format('Y-m-d');
        $checkOut = today()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($user)->post('/bookings', [
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'adults'         => 2,
            'children'       => 0,
            'notes'          => 'Test note',
        ]);

        $booking = Booking::where('room_id', $room->id)->where('guest_id', $guest->id)->first();

        $this->assertNotNull($booking);
        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // 3. total_price = nights × base_price stored correctly
    // -------------------------------------------------------------------------

    public function test_store_calculates_total_price(): void
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create(['base_price' => 50000]);
        $room     = Room::factory()->create(['room_type_id' => $roomType->id, 'status' => RoomStatus::Available->value]);
        $guest    = Guest::factory()->create();

        $checkIn  = today()->addDays(1)->format('Y-m-d');
        $checkOut = today()->addDays(4)->format('Y-m-d'); // 3 nights

        $this->actingAs($user)->post('/bookings', [
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'adults'         => 1,
            'children'       => 0,
        ]);

        $booking = Booking::where('room_id', $room->id)->where('guest_id', $guest->id)->first();

        $this->assertNotNull($booking);
        // 3 nights × 50000 = 150000
        $this->assertEquals(150000, (float) $booking->total_price);
    }

    // -------------------------------------------------------------------------
    // 4. Empty POST returns validation errors
    // -------------------------------------------------------------------------

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsReceptionist()->post('/bookings', []);

        $response->assertSessionHasErrors(['room_id', 'guest_id', 'check_in_date', 'check_out_date', 'adults']);
    }

    // -------------------------------------------------------------------------
    // 5. POST for already-booked room returns error
    // -------------------------------------------------------------------------

    public function test_store_rejects_unavailable_room(): void
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create(['base_price' => 100000]);
        $room     = Room::factory()->create(['room_type_id' => $roomType->id, 'status' => RoomStatus::Available->value]);
        $guest    = Guest::factory()->create();

        $checkIn  = today()->addDays(2)->format('Y-m-d');
        $checkOut = today()->addDays(5)->format('Y-m-d');

        // Create an existing booking that occupies the room for those dates
        Booking::factory()->create([
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'status'         => BookingStatus::Confirmed->value,
            'created_by'     => $user->id,
        ]);

        $anotherGuest = Guest::factory()->create();

        $response = $this->actingAs($user)->post('/bookings', [
            'room_id'        => $room->id,
            'guest_id'       => $anotherGuest->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'adults'         => 1,
            'children'       => 0,
        ]);

        $response->assertSessionHasErrors('room_id');
    }

    // -------------------------------------------------------------------------
    // 6. check_out must be after check_in
    // -------------------------------------------------------------------------

    public function test_store_check_out_must_be_after_check_in(): void
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create();
        $room     = Room::factory()->create(['room_type_id' => $roomType->id]);
        $guest    = Guest::factory()->create();

        $checkIn  = today()->addDays(3)->format('Y-m-d');
        $checkOut = today()->addDays(1)->format('Y-m-d'); // before check-in

        $response = $this->actingAs($user)->post('/bookings', [
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'adults'         => 1,
            'children'       => 0,
        ]);

        $response->assertSessionHasErrors('check_out_date');
    }
}
