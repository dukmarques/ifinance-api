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
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'title',
        'full_amount',
        'is_owner',
        'date',
        'fully_paid',
    ];

    public function installments(): HasMany {
        return $this->hasMany(ExpenseInstallments::class);
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
