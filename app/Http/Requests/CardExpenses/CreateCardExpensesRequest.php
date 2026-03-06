<?php

namespace App\Http\Requests\CardExpenses;

use App\Models\CardExpenses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateCardExpensesRequest extends FormRequest
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
            'total_amount' => 'required|numeric|min:1',
            'is_owner' => 'required|boolean',
            'assignee_id' => [
                'required_if:is_owner,false',
                Rule::exists('expense_assignees', 'id')
                    ->where(fn($query) => $query->where('user_id', Auth::id())),
            ],
            'card_id' => 'required|exists:cards,id',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'initial_installment' => 'required|integer|min:1',
            'final_installment' => 'required|integer|gte:initial_installment',
        ];
    }
}
