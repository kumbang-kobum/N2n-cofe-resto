<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'table_no',
        'customer_name',
        'status',
        'cashier_id',
        'total',
        'discount_amount',
        'refund_total',
        'paid_amount',
        'change_amount',
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
        'discount_amount' => 'float',
        'refund_total' => 'float',
        'paid_amount' => 'float',
        'change_amount' => 'float',
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

    public function refunds()
    {
        return $this->hasMany(SaleRefund::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
