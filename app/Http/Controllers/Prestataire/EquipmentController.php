<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Equipment;

use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\EquipmentReview;
use App\Http\Requests\Prestataire\StoreEquipmentRequest;
use App\Services\Prestataire\EquipmentService;
use App\Traits\ChecksPrestatairePrerequisites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    use ChecksPrestatairePrerequisites;
    
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
        $categories = \App\Models\Category::ofTypeEquipment()->whereNull('parent_id')->with('children')->orderBy('name')->get();

        // Get subcategories based on selected parent category
        $subcategories = collect();
        if ($request->filled('category')) {
            $subcategories = \App\Models\Category::ofTypeEquipment()->where('parent_id', $request->category)->orderBy('name')->get();
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
    public function create(Request $request)
    {
        // Check if an equipment was just created to prevent duplicate submissions
        if (session()->has('equipment_just_created')) {
            return redirect()->route('prestataire.equipment.index')
                ->with('info', 'Vous avez déjà créé un équipement. Créez-en un nouveau si nécessaire.');
        }
        
        // Nettoyer les données de session précédentes
        session()->forget('equipment_creation');

        // Si on vient de l'inventaire, on pré-remplit les données
        if ($request->has('inventory_id')) {
            $inventoryItem = \App\Models\InventoryItem::find($request->inventory_id);
            if ($inventoryItem && $inventoryItem->prestataire_id === Auth::user()->prestataire->id) {
                session(['equipment_creation.step1' => [
                    'name' => $inventoryItem->name,
                    'description' => $inventoryItem->description,
                    'inventory_item_id' => $inventoryItem->id
                ]]);
                
                // Rediriger directement vers l'étape 1
                return redirect()->route('prestataire.equipment.create.step1');
            }
        }
        
        $categories = \App\Models\Category::ofTypeEquipment()->whereNull('parent_id')->with('children')->orderBy('name')->get();
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
        
        $categories = \App\Models\Category::ofTypeEquipment()->whereNull('parent_id')->with('children')->orderBy('name')->get();
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

        // Conserver l'ID de l'inventaire s'il existe
        if (session()->has('equipment_creation.step1.inventory_item_id')) {
            $validated['inventory_item_id'] = session('equipment_creation.step1.inventory_item_id');
        }

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
            'price_per_hour' => 'nullable|numeric|min:0|required_without:price_per_day',
            'price_per_day' => 'nullable|numeric|min:0|required_without:price_per_hour',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'payment_requirement' => 'nullable|in:none,deposit,full',
            'deposit_percentage' => 'nullable|numeric|min:1|max:100',
            'auto_accept_on_deposit' => 'nullable|boolean',
            'cancellation_hours' => 'nullable|integer|min:0|max:168',
            'cancellation_refund_percentage' => 'nullable|numeric|min:0|max:100',
            'condition' => 'required|in:excellent,very_good,good,fair,poor',
            'delivery_included' => 'boolean',
            'license_required' => 'boolean',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            'availability_start_time' => 'nullable|date_format:H:i',
            'availability_end_time' => 'nullable|date_format:H:i|after:availability_start_time',
            'rental_conditions' => 'nullable|string',
        ]);

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            $validated['payment_requirement'] = 'none';
            $validated['deposit_percentage'] = 0;
            $validated['auto_accept_on_deposit'] = false;
        }

        // Bloquer immédiatement le paiement en ligne si Stripe n'est pas configuré.
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($validated['payment_requirement'] ?? 'none')
            : ($validated['payment_requirement'] ?? 'none');
        if (in_array($paymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = $paymentRequirement === 'full' ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.equipment.create.step2'));
            if ($redirect) {
                session(['equipment_creation.step2_draft' => $validated]);
                return $redirect;
            }
        }

        // Traiter les checkboxes qui ne sont pas envoyées si non cochées
        $validated['auto_accept_on_deposit'] = $request->has('auto_accept_on_deposit') ? true : false;
        
        // Valeurs par défaut
        $validated['deposit_percentage'] = $validated['deposit_percentage'] ?? 30;
        $validated['cancellation_hours'] = $validated['cancellation_hours'] ?? 24;
        $validated['cancellation_refund_percentage'] = $validated['cancellation_refund_percentage'] ?? 100;
        $validated['payment_requirement'] = $paymentRequirement;
        if ($paymentRequirement === 'none') {
            $validated['deposit_percentage'] = 0;
            $validated['auto_accept_on_deposit'] = false;
        }

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

        // Sécurité : si un paiement en ligne (acompte/prépaiement) est configuré à l'étape 2,
        // exiger un compte Stripe avant la validation finale.
        $step2 = session('equipment_creation.step2');
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($step2['payment_requirement'] ?? 'none')
            : ($step2['payment_requirement'] ?? 'none');
        if (in_array($paymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = $paymentRequirement === 'full' ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.equipment.create.step4'));
            if ($redirect) {
                return $redirect;
            }
        }

        // Validation de l'étape 4
        $step4Validated = $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'payment_terms_consent' => 'required|accepted',
        ], [
            'payment_terms_consent.required' => 'Vous devez accepter les conditions de paiement.',
            'payment_terms_consent.accepted' => 'Vous devez accepter les conditions de paiement.',
        ]);

        // Récupérer toutes les données des sessions
        $step1 = session('equipment_creation.step1');
        $step3 = session('equipment_creation.step3');

        if (!$step1 || !$step2 || !$step3) {
            return redirect()->route('prestataire.equipment.create.step1')
                           ->with('error', 'Données de session manquantes. Veuillez recommencer.');
        }

        // Combiner toutes les données
        $allData = array_merge($step1, $step2, $step4Validated);
        $allData['payment_requirement'] = $paymentRequirement;
        if ($paymentRequirement === 'none') {
            $allData['deposit_percentage'] = 0;
            $allData['auto_accept_on_deposit'] = false;
        }
        
        // Ajouter les données de consentement
        $allData['payment_consent_at'] = now();
        $allData['payment_consent_ip'] = $request->ip();
        $allData['payment_consent_user_agent'] = $request->userAgent();
        
        // Retirer le champ payment_terms_consent qui n'est pas en BDD
        unset($allData['payment_terms_consent']);

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

        // Ajouter l'ID de l'inventaire si présent
        if (isset($step1['inventory_item_id'])) {
            $allData['inventory_item_id'] = $step1['inventory_item_id'];
        }

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
            'prestataire.user',
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
        
        $isOwner = (int) (Auth::user()?->prestataire?->id ?? 0) === (int) $equipment->prestataire_id;

        return view('equipment.show', compact('equipment', 'stats', 'isOwner'));
    }
    
    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Equipment $equipment)
    {
        // $this->authorize('update', $equipment);
        
        $categories = \App\Models\Category::ofTypeEquipment()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.equipment.edit', compact('equipment', 'categories'));
    }
    
    /**
     * Met à jour un équipement
     */
    public function update(Request $request, Equipment $equipment)
    {
        // $this->authorize('update', $equipment);

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            $request->merge([
                'payment_requirement' => 'none',
                'deposit_percentage' => 0,
                'auto_accept_on_deposit' => false,
            ]);
        }

        // Si l'utilisateur active le paiement en ligne, exiger Stripe
        $requestedPaymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($request->input('payment_requirement', $equipment->payment_requirement ?? 'none'))
            : $request->input('payment_requirement', $equipment->payment_requirement ?? 'none');
        if (in_array($requestedPaymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = ($requestedPaymentRequirement === 'full') ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.equipment.edit', $equipment));
            if ($redirect) {
                return $redirect;
            }
        }
        
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
            'price_per_hour' => 'nullable|numeric|min:0|required_without:price_per_day',
            'price_per_day' => 'nullable|numeric|min:0|required_without:price_per_hour',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'payment_requirement' => 'nullable|in:none,deposit,full',

            
            // État et disponibilité
            'condition' => 'required|in:new,excellent,very_good,good,fair,poor',
            'status' => 'nullable|in:active,inactive,maintenance,rented',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            'availability_start_time' => 'nullable|date_format:H:i',
            'availability_end_time' => 'nullable|date_format:H:i|after:availability_start_time',
            
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

        $validated['payment_requirement'] = $requestedPaymentRequirement;
        if ($requestedPaymentRequirement === 'none') {
            $validated['deposit_percentage'] = 0;
            $validated['auto_accept_on_deposit'] = false;
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

        // Réconciliation: certaines locations terminées peuvent rester dans un statut "actif"
        // si la tâche planifiée n'a pas tourné. On les bascule en "returned" pour éviter un faux blocage.
        $staleActiveStatuses = [
            EquipmentRental::STATUS_CONFIRMED,
            EquipmentRental::STATUS_IN_PREPARATION,
            EquipmentRental::STATUS_READY_FOR_DELIVERY,
            EquipmentRental::STATUS_DELIVERED,
            EquipmentRental::STATUS_IN_USE,
            EquipmentRental::STATUS_READY_FOR_PICKUP,
        ];

        $equipment->rentals()
            ->whereIn('status', $staleActiveStatuses)
            ->whereDate('end_date', '<', now()->toDateString())
            ->update([
                'status' => EquipmentRental::STATUS_RETURNED,
                'actual_end_datetime' => now(),
            ]);

        // Vérifier qu'il n'y a pas de locations encore actives après réconciliation
        $blockingRentals = $equipment->rentals()
            ->active()
            ->orderBy('start_date')
            ->get();

        if ($blockingRentals->isNotEmpty()) {
            $blockingRentalsDetails = $blockingRentals->map(function (EquipmentRental $rental) {
                return [
                    'rental_number' => $rental->rental_number ?: ('LOC-' . $rental->id),
                    'status' => $rental->formatted_status,
                    'status_raw' => $rental->status,
                    'start_date' => $rental->start_date ? $rental->start_date->format('d/m/Y') : null,
                    'end_date' => $rental->end_date ? $rental->end_date->format('d/m/Y') : null,
                    'request_id' => $rental->rental_request_id,
                    'show_url' => $rental->rental_request_id
                        ? route('prestataire.equipment-rental-requests.show', $rental->rental_request_id)
                        : null,
                ];
            })->values()->all();

            $count = count($blockingRentalsDetails);

            return back()
                ->with('error', "Suppression impossible : cet équipement a {$count} location(s) active(s) en cours.")
                ->with('delete_blocked_rentals', $blockingRentalsDetails);
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
