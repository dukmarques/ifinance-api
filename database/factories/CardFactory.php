<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();

        return [
            'name' => fake()->unique()->creditCardType(),
            'closing_day' => fake()->numberBetween(1, 31),
            'due_day' => fake()->numberBetween(1, 31),
            'user_id' => $user->id,
            'limit' => fake()->numberBetween(5000, 100000000),
            'background_color' => fake()->hexColor(),
            'card_flag' => fake()->creditCardType(),
        ];
    }
}
