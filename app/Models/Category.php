<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    /**
     * The services that belong to the category.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_category');
    }

    /**
     * Équipements ayant cette catégorie comme catégorie principale
     */
    public function equipmentsAsCategory()
    {
        return $this->hasMany(\App\Models\Equipment::class, 'category_id');
    }

    /**
     * Équipements ayant cette catégorie comme sous-catégorie
     */
    public function equipmentsAsSubcategory()
    {
        return $this->hasMany(\App\Models\Equipment::class, 'subcategory_id');
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
     * Obtient le nombre total d'équipements (catégorie + sous-catégorie)
     */
    public function getTotalEquipmentCountAttribute()
    {
        return $this->equipmentsAsCategory()->count() + $this->equipmentsAsSubcategory()->count();
    }

    /**
     * Obtient les statistiques de la catégorie
     */
    public function getStats()
    {
        $equipmentCount = $this->getTotalEquipmentCountAttribute();
        $activeEquipmentCount = $this->equipmentsAsCategory()->where('status', 'active')->count() +
                               $this->equipmentsAsSubcategory()->where('status', 'active')->count();
        
        return [
            'equipment_count' => $equipmentCount,
            'active_equipment_count' => $activeEquipmentCount,
            'subcategories_count' => $this->children()->count()
        ];
    }
}