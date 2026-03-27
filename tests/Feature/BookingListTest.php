<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingListTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Receptionist can see bookings list (200)
    // -------------------------------------------------------------------------

    public function test_index_accessible_to_receptionist(): void
    {
        $response = $this->actingAsReceptionist()->get('/bookings');

        $response->assertStatus(200);
        $response->assertViewIs('bookings.index');
    }

    // -------------------------------------------------------------------------
    // 2. ?status=pending only shows pending bookings
    // -------------------------------------------------------------------------

    public function test_index_filters_by_status(): void
    {
        $guest = Guest::factory()->create();
        $room  = Room::factory()->create();
        $user  = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        Booking::factory()->create([
            'guest_id'   => $guest->id,
            'room_id'    => $room->id,
            'created_by' => $user->id,
            'status'     => BookingStatus::Pending->value,
        ]);

        Booking::factory()->create([
            'guest_id'       => $guest->id,
            'room_id'        => $room->id,
            'created_by'     => $user->id,
            'status'         => BookingStatus::Confirmed->value,
            'check_in_date'  => today()->addDays(2),
            'check_out_date' => today()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get('/bookings?status=pending');

        $response->assertStatus(200);

        $bookings = $response->viewData('bookings');
        $this->assertTrue($bookings->every(fn($b) => $b->status === BookingStatus::Pending));
        $this->assertGreaterThan(0, $bookings->count());
    }

    // -------------------------------------------------------------------------
    // 3. ?search=Ivan returns bookings for that guest
    // -------------------------------------------------------------------------

    public function test_index_filters_by_search(): void
    {
        $ivan  = Guest::factory()->create(['first_name' => 'Ivan', 'last_name' => 'Petrov']);
        $maria = Guest::factory()->create(['first_name' => 'Maria', 'last_name' => 'Sidorova']);
        $room  = Room::factory()->create();
        $user  = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        Booking::factory()->create([
            'guest_id'   => $ivan->id,
            'room_id'    => $room->id,
            'created_by' => $user->id,
        ]);

        Booking::factory()->create([
            'guest_id'       => $maria->id,
            'room_id'        => $room->id,
            'created_by'     => $user->id,
            'check_in_date'  => today()->addDays(2),
            'check_out_date' => today()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get('/bookings?search=Ivan');

        $response->assertStatus(200);

        $bookings = $response->viewData('bookings');
        $this->assertGreaterThan(0, $bookings->count());
        $items = $bookings->items();
        $this->assertTrue(
            collect($items)->every(fn($b) => str_contains($b->guest->first_name, 'Ivan') || str_contains($b->guest->last_name, 'Ivan'))
        );
    }

    // -------------------------------------------------------------------------
    // 4. Show page returns 200 with correct view
    // -------------------------------------------------------------------------

    public function test_show_displays_booking_detail(): void
    {
        $user    = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $booking = Booking::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->get("/bookings/{$booking->id}");

        $response->assertStatus(200);
        $response->assertViewIs('bookings.show');
        $response->assertViewHas('booking');
    }

    // -------------------------------------------------------------------------
    // 5. Show page shows payment status text
    // -------------------------------------------------------------------------

    public function test_show_displays_payment_status(): void
    {
        $user    = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        $booking = Booking::factory()->create([
            'created_by'  => $user->id,
            'total_price' => 100000,
        ]);

        // No payments — status should be 'unpaid'
        $response = $this->actingAs($user)->get("/bookings/{$booking->id}");

        $response->assertStatus(200);
        $response->assertSee('Не оплачено');
    }

    // -------------------------------------------------------------------------
    // 6. Unauthenticated user is redirected to login
    // -------------------------------------------------------------------------

    public function test_unauthenticated_redirected(): void
    {
        $booking = Booking::factory()->create();

        $this->get('/bookings')->assertRedirect('/login');
        $this->get("/bookings/{$booking->id}")->assertRedirect('/login');
    }
}
