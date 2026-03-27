<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    public const TYPE_FOOD = 'FOOD';
    public const TYPE_DRINK = 'DRINK';

    protected $fillable = [
        'name',
        'price_default',
        'menu_type',
        'image_path',   // ✅ gambar katalog
        'is_active',
    ];

    public function getMenuTypeLabelAttribute(): string
    {
        return match ($this->menu_type) {
            self::TYPE_DRINK => 'Minuman',
            self::TYPE_FOOD => 'Makanan',
            default => 'Belum dikategorikan',
        };
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }
}
