<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price_default',
        'is_active',
        'image_path',          // ⬅️ tambahkan ini
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    // accessor untuk dapat URL gambar
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        // placeholder default
        return asset('images/product-placeholder.png');
    }
}