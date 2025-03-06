<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardExpensesResource extends JsonResource
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
            'total_amount' => currency_format($this->total_amount),
            'is_owner' => $this->is_owner,
            'card_id' => $this->user_id,
            'category_id' => $this->category_id,
            'installments' => CardInstallmentsResource::collection($this->whenLoaded('installments')),
        ];
    }
}
