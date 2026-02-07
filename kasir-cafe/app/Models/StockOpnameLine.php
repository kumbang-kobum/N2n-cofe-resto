<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameLine extends Model
{
    protected $fillable = [
        'stock_opname_id','item_id',
        'system_qty_base','physical_qty_base','diff_qty_base',
        'physical_qty','input_unit_id',
        'expired_at','unit_cost_base'
    ];

    protected $casts = [
        'system_qty_base' => 'float',
        'physical_qty_base' => 'float',
        'diff_qty_base' => 'float',
        'physical_qty' => 'float',
        'unit_cost_base' => 'float',
        'expired_at' => 'date',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Item::class);
    }

    public function inputUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'input_unit_id');
    }
}