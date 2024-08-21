<?php

namespace App\Services;

use App\Http\Resources\ExpenseResource;
use App\Models\Expenses;

class ExpensesService extends BaseService
{
    public function __construct()
    {
        $this->model = Expenses::class;
        $this->resourceClass = ExpenseResource::class;
    }

    public function update(string $id, array $data): ExpenseResource
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

    public function delete(string $id)
    {
    }

    private function updateRecurrentExpense(Expenses $expense, $data)
    {
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
        } elseif ($data['recurrence_update_type'] === Expenses::EDIT_TYPE_ONLY_MONTH) {
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

    public function destroy(string $id, string $delete_type = null): bool
    {
        $expense = Expenses::query()->findOrFail($id);

        // if ($expense->type === Expenses::TYPE_RECURRENT) {}

        return $expense->delete();
    }
}
