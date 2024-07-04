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
            'total_amount' => ($this->total_amount / 100),
            'is_deleted' => $this->is_deleted,
            'payment_month' => $this->payment_month,
            'description' => $this->description,
        ];
    }
}
