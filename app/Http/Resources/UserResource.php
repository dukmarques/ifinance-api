<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cards_count' => $this->cards->count(),
            'categories_count' => $this->categories->count(),
            'revenues_count' => $this->revenues->count(),
            'expenses_count' => $this->expenses->count(),
            'card_expenses_count' => $this->cardExpenses->count(),
        ];
    }
}
