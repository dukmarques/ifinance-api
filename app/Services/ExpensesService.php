<?php

namespace App\Services;

use App\Models\Expenses;
use App\Http\Resources\ExpenseResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ExpensesService extends BaseService
{
    public function __construct()
    {
        $this->model = Expenses::class;
        $this->resourceClass = ExpenseResource::class;
    }

    public function index(): Collection|array
    {
        $requestData = request()->all();
        $date = createCarbonDateFromString($requestData['date']);

        $query = Expenses::query()
            ->where(function ($query) use ($date) {
                $this->buildRecurringExpensesQuery($query, $date);
            })
            ->orWhere(function ($query) use ($date) {
                $query->whereMonth('payment_month', $date->month)
                    ->whereYear('payment_month', $date->year)
                    ->where('recurrent', '=', false);
            })
            ->with([
                'category',
                'assignee',
                'overrides' => function ($query) use ($date) {
                    $query->whereMonth('expenses_overrides.payment_month', $date->month)
                        ->whereYear('expenses_overrides.payment_month', $date->year);
                }
            ]);

        return ExpenseResource::collection($query->get())->response()->getData(true);
    }

    private function buildRecurringExpensesQuery(Builder $query, Carbon $date): Builder
    {
        $firstDayOfMonth = $date->copy()->startOfMonth();
        $lastDayOfMonth = $date->copy()->endOfMonth();

        return $query->where(function ($query) use ($lastDayOfMonth) {
            $query->whereDate('payment_month', '<=', $lastDayOfMonth);
        })
            ->where(function ($subQuery) use ($firstDayOfMonth) {
                $subQuery->whereDate('deprecated_date', '>=', $firstDayOfMonth)
                    ->orWhereNull('deprecated_date');
            })
            ->where('recurrent', '=', true);
    }

    public function update(string $id, array $data): ExpenseResource
    {
        $expense = Expenses::query()->findOrFail($id);

        if ($expense->isRecurrent()) {
            $recurrentExpenseData = collect($data)->only([
                'update_type',
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

    private function updateRecurrentExpense(Expenses $expense, $data)
    {
        switch ($data['update_type']) {
            case Expenses::EDIT_TYPE_CURRENT_AND_FUTURE:
                return $this->updateRecurrentExpenseCurrentAndFuture($expense, $data);
                break;

            case Expenses::EDIT_TYPE_ONLY_MONTH:
                return $this->updateRecurrentExpenseOnlyMonth($expense, $data);

            case Expenses::EDIT_TYPE_ALL:
            default:
                $expense->update($data);
                return $expense;
                break;
        }
    }

    private function updateRecurrentExpenseCurrentAndFuture(Expenses $expense, array $data)
    {
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
    }

    private function updateRecurrentExpenseOnlyMonth(Expenses $expense, array $data)
    {
        $date = createCarbonDateFromString($data['payment_month']);

        $expense->overrides()->create([
            ...$data,
            'payment_month' => $date->toDateString(),
        ]);

        return $expense->load(['overrides' => function ($query) use ($date) {
            $query->whereDate('payment_month', $date->toDateString());
        }]);
    }

    public function destroy(string $id, string $delete_type = null): bool
    {
        $expense = Expenses::query()->findOrFail($id);

        // if ($expense->type === Expenses::TYPE_RECURRENT) {}

        return $expense->delete();
    }
}
