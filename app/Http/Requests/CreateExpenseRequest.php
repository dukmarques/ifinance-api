<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class CreateExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'type' => 'required|in:simple,recurrent,installments',
            'total_amount' => 'required|numeric|min:1',
            'is_owner' => 'required|boolean',
            'paid' => 'filled|boolean',
            'payment_month' => 'required|date',
            'deprecated_date' => 'filled|date',
            'initial_installment' => 'filled|integer|min:1|required_if:type,installments',
            'final_installment' => 'filled|integer|required_with:initial_installment|gt:initial_installment',
            'description' => 'filled|string|max:300',
            'card_id' => 'required|exists:cards,id',
            'category_id' => 'required|exists:categories,id',
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
