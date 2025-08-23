<?php

namespace App\Http\Requests\Revenues;

use App\Models\Revenues;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRevenuesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->get('id')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|uuid',
            'title' => 'bail|filled|max:100|min:2',
            'amount' => 'bail|filled|numeric|',
            'receiving_date' => 'bail|filled|date|',
            'description' => 'filled|string|max:300',
            'category_id' => 'filled|uuid|nullable',
            'date' => 'filled|date',
            'update_type' => 'filled|string|in:' . implode(',', Revenues::getEditTypes())
        ];
    }
}
