<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetLocation extends Model
{
    protected $fillable = ['name'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'location_id');
    }
}
