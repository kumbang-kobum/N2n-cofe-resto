<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price_default',
        'image_path',   // ✅ gambar katalog
        'is_active',
    ];

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }
}