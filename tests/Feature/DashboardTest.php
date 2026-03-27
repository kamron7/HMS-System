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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Dashboard is accessible to all roles
    // -------------------------------------------------------------------------

    public function test_dashboard_accessible_to_all_roles(): void
    {
        $response = $this->actingAsReceptionist()->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    // -------------------------------------------------------------------------
    // 2. Dashboard shows room stats
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_room_stats(): void
    {
        $roomType = RoomType::factory()->create();

        Room::factory()->count(3)->create(['status' => RoomStatus::Available->value, 'room_type_id' => $roomType->id]);
        Room::factory()->count(2)->create(['status' => RoomStatus::Occupied->value, 'room_type_id' => $roomType->id]);
        Room::factory()->count(1)->create(['status' => RoomStatus::Cleaning->value, 'room_type_id' => $roomType->id]);

        $response = $this->actingAsReceptionist()->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('3'); // available count
        $response->assertSee('2'); // occupied count
        $response->assertSee('6'); // total
    }

    // -------------------------------------------------------------------------
    // 3. Dashboard shows today's check-ins
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_todays_checkins(): void
    {
        $guest   = Guest::factory()->create(['first_name' => 'Иван', 'last_name' => 'Петров']);
        $room    = Room::factory()->create(['number' => '101']);
        $creator = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        Booking::factory()->create([
            'guest_id'       => $guest->id,
            'room_id'        => $room->id,
            'check_in_date'  => today(),
            'check_out_date' => today()->addDay(),
            'status'         => BookingStatus::Confirmed->value,
            'created_by'     => $creator->id,
        ]);

        $response = $this->actingAsReceptionist()->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Иван');
        $response->assertSee('101');
    }

    // -------------------------------------------------------------------------
    // 4. Dashboard shows pending bookings
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_pending_bookings(): void
    {
        $guest   = Guest::factory()->create(['first_name' => 'Мария', 'last_name' => 'Сидорова']);
        $room    = Room::factory()->create(['number' => '202']);
        $creator = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        Booking::factory()->create([
            'guest_id'       => $guest->id,
            'room_id'        => $room->id,
            'check_in_date'  => today()->addDay(),
            'check_out_date' => today()->addDays(3),
            'status'         => BookingStatus::Pending->value,
            'created_by'     => $creator->id,
        ]);

        $response = $this->actingAsReceptionist()->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Мария');
        $response->assertSee('Ожидают подтверждения');
    }

    // -------------------------------------------------------------------------
    // 5. Unauthenticated users are redirected to login
    // -------------------------------------------------------------------------

    public function test_unauthenticated_redirected(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
