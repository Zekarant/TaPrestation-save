<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentSaleReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'urgent_sale_id',
        'user_id',
        'reason',
        'description',
        'status'
    ];

    // Raisons de signalement
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_FAKE = 'fake';
    const REASON_SPAM = 'spam';
    const REASON_FRAUD = 'fraud';
    const REASON_OTHER = 'other';

    // Statuts de signalement
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';

    /**
     * Relation avec la vente urgente
     */
    public function urgentSale()
    {
        return $this->belongsTo(UrgentSale::class);
    }

    /**
     * Relation avec l'utilisateur qui signale
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le libellé de la raison
     */
    public function getReasonLabelAttribute()
    {
        $reasons = [
            self::REASON_INAPPROPRIATE => 'Contenu inapproprié',
            self::REASON_FAKE => 'Produit factice',
            self::REASON_SPAM => 'Spam',
            self::REASON_FRAUD => 'Fraude',
            self::REASON_OTHER => 'Autre'
        ];

        return $reasons[$this->reason] ?? 'Non spécifié';
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_REVIEWED => 'Examiné',
            self::STATUS_RESOLVED => 'Résolu',
            self::STATUS_DISMISSED => 'Rejeté'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }
}