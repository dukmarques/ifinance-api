<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenues>
 */
class RevenuesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $category = Category::inRandomOrder()->first();

        return [
            'title' => fake()->word(),
            'amount' => fake()->randomNumber(5, true),
            'receiving_date' => Carbon::now(),
            'recurrent' => fake()->boolean(),
            'description' => fake()->text(300),
            'deprecated_date' => null,
            'user_id' => $user->id,
            'category_id' => $category->id ?? null,
        ];
    }
}
