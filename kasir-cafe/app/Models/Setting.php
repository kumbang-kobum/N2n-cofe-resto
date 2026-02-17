<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'restaurant_name',
        'restaurant_address',
        'restaurant_phone',
        'tax_enabled',
        'logo_path',
        'license_key',
        'installed_at',
        'installation_code',
    ];

    protected $casts = [
        'tax_enabled' => 'boolean',
    ];
}
