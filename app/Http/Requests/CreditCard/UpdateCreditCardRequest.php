<?php

namespace App\Http\Requests\CreditCard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCreditCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'filled|min:2',
            'closing_day' => 'filled|integer|between:1,31',
            'due_date' => 'filled|integer|between:1,31',
            'card_flag' => 'filled|min:2',
            'limit' => 'filled|integer',
            'background_color' => 'filled|min:2',
        ];
    }
}
