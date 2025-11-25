<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'client_id',
        'prestataire_id',
        'service_id',
        'time_slot_id',
        'start_datetime',
        'end_datetime',
        'status',
        'total_price',
        'client_notes',
        'prestataire_notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber();
            }
        });
    }

    /**
     * Generate a unique booking number
     */
    public static function generateBookingNumber(): string
    {
        do {
            $number = 'BK' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('booking_number', $number)->exists());
        
        return $number;
    }

    /**
     * Relationships
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now())
                    ->whereIn('status', ['pending', 'confirmed']);
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
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->start_datetime > now()->addHours(24);
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'confirmed' && 
               $this->end_datetime <= now();
    }

    /**
     * Actions
     */
    public function confirm(): bool
    {
        if (!$this->canBeConfirmed()) {
            return false;
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Update time slot status
        if ($this->timeSlot) {
            $this->timeSlot->update(['status' => 'booked']);
        }

        // Update corresponding availability slots
        $this->updateAvailabilitySlots(true);

        return true;
    }

    public function cancel(?string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Free up the time slot
        if ($this->timeSlot) {
            $this->timeSlot->update(['status' => 'available']);
        }

        // Update corresponding availability slots
        $this->updateAvailabilitySlots(false);

        return true;
    }

    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Update corresponding availability slots when booking status changes
     */
    private function updateAvailabilitySlots(bool $isBooked): void
    {
        if (!$this->service_id || !$this->timeSlot) {
            return;
        }

        // Find availability slots that match the booking time range
        $availabilities = \App\Models\Availability::where('service_id', $this->service_id)
            ->where('start_time', '<=', $this->timeSlot->start_datetime)
            ->where('end_time', '>=', $this->timeSlot->end_datetime)
            ->get();

        foreach ($availabilities as $availability) {
            // Check if the time slot falls within this availability slot
            if ($this->timeSlot->start_datetime >= $availability->start_time && 
                $this->timeSlot->end_datetime <= $availability->end_time) {
                $availability->update(['is_booked' => $isBooked]);
            }
        }
    }

    /**
     * Utility methods
     */
    public function getDurationInMinutes(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Accessor for booking_datetime (backward compatibility)
     * Returns the start_datetime as the booking datetime
     */
    public function getBookingDatetimeAttribute()
    {
        return $this->start_datetime;
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

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'no_show' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'cancelled' => 'Annulé',
            'completed' => 'Terminé',
            'no_show' => 'Absent',
            default => 'Inconnu',
        };
    }
}