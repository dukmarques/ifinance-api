<?php

namespace App\Services;

use App\Http\Resources\ExpenseResource;
use App\Models\ExpenseInstallments;
use App\Models\Expenses;
use Carbon\Carbon;
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
        $expense = null;

        if ($data['type'] == 'installments') {
            DB::transaction(function () use ($data, &$expense) {
                $expense = Expenses::query()->create($data);
                $installments = $this->generateInstallmentsArray(data: $data, expense_id: $expense->id);
                ExpenseInstallments::query()->insert($installments);
            });

            return new ExpenseResource($expense->load('installments'));
        }

        $expense = Expenses::query()->create($data);
        return new ExpenseResource($expense);
    }

    public function update(string $id, Array $data) {}

    public function delete(string $id) {}

    private function generateInstallmentsArray($data, $expense_id): array {
        $initial_installment = $data['initial_installment'];
        $final_installment = $data['final_installment'];
        $amount = $data['total_amount'] / $final_installment;
        $paymentMonth = Carbon::parse($data['payment_month']);
        $installments = [];

        for ($i = $initial_installment; $i <= $final_installment; $i++) {
            $installments[] = [
                'id' => Str::uuid()->toString(),
                'amount' => $amount,
                'paid' => false,
                'installment_number' => $i,
                'payment_month' => $paymentMonth->copy()->addMonths($i - 1)->toDateString(),
                'expense_id' => $expense_id,
            ];
        }

        return $installments;
    }
}
