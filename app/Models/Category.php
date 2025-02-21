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
class Category extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'name',
        'user_id'
    ];

    protected $hidden = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function revenues(): HasMany {
        return $this->hasMany(Revenues::class);
    }

    public function expenses(): HasMany {
        return $this->hasMany(Expenses::class);
    }

    public function cardExpenses(): HasMany {
        return $this->hasMany(CardExpenses::class);
    }
}
