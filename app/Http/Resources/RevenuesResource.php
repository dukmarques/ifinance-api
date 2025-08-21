<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenuesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'amount' => currency_format($this->amount),
            'receiving_date' => $this->receiving_date,
            'recurrent' => (bool) $this->recurrent,
            'description' => $this->description,
            'deprecated_date' => $this->deprecated_date,
            'user_id' => $this->user_id,
            'override' => $this->whenLoaded('overrides', function () {
                return $this->overrides->isNotEmpty()
                    ? new RevenuesOverridesResource($this->overrides->first())
                    : null;
            }),
        ];

        if ($this->category) {
            $data['category'] = [
                'id' => $this->category_id,
                'name' => $this->category->name,
            ];
        }

        return $data;
    }
}
