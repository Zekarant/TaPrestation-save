<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'accuracy',
        'heading',
        'speed',
        'city',
        'postal_code',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'heading' => 'decimal:2',
        'speed' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    /**
     * Enregistrer une nouvelle position et mettre à jour la position courante du livreur
     */
    public static function recordPosition(int $driverId, array $data): self
    {
        $config = config('delivery.gps');
        
        // Vérifier la précision minimale
        if (isset($data['accuracy']) && $data['accuracy'] > $config['accuracy_threshold']) {
            throw new \Exception('Position GPS trop imprécise');
        }

        // Créer l'entrée d'historique
        $location = self::create([
            'driver_id' => $driverId,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'] ?? null,
            'heading' => $data['heading'] ?? null,
            'speed' => $data['speed'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
        ]);

        // Mettre à jour la position courante du livreur
        DeliveryDriver::where('id', $driverId)->update([
            'current_lat' => $data['latitude'],
            'current_lng' => $data['longitude'],
            'location_updated_at' => now(),
        ]);

        return $location;
    }

    /**
     * Nettoyer les anciennes positions
     */
    public static function cleanupOldPositions(): int
    {
        $retentionHours = config('delivery.gps.history_retention', 24);
        
        return self::where('created_at', '<', now()->subHours($retentionHours))->delete();
    }
}
