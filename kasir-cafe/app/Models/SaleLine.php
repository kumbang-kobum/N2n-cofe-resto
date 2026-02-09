<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleLine extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'float',
        'price' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function refundLines()
    {
        return $this->hasMany(SaleRefundLine::class, 'sale_line_id');
    }
}
