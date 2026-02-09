<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'name',
        'category',
        'location',
        'purchase_date',
        'purchase_cost',
        'condition',
        'is_active',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'float',
        'is_active' => 'boolean',
    ];

    public function incidents(): HasMany
    {
        return $this->hasMany(AssetIncident::class);
    }
}
