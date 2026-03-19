<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_request_id',
        'prestataire_id',
        'type',
        'match_score',
        'match_reasons',
        'is_read',
        'is_interested',
        'read_at',
        'responded_at',
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'is_read' => 'boolean',
        'is_interested' => 'boolean',
        'read_at' => 'datetime',
        'responded_at' => 'datetime',
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

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeInterested($query)
    {
        return $query->where('is_interested', true);
    }

    // ===== ACCESSORS =====

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'auto_match' => 'Matching automatique',
            'manual_invite' => 'Invitation directe',
            'category_match' => 'Catégorie correspondante',
            default => $this->type,
        };
    }

    // ===== METHODS =====

    /**
     * Marquer comme lu
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Répondre à l'invitation
     */
    public function respond(bool $interested): void
    {
        $this->update([
            'is_interested' => $interested,
            'responded_at' => now(),
        ]);
    }
}
