<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseAssignees extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseAssigneesFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'expense_assignees';

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expense(): HasMany
    {
        return $this->hasMany(Expenses::class, 'assignee_id', 'id');
    }

    public function cardExpense(): HasMany
    {
        return $this->hasMany(CardExpenses::class, 'assignee_id', 'id');
    }
}
