<?php

namespace App\Models;

use App\Models\Scopes\AuthScope;
use App\Traits\HasEditTypes;
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
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use HasEditTypes;

    public const ONLY_MONTH = 'only_month';
    public const CURRENT_MONTH_AND_FOLLOWERS = 'current_month_and_followers';
    public const ALL_MONTH = 'all_month';

    protected $fillable = [
        'id',
        'title',
        'amount',
        'receiving_date',
        'recurrent',
        'description',
        'deprecated_date',
        'user_id',
        'category_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(RevenuesOverrides::class);
    }

    public static function getEditTypes(): array
    {
        return [
            self::ONLY_MONTH,
            self::CURRENT_MONTH_AND_FOLLOWERS,
            self::ALL_MONTH
        ];
    }
}
