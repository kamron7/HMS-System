<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsManager(): static
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Owner can access finances page (200)
    // -------------------------------------------------------------------------

    public function test_finances_page_accessible_to_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $response = $this->actingAs($owner)->get('/finances');

        $response->assertStatus(200);
        $response->assertViewIs('finances.index');
    }

    // -------------------------------------------------------------------------
    // 2. Receptionist gets 403
    // -------------------------------------------------------------------------

    public function test_finances_page_blocked_for_receptionist(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        $response = $this->actingAs($receptionist)->get('/finances');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 3. Revenue: payment paid_at today → appears in revenue
    // -------------------------------------------------------------------------

    public function test_revenue_calculation(): void
    {
        $payment = Payment::factory()->create([
            'amount'  => 100000,
            'paid_at' => now(),
        ]);

        $response = $this->actingAsManager()->get('/finances');

        $response->assertStatus(200);
        $response->assertViewHas('revenue', fn($revenue) => (float) $revenue >= 100000);
    }

    // -------------------------------------------------------------------------
    // 4. Expenses: expense today → appears in expenses total
    // -------------------------------------------------------------------------

    public function test_expenses_calculation(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);

        Expense::factory()->create([
            'amount'       => 50000,
            'expense_date' => today(),
            'created_by'   => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get('/finances');

        $response->assertStatus(200);
        $response->assertViewHas('expenses', fn($expenses) => (float) $expenses >= 50000);
    }

    // -------------------------------------------------------------------------
    // 5. Profit = revenue - expenses
    // -------------------------------------------------------------------------

    public function test_profit_is_revenue_minus_expenses(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);

        Payment::factory()->create([
            'amount'  => 200000,
            'paid_at' => now(),
        ]);

        Expense::factory()->create([
            'amount'       => 80000,
            'expense_date' => today(),
            'created_by'   => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get('/finances');

        $response->assertStatus(200);
        $response->assertViewHas('profit', fn($profit) => (float) $profit === 200000.0 - 80000.0);
    }

    // -------------------------------------------------------------------------
    // 6. Period filter: payment from last year NOT included in current month
    // -------------------------------------------------------------------------

    public function test_period_filter_excludes_old_data(): void
    {
        // Payment from last year
        Payment::factory()->create([
            'amount'  => 999999,
            'paid_at' => now()->subYear(),
        ]);

        $response = $this->actingAsManager()->get('/finances');

        $response->assertStatus(200);
        // Revenue for current month should NOT include last year's payment
        $response->assertViewHas('revenue', fn($revenue) => (float) $revenue < 999999);
    }
}
