<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'schedule_date',
        'is_day_off',
        'note',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'is_day_off' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(AttendanceShift::class, 'shift_id');
    }
}
