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
            'total_amount' => currency_format($this->total_amount),
            'is_deleted' => $this->is_deleted,
            'payment_month' => $this->payment_month,
            'description' => $this->description,
        ];
    }
}
