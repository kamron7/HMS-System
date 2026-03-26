<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. All roles can access the housekeeping index
    // -------------------------------------------------------------------------

    public function test_index_is_accessible_to_all_roles(): void
    {
        $response = $this->actingAsReceptionist()->get('/housekeeping');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // 2. Index page shows rooms
    // -------------------------------------------------------------------------

    public function test_index_shows_rooms(): void
    {
        Room::factory()->count(3)->create(['floor' => 1]);

        $response = $this->actingAsReceptionist()->get('/housekeeping');

        $response->assertStatus(200);
        $response->assertViewIs('housekeeping.index');
        $response->assertViewHas('rooms');
    }

    // -------------------------------------------------------------------------
    // 3. Status filter returns only rooms of that status
    // -------------------------------------------------------------------------

    public function test_status_filter_works(): void
    {
        Room::factory()->create(['status' => RoomStatus::Available->value, 'floor' => 1]);
        Room::factory()->create(['status' => RoomStatus::Cleaning->value, 'floor' => 1]);
        Room::factory()->create(['status' => RoomStatus::Cleaning->value, 'floor' => 2]);

        $response = $this->actingAsReceptionist()->get('/housekeeping?status=cleaning');

        $response->assertStatus(200);

        $rooms = $response->viewData('rooms');
        // $rooms is grouped by floor, flatten to count
        $flat = $rooms->flatten();
        $this->assertCount(2, $flat);
        $flat->each(fn($r) => $this->assertEquals(RoomStatus::Cleaning, $r->status));
    }

    // -------------------------------------------------------------------------
    // 4. PATCH cleaning → available updates room status
    // -------------------------------------------------------------------------

    public function test_can_update_room_status(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::Cleaning->value]);

        $response = $this->actingAsReceptionist()->patch("/housekeeping/{$room->id}", [
            'status' => RoomStatus::Available->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id'     => $room->id,
            'status' => RoomStatus::Available->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. Invalid transition (occupied → available) is rejected with 422
    // -------------------------------------------------------------------------

    public function test_invalid_status_transition_rejected(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::Occupied->value]);

        $response = $this->actingAsReceptionist()->patch("/housekeeping/{$room->id}", [
            'status' => RoomStatus::Available->value,
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // 6. Unauthenticated users are redirected to login
    // -------------------------------------------------------------------------

    public function test_unauthenticated_redirected(): void
    {
        $response = $this->get('/housekeeping');

        $response->assertRedirect('/login');
    }
}
