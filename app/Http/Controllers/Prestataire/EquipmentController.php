<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Equipment;

use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\EquipmentReview;
use App\Http\Requests\Prestataire\StoreEquipmentRequest;
use App\Services\Prestataire\EquipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    /**
     * Affiche la liste des équipements du prestataire
     */
        public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        
        $query = $prestataire->equipments()
                            ->with(['category', 'subcategory', 'rentalRequests', 'rentals'])
                            ->withCount(['rentalRequests', 'rentals', 'reviews']);
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category)
                  ->orWhere('subcategory_id', $request->category);
            });
        }
        
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->subcategory);
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'price':
                $query->orderBy('price_per_day', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('average_rating', $sortOrder);
                break;
            case 'rentals':
                $query->orderBy('total_rentals', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $equipment = $query->paginate(12);
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        // Get subcategories based on selected parent category
        $subcategories = collect();
        if ($request->filled('category')) {
            $subcategories = \App\Models\Category::where('parent_id', $request->category)->orderBy('name')->get();
        }

        $rentalRequests = $prestataire->equipmentRentalRequests()
            ->with(['equipment', 'client.user'])
            ->latest()
            ->take(10)
            ->get();

        // Statistiques
        $stats = [
            'total' => $prestataire->equipments()->count(),
            'active' => $prestataire->equipments()->active()->count(),
            'rented' => $prestataire->equipments()->where('status', 'rented')->count(),
            'pending_requests' => $prestataire->equipmentRentalRequests()->pending()->count(),
        ];

        return view('prestataire.equipment.index', compact('equipment', 'categories', 'subcategories', 'stats', 'rentalRequests'));
    }
    
    /**
     * Affiche le formulaire de création d'équipement
     */
    public function create()
    {
        // Check if an equipment was just created to prevent duplicate submissions
        if (session()->has('equipment_just_created')) {
            return redirect()->route('prestataire.equipment.index')
                ->with('info', 'Vous avez déjà créé un équipement. Créez-en un nouveau si nécessaire.');
        }
        
        // Nettoyer les données de session précédentes
        session()->forget('equipment_creation');
        
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.equipment.create', compact('categories'));
    }

    /**
     * Étape 1 : Informations de base
     */
    public function createStep1()
    {
        // Check if an equipment was just created to prevent duplicate submissions
        if (session()->has('equipment_just_created')) {
            return redirect()->route('prestataire.equipment.index')
                ->with('info', 'Vous avez déjà créé un équipement. Créez-en un nouveau si nécessaire.');
        }
        
        // Check if we're trying to access a wizard step for an equipment that's already published
        $equipmentId = session('equipment_creation.equipment_id');
        if ($equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment && $equipment->status === 'active') {
                return redirect()->route('prestataire.equipment.show', $equipment)
                    ->with('info', 'Cet équipement est déjà publié. Vous ne pouvez pas modifier les étapes du wizard.');
            }
        }
        
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.equipment.create-step1', compact('categories'));
    }

    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'technical_specifications' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
        ]);

        session(['equipment_creation.step1' => $validated]);
        return redirect()->route('prestataire.equipment.create.step2');
    }

    /**
     * Étape 2 : Tarifs et conditions
     */
    public function createStep2()
    {
        // Check if we're trying to access a wizard step for an equipment that's already published
        $equipmentId = session('equipment_creation.equipment_id');
        if ($equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment && $equipment->status === 'active') {
                return redirect()->route('prestataire.equipment.show', $equipment)
                    ->with('info', 'Cet équipement est déjà publié. Vous ne pouvez pas modifier les étapes du wizard.');
            }
        }
        
        if (!session('equipment_creation.step1')) {
            return redirect()->route('prestataire.equipment.create.step1')
                           ->with('error', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        return view('prestataire.equipment.create-step2');
    }

    public function storeStep2(Request $request)
    {
        $validated = $request->validate([
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'required|numeric|min:1',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,very_good,good,fair,poor',
            'delivery_included' => 'boolean',
            'license_required' => 'boolean',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            'rental_conditions' => 'nullable|string',
        ]);

        session(['equipment_creation.step2' => $validated]);
        return redirect()->route('prestataire.equipment.create.step3');
    }

    /**
     * Étape 3 : Photos
     */
    public function createStep3()
    {
        // Check if we're trying to access a wizard step for an equipment that's already published
        $equipmentId = session('equipment_creation.equipment_id');
        if ($equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment && $equipment->status === 'active') {
                return redirect()->route('prestataire.equipment.show', $equipment)
                    ->with('info', 'Cet équipement est déjà publié. Vous ne pouvez pas modifier les étapes du wizard.');
            }
        }
        
        if (!session('equipment_creation.step1') || !session('equipment_creation.step2')) {
            return redirect()->route('prestataire.equipment.create.step1')
                           ->with('error', 'Veuillez compléter les étapes précédentes.');
        }
        return view('prestataire.equipment.create-step3');
    }

    public function storeStep3(Request $request)
    {
        $validated = $request->validate([
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Stocker temporairement les images
        $tempImagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $tempImagePaths[] = $photo->store('temp_equipment_photos', 'public');
            }
        }

        // Ne pas stocker l'objet UploadedFile dans la session, seulement les chemins
        session(['equipment_creation.step3' => ['temp_image_paths' => $tempImagePaths]]);
        return redirect()->route('prestataire.equipment.create.step4');
    }

    /**
     * Étape 4 : Localisation et résumé
     */
    public function createStep4()
    {
        // Check if we're trying to access a wizard step for an equipment that's already published
        $equipmentId = session('equipment_creation.equipment_id');
        if ($equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment && $equipment->status === 'active') {
                return redirect()->route('prestataire.equipment.show', $equipment)
                    ->with('info', 'Cet équipement est déjà publié. Vous ne pouvez pas modifier les étapes du wizard.');
            }
        }
        
        if (!session('equipment_creation.step1') || !session('equipment_creation.step2') || !session('equipment_creation.step3')) {
            return redirect()->route('prestataire.equipment.create.step1')
                           ->with('error', 'Veuillez compléter les étapes précédentes.');
        }

        // Récupérer les noms des catégories pour l'affichage
        $step1 = session('equipment_creation.step1');
        $categoryName = null;
        $subcategoryName = null;

        if ($step1['category_id']) {
            $category = \App\Models\Category::find($step1['category_id']);
            $categoryName = $category ? $category->name : null;
        }

        if ($step1['subcategory_id']) {
            $subcategory = \App\Models\Category::find($step1['subcategory_id']);
            $subcategoryName = $subcategory ? $subcategory->name : null;
        }

        return view('prestataire.equipment.create-step4', compact('categoryName', 'subcategoryName'));
    }

    /**
     * Enregistre un nouvel équipement (version multi-étapes)
     */
    public function store(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        if (!$prestataire) {
            return redirect()->back()->with('error', 'Vous devez être un prestataire pour ajouter un équipement.');
        }

        // Validation de l'étape 4
        $step4Validated = $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Récupérer toutes les données des sessions
        $step1 = session('equipment_creation.step1');
        $step2 = session('equipment_creation.step2');
        $step3 = session('equipment_creation.step3');

        if (!$step1 || !$step2 || !$step3) {
            return redirect()->route('prestataire.equipment.create.step1')
                           ->with('error', 'Données de session manquantes. Veuillez recommencer.');
        }

        // Combiner toutes les données
        $allData = array_merge($step1, $step2, $step4Validated);

        // Gérer les images
        if (isset($step3['temp_image_paths']) && !empty($step3['temp_image_paths'])) {
            $finalPaths = [];
            foreach ($step3['temp_image_paths'] as $tempPath) {
                $finalPath = str_replace('temp_equipment_photos/', 'equipment_photos/', $tempPath);
                
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);
                    $finalPaths[] = $finalPath;
                }
            }
            
            // La première photo devient la photo principale
            if (!empty($finalPaths)) {
                $allData['main_photo'] = $finalPaths[0];
                $allData['photos'] = $finalPaths;
            }
        }

        // Générer un slug unique
        $allData['slug'] = $this->generateUniqueSlug($allData['name']);
        $allData['prestataire_id'] = $prestataire->id;
        $allData['status'] = 'active';

        // Créer l'équipement avec gestion des doublons
        try {
            $equipment = Equipment::create($allData);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'equipment_slug_unique') !== false) {
                // Retry with a more unique slug
                $allData['slug'] = $this->generateUniqueSlug($allData['name'] . '-' . time());
                $equipment = Equipment::create($allData);
            } else {
                throw $e; // Re-throw if it's a different error
            }
        }

        // Nettoyer les sessions
        session()->forget(['equipment_creation']);

        // Set a flag to indicate that an equipment was just created
        session()->flash('equipment_just_created', true);

        return redirect()->route('prestataire.equipment.index')
                        ->with('success', 'Équipement ajouté avec succès!');
    }
    
    /**
     * Affiche les détails d'un équipement
     */
    public function show(Equipment $equipment)
    {
        // $this->authorize('view', $equipment);
        
        $equipment->load([
            'category',
            'subcategory',
            'rentalRequests' => function ($query) {
                $query->latest()->with('client.user');
            },
            'rentals' => function ($query) {
                $query->latest()->with('client.user');
            },
            'reviews' => function ($query) {
                $query->approved()->latest()->with('client.user');
            }
        ]);
        
        // Statistiques
        $stats = [
            'total_requests' => $equipment->rentalRequests()->count(),
            'pending_requests' => $equipment->rentalRequests()->pending()->count(),
            'total_rentals' => $equipment->rentals()->count(),
            'active_rentals' => $equipment->rentals()->active()->count(),
            'total_revenue' => $equipment->rentals()->sum('final_amount'),
            'average_rating' => $equipment->reviews()->approved()->avg('overall_rating'),
            'total_reviews' => $equipment->reviews()->approved()->count(),
        ];
        
        return view('prestataire.equipment.show', compact('equipment', 'stats'));
    }
    
    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Equipment $equipment)
    {
        // $this->authorize('update', $equipment);
        
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.equipment.edit', compact('equipment', 'categories'));
    }
    
    /**
     * Met à jour un équipement
     */
    public function update(Request $request, Equipment $equipment)
    {
        // $this->authorize('update', $equipment);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'technical_specifications' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            
            // Détails techniques
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'power_requirements' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            
            // Tarification
            'price_per_hour' => 'nullable|numeric|min:0',
            'daily_rate' => 'required|numeric|min:1',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',

            
            // État et disponibilité
            'condition' => 'required|in:new,excellent,very_good,good,fair,poor',
            'status' => 'nullable|in:active,inactive,maintenance,rented',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            
            // Localisation
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',

            
            // Conditions de location
            'minimum_rental_days' => 'nullable|integer|min:1',
            'maximum_rental_days' => 'nullable|integer|min:1',
            'age_restriction' => 'nullable|integer|min:16|max:99',
            'experience_required' => 'boolean',
            'insurance_required' => 'boolean',
            'license_required' => 'boolean',
            'rental_conditions' => 'nullable|string',
            
            // Instructions et accessoires
            'usage_instructions' => 'nullable|string',
            'safety_instructions' => 'nullable|string',
            'accessories' => 'nullable|string',
        ]);
        
        // Mise à jour du slug si le nom a changé
        if ($equipment->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $equipment->id);
        }
        
        // Gestion de la photo principale
        if ($request->hasFile('main_photo')) {
            // Supprimer l'ancienne photo
            if ($equipment->main_photo) {
                Storage::disk('public')->delete($equipment->main_photo);
            }
            $validated['main_photo'] = $request->file('main_photo')
                ->store('equipment_photos', 'public');
        }
        
        // Gestion des photos de galerie
        if ($request->hasFile('photos')) {
            // Get existing photos
            $existingPhotos = $equipment->photos ?? [];
            
            // Process new photos
            $newPhotos = [];
            foreach ($request->file('photos') as $photo) {
                $newPhotos[] = $photo->store('equipment_photos', 'public');
            }
            
            // Merge existing and new photos (limit to 5 total)
            $allPhotos = array_merge($existingPhotos, $newPhotos);
            $allPhotos = array_slice($allPhotos, 0, 5); // Limit to 5 photos
            
            $validated['photos'] = $allPhotos;
        }
        
        // Les champs sont déjà des strings, pas de conversion nécessaire pour les accessoires
        try {
            $equipment->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error for slug updates
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'equipment_slug_unique') !== false) {
                // Retry with a more unique slug
                if ($equipment->name !== $validated['name']) {
                    $validated['slug'] = $this->generateUniqueSlug($validated['name'] . '-' . time(), $equipment->id);
                }
                $equipment->update($validated);
            } else {
                throw $e; // Re-throw if it's a different error
            }
        }
        
        // Mise à jour des catégories
        $equipment->category_id = $validated['category_id'];
        if (isset($validated['subcategory_id']) && !empty($validated['subcategory_id'])) {
            $equipment->subcategory_id = $validated['subcategory_id'];
        } else {
            $equipment->subcategory_id = null;
        }
        $equipment->save();
        
        return redirect()->route('equipment.show', $equipment)
                        ->with('success', 'Équipement mis à jour avec succès!');
    }
    
    /**
     * Supprime un équipement
     */
    public function destroy(Equipment $equipment)
    {
        $this->authorize('delete', $equipment);
        
        // Vérifier qu'il n'y a pas de locations actives
        if ($equipment->rentals()->active()->exists()) {
            return back()->with('error', 'Impossible de supprimer un équipement avec des locations actives.');
        }
        
        // Supprimer les photos
        if ($equipment->main_photo) {
            Storage::disk('public')->delete($equipment->main_photo);
        }
        
        if ($equipment->photos) {
            foreach ($equipment->photos as $photo) {
                if ($photo && Storage::disk('public')->exists($photo)) {
                    Storage::disk('public')->delete($photo);
                }
            }
        }
        
        $equipment->delete();
        
        return redirect()->route('prestataire.equipment.index')
                        ->with('success', 'Équipement supprimé avec succès!');
    }
    
    /**
     * Active/désactive un équipement
     */
    public function toggleStatus(Equipment $equipment)
    {
        $this->authorize('update', $equipment);
        
        $newStatus = $equipment->status === 'active' ? 'inactive' : 'active';
        $equipment->update(['status' => $newStatus]);
        
        $message = $newStatus === 'active' ? 'Équipement activé' : 'Équipement désactivé';
        
        return back()->with('success', $message);
    }
    
    /**
     * Duplique un équipement
     */
    public function duplicate(Equipment $equipment)
    {
        $this->authorize('view', $equipment);
        
        $newEquipment = $equipment->replicate();
        $newEquipment->name = $equipment->name . ' (Copie)';
        $newEquipment->slug = $this->generateUniqueSlug($newEquipment->name);
        $newEquipment->status = 'inactive';
        $newEquipment->total_rentals = 0;
        $newEquipment->total_reviews = 0;
        $newEquipment->average_rating = 0;
        $newEquipment->view_count = 0;
        $newEquipment->last_rented_at = null;
        
        // Save with error handling for slug duplicates
        try {
            $newEquipment->save();
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'equipment_slug_unique') !== false) {
                // Retry with a more unique slug
                $newEquipment->slug = $this->generateUniqueSlug($newEquipment->name . '-' . time());
                $newEquipment->save();
            } else {
                throw $e; // Re-throw if it's a different error
            }
        }
        
        // Copier les catégories
        $newEquipment->category_id = $equipment->category_id;
        $newEquipment->subcategory_id = $equipment->subcategory_id;
        $newEquipment->save();
        
        return redirect()->route('prestataire.equipment.edit', $newEquipment)
                        ->with('success', 'Équipement dupliqué avec succès!');
    }
    
    /**
     * Génère un slug unique
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $query = Equipment::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                return $slug;
            }
            
            // Add a random component to reduce race conditions
            $randomSuffix = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 3);
            $slug = $originalSlug . '-' . $counter . '-' . $randomSuffix;
            $counter++;
        }
    }
}