<?php

namespace App\Services;

use App\Models\Card;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CardService {
    public function index() {
        return Card::where('user_id', Auth::id())->get();
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
