<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardExpenses>
 */
class CardExpensesFactory extends Factory
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
            'total_amount' => fake()->randomNumber(5, true),
            'is_owner' => true,
            'user_id' => $user->id,
            'card_id' => $card?->id ?: null,
            'category_id' => $category?->id ?: null,
        ];
    }
}
