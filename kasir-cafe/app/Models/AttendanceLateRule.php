<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_minutes',
        'max_minutes',
        'deduction_amount',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_minutes' => 'integer',
        'max_minutes' => 'integer',
        'deduction_amount' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
