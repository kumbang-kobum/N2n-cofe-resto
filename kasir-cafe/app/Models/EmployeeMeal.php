<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'cashier_id',
        'consumed_at',
        'total_amount',
        'cogs_total',
        'expense_cogs_total',
        'company_covered_amount',
        'excess_amount',
        'is_over_allowance',
        'payroll_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
        'total_amount' => 'float',
        'cogs_total' => 'float',
        'expense_cogs_total' => 'float',
        'company_covered_amount' => 'float',
        'excess_amount' => 'float',
        'is_over_allowance' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function lines()
    {
        return $this->hasMany(EmployeeMealLine::class);
    }
}
