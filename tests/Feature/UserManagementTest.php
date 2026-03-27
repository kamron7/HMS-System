<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function actingAsOwner(): static
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        return $this->actingAs($owner);
    }

    private function ownerUser(): User
    {
        return User::factory()->create(['role' => 'owner', 'is_active' => true]);
    }

    // -------------------------------------------------------------------------
    // 1. Index accessible to owner
    // -------------------------------------------------------------------------

    public function test_index_accessible_to_owner(): void
    {
        $response = $this->actingAsOwner()->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }

    // -------------------------------------------------------------------------
    // 2. Manager cannot access
    // -------------------------------------------------------------------------

    public function test_manager_cannot_access(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);

        $response = $this->actingAs($manager)->get('/users');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 3. Can create user
    // -------------------------------------------------------------------------

    public function test_can_create_user(): void
    {
        $response = $this->actingAsOwner()->post('/users', [
            'name'                  => 'Новый Сотрудник',
            'email'                 => 'newstaff@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'receptionist',
        ]);

        $this->assertDatabaseHas('users', [
            'name'      => 'Новый Сотрудник',
            'email'     => 'newstaff@example.com',
            'role'      => 'receptionist',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // 4. Store validates password confirmation
    // -------------------------------------------------------------------------

    public function test_store_validates_password_confirmation(): void
    {
        $response = $this->actingAsOwner()->post('/users', [
            'name'                  => 'Тест',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different999',
            'role'                  => 'receptionist',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // -------------------------------------------------------------------------
    // 5. Can update user
    // -------------------------------------------------------------------------

    public function test_can_update_user(): void
    {
        $user = User::factory()->create([
            'name'  => 'Старое Имя',
            'email' => 'old@example.com',
            'role'  => 'receptionist',
        ]);

        $response = $this->actingAsOwner()->put("/users/{$user->id}", [
            'name'  => 'Новое Имя',
            'email' => 'new@example.com',
            'role'  => 'manager',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Новое Имя',
            'email' => 'new@example.com',
            'role'  => 'manager',
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. Update without password keeps existing hash
    // -------------------------------------------------------------------------

    public function test_update_without_password_keeps_existing(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('original_password'),
            'role'     => 'receptionist',
        ]);

        $originalHash = $user->fresh()->password;

        $this->actingAsOwner()->put("/users/{$user->id}", [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => 'receptionist',
            // no password fields
        ]);

        $this->assertEquals($originalHash, $user->fresh()->password);
    }

    // -------------------------------------------------------------------------
    // 7. Can toggle user active
    // -------------------------------------------------------------------------

    public function test_can_toggle_user_active(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAsOwner()->patch("/users/{$user->id}/toggle-active");

        $this->assertFalse($user->fresh()->is_active);

        $this->actingAsOwner()->patch("/users/{$user->id}/toggle-active");

        $this->assertTrue($user->fresh()->is_active);
    }

    // -------------------------------------------------------------------------
    // 8. Cannot deactivate self
    // -------------------------------------------------------------------------

    public function test_cannot_deactivate_self(): void
    {
        $owner = $this->ownerUser();

        $response = $this->actingAs($owner)->patch("/users/{$owner->id}/toggle-active");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Owner should still be active
        $this->assertTrue($owner->fresh()->is_active);
    }
}
