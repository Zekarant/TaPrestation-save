<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait pour gérer les UUIDs sur les modèles
 * Solution simple et native Laravel pour sécuriser les URLs
 */
trait HasUuid
{
    /**
     * Boot du trait - génère automatiquement un UUID à la création
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Retourne la clé pour le route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Trouve un modèle par son UUID
     */
    public static function findByUuid(string $uuid): ?static
    {
        return static::where('uuid', $uuid)->first();
    }

    /**
     * Trouve un modèle par son UUID ou échoue
     */
    public static function findByUuidOrFail(string $uuid): static
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }
}
