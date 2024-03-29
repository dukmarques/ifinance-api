<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TransactionService {

    public function index(): Collection {
        return Transaction::where('user_id', Auth::id())
            ->with('card')
            ->with('category')
            ->get();
    }

    public function show(string $id): Transaction {
        return Transaction::find($id);
    }

    public function store(array $data): Transaction {
        $data['user_id'] = Auth::id();

        if ($data['type'] === 'entry') {
            $data['is_owner'] = true;
            $data['paid_out'] = false;
            $data['card_id'] = null;
            $data['category_id'] = null;
        } else {
            $data['card_id'] = $data['card_id'] ?? null;
            $data['category_id'] = $data['category_id'] ?? null;
        }

        $data['pay_month'] = Carbon::createFromFormat('Y-m', $data['pay_month']);
        return Transaction::create($data);
    }

    public function update(string $id, array $data): Transaction|null {
        $transaction = Transaction::find($id);

        if (!$transaction) return null;

        if ($data['type'] === 'entry') {
            $data['is_owner'] = true;
            $data['paid_out'] = false;
            $data['card_id'] = null;
            $data['category_id'] = null;
        } else {
            $data['card_id'] = $data['card_id'] ?? null;
            $data['category_id'] = $data['category_id'] ?? null;
        }

        $data['pay_month'] = Carbon::createFromFormat('Y-m', $data['pay_month']);
        $transaction->update($data);
        return $transaction;
    }

    public function destroy(string $id): bool {
        return Transaction::destroy($id);
    }
}
