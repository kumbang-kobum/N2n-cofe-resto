<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseLine extends Model
{
    protected $fillable = [
        'purchase_id',
        'item_id',
        'unit_id',
        'qty',
        'unit_cost',
        'qty_base',
        'unit_cost_base',
        'expired_at',
    ];

    protected $casts = [
        'qty'            => 'float',
        'qty_base'       => 'float',
        'unit_cost'      => 'float',
        'unit_cost_base' => 'float',
        'expired_at'     => 'datetime',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}