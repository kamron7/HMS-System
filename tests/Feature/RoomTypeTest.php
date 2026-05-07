<?php

namespace Tests\Feature;

use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeTest extends TestCase
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

    public function test_index_lists_room_types(): void
    {
        RoomType::factory()->count(3)->create();

        $response = $this->actingAsManager()->get('/room-types');

        $response->assertStatus(200);
        $response->assertViewIs('room-types.index');
        $response->assertViewHas('roomTypes');
    }

    // -------------------------------------------------------------------------
    // 2. Receptionist gets 403 on index
    // -------------------------------------------------------------------------

    public function test_receptionist_cannot_access(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        $response = $this->actingAs($receptionist)->get('/room-types');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 3. POST with valid data creates record and redirects
    // -------------------------------------------------------------------------

    public function test_can_create_room_type(): void
    {
        $response = $this->actingAsManager()->post('/room-types', [
            'name'        => 'Люкс',
            'base_price'  => 150000,
            'capacity'    => 2,
            'description' => 'Просторный номер',
            'amenities'   => 'Wi-Fi, ТВ, Сейф',
        ]);

        $response->assertRedirect('/room-types');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_types', [
            'name'       => 'Люкс',
            'capacity'   => 2,
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. POST with empty data returns validation errors
    // -------------------------------------------------------------------------

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsManager()->post('/room-types', []);

        $response->assertSessionHasErrors(['name', 'base_price', 'capacity']);
    }

    // -------------------------------------------------------------------------
    // 5. PUT updates record and redirects
    // -------------------------------------------------------------------------

    public function test_can_update_room_type(): void
    {
        $roomType = RoomType::factory()->create([
            'name'       => 'Стандарт',
            'base_price' => 80000,
            'capacity'   => 2,
        ]);

        $response = $this->actingAsManager()->put("/room-types/{$roomType->id}", [
            'name'        => 'Стандарт Плюс',
            'base_price'  => 90000,
            'capacity'    => 3,
            'description' => null,
            'amenities'   => '',
        ]);

        $response->assertRedirect('/room-types');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_types', [
            'id'       => $roomType->id,
            'name'     => 'Стандарт Плюс',
            'capacity' => 3,
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. Amenities stored as JSON array
    // -------------------------------------------------------------------------

    public function test_amenities_stored_as_json_array(): void
    {
        $this->actingAsManager()->post('/room-types', [
            'name'       => 'Делюкс',
            'base_price' => 120000,
            'capacity'   => 2,
            'amenities'  => 'Wi-Fi, TV',
        ]);

        $roomType = RoomType::where('name', 'Делюкс')->first();

        $this->assertNotNull($roomType);
        $this->assertEquals(['Wi-Fi', 'TV'], $roomType->amenities);
    }
}
