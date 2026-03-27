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

class BookingEditTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create(['base_price' => 100000]);
        $room     = Room::factory()->create(['room_type_id' => $roomType->id, 'status' => RoomStatus::Available->value]);
        $guest    = Guest::factory()->create();

        return Booking::factory()->create(array_merge([
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => '2025-01-01',
            'check_out_date' => '2025-01-05',
            'adults'         => 2,
            'children'       => 0,
            'status'         => BookingStatus::Pending->value,
            'total_price'    => 400000,
            'created_by'     => $user->id,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // 1. GET edit returns 200 for pending booking
    // -------------------------------------------------------------------------

    public function test_edit_page_accessible_for_pending_booking(): void
    {
        $booking = $this->makeBooking(['status' => BookingStatus::Pending->value]);

        $response = $this->actingAsReceptionist()->get(route('bookings.edit', $booking));

        $response->assertStatus(200);
        $response->assertViewIs('bookings.edit');
    }

    // -------------------------------------------------------------------------
    // 2. GET edit for checked_in booking → redirect with error
    // -------------------------------------------------------------------------

    public function test_edit_redirects_for_checked_in_booking(): void
    {
        $booking = $this->makeBooking(['status' => BookingStatus::CheckedIn->value]);

        $response = $this->actingAsReceptionist()->get(route('bookings.edit', $booking));

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('error');
    }

    // -------------------------------------------------------------------------
    // 3. PUT valid data updates dates, total_price recalculated
    // -------------------------------------------------------------------------

    public function test_can_update_booking_dates(): void
    {
        $booking = $this->makeBooking([
            'status'         => BookingStatus::Pending->value,
            'check_in_date'  => '2025-01-01',
            'check_out_date' => '2025-01-05',
            'total_price'    => 400000, // 4 nights × 100000
        ]);

        $response = $this->actingAsReceptionist()->put(route('bookings.update', $booking), [
            'check_in_date'  => '2025-02-01',
            'check_out_date' => '2025-02-03', // 2 nights
            'adults'         => 1,
            'children'       => 0,
            'notes'          => 'Updated note',
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals('2025-02-01', $booking->check_in_date->format('Y-m-d'));
        $this->assertEquals('2025-02-03', $booking->check_out_date->format('Y-m-d'));
        // 2 nights × 100000 = 200000
        $this->assertEquals(200000, (float) $booking->total_price);
        $this->assertEquals('Updated note', $booking->notes);
    }

    // -------------------------------------------------------------------------
    // 4. PUT empty data returns validation errors
    // -------------------------------------------------------------------------

    public function test_update_validates_required_fields(): void
    {
        $booking = $this->makeBooking(['status' => BookingStatus::Pending->value]);

        $response = $this->actingAsReceptionist()->put(route('bookings.update', $booking), []);

        $response->assertSessionHasErrors(['check_in_date', 'check_out_date', 'adults']);
    }

    // -------------------------------------------------------------------------
    // 5. PUT dates conflicting with another booking → error
    // -------------------------------------------------------------------------

    public function test_update_rejects_conflicting_dates(): void
    {
        $user     = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $roomType = RoomType::factory()->create(['base_price' => 100000]);
        $room     = Room::factory()->create(['room_type_id' => $roomType->id, 'status' => RoomStatus::Available->value]);
        $guest    = Guest::factory()->create();

        // First booking: Jan 1-5
        $booking1 = Booking::factory()->create([
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => '2025-01-01',
            'check_out_date' => '2025-01-05',
            'status'         => BookingStatus::Pending->value,
            'total_price'    => 400000,
            'created_by'     => $user->id,
        ]);

        // Second booking: Jan 8-12
        Booking::factory()->create([
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'check_in_date'  => '2025-01-08',
            'check_out_date' => '2025-01-12',
            'status'         => BookingStatus::Confirmed->value,
            'total_price'    => 400000,
            'created_by'     => $user->id,
        ]);

        // Try to update booking1 to overlap with booking2 (Jan 7-10)
        $response = $this->actingAs($user)->put(route('bookings.update', $booking1), [
            'check_in_date'  => '2025-01-07',
            'check_out_date' => '2025-01-10',
            'adults'         => 2,
            'children'       => 0,
            'notes'          => '',
        ]);

        $response->assertSessionHasErrors('check_in_date');
    }

    // -------------------------------------------------------------------------
    // 6. PUT same dates for same booking → success (no false conflict)
    // -------------------------------------------------------------------------

    public function test_update_allows_same_dates_for_same_booking(): void
    {
        $booking = $this->makeBooking([
            'status'         => BookingStatus::Pending->value,
            'check_in_date'  => '2025-01-01',
            'check_out_date' => '2025-01-05',
        ]);

        // Update to a subset of its own dates: Jan 2-4
        $response = $this->actingAsReceptionist()->put(route('bookings.update', $booking), [
            'check_in_date'  => '2025-01-02',
            'check_out_date' => '2025-01-04',
            'adults'         => 2,
            'children'       => 0,
            'notes'          => '',
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('success');
    }
}
