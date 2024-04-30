<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RevenuesOverrides>
 */
class RevenuesOverridesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'amount' => fake()->randomDigit(),
            'receiving_date' => Carbon::now(),
            'description' => fake()->text(300),
        ];
    }
}
