<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenuesOverridesResource extends JsonResource
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
            'receiving_date' => $this->receiving_date,
            'description' => $this->description,
            'revenues_id' => $this->revenues_id,
            'is_deleted' => $this->is_deleted,
        ];
    }
}
