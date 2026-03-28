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
        'funding_source',
        'note',
        'cashier_id',
        'petty_cash_fund_id',
        'status',
        'approved_by',
        'approved_at',
        'approval_note',
        'receipt_path',
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

    public function pettyCashFund()
    {
        return $this->belongsTo(PettyCashFund::class);
    }
}
