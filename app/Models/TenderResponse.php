<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderResponse extends Model
{
    use HasFactory, SoftDeletes;

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        return static::withTrashed()
            ->where($field, $value)
            ->first();
    }

    protected $fillable = [
        'tender_request_id',
        'prestataire_id',
        'cover_letter',
        'proposed_price',
        'price_type',
        'price_details',
        'proposed_start_date',
        'proposed_end_date',
        'estimated_duration_hours',
        'attachments',
        'status',
        'match_score',
        'match_details',
        'viewed_at',
        'responded_at',
        'client_message',
        'rejection_reason',
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date' => 'date',
        'attachments' => 'array',
        'match_details' => 'array',
        'viewed_at' => 'datetime',
        'responded_at' => 'datetime',
        'proposed_price' => 'decimal:2',
    ];

    // ===== RELATIONS =====

    public function tenderRequest()
    {
        return $this->belongsTo(TenderRequest::class);
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    public function scopeByScore($query)
    {
        return $query->orderByDesc('match_score');
    }

    // ===== ACCESSORS =====

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'viewed' => 'Vu',
            'shortlisted' => 'Présélectionné',
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté',
            'withdrawn' => 'Retiré',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'viewed' => 'blue',
            'shortlisted' => 'purple',
            'accepted' => 'green',
            'rejected' => 'red',
            'withdrawn' => 'gray',
            default => 'gray',
        };
    }

    public function getPriceDisplayAttribute(): string
    {
        $price = number_format($this->proposed_price, 2, ',', ' ') . ' €';
        
        return match($this->price_type) {
            'hourly' => $price . '/heure',
            'daily' => $price . '/jour',
            default => $price,
        };
    }

    // ===== METHODS =====

    /**
     * Marquer comme vu
     */
    public function markAsViewed(): void
    {
        if ($this->status === 'pending') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Présélectionner
     */
    public function shortlist(): void
    {
        $this->update([
            'status' => 'shortlisted',
            'responded_at' => now(),
        ]);
    }

    /**
     * Rejeter
     */
    public function reject(?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'responded_at' => now(),
        ]);
    }

    /**
     * Retirer (par le prestataire)
     */
    public function withdraw(): void
    {
        $this->update(['status' => 'withdrawn']);
    }
}
