<?php

namespace App\Services;

use App\Http\Resources\ExpenseResource;
use App\Models\ExpenseInstallments;
use App\Models\Expenses;
use App\Models\ExpensesOverride;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpensesService
{
    public function index() {}

    public function show(string $id) {}

    public function store(Array $data): ExpenseResource {
        $data['user_id'] = Auth::user()->id;
        $expense = Expenses::query()->create($data);
        return new ExpenseResource($expense);
    }

    public function update(string $id, Array $data): ExpenseResource
    {
        $expense = Expenses::query()->findOrFail($id);

        if ($expense->type === Expenses::TYPE_RECURRENT) {
            $recurrentExpenseData = collect($data)->only([
                'recurrence_update_type',
                'title',
                'amount',
                'is_owner',
                'paid',
                'payment_month',
                'deprecated_date',
                'description',
                'card_id',
                'category_id',
            ])->toArray();

            $expense = $this->updateRecurrentExpense(expense: $expense, data: $recurrentExpenseData);
            return new ExpenseResource($expense);
        }

        $simpleExpenseData = collect($data)->only([
            'title',
            'amount',
            'is_owner',
            'paid',
            'payment_month',
            'description',
            'card_id',
            'category_id',
        ])->toArray();

        $expense->update($simpleExpenseData);
        return new ExpenseResource($expense);
    }

    public function delete(string $id) {}

    private function updateRecurrentExpense(Expenses $expense, $data) {
        if ($data['recurrence_update_type'] === Expenses::EDIT_TYPE_CURRENT_AND_FUTURE) {
            $date = createCarbonDateFromString($data['payment_month']);

            $newExpense = $expense->replicate()->fill([
                ...$data,
                'payment_month' => $date->toDateString(),
                'deprecated_date' => null,
            ]);
            $newExpense->save();

            $expense->update([
                'deprecated_date' => $date->subMonth()->toDateString(),
            ]);

            return $newExpense;
        } else if ($data['recurrence_update_type'] === Expenses::EDIT_TYPE_ONLY_MONTH) {
            $date = createCarbonDateFromString($data['payment_month']);

            $expense->overrides()->create([
                ...$data,
                'payment_month' => $date->toDateString(),
            ]);

            return $expense->load(['overrides' => function ($query) use ($date) {
                $query->whereDate('payment_month', $date->toDateString());
            }]);
        }

        $expense->update($data);
        return $expense;
    }
}
