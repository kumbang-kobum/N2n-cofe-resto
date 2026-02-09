<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleRefundLine extends Model
{
    protected $fillable = [
        'refund_id',
        'sale_line_id',
        'qty',
        'amount',
    ];

    protected $casts = [
        'qty' => 'float',
        'amount' => 'float',
    ];

    public function refund(): BelongsTo
    {
        return $this->belongsTo(SaleRefund::class, 'refund_id');
    }

    public function saleLine(): BelongsTo
    {
        return $this->belongsTo(SaleLine::class, 'sale_line_id');
    }
}
