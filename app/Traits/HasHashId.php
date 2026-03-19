<?php

namespace App\Traits;

use App\Services\HashIdService;

/**
 * Trait pour ajouter le support des HashIds aux modèles Eloquent
 * Masque les IDs réels dans les URLs pour plus de sécurité
 */
trait HasHashId
{
    /**
     * Obtenir le hash de l'ID du modèle
     */
    public function getHashIdAttribute(): string
    {
        return $this->encodeId($this->getKey());
    }
    
    /**
     * Encoder un ID en hash
     */
    public static function encodeId(int $id): string
    {
        return app(HashIdService::class)->encode($id);
    }
    
    /**
     * Décoder un hash en ID
     */
    public static function decodeId(string $hash): ?int
    {
        return app(HashIdService::class)->decode($hash);
    }
    
    /**
     * Trouver un modèle par son hash
     */
    public static function findByHash(string $hash)
    {
        $id = static::decodeId($hash);
        
        if ($id === null) {
            return null;
        }
        
        return static::find($id);
    }
    
    /**
     * Trouver un modèle par son hash ou échouer
     */
    public static function findByHashOrFail(string $hash)
    {
        $id = static::decodeId($hash);
        
        if ($id === null) {
            abort(404);
        }
        
        return static::findOrFail($id);
    }
    
    /**
     * Obtenir la clé de route pour le model binding
     */
    public function getRouteKey()
    {
        return $this->hash_id;
    }
    
    /**
     * Résoudre le model binding avec le hash
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Si c'est un ID numérique direct (fallback pour compatibilité)
        if (is_numeric($value)) {
            return $this->where($this->getRouteKeyName(), $value)->first();
        }
        
        // Sinon, décoder le hash
        $id = static::decodeId($value);
        
        if ($id === null) {
            return null;
        }
        
        return $this->where($this->getRouteKeyName(), $id)->first();
    }
    
    /**
     * Nom de la clé de route (toujours l'ID primaire)
     */
    public function getRouteKeyName(): string
    {
        return $this->primaryKey ?? 'id';
    }
}
