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
        'pos_low_margin_warning_enabled',
        'logo_path',
        'tv_video_path',
        'tv_running_text',
        'license_key',
        'installed_at',
        'installation_code',
    ];

    protected $casts = [
        'tax_enabled' => 'boolean',
        'pos_low_margin_warning_enabled' => 'boolean',
    ];
}
