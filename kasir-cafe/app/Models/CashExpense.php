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
        'status',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    protected $casts = [
        'expense_at' => 'datetime',
        'amount' => 'float',
        'approved_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
