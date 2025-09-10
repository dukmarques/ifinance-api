<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseOverrideResource extends JsonResource
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
            'amount' => currency_format($this->amount),
            'paid' => (bool) $this->isPaid(),
            'is_deleted' => (bool) $this->is_deleted,
            'payment_month' => $this->payment_month,
            'description' => $this->description,
        ];
    }
}
