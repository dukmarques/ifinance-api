<?php

namespace App\Http\Resources;

use App\Models\ExpensesOverride;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
            'title' => $this->title,
            'type' => $this->type,
            'amount' => ($this->amount / 100),
            'is_owner' => $this->is_owner,
            'paid' => $this->paid,
            'payment_month' => $this->payment_month,
            'deprecated_date' => $this->deprecated_date,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'card_id' => $this->card_id,
            'user_id' => $this->user_id,
            'overrides' => ExpenseOverrideResource::collection($this->whenLoaded('overrides')),
        ];
    }
}
