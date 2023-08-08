<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TransactionService {

    public function index(): Collection {
        return Transaction::where('user_id', Auth::id())->get();
    }

    public function show(string $id): Transaction {
        return Transaction::find($id);
    }

    public function store(array $data): Transaction {
        $data['user_id'] = Auth::id();
        return Transaction::create($data);
    }

    public function update(string $id, array $data): Transaction|null {
        $transaction = Transaction::find($id);

        if (!$transaction) return null;

        $transaction->update($data);
        return $transaction;
    }

    public function destroy(string $id): bool {
        return Transaction::destroy($id);
    }
}
