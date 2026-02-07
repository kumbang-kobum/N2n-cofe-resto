<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemBatch extends Model
{
    protected $fillable = [
        'item_id',
        'received_at',
        'expired_at',
        'qty_on_hand_base',
        'unit_cost_base',
        'status',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'expired_at' => 'date',
        'qty_on_hand_base' => 'float',
        'unit_cost_base' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockMoves(): HasMany
    {
        return $this->hasMany(StockMove::class, 'batch_id');
    }
}