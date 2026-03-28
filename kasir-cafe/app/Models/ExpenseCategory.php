<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'approval_limit_amount',
        'is_active',
    ];

    protected $casts = [
        'approval_limit_amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(CashExpense::class);
    }
}
