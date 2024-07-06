<?php

namespace App\Services;

use App\Http\Resources\CardExpensesResource;
use App\Models\CardExpenses;
use App\Models\CardInstallments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CardExpensesService
{

    public function store(Array $data): CardExpensesResource {
        $data['user_id'] = Auth::id();

        $cardExpense = CardExpenses::query()->create($data);
        $installments = $this->generateInstallmentsArray(data: $data, cardExpenseId: $cardExpense->id);
        CardInstallments::query()->insert($installments);

        return new CardExpensesResource($cardExpense->load('installments'));
    }

    private function generateInstallmentsArray($data, string $cardExpenseId): array {
        $initial_installment = $data['initial_installment'];
        $final_installment = $data['final_installment'];
        $amount = $data['total_amount'] / $final_installment;
        $date = createCarbonDateFromString($data['date']);

        $installments = [];
        for ($i = $initial_installment; $i <= $final_installment; $i++) {
            $installments[] = [
                'id' => Str::uuid()->toString(),
                'title' => $data['title'],
                'amount' => $amount,
                'paid' => false,
                'installment_number' => $i,
                'payment_month' => $date->copy()->addMonths($i - 1)->toDateString(),
                'card_expenses_id' => $cardExpenseId,
            ];
        }

        return $installments;
    }
}
