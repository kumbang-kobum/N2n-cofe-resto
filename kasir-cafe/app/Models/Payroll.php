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
        'employee_master_id',
        'base_salary',
        'overtime_amount',
        'bonus_amount',
        'deduction_amount',
        'late_deduction_amount',
        'meal_deduction_amount',
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
        'late_deduction_amount' => 'float',
        'meal_deduction_amount' => 'float',
        'net_amount' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function employeeMaster()
    {
        return $this->belongsTo(Employee::class, 'employee_master_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getEmployeeDisplayNameAttribute(): string
    {
        return (string) ($this->employeeMaster->name ?? $this->employee->name ?? '-');
    }

    public function getTotalDeductionAmountAttribute(): float
    {
        return (float) $this->deduction_amount + (float) $this->late_deduction_amount + (float) $this->meal_deduction_amount;
    }

    public function getExpenseAmountAttribute(): float
    {
        return (float) $this->base_salary
            + (float) $this->overtime_amount
            + (float) $this->bonus_amount
            - (float) $this->deduction_amount
            - (float) $this->late_deduction_amount
            - (float) $this->meal_deduction_amount;
    }

    public function employeeMeals()
    {
        return $this->hasMany(EmployeeMeal::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
