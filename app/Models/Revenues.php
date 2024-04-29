<?php

namespace App\Models;

use App\Models\Scopes\AuthScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([AuthScope::class])]
class Revenues extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'title',
        'amount',
        'receiving_date',
        'recurrent',
        'description',
        'deprecated',
        'user_id',
        'category_id',
    ];

    public function scopeWithUpdates() {

    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function revenuesUpdates(Carbon $date): HasMany
    {
        return $this->hasMany(RevenuesOverrides::class)
            ->whereMonth('receiving_date', $date->month)
            ->whereYear('receiving_date', $date->year);
    }
}
