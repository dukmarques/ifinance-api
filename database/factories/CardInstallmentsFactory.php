<?php

namespace Database\Factories;

use App\Models\CardExpenses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardInstallments>
 */
class CardInstallmentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cardExpense = CardExpenses::query()->inRandomOrder()->first();

        return [
            'title' => fake()->text(100),
            'amount' => 0,
            'paid' => false,
            'installment_number' => 0,
            'payment_month' => Carbon::now(),
            'notes' => null,
            'card_expenses_id' => $cardExpense->id,
        ];
    }

    public function withAmountAndPaymentMonth(int $amount, int $installmentNumber, string $paymentMonth) {
        return $this->state(function (array $attributes) use ($amount, $installmentNumber, $paymentMonth) {
            return [
                'amount' => $amount,
                'installment_number' => $installmentNumber,
                'payment_month' => $paymentMonth,
            ];
        });
    }
}
