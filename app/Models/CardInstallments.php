<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardInstallments extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'title',
        'amount',
        'paid',
        'installment_number',
        'pay_month',
        'notes',
        'card_expense_id',
    ];

    public function expense(): BelongsTo {
        return $this->belongsTo(CardExpenses::class);
    }
}
