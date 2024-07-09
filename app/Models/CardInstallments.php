<?php

namespace App\Models;

use App\Traits\HasEditTypes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardInstallments extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasEditTypes;

    protected $fillable = [
        'id',
        'title',
        'amount',
        'paid',
        'installment_number',
        'payment_month',
        'notes',
        'card_expenses_id',
    ];

    public function expense(): BelongsTo {
        return $this->belongsTo(CardExpenses::class);
    }
}
