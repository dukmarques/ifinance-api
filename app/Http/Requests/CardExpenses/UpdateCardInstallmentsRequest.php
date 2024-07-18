<?php

namespace App\Http\Requests\CardExpenses;

use App\Models\CardExpenses;
use App\Models\CardInstallments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCardInstallmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $cardExpenseId = $this->route('card_expense');
        $installmentId = $this->route('installment');

        $cardExpense = CardExpenses::query()->findOrFail($cardExpenseId);
        $installment = CardInstallments::query()->findOrFail($installmentId);

        return Auth::check()
            && $installment->card_expenses_id === $cardExpenseId
            && $cardExpense->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'filled|string|max:100',
            'amount' => 'filled|numeric|min:0.01',
            'paid' => 'filled|boolean',
            'notes' => 'filled|string|max:300',
            'update_type' => 'required|in:' . implode(',', CardInstallments::$editTypes),
        ];
    }
}
