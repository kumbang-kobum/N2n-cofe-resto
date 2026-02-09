<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'status',
        'cashier_id',
        'total',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'paid_at',
        'payment_method',
        'cogs_total',
        'profit_gross',
    ];

    protected $casts = [
        'total'        => 'float',
        'tax_rate'     => 'float',
        'tax_amount'   => 'float',
        'grand_total'  => 'float',
        'cogs_total'   => 'float',
        'profit_gross' => 'float',
        'paid_at'      => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(SaleLine::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
