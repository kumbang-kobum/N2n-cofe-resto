<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AuditLog;

class StockOpname extends Model
{
    protected $fillable = [
        'code','counted_at','status','note','created_by','posted_by','posted_at'
    ];

    protected $casts = [
        'counted_at' => 'date',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(StockOpnameLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}