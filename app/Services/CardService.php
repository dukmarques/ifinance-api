<?php

namespace App\Services;

use App\Models\Card;
use App\Models\User;

class CardService {
    public function index($user_id) {
        $user = User::find($user_id);

        return $user?->cards;
    }

    public function show($user_id, $card_id) {
        return User::find($user_id)->cards()->find($card_id);
    }

    public function store($user_id, $data): Card|null {
        $user = User::find($user_id);
        if(!$user) return null;

        $data['user_id'] = $user_id;
        return Card::create($data);
    }

    public function update($user_id, $card_id, $data): Card|null {
        $card = User::find($user_id)->cards()->find($card_id);

        if(!$card) return null;

        $card->update($data);
        return $card;
    }

    public function destroy($user_id, $card_id): bool {
        return Card::where('user_id', $user_id)->where('id', $card_id)->delete();
    }
}
