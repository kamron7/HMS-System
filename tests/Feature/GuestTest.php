<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsReceptionist(): static
    {
        $user = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Receptionist can access guests index (200)
    // -------------------------------------------------------------------------

    public function test_index_accessible_to_all_roles(): void
    {
        $response = $this->actingAsReceptionist()->get('/guests');

        $response->assertStatus(200);
        $response->assertViewIs('guests.index');
    }

    // -------------------------------------------------------------------------
    // 2. ?q=Ivan filters by name
    // -------------------------------------------------------------------------

    public function test_index_search_filters_by_name(): void
    {
        Guest::factory()->create(['first_name' => 'Ivan', 'last_name' => 'Petrov']);
        Guest::factory()->create(['first_name' => 'Maria', 'last_name' => 'Sidorova']);

        $response = $this->actingAsReceptionist()->get('/guests?q=Ivan');

        $response->assertStatus(200);
        $response->assertSee('Ivan');
        $response->assertDontSee('Maria');
    }

    // -------------------------------------------------------------------------
    // 3. GET /guests/search?q=... returns JSON with id/full_name/phone
    // -------------------------------------------------------------------------

    public function test_search_endpoint_returns_json(): void
    {
        Guest::factory()->create([
            'first_name' => 'Ivan',
            'last_name'  => 'Petrov',
            'phone'      => '+998901234567',
        ]);

        $response = $this->actingAsReceptionist()->getJson('/guests/search?q=Ivan');

        $response->assertStatus(200);
        $response->assertJsonStructure([['id', 'full_name', 'phone']]);
        $response->assertJsonFragment([
            'full_name' => 'Ivan Petrov',
            'phone'     => '+998901234567',
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. GET /guests/search (no q) returns []
    // -------------------------------------------------------------------------

    public function test_search_returns_empty_for_no_query(): void
    {
        Guest::factory()->count(3)->create();

        $response = $this->actingAsReceptionist()->getJson('/guests/search');

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    // -------------------------------------------------------------------------
    // 5. POST creates guest, redirects to show
    // -------------------------------------------------------------------------

    public function test_can_create_guest(): void
    {
        $response = $this->actingAsReceptionist()->post('/guests', [
            'first_name'      => 'Алексей',
            'last_name'       => 'Иванов',
            'phone'           => '+998901112233',
            'email'           => 'aleksey@example.com',
            'passport_number' => 'AA1234567',
            'nationality'     => 'Uzbekistan',
        ]);

        $this->assertDatabaseHas('guests', [
            'first_name' => 'Алексей',
            'last_name'  => 'Иванов',
            'phone'      => '+998901112233',
        ]);

        $guest = Guest::where('first_name', 'Алексей')->firstOrFail();
        $response->assertRedirect(route('guests.show', $guest));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // 6. POST empty returns errors on first_name and last_name
    // -------------------------------------------------------------------------

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsReceptionist()->post('/guests', []);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
    }

    // -------------------------------------------------------------------------
    // 7. PUT updates guest, redirects to show
    // -------------------------------------------------------------------------

    public function test_can_update_guest(): void
    {
        $guest = Guest::factory()->create([
            'first_name' => 'Старое',
            'last_name'  => 'Имя',
        ]);

        $response = $this->actingAsReceptionist()->put("/guests/{$guest->id}", [
            'first_name' => 'Новое',
            'last_name'  => 'Имя',
            'phone'      => '+998900000000',
        ]);

        $response->assertRedirect(route('guests.show', $guest));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'id'         => $guest->id,
            'first_name' => 'Новое',
        ]);
    }

    // -------------------------------------------------------------------------
    // 8. Show page displays guest info
    // -------------------------------------------------------------------------

    public function test_show_displays_guest_info(): void
    {
        $guest = Guest::factory()->create([
            'first_name' => 'Тест',
            'last_name'  => 'Гость',
        ]);

        $response = $this->actingAsReceptionist()->get("/guests/{$guest->id}");

        $response->assertStatus(200);
        $response->assertSee('Тест');
        $response->assertSee('Гость');
    }
}
