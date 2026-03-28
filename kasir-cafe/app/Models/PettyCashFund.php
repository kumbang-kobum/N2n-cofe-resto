<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PettyCashFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'period_start',
        'period_end',
        'opening_balance',
        'returned_amount',
        'counted_cash_amount',
        'difference_amount',
        'status',
        'note',
        'reconciliation_note',
        'created_by',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance' => 'float',
        'returned_amount' => 'float',
        'counted_cash_amount' => 'float',
        'difference_amount' => 'float',
        'closed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CashExpense::class);
    }

    public function getApprovedUsedTotalAttribute(): float
    {
        return (float) ($this->approved_used_total ?? $this->expenses()->where('status', 'APPROVED')->sum('amount'));
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) $this->opening_balance - $this->approved_used_total - (float) $this->returned_amount;
    }
}
