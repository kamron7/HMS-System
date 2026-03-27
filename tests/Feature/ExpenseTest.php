<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsManager(): static
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        return $this->actingAs($user);
    }

    // -------------------------------------------------------------------------
    // 1. Manager can see expenses index (200)
    // -------------------------------------------------------------------------

    public function test_index_accessible_to_manager(): void
    {
        $response = $this->actingAsManager()->get('/expenses');

        $response->assertStatus(200);
        $response->assertViewIs('expenses.index');
    }

    // -------------------------------------------------------------------------
    // 2. Receptionist gets 403
    // -------------------------------------------------------------------------

    public function test_receptionist_cannot_access(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        $response = $this->actingAs($receptionist)->get('/expenses');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // 3. POST valid data creates expense, redirects
    // -------------------------------------------------------------------------

    public function test_can_create_expense(): void
    {
        $response = $this->actingAsManager()->post('/expenses', [
            'category'     => 'salary',
            'description'  => 'Зарплата за март',
            'amount'       => '500000.00',
            'expense_date' => '2026-03-01',
        ]);

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'category'    => 'salary',
            'description' => 'Зарплата за март',
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. POST empty → validation errors
    // -------------------------------------------------------------------------

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsManager()->post('/expenses', []);

        $response->assertSessionHasErrors(['category', 'description', 'amount', 'expense_date']);
    }

    // -------------------------------------------------------------------------
    // 5. POST invalid category → error
    // -------------------------------------------------------------------------

    public function test_category_must_be_valid(): void
    {
        $response = $this->actingAsManager()->post('/expenses', [
            'category'     => 'invalid_category',
            'description'  => 'Тест',
            'amount'       => '1000',
            'expense_date' => '2026-03-01',
        ]);

        $response->assertSessionHasErrors('category');
    }

    // -------------------------------------------------------------------------
    // 6. PUT updates expense, redirects
    // -------------------------------------------------------------------------

    public function test_can_update_expense(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $expense = Expense::factory()->create([
            'category'    => 'other',
            'description' => 'Старое описание',
            'amount'      => '10000.00',
            'expense_date' => '2026-03-01',
            'created_by'  => $manager->id,
        ]);

        $response = $this->actingAs($manager)->put("/expenses/{$expense->id}", [
            'category'     => 'supplies',
            'description'  => 'Новое описание',
            'amount'       => '25000.00',
            'expense_date' => '2026-03-15',
        ]);

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id'          => $expense->id,
            'category'    => 'supplies',
            'description' => 'Новое описание',
        ]);
    }

    // -------------------------------------------------------------------------
    // 7. DELETE removes from DB, redirects
    // -------------------------------------------------------------------------

    public function test_can_delete_expense(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $expense = Expense::factory()->create([
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    // -------------------------------------------------------------------------
    // 8. ?category=salary only shows salary expenses
    // -------------------------------------------------------------------------

    public function test_index_filters_by_category(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);

        Expense::factory()->create([
            'category'    => 'salary',
            'description' => 'Зарплата сотрудникам',
            'created_by'  => $manager->id,
        ]);
        Expense::factory()->create([
            'category'    => 'other',
            'description' => 'Прочие расходы',
            'created_by'  => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get('/expenses?category=salary');

        $response->assertStatus(200);
        $response->assertSee('Зарплата сотрудникам');
        $response->assertDontSee('Прочие расходы');
    }
}
