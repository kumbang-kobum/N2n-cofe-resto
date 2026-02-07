<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMove extends Model
{
    protected $fillable = [
        'moved_at',
        'item_id',
        'batch_id',
        'qty_base',
        'type',
        'ref_type',
        'ref_id',
        'created_by',
        'note',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
        'qty_base' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ItemBatch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}