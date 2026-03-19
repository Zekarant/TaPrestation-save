<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class UrgentSaleReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'urgent_sale_id',
        'client_id',
        'quantity',
        'status',
        'message',
        'seller_notes',
        'client_rating',
        'client_rating_comment',
        'client_rated_at',
        'seller_rating',
        'seller_rating_comment',
        'seller_rated_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'client_rating' => 'integer',
        'seller_rating' => 'integer',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'client_rated_at' => 'datetime',
        'seller_rated_at' => 'datetime',
    ];

    // Statuts possibles
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Relation avec la vente urgente
     */
    public function urgentSale()
    {
        return $this->belongsTo(UrgentSale::class);
    }

    /**
     * Relation avec le client
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Scope pour les réservations en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope pour les réservations confirmées
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope pour les réservations complétées (vendues)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Confirmer la réservation
     */
    public function confirm($notes = null)
    {
        DB::transaction(function () use ($notes) {
            $this->update([
                'status' => self::STATUS_CONFIRMED,
                'seller_notes' => $notes,
                'confirmed_at' => now(),
            ]);

            // Lock pour éviter la sur-réservation concurrente (audit 3.6)
            $sale = \App\Models\UrgentSale::lockForUpdate()->find($this->urgent_sale_id);
            $sale->increment('reserved_quantity', $this->quantity);
        });
    }

    /**
     * Annuler la réservation
     */
    public function cancel()
    {
        DB::transaction(function () {
            if ($this->status === self::STATUS_CONFIRMED) {
                $this->urgentSale->decrement('reserved_quantity', $this->quantity);
            }

            $this->update([
                'status' => self::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
        });
    }

    /**
     * Marquer comme vendu/complété
     */
    public function complete()
    {
        DB::transaction(function () {
            if ($this->status === self::STATUS_CONFIRMED) {
                $this->urgentSale->decrement('reserved_quantity', $this->quantity);
            }
            $this->urgentSale->increment('sold_quantity', $this->quantity);

            $this->update([
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->syncWithInventory();
        });
    }

    /**
     * Synchronise la quantité d'inventaire après une vente complétée
     */
    protected function syncWithInventory(): void
    {
        if (!$this->urgentSale || !$this->urgentSale->inventory_item_id) {
            return;
        }

        $inventoryItem = $this->urgentSale->inventoryItem;
        if (!$inventoryItem) {
            return;
        }

        // Diminuer le stock de l'inventaire du nombre vendu
        $inventoryItem->decrement('quantity', $this->quantity);
    }

    /**
     * Label du statut
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Réservé',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_COMPLETED => 'Vendu',
            default => 'Inconnu',
        };
    }

    /**
     * Couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_COMPLETED => 'green',
            default => 'gray',
        };
    }
}
