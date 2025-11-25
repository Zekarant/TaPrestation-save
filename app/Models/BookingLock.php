<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BookingLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_slot_id',
        'client_id',
        'session_id',
        'locked_at',
        'expires_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForTimeSlot($query, $timeSlotId)
    {
        return $query->where('time_slot_id', $timeSlotId);
    }

    /**
     * Status checks
     */
    public function isActive(): bool
    {
        return $this->expires_at > now();
    }

    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    public function belongsToClient(Client $client): bool
    {
        return $this->client_id === $client->id;
    }

    public function belongsToSession(string $sessionId): bool
    {
        return $this->session_id === $sessionId;
    }

    /**
     * Actions
     */
    public function extend(int $minutes = 15): bool
    {
        if ($this->isActive()) {
            $this->update([
                'expires_at' => now()->addMinutes($minutes)
            ]);
            return true;
        }
        
        return false;
    }

    public function release(): bool
    {
        return $this->delete();
    }

    /**
     * Utility methods
     */
    public function getRemainingTimeInSeconds(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInSeconds($this->expires_at);
    }

    public function getRemainingTimeFormatted(): string
    {
        $seconds = $this->getRemainingTimeInSeconds();
        
        if ($seconds <= 0) {
            return 'Expiré';
        }
        
        $minutes = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes > 0) {
            return "{$minutes}min {$remainingSeconds}s";
        }
        
        return "{$seconds}s";
    }

    /**
     * Static methods
     */
    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }

    public static function lockTimeSlot(
        TimeSlot $timeSlot, 
        Client $client, 
        string $sessionId, 
        int $minutes = 15
    ): ?static {
        // Remove any existing locks for this slot
        static::where('time_slot_id', $timeSlot->id)->delete();
        
        // Create new lock
        return static::create([
            'time_slot_id' => $timeSlot->id,
            'client_id' => $client->id,
            'session_id' => $sessionId,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    public static function releaseForClient(Client $client): int
    {
        return static::where('client_id', $client->id)->delete();
    }

    public static function releaseForSession(string $sessionId): int
    {
        return static::where('session_id', $sessionId)->delete();
    }

    public static function getActiveLocksForClient(Client $client)
    {
        return static::active()
                    ->forClient($client->id)
                    ->with(['timeSlot.service', 'timeSlot.prestataire'])
                    ->get();
    }

    public static function hasActiveLockForTimeSlot(TimeSlot $timeSlot): bool
    {
        return static::active()->forTimeSlot($timeSlot->id)->exists();
    }

    public static function getActiveLockForTimeSlot(TimeSlot $timeSlot): ?static
    {
        return static::active()->forTimeSlot($timeSlot->id)->first();
    }
}