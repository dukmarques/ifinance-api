<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardInstallmentsResource extends JsonResource
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
            'amount' => ($this->amount / 100),
            'paid' => $this->paid,
            'installment_number' => $this->installment_number,
            'payment_month' => $this->payment_month,
            'notes' => $this->notes,
            'card_expenses_id' => $this->card_expenses_id,
        ];
    }
}
