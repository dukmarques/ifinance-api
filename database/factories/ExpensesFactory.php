<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Category;
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
        $card = Card::query()->inRandomOrder()->first();
        $category = Category::query()->inRandomOrder()->first();

        return [
            'title' => fake()->word(),
            'type' => fake()->randomElement(['simple', 'recurrent', 'installments']),
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => true,
            'paid' => fake()->boolean(),
            'payment_month' => Carbon::now()->toDateString(),
            'deprecated_date' => null,
            'description' => fake()->text(300),
            'user_id' => $user->id || null,
            'card_id' => $card->id || null,
            'category_id' => $category->id || null,
        ];
    }
}
