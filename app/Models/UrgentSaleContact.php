<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentSaleContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'urgent_sale_id',
        'user_id',
        'message',
        'phone',
        'email',
        'status',
        'response',
        'responded_at'
    ];

    protected $dates = [
        'responded_at'
    ];

    // Statuts de contact
    const STATUS_PENDING = 'pending';
    const STATUS_RESPONDED = 'responded';
    const STATUS_CLOSED = 'closed';

    /**
     * Relation avec la vente urgente
     */
    public function urgentSale()
    {
        return $this->belongsTo(UrgentSale::class);
    }

    /**
     * Relation avec l'utilisateur qui contacte
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les contacts en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope pour les contacts récents
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_RESPONDED => 'Répondu',
            self::STATUS_CLOSED => 'Fermé'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Marquer comme répondu
     */
    public function markAsResponded($response = null)
    {
        $this->update([
            'status' => self::STATUS_RESPONDED,
            'response' => $response,
            'responded_at' => now()
        ]);
    }
}