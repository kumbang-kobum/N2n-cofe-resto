<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'status',
        'cashier_id',
        'total',
        'paid_at',
        'payment_method',
        'cogs_total',
        'profit_gross',
    ];

    protected $casts = [
        'total' => 'float',
        'cogs_total' => 'float',
        'profit_gross' => 'float',
        'paid_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}