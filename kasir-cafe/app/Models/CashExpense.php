<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_at',
        'category',
        'amount',
        'note',
        'cashier_id',
    ];

    protected $casts = [
        'expense_at' => 'datetime',
        'amount' => 'float',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
