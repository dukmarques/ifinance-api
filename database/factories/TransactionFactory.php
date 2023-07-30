<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $card = Card::inRandomOrder()->first();
        $category = Category::inRandomOrder()->first();

        return [
            'title' => fake()->word(),
            'price' => fake()->randomDigit(),
            'type' => fake()->randomElement(['entry', 'exit']),
            'is_owner' => fake()->boolean(),
            'date' => fake()->date(),
            'pay_month' => fake()->date(),
            'paidOut' => fake()->boolean(),
            'user_id' => $user->id,
            'card_id' => $card->id,
            'category_id' => $category->id
        ];
    }
}
