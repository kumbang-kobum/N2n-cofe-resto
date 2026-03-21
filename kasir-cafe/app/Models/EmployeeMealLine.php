<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMealLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_meal_id',
        'product_id',
        'qty',
        'price',
        'line_total',
    ];

    protected $casts = [
        'qty' => 'float',
        'price' => 'float',
        'line_total' => 'float',
    ];

    public function meal()
    {
        return $this->belongsTo(EmployeeMeal::class, 'employee_meal_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
