<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenuesOverrides extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'title',
        'amount',
        'receiving_date',
        'description',
        'revenues_id',
        'is_deleted',
    ];

    public function revenues(): BelongsTo
    {
        return $this->belongsTo(Revenues::class);
    }
}
