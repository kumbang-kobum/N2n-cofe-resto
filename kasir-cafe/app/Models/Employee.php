<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'name',
        'position',
        'department',
        'meal_allowance_monthly',
        'uses_app',
        'user_id',
        'is_active',
        'note',
    ];

    protected $casts = [
        'meal_allowance_monthly' => 'float',
        'uses_app' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meals()
    {
        return $this->hasMany(EmployeeMeal::class);
    }
}
