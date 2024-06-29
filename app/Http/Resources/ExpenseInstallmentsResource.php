<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseInstallmentsResource extends JsonResource
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
            'amount' => ($this->amount / 100),
            'paid' => $this->paid,
            'installment_number' => $this->installment_number,
            'payment_month' => $this->payment_month,
            'notes' => $this->notes,
        ];
    }
}
