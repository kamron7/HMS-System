<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'category'     => 'other',
            'description'  => fake()->sentence(),
            'amount'       => fake()->numberBetween(10000, 500000),
            'expense_date' => today(),
            'created_by'   => User::factory(),
        ];
    }
}
