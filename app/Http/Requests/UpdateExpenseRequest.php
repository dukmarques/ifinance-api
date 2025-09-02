<?php

namespace App\Http\Requests;

use App\Models\Expenses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
            'type' => 'filled|in:' . implode(',', Expenses::$expenseTypes),
            'amount' => 'filled|numeric|min:1',
            'is_owner' => 'filled|boolean',
            'assignee_id' => 'filled|exists:expense_assignees,id',
            'paid' => 'filled|boolean',
            'payment_month' => 'filled|date',
            'deprecated_date' => 'filled|date',
            'description' => 'filled|string|max:300',
            'category_id' => 'exists:categories,id',
            'recurrence_update_type' => 'filled|in:' . implode(',', Expenses::$editTypes),
        ];
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
