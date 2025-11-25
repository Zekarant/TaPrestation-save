<?php

namespace App\Services\Prestataire;

use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EquipmentService
{
    public function createEquipment(array $validatedData): Equipment
    {
        $prestataire = Auth::user()->prestataire;

        // Préparer les données avec des valeurs par défaut
        $data = [
            'prestataire_id' => $prestataire->id,
            'name' => $validatedData['name'],
            'slug' => $this->generateUniqueSlug($validatedData['name']),
            'description' => $validatedData['description'],
            'technical_specifications' => $validatedData['technical_specifications'] ?? null,
            'price_per_hour' => $validatedData['price_per_hour'] ?? null,
            'price_per_day' => $validatedData['price_per_day'],
            'price_per_week' => $validatedData['price_per_week'] ?? null,
            'price_per_month' => $validatedData['price_per_month'] ?? null,
            'security_deposit' => $validatedData['security_deposit'],
            'condition' => $validatedData['condition'] ?? 'excellent',
            'status' => $validatedData['status'] ?? 'active',
            'delivery_included' => $validatedData['delivery_included'] ?? false,
            'available_from' => $validatedData['available_from'] ?? now()->format('Y-m-d'),
            'available_until' => $validatedData['available_until'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'city' => $validatedData['city'],
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country' => $validatedData['country'],
            'accessories' => $validatedData['accessories'] ?? null,
            'rental_conditions' => $validatedData['rental_conditions'] ?? null,
            'license_required' => $validatedData['license_required'] ?? false,
            'is_available' => $validatedData['is_available'] ?? true,
            // Valeurs par défaut pour les champs non présents dans le formulaire
            'minimum_rental_duration' => 1,
            'delivery_radius' => 50,
        ];

        // Gestion de la photo principale
        if (request()->hasFile('main_photo')) {
            $path = request()->file('main_photo')->store('equipment_photos', 'public');
            $data['main_photo'] = $path;
        }

        // Ajouter les champs de catégorie
        $data['category_id'] = $validatedData['category_id'];
        if (isset($validatedData['subcategory_id']) && !empty($validatedData['subcategory_id'])) {
            $data['subcategory_id'] = $validatedData['subcategory_id'];
        }

        $equipment = Equipment::create($data);

        return $equipment;
    }

    /**
     * Génère un slug unique pour l'équipement
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Vérifie si un slug existe déjà
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = \App\Models\Equipment::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}