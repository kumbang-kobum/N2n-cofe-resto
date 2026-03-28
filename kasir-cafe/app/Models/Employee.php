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
        'default_shift_id',
        'face_reference_path',
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

    public function defaultShift()
    {
        return $this->belongsTo(AttendanceShift::class, 'default_shift_id');
    }

    public function schedules()
    {
        return $this->hasMany(AttendanceSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
