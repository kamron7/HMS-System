<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsManager(): static
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Authenticated manager can see index page
    // -------------------------------------------------------------------------

    public function test_index_lists_rooms(): void
    {
        Room::factory()->count(3)->create();

        $response = $this->actingAsManager()->get('/rooms');

        $response->assertStatus(200);
        $response->assertViewIs('rooms.index');
        $response->assertViewHas('rooms');
    }

    // -------------------------------------------------------------------------
    // 2. Receptionist gets 403 on index
    // -------------------------------------------------------------------------

    public function test_receptionist_cannot_access(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        $response = $this->actingAs($receptionist)->get('/rooms');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 3. POST with valid data creates room and redirects
    // -------------------------------------------------------------------------

    public function test_can_create_room(): void
    {
        $roomType = RoomType::factory()->create();

        $response = $this->actingAsManager()->post('/rooms', [
            'number'       => '101',
            'room_type_id' => $roomType->id,
            'floor'        => 1,
            'status'       => RoomStatus::Available->value,
            'notes'        => null,
        ]);

        $response->assertRedirect('/rooms');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'number'       => '101',
            'room_type_id' => $roomType->id,
            'floor'        => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. POST with empty data returns validation errors
    // -------------------------------------------------------------------------

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsManager()->post('/rooms', []);

        $response->assertSessionHasErrors(['number', 'room_type_id', 'status']);
    }

    // -------------------------------------------------------------------------
    // 5. Duplicate room number fails validation
    // -------------------------------------------------------------------------

    public function test_room_number_must_be_unique(): void
    {
        Room::factory()->create(['number' => '101']);

        $roomType = RoomType::factory()->create();

        $response = $this->actingAsManager()->post('/rooms', [
            'number'       => '101',
            'room_type_id' => $roomType->id,
            'floor'        => 1,
            'status'       => RoomStatus::Available->value,
        ]);

        $response->assertSessionHasErrors('number');
    }

    // -------------------------------------------------------------------------
    // 6. PUT updates room and redirects
    // -------------------------------------------------------------------------

    public function test_can_update_room(): void
    {
        $room = Room::factory()->create([
            'number' => '201',
            'floor'  => 2,
            'status' => RoomStatus::Available->value,
        ]);

        $response = $this->actingAsManager()->put("/rooms/{$room->id}", [
            'number'       => '202',
            'room_type_id' => $room->room_type_id,
            'floor'        => 2,
            'status'       => RoomStatus::Maintenance->value,
            'notes'        => 'Ремонт санузла',
        ]);

        $response->assertRedirect('/rooms');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id'     => $room->id,
            'number' => '202',
            'status' => RoomStatus::Maintenance->value,
            'notes'  => 'Ремонт санузла',
        ]);
    }

    // -------------------------------------------------------------------------
    // 7. PUT with the same room number doesn't fail unique validation
    // -------------------------------------------------------------------------

    public function test_update_allows_same_number(): void
    {
        $room = Room::factory()->create([
            'number' => '301',
            'floor'  => 3,
            'status' => RoomStatus::Available->value,
        ]);

        $response = $this->actingAsManager()->put("/rooms/{$room->id}", [
            'number'       => '301',
            'room_type_id' => $room->room_type_id,
            'floor'        => 3,
            'status'       => RoomStatus::Cleaning->value,
        ]);

        $response->assertRedirect('/rooms');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id'     => $room->id,
            'number' => '301',
            'status' => RoomStatus::Cleaning->value,
        ]);
    }
}
