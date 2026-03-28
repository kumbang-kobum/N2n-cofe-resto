<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'late_tolerance_minutes',
        'overtime_after_minutes',
        'is_active',
        'note',
    ];

    protected $casts = [
        'late_tolerance_minutes' => 'integer',
        'overtime_after_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
