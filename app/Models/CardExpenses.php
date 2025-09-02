<?php

namespace App\Models;

use App\Traits\HasEditTypes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardExpenses extends Model
{
    use HasFactory;
    use HasUuids;
    use HasEditTypes;

    protected $fillable = [
        'id',
        'total_amount',
        'is_owner',
        'assignee_id',
        'user_id',
        'card_id',
        'category_id',
    ];

    public function installments(): HasMany
    {
        return $this->hasMany(CardInstallments::class, 'card_expenses_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(ExpenseAssignees::class, 'assignee_id', 'id');
    }
}
