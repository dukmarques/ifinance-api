<?php

namespace App\Http\Resources;

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
            'recurrent' => $this->recurrent,
            'amount' => currency_format($this->amount),
            'is_owner' => $this->is_owner,
            'paid' => $this->paid,
            'assignee' => new ExpenseAssigneeResource($this->whenLoaded('assignee')),
            'payment_month' => $this->payment_month,
            'deprecated_date' => $this->deprecated_date,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user_id' => $this->user_id,
            'override' => $this->whenLoaded('overrides', function () {
                return $this->overrides->isNotEmpty()
                    ? new ExpenseOverrideResource($this->overrides->first())
                    : null;
            }),
        ];
    }
}
