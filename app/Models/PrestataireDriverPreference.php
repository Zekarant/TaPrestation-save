<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestataireDriverPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'driver_id',
        'status',
        'priority',
        'notes',
        'block_reason',
        'blocked_at',
    ];

    protected $casts = [
        'priority' => 'integer',
        'blocked_at' => 'datetime',
    ];

    const STATUS_PREFERRED = 'preferred';
    const STATUS_NEUTRAL = 'neutral';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Get the prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the driver
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    /**
     * Scope for preferred drivers
     */
    public function scopePreferred($query)
    {
        return $query->where('status', self::STATUS_PREFERRED);
    }

    /**
     * Scope for blocked drivers
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }

    /**
     * Check if a driver is blocked by prestataire
     */
    public static function isDriverBlocked(int $prestataireId, int $driverId): bool
    {
        return self::where('prestataire_id', $prestataireId)
            ->where('driver_id', $driverId)
            ->where('status', self::STATUS_BLOCKED)
            ->exists();
    }

    /**
     * Check if a driver is preferred by prestataire
     */
    public static function isDriverPreferred(int $prestataireId, int $driverId): bool
    {
        return self::where('prestataire_id', $prestataireId)
            ->where('driver_id', $driverId)
            ->where('status', self::STATUS_PREFERRED)
            ->exists();
    }

    /**
     * Get preferred driver IDs for a prestataire
     */
    public static function getPreferredDriverIds(int $prestataireId): array
    {
        return self::where('prestataire_id', $prestataireId)
            ->where('status', self::STATUS_PREFERRED)
            ->orderBy('priority', 'desc')
            ->pluck('driver_id')
            ->toArray();
    }

    /**
     * Get blocked driver IDs for a prestataire
     */
    public static function getBlockedDriverIds(int $prestataireId): array
    {
        return self::where('prestataire_id', $prestataireId)
            ->where('status', self::STATUS_BLOCKED)
            ->pluck('driver_id')
            ->toArray();
    }

    /**
     * Set driver preference
     */
    public static function setPreference(int $prestataireId, int $driverId, string $status, ?string $notes = null, ?string $blockReason = null): self
    {
        $data = [
            'status' => $status,
            'notes' => $notes,
        ];

        if ($status === self::STATUS_BLOCKED) {
            $data['block_reason'] = $blockReason;
            $data['blocked_at'] = now();
        } else {
            $data['block_reason'] = null;
            $data['blocked_at'] = null;
        }

        return self::updateOrCreate(
            ['prestataire_id' => $prestataireId, 'driver_id' => $driverId],
            $data
        );
    }
}
