<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
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
            'closing_day' => $this->closing_day,
            'due_day' => $this->due_day,
            'limit' => ($this->limit/100),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'card_expenses' => CardExpensesResource::collection($this->whenLoaded('cardExpenses')),
            'card_expenses_count' => $this->cardExpenses->count(),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'expenses_count' => $this->expenses->count(),
        ];
    }
}
