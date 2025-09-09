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
        $assignee = ExpenseAssignees::query()->inRandomOrder()->first()
            ?: ExpenseAssignees::factory()->create(['user_id' => $user->id]);
        $isOwner = fake()->boolean();

        return [
            'title' => fake()->word(),
            'recurrent' => fake()->boolean(),
            'amount' => fake()->randomNumber(5, true),
            'is_owner' => $isOwner,
            'assignee_id' => $isOwner ? $assignee->id : null,
            'paid' => fake()->boolean(),
            'payment_month' => Carbon::now()->toDateString(),
            'deprecated_date' => null,
            'description' => fake()->text(300),
            'user_id' => $user->id,
            'category_id' => $category?->id ?: null,
        ];
    }
}
