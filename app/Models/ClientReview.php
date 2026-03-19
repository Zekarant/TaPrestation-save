<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'prestataire_id',
        'booking_id',
        'rating',
        'comment',
        'punctuality',
        'communication',
        'respect',
        'would_work_again',
    ];

    protected $casts = [
        'rating' => 'integer',
        'would_work_again' => 'boolean',
    ];

    /**
     * Le client qui a été évalué (table clients)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * L'utilisateur du client (via la relation client)
     */
    public function clientUser()
    {
        return $this->hasOneThrough(User::class, Client::class, 'id', 'id', 'client_id', 'user_id');
    }

    /**
     * Le prestataire qui a donné l'avis (table prestataires)
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'prestataire_id');
    }

    /**
     * L'utilisateur du prestataire (via la relation prestataire)
     */
    public function prestataireUser()
    {
        return $this->hasOneThrough(User::class, Prestataire::class, 'id', 'id', 'prestataire_id', 'user_id');
    }

    /**
     * La réservation associée à cet avis
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope pour les avis d'un client spécifique
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope pour les avis donnés par un prestataire
     */
    public function scopeByPrestataire($query, $prestataireId)
    {
        return $query->where('prestataire_id', $prestataireId);
    }

    /**
     * Obtenir le libellé de la ponctualité
     */
    public function getPunctualityLabelAttribute()
    {
        return match($this->punctuality) {
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'average' => 'Moyen',
            'poor' => 'Mauvais',
            default => 'Non évalué'
        };
    }

    /**
     * Obtenir le libellé de la communication
     */
    public function getCommunicationLabelAttribute()
    {
        return match($this->communication) {
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'average' => 'Moyen',
            'poor' => 'Mauvais',
            default => 'Non évalué'
        };
    }

    /**
     * Obtenir le libellé du respect
     */
    public function getRespectLabelAttribute()
    {
        return match($this->respect) {
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'average' => 'Moyen',
            'poor' => 'Mauvais',
            default => 'Non évalué'
        };
    }
}
