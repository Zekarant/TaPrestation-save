<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EquipmentCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'parent_id',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
        'image',
        'equipment_count',
        'featured'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'equipment_count' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * Boot method pour générer automatiquement le slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }

    /**
     * Relation avec les équipements
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_category_equipment')
                    ->withTimestamps();
    }

    /**
     * Relation avec la catégorie parente
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'parent_id');
    }

    /**
     * Relation avec les sous-catégories
     */
    public function children(): HasMany
    {
        return $this->hasMany(EquipmentCategory::class, 'parent_id')
                    ->orderBy('name');
    }

    /**
     * Relation récursive avec toutes les sous-catégories
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope pour les catégories actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les catégories principales (sans parent)
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope pour les sous-catégories
     */
    public function scopeSubCategories($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope pour les catégories en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
    }

    /**
     * Vérifie si la catégorie est une catégorie principale
     */
    public function isMainCategory()
    {
        return is_null($this->parent_id);
    }

    /**
     * Vérifie si la catégorie a des sous-catégories
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Vérifie si la catégorie a des équipements
     */
    public function hasEquipment()
    {
        return $this->equipment()->count() > 0;
    }

    /**
     * Obtient le chemin complet de la catégorie
     */
    public function getFullPathAttribute()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $path->implode(' > ');
    }

    /**
     * Obtient l'URL de la catégorie
     */
    public function getUrlAttribute()
    {
        return route('equipment.category', $this->slug);
    }

    /**
     * Obtient le nombre d'équipements actifs
     */
    public function getActiveEquipmentCountAttribute()
    {
        return $this->equipment()->active()->count();
    }

    /**
     * Obtient le nombre d'équipements disponibles
     */
    public function getAvailableEquipmentCountAttribute()
    {
        return $this->equipment()->available()->count();
    }

    /**
     * Met à jour le compteur d'équipements
     */
    public function updateEquipmentCount()
    {
        $this->update([
            'equipment_count' => $this->equipment()->count()
        ]);
    }

    /**
     * Obtient toutes les catégories descendantes (enfants et petits-enfants)
     */
    public function getAllDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Obtient tous les ancêtres de la catégorie
     */
    public function getAllAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    /**
     * Obtient le niveau de profondeur de la catégorie
     */
    public function getDepthAttribute()
    {
        return $this->getAllAncestors()->count();
    }

    /**
     * Vérifie si la catégorie peut être supprimée
     */
    public function canBeDeleted()
    {
        return !$this->hasEquipment() && !$this->hasChildren();
    }

    /**
     * Obtient l'icône avec une icône par défaut
     */
    public function getIconWithDefaultAttribute()
    {
        return $this->icon ?: 'cube';
    }

    /**
     * Obtient la couleur avec une couleur par défaut
     */
    public function getColorWithDefaultAttribute()
    {
        return $this->color ?: '#6B7280';
    }

    /**
     * Obtient l'image avec une image par défaut
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/default-category.svg');
    }

    /**
     * Scope pour obtenir les catégories avec leurs équipements
     */
    public function scopeWithEquipmentCount($query)
    {
        return $query->withCount(['equipment' => function ($query) {
            $query->active();
        }]);
    }

    /**
     * Scope pour obtenir les catégories populaires
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->withEquipmentCount()
                    ->orderBy('equipment_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Obtient les catégories recommandées basées sur la popularité
     */
    public static function getRecommended($limit = 6)
    {
        return static::active()
                    ->featured()
                    ->withEquipmentCount()
                    ->orderBy('equipment_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtient l'arbre des catégories
     */
    public static function getTree()
    {
        return static::active()
                    ->main()
                    ->with(['allChildren' => function ($query) {
                        $query->active()->ordered();
                    }])
                    ->ordered()
                    ->get();
    }

    /**
     * Recherche dans les catégories
     */
    public static function searchCategories($search, $limit = 10)
    {
        return static::active()
                    ->search($search)
                    ->withEquipmentCount()
                    ->orderBy('equipment_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtient les statistiques de la catégorie
     */
    public function getStats()
    {
        $totalEquipment = $this->equipment()->count();
        $activeEquipment = $this->equipment()->active()->count();
        $availableEquipment = $this->equipment()->available()->count();
        $avgRating = $this->equipment()->avg('average_rating') ?: 0;
        
        return [
            'total_equipment' => $totalEquipment,
            'active_equipment' => $activeEquipment,
            'available_equipment' => $availableEquipment,
            'average_rating' => round($avgRating, 1),
            'subcategories_count' => $this->children()->count()
        ];
    }

    /**
     * Formate le nom pour l'affichage
     */
    public function getDisplayNameAttribute()
    {
        if ($this->isMainCategory()) {
            return $this->name;
        }
        
        return $this->parent->name . ' > ' . $this->name;
    }
}