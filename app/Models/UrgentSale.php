<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\NormalizesPhotoPaths;

class UrgentSale extends Model
{
    use HasFactory, SoftDeletes, NormalizesPhotoPaths;

    protected $fillable = [
        'prestataire_id',
        'user_id', // Pour les clients qui publient sans être prestataire
        'title',
        'description',
        'price',
        'payment_requirement',
        'condition',
        'category_id',
        'photos',
        'quantity',
        'reserved_quantity',
        'sold_quantity',
        'location',
        'latitude',
        'longitude',
        'status',
        'slug',
        'views_count',
        'contact_count',
        'inventory_item_id',
        // Consentement aux conditions de paiement (vendeur)
        'payment_consent_at',
        'payment_consent_ip',
        'payment_consent_user_agent',
    ];

    protected $casts = [
        'photos' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'sold_quantity' => 'integer',
        'views_count' => 'integer',
        'contact_count' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Résoudre le modèle par ID ou slug
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Si c'est un nombre, chercher par ID
        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        // Sinon, chercher par slug
        return $this->where('slug', $value)->firstOrFail();
    }

    // Statuts possibles
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_REPORTED = 'reported';
    const STATUS_BLOCKED = 'blocked';

    // États possibles
    const CONDITION_NEW = 'new';
    const CONDITION_GOOD = 'good';
    const CONDITION_USED = 'used';
    const CONDITION_FAIR = 'fair';

    // Options de condition pour les formulaires
    const CONDITION_OPTIONS = [
        self::CONDITION_NEW => 'Neuf',
        self::CONDITION_GOOD => 'Bon état',
        self::CONDITION_USED => 'Usagé',
        self::CONDITION_FAIR => 'État correct'
    ];

    /**
     * Get the category that owns the urgent sale.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Relation avec le prestataire
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Relation directe avec l'utilisateur (pour les clients non-prestataires)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le vendeur (prestataire->user ou user direct)
     */
    public function getSeller()
    {
        if ($this->prestataire_id && $this->prestataire) {
            return $this->prestataire->user;
        }
        return $this->user;
    }

    /**
     * Obtenir le nom du vendeur
     */
    public function getSellerNameAttribute()
    {
        $seller = $this->getSeller();
        return $seller ? $seller->name : 'Vendeur inconnu';
    }

    /**
     * Vérifier si c'est une annonce client (non-prestataire)
     */
    public function isClientListing(): bool
    {
        return empty($this->prestataire_id) && !empty($this->user_id);
    }

    /**
     * Relation avec les signalements
     */
    public function reports()
    {
        return $this->hasMany(UrgentSaleReport::class);
    }

    /**
     * Relation avec les contacts
     */
    public function contacts()
    {
        return $this->hasMany(UrgentSaleContact::class);
    }

    /**
     * Relation avec les réservations
     */
    public function reservations()
    {
        return $this->hasMany(UrgentSaleReservation::class);
    }

    /**
     * Réservations en attente
     */
    public function pendingReservations()
    {
        return $this->reservations()->pending();
    }

    /**
     * Réservations confirmées
     */
    public function confirmedReservations()
    {
        return $this->reservations()->confirmed();
    }

    /**
     * Quantité disponible (stock - réservé - vendu)
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity - ($this->reserved_quantity ?? 0) - ($this->sold_quantity ?? 0));
    }

    /**
     * Scope pour les ventes actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pour les ventes récentes
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Générer un slug unique
     */
    public function generateSlug()
    {
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $counter = 1;

        $query = self::where('slug', $slug);

        // Si l'objet existe déjà (mise à jour), exclure son propre ID
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            $query = self::where('slug', $slug);
            if ($this->exists) {
                $query->where('id', '!=', $this->id);
            }
        }

        return $slug;
    }

    /**
     * Boot method pour générer automatiquement le slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($urgentSale) {
            if (empty($urgentSale->slug)) {
                $urgentSale->slug = $urgentSale->generateSlug();
            }
        });

        static::updating(function ($urgentSale) {
            if ($urgentSale->isDirty('title')) {
                $urgentSale->slug = $urgentSale->generateSlug();
            }
        });
    }

    /**
     * Obtenir la première photo
     */
    public function getFirstPhotoAttribute()
    {
        if ($this->photos && is_array($this->photos) && count($this->photos) > 0) {
            return $this->photos[0];
        }
        return null;
    }

    /**
     * Obtenir l'URL de la première photo
     */
    public function getFirstPhotoUrlAttribute()
    {
        if ($this->first_photo) {
            return storage_asset_url($this->first_photo);
        }
        return asset('images/default-product.jpg');
    }

    /**
     * Obtenir le libellé de l'état
     */
    public function getConditionLabelAttribute()
    {
        $conditions = [
                // Constantes du modèle
            self::CONDITION_NEW => 'Neuf',
            self::CONDITION_GOOD => 'Bon état',
            self::CONDITION_USED => 'Usagé',
            self::CONDITION_FAIR => 'État correct',
            // Valeurs utilisées dans les contrôleurs
            'excellent' => 'Excellent',
            'very_good' => 'Très bon état',
            'poor' => 'Mauvais état'
        ];

        return $conditions[$this->condition] ?? 'Non spécifié';
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_SOLD => 'Vendu',
            self::STATUS_WITHDRAWN => 'Retiré',
            self::STATUS_REPORTED => 'Signalé',
            self::STATUS_BLOCKED => 'Bloqué'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Incrémenter le nombre de vues
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Incrémenter le nombre de contacts
     */
    public function incrementContacts()
    {
        $this->increment('contact_count');
    }

    /**
     * Vérifier si la vente peut être modifiée
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_ACTIVE]);
    }

    /**
     * Vérifier si la vente peut être contactée
     */
    public function canBeContacted()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Vérifier si la vente est active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
