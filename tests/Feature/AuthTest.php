<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // 1. Unauthenticated redirect
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // 2. Successful login
    // -------------------------------------------------------------------------

    public function test_user_with_correct_credentials_is_redirected_to_dashboard(): void
    {
        // We need a minimal dashboard route for the redirect target to resolve;
        // register one inline so we don't depend on a real DashboardController.
        Route::get('/dashboard', fn() => 'ok')->middleware('web');

        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => bcrypt('secret123'),
            'is_active' => true,
            'role'      => UserRole::Receptionist->value,
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // 3. Wrong credentials
    // -------------------------------------------------------------------------

    public function test_user_with_wrong_credentials_gets_validation_error(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // 4. Inactive user is blocked even with correct credentials
    // -------------------------------------------------------------------------

    public function test_inactive_user_with_correct_credentials_is_blocked(): void
    {
        $user = User::factory()->create([
            'email'     => 'inactive@example.com',
            'password'  => bcrypt('secret123'),
            'is_active' => false,
            'role'      => UserRole::Receptionist->value,
        ]);

        $response = $this->post('/login', [
            'email'    => 'inactive@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // 5. Manager visiting /users returns 403
    // -------------------------------------------------------------------------

    public function test_manager_visiting_users_gets_403(): void
    {
        $manager = User::factory()->create([
            'role'      => UserRole::Manager->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->get('/users');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 6. Receptionist visiting /finances returns 403
    // -------------------------------------------------------------------------

    public function test_receptionist_visiting_finances_gets_403(): void
    {
        $receptionist = User::factory()->create([
            'role'      => UserRole::Receptionist->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($receptionist)->get('/finances');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 7. Owner visiting /users returns 200
    // -------------------------------------------------------------------------

    public function test_owner_visiting_users_gets_200(): void
    {
        // Register a stub route so the test doesn't fail due to missing controller.
        // This works because Route::get called here runs after the application routes
        // have been loaded — Laravel will prefer the first matching route, so we
        // temporarily override with a closure that returns 200 for this test.
        // A cleaner approach: bind the route before the test's request is dispatched.
        Route::get('/users', fn() => response('stub', 200))
            ->middleware(['web', 'auth', 'role:owner']);

        $owner = User::factory()->create([
            'role'      => UserRole::Owner->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/users');

        $response->assertStatus(200);
    }
}
