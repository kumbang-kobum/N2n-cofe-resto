<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'restaurant_name',
        'restaurant_address',
        'restaurant_phone',
        'logo_path',
        'license_key',
        'installed_at',
        'installation_code',
    ];
}
