<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            // 'revenues' => RevenueResource::collection($this->whenLoaded('revenues')),
            'revenues_count' => $this->revenues->count(),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'expenses_count' => $this->expenses->count(),
            'card_expenses' => CardExpensesResource::collection($this->whenLoaded('cardExpenses')),
            'card_expenses_count' => $this->cardExpenses->count(),
        ];
    }
}
