<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetIncident extends Model
{
    protected $fillable = [
        'asset_id',
        'type',
        'incident_date',
        'description',
        'cost',
        'status',
        'reported_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'cost' => 'float',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
