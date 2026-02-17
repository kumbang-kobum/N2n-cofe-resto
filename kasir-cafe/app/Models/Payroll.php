<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_month',
        'employee_id',
        'base_salary',
        'overtime_amount',
        'bonus_amount',
        'deduction_amount',
        'net_amount',
        'status',
        'note',
        'approval_note',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'period_month' => 'date',
        'base_salary' => 'float',
        'overtime_amount' => 'float',
        'bonus_amount' => 'float',
        'deduction_amount' => 'float',
        'net_amount' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
