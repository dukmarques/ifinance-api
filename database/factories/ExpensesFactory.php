<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ExpenseAssignees;
use App\Models\Expenses;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expenses>
 */
class ExpensesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::query()->inRandomOrder()->first();
        $category = Category::query()->inRandomOrder()->first();
        $assignee = ExpenseAssignees::query()->inRandomOrder()->first();

        return [
            'title' => fake()->word(),
            'type' => fake()->randomElement(Expenses::$expenseTypes),
            'amount' => fake()->randomNumber(5, true),
            'is_owner' => true,
            'assignee_id' => $assignee?->id ?: null,
            'paid' => fake()->boolean(),
            'payment_month' => Carbon::now()->toDateString(),
            'deprecated_date' => null,
            'description' => fake()->text(300),
            'user_id' => $user->id,
            'category_id' => $category?->id ?: null,
        ];
    }
}
