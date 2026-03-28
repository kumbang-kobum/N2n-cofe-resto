<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'scheduled_shift_id',
        'clock_in_at',
        'clock_out_at',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'late_deduction_amount',
        'verification_status',
        'verification_score',
        'clock_in_photo_path',
        'clock_out_photo_path',
        'payroll_id',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'note',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'late_deduction_amount' => 'float',
        'verification_score' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(AttendanceShift::class, 'scheduled_shift_id');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
