<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CardService
{
    public function index(): Collection|array
    {
        return Card::withCount('cardExpenses')
            ->get();
    }

    public function show($id) {
        return Card::find($id);
    }

    public function store($data): Card|null {
        $data['user_id'] = Auth::id();
        return Card::create($data);
    }

    public function update($id, $data): Card|null {
        $card = Card::find($id);

        if(!$card) return null;

        $card->update($data);
        return $card;
    }

    public function destroy($id): bool {
        return Card::destroy($id);
    }
}
