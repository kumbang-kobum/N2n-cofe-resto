<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AuditLog;
use Carbon\Carbon;

class StockOpname extends Model
{
    protected $fillable = [
    'code','counted_at','status','note',
    'created_by','posted_by','posted_at',
    'cancelled_at','cancelled_by','cancel_reason',
    ];

    protected $casts = [
        'counted_at' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public static function nextCode($countedAt): string
    {
        $date = $countedAt instanceof Carbon
            ? $countedAt->copy()
            : Carbon::parse($countedAt);

        $prefix = 'SOP-' . $date->format('Ymd') . '-';

        // cari code terakhir untuk tanggal tsb: SOP-YYYYMMDD-####
        $last = self::query()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $nextNumber = 1;

        if ($last) {
            $lastSeq = (int) substr($last, -4); // ambil ####
            $nextNumber = $lastSeq + 1;
        }

        return $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }
}