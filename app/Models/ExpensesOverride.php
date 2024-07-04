<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensesOverride extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'title',
        'total_amount',
        'is_deleted',
        'payment_month',
        'description',
        'expense_id',
    ];

    public function expense(): BelongsTo {
        return $this->belongsTo(Expenses::class, 'id', 'expense_id');
    }
}
