<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'service_id',
        'start_datetime',
        'end_datetime',
        'status',
        // 'price', // Supprimé pour confidentialité
        'requires_approval',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        // 'price' => 'decimal:2', // Supprimé pour confidentialité
        'requires_approval' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function bookingLock(): HasOne
    {
        return $this->hasOne(BookingLock::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('start_datetime', '>', now());
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_datetime', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeForPrestataire($query, $prestataireId)
    {
        return $query->where('prestataire_id', $prestataireId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_datetime', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }

    /**
     * Status checks
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && 
               $this->start_datetime > now() &&
               !$this->hasActiveLock();
    }

    public function isBooked(): bool
    {
        return $this->status === 'booked';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function hasActiveLock(): bool
    {
        return $this->bookingLock()->where('expires_at', '>', now())->exists();
    }

    public function canBeBooked(): bool
    {
        return $this->isAvailable() && 
               $this->start_datetime > now()->addMinutes(30); // Minimum 30 minutes advance booking
    }

    /**
     * Actions
     */
    public function lock(Client $client, string $sessionId, int $minutesToLock = 15): ?BookingLock
    {
        if (!$this->canBeBooked()) {
            return null;
        }

        // Remove any existing locks for this slot
        $this->bookingLock()->delete();

        return BookingLock::create([
            'time_slot_id' => $this->id,
            'client_id' => $client->id,
            'session_id' => $sessionId,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes($minutesToLock),
        ]);
    }

    public function unlock(): bool
    {
        return $this->bookingLock()->delete() > 0;
    }
    
    public function lockForBooking(int $bookingId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        // Update status to booked
        $this->update(['status' => 'booked']);
        
        return true;
    }

    public function book(Client $client, array $bookingData = []): ?Booking
    {
        if (!$this->canBeBooked()) {
            return null;
        }

        // Create the booking
        $booking = Booking::create(array_merge([
            'client_id' => $client->id,
            'prestataire_id' => $this->prestataire_id,
            'service_id' => $this->service_id,
            'time_slot_id' => $this->id,
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'status' => $this->requires_approval ? 'pending' : 'confirmed',
            // 'total_price' => $this->price ?? $this->service->price ?? 0, // Supprimé pour confidentialité
            'total_price' => 0, // Valeur par défaut
        ], $bookingData));

        // Update slot status
        $this->update([
            'status' => $this->requires_approval ? 'pending' : 'booked'
        ]);

        // Remove any locks
        $this->unlock();

        return $booking;
    }

    public function free(): bool
    {
        if ($this->isBooked() || $this->isPending()) {
            $this->update(['status' => 'available']);
            $this->unlock();
            return true;
        }
        
        return false;
    }
    
    public function releaseLock(): bool
    {
        if ($this->isBooked() || $this->isPending()) {
            $this->update(['status' => 'available']);
            $this->unlock();
            return true;
        }
        
        return false;
    }

    public function block(): bool
    {
        if ($this->isAvailable()) {
            $this->update(['status' => 'blocked']);
            return true;
        }
        
        return false;
    }

    /**
     * Utility methods
     */
    public function getDurationInMinutes(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    public function getDurationFormatted(): string
    {
        $minutes = $this->getDurationInMinutes();
        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours > 0) {
            return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}min" : "{$hours}h";
        }
        
        return "{$minutes}min";
    }

    public function getFormattedTimeRange(): string
    {
        return $this->start_datetime->format('H:i') . ' - ' . $this->end_datetime->format('H:i');
    }

    public function getFormattedDate(): string
    {
        return $this->start_datetime->format('d/m/Y');
    }

    public function getFormattedDateTime(): string
    {
        return $this->start_datetime->format('d/m/Y à H:i');
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'available' => 'bg-green-100 text-green-800',
            'booked' => 'bg-blue-100 text-blue-800',
            'blocked' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Disponible',
            'booked' => 'Réservé',
            'blocked' => 'Bloqué',
            'pending' => 'En attente',
            default => 'Inconnu',
        };
    }

    /**
     * Static methods for slot generation
     */
    public static function generateSlotsForDay(
        Prestataire $prestataire, 
        Service $service, 
        Carbon $date, 
        array $availability
    ): array {
        $slots = [];
        $slotDuration = $availability['slot_duration'] ?? 60; // minutes
        $startTime = Carbon::parse($availability['start_time']);
        $endTime = Carbon::parse($availability['end_time']);
        
        $currentSlot = $date->copy()->setTimeFrom($startTime);
        $dayEnd = $date->copy()->setTimeFrom($endTime);
        
        while ($currentSlot->addMinutes($slotDuration) <= $dayEnd) {
            $slotEnd = $currentSlot->copy()->addMinutes($slotDuration);
            
            $slots[] = static::create([
                'prestataire_id' => $prestataire->id,
                'service_id' => $service->id,
                'start_datetime' => $currentSlot->copy(),
                'end_datetime' => $slotEnd,
                'status' => 'available',
                // 'price' => $service->price, // Supprimé pour confidentialité
                'requires_approval' => $service->requires_approval ?? false,
            ]);
            
            $currentSlot = $slotEnd;
        }
        
        return $slots;
    }
}