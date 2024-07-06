<?php

namespace App\Models;

use App\Models\Scopes\AuthScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(AuthScope::class)]
class Expenses extends Model
{
    use HasFactory, HasUuids;

    const EDIT_TYPE_ONLY_MONTH = 'only_month';
    const EDIT_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    const EDIT_TYPE_ALL = 'all';

    public static array $editTypes = [
        self::EDIT_TYPE_ONLY_MONTH,
        self::EDIT_TYPE_CURRENT_AND_FUTURE,
        self::EDIT_TYPE_ALL,
    ];

    const TYPE_SIMPLE = 'simple';
    const TYPE_RECURRENT = 'recurrent';

    public static array $expenseTypes = [
        self::TYPE_SIMPLE,
        self::TYPE_RECURRENT,
    ];

    protected $fillable = [
        'id',
        'title',
        'type',
        'amount',
        'is_owner',
        'paid',
        'payment_month',
        'deprecated_date',
        'description',
        'user_id',
        'card_id',
        'category_id',
    ];

    public function overrides(): HasMany {
        return $this->hasMany(ExpensesOverride::class, 'expense_id', 'id');
    }
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo {
        return $this->belongsTo(Card::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
