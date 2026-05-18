<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleRefund extends Model
{
    protected $fillable = [
        'sale_id',
        'total_refund',
        'refunded_at',
        'refunded_by',
        'note',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
    ];

    protected $casts = [
        'total_refund' => 'float',
        'refunded_at'  => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleRefundLine::class, 'refund_id');
    }
}
