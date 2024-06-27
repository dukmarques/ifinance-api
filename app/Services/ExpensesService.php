<?php

namespace App\Services;

use App\Http\Resources\ExpenseResource;
use App\Models\Expenses;
use Illuminate\Support\Facades\Auth;

class ExpensesService
{
    public function index() {}

    public function show(string $id) {}

    public function store(Array $data) {
        $expense = Expenses::query()->create($data + ['user_id' => Auth::user()->id]);

        if ($data['type'] == 'installments') {
        }

        return new ExpenseResource($expense);
    }

    public function update(string $id, Array $data) {}

    public function delete(string $id) {}
}
