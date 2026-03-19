<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $table = 'delivery_tracking_events';

    protected $fillable = [
        'delivery_order_id',
        'driver_id',
        'status',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'signature',
        'photo',
        'notes',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'json',
    ];

    // Status constants
    const STATUS_ORDER_CREATED = 'order_created';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_DRIVER_ASSIGNED = 'driver_assigned';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERY_ATTEMPT = 'delivery_attempt';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the delivery order
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Get the driver who recorded this event
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ORDER_CREATED => 'Commande créée',
            self::STATUS_PREPARING => 'En préparation',
            self::STATUS_READY_FOR_PICKUP => 'Prêt pour enlèvement',
            self::STATUS_DRIVER_ASSIGNED => 'Livreur assigné',
            self::STATUS_PICKED_UP => 'Colis récupéré',
            self::STATUS_IN_TRANSIT => 'En transit',
            self::STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
            self::STATUS_DELIVERY_ATTEMPT => 'Tentative de livraison',
            self::STATUS_DELIVERED => 'Livré',
            self::STATUS_FAILED => 'Échec de livraison',
            self::STATUS_RETURNED => 'Retourné à l\'expéditeur',
            self::STATUS_CANCELLED => 'Annulé',
            default => 'Inconnu'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ORDER_CREATED => '📋',
            self::STATUS_PREPARING => '📦',
            self::STATUS_READY_FOR_PICKUP => '✅',
            self::STATUS_DRIVER_ASSIGNED => '👤',
            self::STATUS_PICKED_UP => '🚚',
            self::STATUS_IN_TRANSIT => '🛣️',
            self::STATUS_OUT_FOR_DELIVERY => '🏃',
            self::STATUS_DELIVERY_ATTEMPT => '🔔',
            self::STATUS_DELIVERED => '🎉',
            self::STATUS_FAILED => '❌',
            self::STATUS_RETURNED => '↩️',
            self::STATUS_CANCELLED => '🚫',
            default => '📍'
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ORDER_CREATED => 'gray',
            self::STATUS_PREPARING => 'yellow',
            self::STATUS_READY_FOR_PICKUP => 'blue',
            self::STATUS_DRIVER_ASSIGNED => 'indigo',
            self::STATUS_PICKED_UP => 'purple',
            self::STATUS_IN_TRANSIT => 'blue',
            self::STATUS_OUT_FOR_DELIVERY => 'cyan',
            self::STATUS_DELIVERY_ATTEMPT => 'orange',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_RETURNED => 'pink',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    /**
     * Create a tracking event
     */
    public static function createEvent(
        int $deliveryOrderId, 
        string $status, 
        string $title,
        ?string $description = null,
        ?int $driverId = null,
        ?array $location = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'delivery_order_id' => $deliveryOrderId,
            'driver_id' => $driverId,
            'status' => $status,
            'title' => $title,
            'description' => $description,
            'location' => $location['address'] ?? null,
            'latitude' => $location['lat'] ?? null,
            'longitude' => $location['lng'] ?? null,
            'created_by' => auth()->id(),
            'metadata' => $metadata,
        ]);
    }
}
