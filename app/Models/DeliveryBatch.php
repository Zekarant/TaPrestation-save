<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryBatch extends Model
{
    protected $fillable = [
        'driver_id',
        'status',
        'total_distance',
        'total_time',
        'driver_earnings',
        'platform_fee',
        'first_pickup_lat',
        'first_pickup_lng',
        'last_dropoff_lat',
        'last_dropoff_lng',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_distance' => 'decimal:2',
        'total_time' => 'integer',
        'driver_earnings' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'first_pickup_lat' => 'decimal:7',
        'first_pickup_lng' => 'decimal:7',
        'last_dropoff_lat' => 'decimal:7',
        'last_dropoff_lng' => 'decimal:7',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKING_UP = 'picking_up';
    const STATUS_DELIVERING = 'delivering';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(FoodOrder::class, 'delivery_batch_orders', 'batch_id', 'food_order_id')
            ->withPivot(['pickup_order', 'delivery_order', 'distance_to_pickup', 'distance_to_dropoff', 'picked_up_at', 'delivered_at'])
            ->withTimestamps();
    }

    public function batchOrders(): HasMany
    {
        return $this->hasMany(DeliveryBatchOrder::class, 'batch_id');
    }

    /**
     * Nombre de commandes dans ce batch
     */
    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Vérifie si le batch peut accepter une nouvelle commande
     */
    public function canAddOrder(): bool
    {
        $maxOrders = config('delivery.batch.max_orders', 3);
        return $this->status === self::STATUS_AVAILABLE && $this->order_count < $maxOrders;
    }

    /**
     * Assigner un livreur au batch
     */
    public function assignDriver(DeliveryDriver $driver): bool
    {
        if ($this->status !== self::STATUS_AVAILABLE) {
            return false;
        }

        $this->update([
            'driver_id' => $driver->id,
            'status' => self::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        // Mettre à jour toutes les commandes du batch
        foreach ($this->orders as $order) {
            $order->update([
                'driver_id' => $driver->id,
                'driver_accepted_at' => now(),
                'delivery_status' => FoodOrder::DELIVERY_STATUS_ASSIGNED,
            ]);
        }

        return true;
    }

    /**
     * Commencer la récupération
     */
    public function startPickup(): void
    {
        $this->update([
            'status' => self::STATUS_PICKING_UP,
            'started_at' => now(),
        ]);
    }

    /**
     * Commencer les livraisons
     */
    public function startDelivering(): void
    {
        $this->update(['status' => self::STATUS_DELIVERING]);
    }

    /**
     * Compléter le batch
     */
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Obtenir le résumé des villes pour l'affichage
     */
    public function getCitiesSummaryAttribute(): string
    {
        $pickupCities = $this->orders->pluck('prestataire.ville')->unique()->filter()->values();
        $dropoffCities = $this->orders->pluck('delivery_address')->map(function ($address) {
            // Extraire la ville de l'adresse (simpliste)
            preg_match('/(\d{5})\s+([^,]+)/i', $address ?? '', $matches);
            return $matches[2] ?? null;
        })->unique()->filter()->values();

        $pickup = $pickupCities->isNotEmpty() ? $pickupCities->first() : 'Restaurant';
        $dropoff = $dropoffCities->isNotEmpty() ? $dropoffCities->first() : 'Client';

        if ($this->order_count > 1) {
            return "{$pickup} → {$dropoff} ({$this->order_count} livraisons)";
        }

        return "{$pickup} → {$dropoff}";
    }
}
