<?php

namespace App\Http\Requests;

use App\Models\Expenses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $expenseId = $this->route('expense');
        $expense = Expenses::query()->findOrFail($expenseId);
        return Auth::check() && $expense->user_id === Auth::id();
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
            'recurrent' => 'filled|boolean',
            'amount' => 'filled|numeric|min:1',
            'is_owner' => 'filled|boolean',
            'assignee_id' => [
                'filled',
                Rule::exists('expense_assignees', 'id')
                    ->where(fn($query) => $query->where('user_id', Auth::id())),
            ],
            'owner' => 'filled|string|max:50',
            'paid' => 'filled|boolean',
            'payment_month' => 'filled|date',
            'deprecated_date' => 'filled|date',
            'description' => 'filled|string|max:300',
            'category_id' => 'exists:categories,id',
            'update_type' => [
                Rule::requiredIf(fn() => $this->isRecurrentExpense()),
                'in:' . implode(',', Expenses::$editTypes),
            ],
        ];
    }

    private function isRecurrentExpense(): bool
    {
        $expenseId = $this->route('expense');
        $expense = Expenses::query()->find($expenseId);

        return (bool) $expense?->isRecurrent();
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->has('deprecated_date')) {
                    $paymentMonth = $this->input('payment_month');
                    $deprecatedDate = $this->input('deprecated_date');

                    if (isSameMonthAndYear($paymentMonth, $deprecatedDate)) {
                        $validator->errors()->add(
                            'deprecated_date',
                            'The deprecated date must be a date after the payment month.',
                        );
                    }

                    if (isDateGreaterThan($paymentMonth, $deprecatedDate)) {
                        $validator->errors()->add(
                            'payment_month',
                            'The payment month must not be greater than the deprecated month.',
                        );
                    }
                }
            }
        ];
    }
}
