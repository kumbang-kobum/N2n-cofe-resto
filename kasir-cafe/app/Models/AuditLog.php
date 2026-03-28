<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

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

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function actionLabel(): string
    {
        return Str::of($this->action)
            ->replace('_', ' ')
            ->lower()
            ->title()
            ->toString();
    }

    public function actorLabel(): string
    {
        return $this->actor?->name
            ?? ($this->actor_id ? 'User #' . $this->actor_id : 'Sistem');
    }

    public function auditableLabel(): string
    {
        $model = $this->auditable;
        $type = class_basename((string) $this->auditable_type);

        if ($model) {
            foreach (['name', 'title', 'code', 'sku'] as $field) {
                if (isset($model->{$field}) && filled($model->{$field})) {
                    return (string) $model->{$field};
                }
            }
        }

        return trim($type . ' #' . $this->auditable_id);
    }

    public function summaryText(): string
    {
        return $this->actorLabel() . ' melakukan ' . Str::lower($this->actionLabel()) . ' pada ' . $this->auditableLabel() . '.';
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function formattedMeta(): array
    {
        if (! is_array($this->meta) || $this->meta === []) {
            return [];
        }

        $rows = [];
        foreach ($this->meta as $key => $value) {
            $rows[] = [
                'label' => Str::of((string) $key)->replace('_', ' ')->title()->toString(),
                'value' => $this->stringifyMetaValue($value),
            ];
        }

        return $rows;
    }

    protected function stringifyMetaValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-';
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
