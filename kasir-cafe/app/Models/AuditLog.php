<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'action',
        'auditable_type',
        'auditable_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(?int $actorId, string $action, Model $auditable, array $meta = []): void
    {
        static::create([
            'actor_id' => $actorId,
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'meta' => $meta ?: null,
        ]);
    }
}