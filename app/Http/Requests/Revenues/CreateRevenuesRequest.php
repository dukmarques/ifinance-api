<?php

namespace App\Http\Requests\Revenues;

use Illuminate\Foundation\Http\FormRequest;

class CreateRevenuesRequest extends FormRequest
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
            'title' => 'bail|required|max:100|min:2',
            'amount' => 'bail|required|numeric|',
            'receiving_date' => 'bail|required|date|',
            'recurrent' => 'bail|required|boolean',
            'description' => 'sometimes|nullable|string|max:300',
            'category_id' => 'sometimes|nullable|uuid',
        ];
    }
}
