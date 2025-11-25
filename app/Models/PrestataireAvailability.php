<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PrestataireAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'slot_duration' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeForPrestataire($query, $prestataireId)
    {
        return $query->where('prestataire_id', $prestataireId);
    }

    /**
     * Utility methods
     */
    public function getDayName(): string
    {
        $days = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];
        
        return $days[$this->day_of_week] ?? 'Inconnu';
    }

    public function getFormattedTimeRange(): string
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' . Carbon::parse($this->end_time)->format('H:i');
    }

    public function getWorkingMinutes(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    public function getAvailableSlots(): int
    {
        return intval($this->getWorkingMinutes() / $this->slot_duration);
    }

    /**
     * Generate time slots for a specific date
     */
    public function generateSlotsForDate(Carbon $date, ?Service $service = null): array
    {
        if (!$this->is_active || $this->day_of_week !== $date->dayOfWeek) {
            return [];
        }
        
        $slots = [];
        $currentTime = $date->copy()->setTimeFromTimeString($this->start_time);
        $endTime = $date->copy()->setTimeFromTimeString($this->end_time);
        
        while ($currentTime->copy()->addMinutes($this->slot_duration) <= $endTime) {
            $slotEnd = $currentTime->copy()->addMinutes($this->slot_duration);
            
            $slots[] = [
                'start_datetime' => $currentTime->copy(),
                'end_datetime' => $slotEnd,
                'duration' => $this->slot_duration,
                'service_id' => $service?->id,
                // 'price' => $service?->price, // Supprimé pour confidentialité
                'requires_approval' => $service?->requires_approval ?? false,
            ];
            
            $currentTime = $slotEnd;
        }
        
        return $slots;
    }

    /**
     * Check if a time slot conflicts with this availability
     */
    public function conflictsWith(Carbon $startTime, Carbon $endTime): bool
    {
        if ($this->day_of_week !== $startTime->dayOfWeek) {
            return false;
        }
        
        $availabilityStart = $startTime->copy()->setTimeFromTimeString($this->start_time);
        $availabilityEnd = $startTime->copy()->setTimeFromTimeString($this->end_time);
        
        // Check if slot is outside availability hours
        if ($startTime < $availabilityStart || $endTime > $availabilityEnd) {
            return true;
        }
        
        return false;
    }

    /**
     * Static methods
     */
    public static function getDefaultAvailability(): array
    {
        return [
            ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'slot_duration' => 60],
            ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '17:00', 'slot_duration' => 60],
            ['day_of_week' => 3, 'start_time' => '09:00', 'end_time' => '17:00', 'slot_duration' => 60],
            ['day_of_week' => 4, 'start_time' => '09:00', 'end_time' => '17:00', 'slot_duration' => 60],
            ['day_of_week' => 5, 'start_time' => '09:00', 'end_time' => '17:00', 'slot_duration' => 60],
        ];
    }

    public static function createDefaultForPrestataire(Prestataire $prestataire): void
    {
        foreach (static::getDefaultAvailability() as $availability) {
            static::create(array_merge($availability, [
                'prestataire_id' => $prestataire->id,
                'is_active' => true,
            ]));
        }
    }

    public static function getAvailabilityForWeek(Prestataire $prestataire, Carbon $startOfWeek): array
    {
        $availabilities = static::active()
            ->forPrestataire($prestataire->id)
            ->orderBy('day_of_week')
            ->get();
        
        $weekAvailability = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;
            
            $availability = $availabilities->where('day_of_week', $dayOfWeek)->first();
            
            $weekAvailability[$i] = [
                'date' => $date,
                'day_name' => $date->format('l'),
                'availability' => $availability,
                'is_available' => $availability && $availability->is_active,
            ];
        }
        
        return $weekAvailability;
    }
}