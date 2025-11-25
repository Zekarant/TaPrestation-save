<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestataireVerificationRequest extends Model
{
    protected $fillable = [
        'prestataire_id',
        'status',
        'admin_comment',
        'documents',
        'document_type',
        'submitted_at',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    /**
     * Relation avec le prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Relation avec l'administrateur qui a examiné la demande
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope pour les demandes en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les demandes approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope pour les demandes rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Vérifier si la demande est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la demande est approuvée
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Vérifier si la demande est rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Obtenir les URLs des documents
     */
    public function getDocumentUrlsAttribute(): array
    {
        if (!$this->documents) {
            return [];
        }

        return collect($this->documents)->map(function ($document) {
            return asset('storage/' . $document);
        })->toArray();
    }
}
