<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'delivery_provider_id',
        'driver_id',
        'zone_id',
        'tracking_number',
        'reference_number',
        'status',
        'priority',
        'shipping_type',
        'weight',
        'dimensions',
        'package_count',
        'fragile',
        'requires_signature',
        'shipping_cost',
        'insurance_cost',
        'total_cost',
        'pickup_address',
        'pickup_city',
        'pickup_postal_code',
        'pickup_contact_name',
        'pickup_contact_phone',
        'pickup_instructions',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_city',
        'delivery_postal_code',
        'delivery_contact_name',
        'delivery_contact_phone',
        'delivery_instructions',
        'delivery_lat',
        'delivery_lng',
        'scheduled_pickup_at',
        'picked_up_at',
        'scheduled_delivery_at',
        'estimated_delivery',
        'delivered_at',
        'delivery_attempts',
        'max_delivery_attempts',
        'last_attempt_at',
        'next_attempt_at',
        'failure_reason',
        'signature_image',
        'delivery_photo',
        'recipient_name',
        'tracking_history',
        'notes',
        'internal_notes',
        'customer_rating',
        'customer_feedback',
        'metadata',
    ];

    protected $casts = [
        'dimensions' => 'json',
        'tracking_history' => 'json',
        'metadata' => 'json',
        'fragile' => 'boolean',
        'requires_signature' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'weight' => 'decimal:3',
        'pickup_lat' => 'decimal:8',
        'pickup_lng' => 'decimal:8',
        'delivery_lat' => 'decimal:8',
        'delivery_lng' => 'decimal:8',
        'scheduled_pickup_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'scheduled_delivery_at' => 'datetime',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'next_attempt_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_DRIVER_ASSIGNED = 'driver_assigned';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Shipping types
    const SHIPPING_STANDARD = 'standard';
    const SHIPPING_EXPRESS = 'express';
    const SHIPPING_SAME_DAY = 'same_day';
    const SHIPPING_OVERNIGHT = 'overnight';

    /**
     * Get the booking associated with this delivery
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the delivery provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class, 'delivery_provider_id');
    }

    /**
     * Get assigned driver
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    /**
     * Get delivery zone
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    /**
     * Get tracking events
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(DeliveryTracking::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get status label in French
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PREPARING => 'En préparation',
            self::STATUS_READY_FOR_PICKUP => 'Prêt pour enlèvement',
            self::STATUS_DRIVER_ASSIGNED => 'Livreur assigné',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_IN_TRANSIT => 'En transit',
            self::STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
            self::STATUS_DELIVERED => 'Livré',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_RETURNED => 'Retourné',
            self::STATUS_CANCELLED => 'Annulé',
            default => 'Inconnu'
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PREPARING => 'indigo',
            self::STATUS_READY_FOR_PICKUP => 'purple',
            self::STATUS_DRIVER_ASSIGNED => 'violet',
            self::STATUS_PICKED_UP => 'cyan',
            self::STATUS_IN_TRANSIT => 'blue',
            self::STATUS_OUT_FOR_DELIVERY => 'teal',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_RETURNED => 'orange',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Basse',
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Normale'
        };
    }

    /**
     * Check status helpers
     */
    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isConfirmed(): bool { return $this->status === self::STATUS_CONFIRMED; }
    public function isPreparing(): bool { return $this->status === self::STATUS_PREPARING; }
    public function isReadyForPickup(): bool { return $this->status === self::STATUS_READY_FOR_PICKUP; }
    public function isPickedUp(): bool { return $this->status === self::STATUS_PICKED_UP; }
    public function isInTransit(): bool { return in_array($this->status, [self::STATUS_IN_TRANSIT, self::STATUS_OUT_FOR_DELIVERY]); }
    public function isDelivered(): bool { return $this->status === self::STATUS_DELIVERED; }
    public function isFailed(): bool { return $this->status === self::STATUS_FAILED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }
    public function isCompleted(): bool { return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_FAILED, self::STATUS_RETURNED, self::STATUS_CANCELLED]); }

    /**
     * Update status with tracking event
     */
    public function updateStatus(string $status, ?string $description = null, ?array $location = null): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => $status]);

        // Create tracking event
        DeliveryTracking::createEvent(
            $this->id,
            $status,
            $this->status_label,
            $description,
            $this->driver_id,
            $location
        );

        // Update timestamps based on status
        match($status) {
            self::STATUS_PICKED_UP => $this->update(['picked_up_at' => now()]),
            self::STATUS_DELIVERED => $this->update(['delivered_at' => now()]),
            self::STATUS_FAILED => $this->update([
                'delivery_attempts' => ($this->delivery_attempts ?? 0) + 1,
                'last_attempt_at' => now(),
            ]),
            default => null
        };
    }

    /**
     * Assign driver
     */
    public function assignDriver(DeliveryDriver $driver): void
    {
        $this->update([
            'driver_id' => $driver->id,
            'status' => self::STATUS_DRIVER_ASSIGNED,
        ]);

        DeliveryTracking::createEvent(
            $this->id,
            DeliveryTracking::STATUS_DRIVER_ASSIGNED,
            'Livreur assigné',
            "Livreur: {$driver->full_name} ({$driver->vehicle_icon})",
            $driver->id
        );
    }

    /**
     * Mark as ready for pickup
     */
    public function markReadyForPickup(): void
    {
        $this->updateStatus(self::STATUS_READY_FOR_PICKUP, 'Colis prêt pour enlèvement par le livreur');
    }

    /**
     * Mark as picked up
     */
    public function markAsPickedUp(?array $location = null): void
    {
        $this->updateStatus(self::STATUS_PICKED_UP, 'Colis récupéré par le livreur', $location);
    }

    /**
     * Mark as in transit
     */
    public function markAsInTransit(?array $location = null): void
    {
        $this->updateStatus(self::STATUS_IN_TRANSIT, 'Colis en route vers la destination', $location);
    }

    /**
     * Mark as out for delivery
     */
    public function markAsOutForDelivery(?array $location = null): void
    {
        $this->updateStatus(self::STATUS_OUT_FOR_DELIVERY, 'Livreur en route pour la livraison', $location);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(?string $signatureImage = null, ?string $photo = null, ?string $recipientName = null): void
    {
        $this->update([
            'signature_image' => $signatureImage,
            'delivery_photo' => $photo,
            'recipient_name' => $recipientName ?? $this->delivery_contact_name,
        ]);

        $this->updateStatus(self::STATUS_DELIVERED, 'Colis livré avec succès');

        // Record completion for driver
        if ($this->driver) {
            $deliveryTime = $this->picked_up_at ? now()->diffInMinutes($this->picked_up_at) : 0;
            $earnings = ($this->shipping_cost ?? 0) * ($this->driver->commission_rate ?? 0.8);
            $this->driver->recordDeliveryCompletion($earnings, $deliveryTime);
        }
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $reason): void
    {
        $this->update(['failure_reason' => $reason]);
        $this->updateStatus(self::STATUS_FAILED, "Échec de livraison: {$reason}");

        if ($this->driver) {
            $this->driver->recordDeliveryFailure();
        }
    }

    /**
     * Schedule next delivery attempt
     */
    public function scheduleRetry(\DateTime $nextAttempt): void
    {
        $this->update([
            'next_attempt_at' => $nextAttempt,
            'status' => self::STATUS_PENDING,
        ]);

        DeliveryTracking::createEvent(
            $this->id,
            'retry_scheduled',
            'Nouvelle tentative programmée',
            "Prochaine tentative: {$nextAttempt->format('d/m/Y H:i')}"
        );
    }

    /**
     * Cancel delivery
     */
    public function cancel(string $reason = 'Annulé par le client'): void
    {
        $this->updateStatus(self::STATUS_CANCELLED, $reason);
    }

    /**
     * Calculate estimated arrival
     */
    public function calculateEstimatedArrival(): ?\DateTime
    {
        if (!$this->picked_up_at) {
            return null;
        }

        $baseMinutes = match($this->shipping_type) {
            self::SHIPPING_SAME_DAY => 120,
            self::SHIPPING_EXPRESS => 60,
            self::SHIPPING_OVERNIGHT => 1440,
            default => 180
        };

        return $this->picked_up_at->copy()->addMinutes($baseMinutes);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        return match($this->status) {
            self::STATUS_PENDING => 5,
            self::STATUS_CONFIRMED => 10,
            self::STATUS_PREPARING => 20,
            self::STATUS_READY_FOR_PICKUP => 30,
            self::STATUS_DRIVER_ASSIGNED => 40,
            self::STATUS_PICKED_UP => 50,
            self::STATUS_IN_TRANSIT => 70,
            self::STATUS_OUT_FOR_DELIVERY => 85,
            self::STATUS_DELIVERED => 100,
            self::STATUS_FAILED, self::STATUS_RETURNED, self::STATUS_CANCELLED => 100,
            default => 0
        };
    }

    /**
     * Generate tracking number
     */
    public static function generateTrackingNumber(): string
    {
        $prefix = 'TP';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -6));
        $check = random_int(10, 99);
        
        return "{$prefix}{$date}{$random}{$check}";
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNumber(): string
    {
        return 'REF-' . now()->format('Ymd') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->tracking_number)) {
                $order->tracking_number = self::generateTrackingNumber();
            }
            if (empty($order->reference_number)) {
                $order->reference_number = self::generateReferenceNumber();
            }
            if (empty($order->status)) {
                $order->status = self::STATUS_PENDING;
            }
            if (empty($order->priority)) {
                $order->priority = self::PRIORITY_NORMAL;
            }
            if (empty($order->delivery_attempts)) {
                $order->delivery_attempts = 0;
            }
            if (empty($order->max_delivery_attempts)) {
                $order->max_delivery_attempts = 3;
            }
        });

        static::created(function ($order) {
            DeliveryTracking::createEvent(
                $order->id,
                DeliveryTracking::STATUS_ORDER_CREATED,
                'Commande de livraison créée',
                "N° de suivi: {$order->tracking_number}"
            );
        });
    }

    /**
     * Scopes
     */
    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }
    public function scopeInTransit($query) { return $query->whereIn('status', [self::STATUS_IN_TRANSIT, self::STATUS_OUT_FOR_DELIVERY]); }
    public function scopeDelivered($query) { return $query->where('status', self::STATUS_DELIVERED); }
    public function scopeUrgent($query) { return $query->where('priority', self::PRIORITY_URGENT); }
    public function scopeToday($query) { return $query->whereDate('scheduled_delivery_at', today()); }
    public function scopeForDriver($query, int $driverId) { return $query->where('driver_id', $driverId); }
}
