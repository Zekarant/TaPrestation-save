<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prestataire\StoreServiceStepRequest;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Models\Category;
use App\Models\Booking;
use App\Traits\ChecksPrestatairePrerequisites;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreServiceRequest;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    use ChecksPrestatairePrerequisites;
    /**
     * Affiche la liste des services du prestataire.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Base query
        $query = $prestataire->services()->with('categories', 'bookings');

        // Parent category filter
        if ($request->filled('parent_category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->parent_category)
                  ->orWhere('categories.parent_id', $request->parent_category);
            });
        }

        // Subcategory filter
        if ($request->filled('subcategory')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->subcategory);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status == 'reservable') {
                $query->where('reservable', true);
            } elseif ($request->status == 'non-reservable') {
                $query->where('reservable', false);
            }
        }

        // Sorting
        $sort = $request->input('sort', 'created_at_desc');
        switch ($sort) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $services = $query->paginate(12);

        // Stats
        $stats = [
            'total' => $prestataire->services()->count(),
            'reservable' => $prestataire->services()->where('reservable', true)->count(),
            'total_bookings' => Booking::whereIn('service_id', $prestataire->services->pluck('id'))->count(),
            'confirmed_bookings' => Booking::whereIn('service_id', $prestataire->services->pluck('id'))->where('status', 'confirmed')->count(),
        ];

        // Get parent categories (categories without parent) - uniquement type service
        $parentCategories = Category::ofTypeService()->whereNull('parent_id')->orderBy('name')->get();
        
        // Get subcategories based on selected parent category
        $subcategories = collect();
        if ($request->filled('parent_category')) {
            $subcategories = Category::ofTypeService()->where('parent_id', $request->parent_category)->orderBy('name')->get();
        }

        return view('prestataire.services.index', [
            'services' => $services,
            'prestataire' => $prestataire,
            'stats' => $stats,
            'parentCategories' => $parentCategories,
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check if a service was just created to prevent duplicate submissions
        if (session()->has('service_just_created')) {
            return redirect()->route('prestataire.services.index')
                ->with('info', 'Vous avez déjà créé un service. Créez-en un nouveau si nécessaire.');
        }
        
        // Nettoyer les données de session précédentes
        session()->forget('service_creation');
        
        $categories = Category::ofTypeService()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        
        return view('prestataire.services.create', [
            'categories' => $categories
        ]);
    }

    /**
     * Affiche l'étape 1 de création de service (Informations de base)
     */
    public function createStep1()
    {
        // Check if a service was just created to prevent duplicate submissions
        if (session()->has('service_just_created')) {
            return redirect()->route('prestataire.services.index')
                ->with('info', 'Vous avez déjà créé un service. Créez-en un nouveau si nécessaire.');
        }

        // Exiger des disponibilités actives pour créer un service (quel que soit le mode)
        $redirect = $this->redirectIfAvailabilityMissing(route('prestataire.services.create.step1'));
        if ($redirect) {
            return $redirect;
        }
        
        return view('prestataire.services.create-step1');
    }

    /**
     * Traite l'étape 1 de création de service
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'delivery_time' => 'nullable|integer'
        ]);

        // Stocker les données dans la session
        session(['service_creation.step1' => [
            'title' => $request->title,
            'description' => $request->description,
            'delivery_time' => $request->delivery_time
        ]]);

        return redirect()->route('prestataire.services.create.step2');
    }

    /**
     * Affiche l'étape 2 de création de service (Prix et Catégorie)
     */
    public function createStep2()
    {
        // Vérifier que l'étape 1 est complétée
        if (!session()->has('service_creation.step1')) {
            return redirect()->route('prestataire.services.create.step1')
                ->with('error', 'Veuillez d\'abord compléter l\'étape 1.');
        }

        // Exiger des disponibilités actives pour créer un service
        $redirect = $this->redirectIfAvailabilityMissing(route('prestataire.services.create.step2'));
        if ($redirect) {
            return $redirect;
        }

        $categories = Category::ofTypeService()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        $prestataire = Auth::user()->prestataire;
        $availabilities = $prestataire->availabilities()->active()->get();
        $equipment = $prestataire->equipments()->where('status', 'active')->get();
        
        return view('prestataire.services.create-step2', [
            'categories' => $categories,
            'availabilities' => $availabilities,
            'equipment' => $equipment
        ]);
    }

    /**
     * Traite l'étape 2 de création de service
     */
    public function storeStep2(StoreServiceStepRequest $request)
    {
        $validated = $request->validated();

        // Checkbox -> bool (robuste)
        $validated['reservable'] = $request->boolean('reservable');
        
        // Pour "sur devis", forcer le prix à null et pas de paiement en ligne requis
        if ($validated['price_type'] === 'quote') {
            $validated['price'] = null;
            $validated['payment_requirement'] = 'none';
            $validated['deposit_percentage'] = 0;
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            $validated['payment_requirement'] = 'none';
            $validated['deposit_percentage'] = 0;
        }

        if (!isset($validated['cancellation_hours'])) {
            $validated['cancellation_hours'] = 24;
        }
        if (!isset($validated['cancellation_refund_percentage'])) {
            $validated['cancellation_refund_percentage'] = 100;
        }

        // Bloquer immédiatement le paiement en ligne si Stripe n'est pas configuré.
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($validated['payment_requirement'] ?? 'none')
            : ($validated['payment_requirement'] ?? 'none');
        if (in_array($paymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = $paymentRequirement === 'full' ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.services.create.step2'));
            if ($redirect) {
                session(['service_creation.step2_draft' => $validated]);
                return $redirect;
            }
        }

        // Exiger des disponibilités actives pour créer un service (même non réservable)
        $redirect = $this->redirectIfAvailabilityMissing(route('prestataire.services.create.step2'));
        if ($redirect) {
            session(['service_creation.step2_draft' => $validated]);
            return $redirect;
        }

        // Vérifier si service réservable et disponibilités configurées
        if (!empty($validated['reservable'])) {
            $redirect = $this->redirectIfAvailabilityRequired(true, route('prestataire.services.create.step2'));
            if ($redirect) {
                session(['service_creation.step2_draft' => $validated]);
                return $redirect;
            }
        }
        
        // Valeur par défaut si location_type non fourni
        if (empty($validated['location_type'])) {
            $validated['location_type'] = 'provider_location';
        }
        
        // Valeurs par défaut pour la durée estimée
        if (empty($validated['estimated_duration'])) {
            $validated['estimated_duration'] = 1;
        }
        if (empty($validated['duration_unit'])) {
            $validated['duration_unit'] = 'hours';
        }
        if (!isset($validated['buffer_time'])) {
            $validated['buffer_time'] = 15;
        }

        $validated['payment_requirement'] = $paymentRequirement;
        if ($paymentRequirement === 'none') {
            $validated['deposit_percentage'] = 0;
        }

        // Stocker en session
        session(['service_creation.step2' => $validated]);

        return redirect()->route('prestataire.services.create.step3');
    }

    /**
     * Affiche l'étape 3 de création de service (Photos)
     */
    public function createStep3()
    {
        // Vérifier que les étapes précédentes sont complétées
        if (!session()->has('service_creation.step1') || !session()->has('service_creation.step2')) {
            return redirect()->route('prestataire.services.create.step1')
                ->with('error', 'Veuillez compléter toutes les étapes précédentes.');
        }

        return view('prestataire.services.create-step3');
    }

    /**
     * Traite l'étape 3 de création de service
     */
    public function storeStep3(Request $request)
    {
        $request->validate([
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,bmp,webp|max:2048'
        ]);

        // Stocker les images temporairement
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $fileName = 'temp_' . time() . '_' . $index . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('temp/services', $fileName, 'public');
                $imagePaths[] = [
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType()
                ];
            }
        }

        session(['service_creation.step3' => [
            'images' => $imagePaths
        ]]);

        return redirect()->route('prestataire.services.create.step4');
    }

    /**
     * Affiche l'étape 4 de création de service (Localisation)
     */
    public function createStep4()
    {
        // Vérifier que toutes les étapes précédentes sont complétées
        if (!session()->has('service_creation.step1') || 
            !session()->has('service_creation.step2') || 
            !session()->has('service_creation.step3')) {
            return redirect()->route('prestataire.services.create.step1')
                ->with('error', 'Veuillez compléter toutes les étapes précédentes.');
        }

        // Récupérer toutes les données pour l'affichage du résumé
        $step1Data = session('service_creation.step1');
        $step2Data = session('service_creation.step2');
        $step3Data = session('service_creation.step3');

        // Récupérer les noms des catégories
        $category = Category::find($step2Data['category_id']);
        $subcategory = !empty($step2Data['subcategory_id']) ? Category::find($step2Data['subcategory_id']) : null;

        // Récupérer le matériel sélectionné
        $selectedEquipment = [];
        if (!empty($step2Data['equipment_ids'])) {
            $selectedEquipment = \App\Models\Equipment::whereIn('id', $step2Data['equipment_ids'])->get();
        }

        return view('prestataire.services.create-step4', [
            'step1Data' => $step1Data,
            'step2Data' => $step2Data,
            'step3Data' => $step3Data,
            'category' => $category,
            'subcategory' => $subcategory,
            'selectedEquipment' => $selectedEquipment
        ]);
    }

    /**
     * Enregistre un nouveau service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        // Vérifier si nous utilisons le processus multi-étapes
        if (session()->has('service_creation')) {
            return $this->storeFromSession($request);
        }

        // Processus de création classique (pour compatibilité)
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'delivery_time' => 'nullable|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,bmp,webp'
        ]);

        $data = $request->only(['title', 'description', 'price', 'delivery_time', 'latitude', 'longitude', 'address']);
        $data['reservable'] = $request->has('reservable');

        $service = $prestataire->services()->create($data);

        // Gérer les catégories
        $categoriesToSync = [];
        
        if ($request->filled('category_id')) {
            $categoriesToSync[] = $request->category_id;
        }
        
        if ($request->filled('subcategory_id')) {
            $categoriesToSync[] = $request->subcategory_id;
        }
        
        if (!empty($categoriesToSync)) {
            $service->categories()->sync($categoriesToSync);
        }

        if ($request->hasFile('images')) {
            $this->handleImageUpload($request->file('images'), $service);
        }

        // Set a flag to indicate that a service was just created
        session()->flash('service_just_created', true);

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service créé avec succès.');
    }

    /**
     * Enregistre un service à partir des données de session (processus multi-étapes)
     */
    private function storeFromSession(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'payment_terms_consent' => 'required|accepted',
        ], [
            'payment_terms_consent.required' => 'Vous devez accepter les conditions de paiement.',
            'payment_terms_consent.accepted' => 'Vous devez accepter les conditions de paiement.',
        ]);

        $prestataire = Auth::user()->prestataire;

        // Récupérer toutes les données des étapes
        $step1Data = session('service_creation.step1');
        $step2Data = session('service_creation.step2');
        $step3Data = session('service_creation.step3');

        // Sécurité : si paiement en ligne, exiger Stripe avant la validation finale
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($step2Data['payment_requirement'] ?? 'none')
            : ($step2Data['payment_requirement'] ?? 'none');
        if (in_array($paymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = $paymentRequirement === 'full' ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.services.create.step4'));
            if ($redirect) {
                return $redirect;
            }
        }

        // Sécurité : exiger des disponibilités actives pour créer un service
        $redirect = $this->redirectIfAvailabilityMissing(route('prestataire.services.create.step4'));
        if ($redirect) {
            return $redirect;
        }

        // Créer le service avec toutes les données
        $serviceData = array_merge($step1Data, $step2Data, [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            // Enregistrer le consentement
            'payment_consent_at' => now(),
            'payment_consent_ip' => $request->ip(),
            'payment_consent_user_agent' => $request->userAgent(),
        ]);

        $serviceData['payment_requirement'] = $paymentRequirement;
        if ($paymentRequirement === 'none') {
            $serviceData['deposit_percentage'] = 0;
        }

        $service = $prestataire->services()->create($serviceData);

        // Gérer les catégories
        $categoriesToSync = [];
        
        if (!empty($step2Data['category_id'])) {
            $categoriesToSync[] = $step2Data['category_id'];
        }
        
        if (!empty($step2Data['subcategory_id'])) {
            $categoriesToSync[] = $step2Data['subcategory_id'];
        }
        
        if (!empty($categoriesToSync)) {
            $service->categories()->sync($categoriesToSync);
        }

        // Gérer le matériel
        if (!empty($step2Data['equipment_ids'])) {
            $service->equipment()->sync($step2Data['equipment_ids']);
        }

        // Gérer les images temporaires
        if (!empty($step3Data['images'])) {
            $this->handleTempImageUpload($step3Data['images'], $service);
        }

        // Nettoyer la session
        session()->forget('service_creation');

        // Set a flag to indicate that a service was just created
        session()->flash('service_just_created', true);

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service créé avec succès.');
    }

    /**
     * Affiche les détails d'un service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function show(Service $service)
    {
        // Vérifier que le service appartient bien au prestataire connecté
        $this->authorize('update', $service);
        
        $service->load(['images', 'categories', 'bookings' => function($query) {
            $query->latest()->take(10);
        }]);

        // Statistiques du service
        $stats = [
            'total_bookings' => $service->bookings()->count(),
            'confirmed_bookings' => $service->bookings()->where('status', 'confirmed')->count(),
            'pending_bookings' => $service->bookings()->where('status', 'pending')->count(),
            'total_revenue' => $service->bookings()->where('status', 'confirmed')->sum('total_amount') ?? 0,
        ];

        return view('prestataire.services.show', [
            'service' => $service,
            'stats' => $stats,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function edit(Service $service)
    {
        // Vérifier que le service appartient bien au prestataire connecté
        $this->authorize('update', $service);
        
        $categories = Category::ofTypeService()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        $selectedCategories = $service->categories->pluck('id')->toArray();

        return view('prestataire.services.edit', [
            'service' => $service->load('images'), // Eager load images
            'categories' => $categories,
            'selectedCategories' => $selectedCategories
        ]);
    }

    /**
     * Met à jour un service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(StoreServiceRequest $request, Service $service)
    {
        $this->authorize('update', $service);

        $data = $request->validated();
        $data['reservable'] = $request->has('reservable');

        if (!array_key_exists('cancellation_hours', $data) || $data['cancellation_hours'] === null) {
            $data['cancellation_hours'] = (int) ($service->cancellation_hours ?? 24);
        }
        if (!array_key_exists('cancellation_refund_percentage', $data) || $data['cancellation_refund_percentage'] === null) {
            $data['cancellation_refund_percentage'] = (float) ($service->cancellation_refund_percentage ?? 100);
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            $data['payment_requirement'] = 'none';
            $data['deposit_percentage'] = 0;
        }

        // Si l'utilisateur active le paiement en ligne, exiger Stripe
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($data['payment_requirement'] ?? ($service->payment_requirement ?? 'none'))
            : ($data['payment_requirement'] ?? ($service->payment_requirement ?? 'none'));
        if (in_array($paymentRequirement, ['deposit', 'full'], true)) {
            $paymentPolicy = $paymentRequirement === 'full' ? 'full_prepay' : 'deposit';
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.services.edit', $service));
            if ($redirect) {
                return $redirect;
            }
        }

        $data['payment_requirement'] = $paymentRequirement;
        if ($paymentRequirement === 'none') {
            $data['deposit_percentage'] = 0;
        }
        
        // Handle quantity field based on price_type
        if (isset($data['price_type']) && !in_array($data['price_type'], ['heure', 'jour'])) {
            $data['quantity'] = null;
        }
        // If price_type is 'heure' or 'jour', keep the quantity value from the request
        // The quantity will be included in $data from $request->validated() if it was sent in the request

        $service->update($data);

        // Gestion des catégories
        if ($request->has('categories')) {
            $service->categories()->sync($request->categories);
        }

        // Gestion des images
        if ($request->hasFile('images')) {
            // Supprimer les images existantes si demandé
            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imageId) {
                    $serviceImage = $service->images()->find($imageId);
                    if ($serviceImage) {
                        Storage::disk('public')->delete($serviceImage->image_path);
                        $serviceImage->delete();
                    }
                }
            }

            // Ajouter de nouvelles images
            $this->handleImageUpload($request->file('images'), $service);
        }

        return redirect()->route('services.show', $service)
            ->with('success', 'Service mis à jour avec succès.');
    }

    /**
     * Supprime un service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        // Supprimer les images associées
        foreach ($service->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $service->delete();

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service supprimé avec succès.');
    }

    /**
     * Gère le téléchargement d'images pour un service.
     *
     * @param  array  $images
     * @param  \App\Models\Service  $service
     * @return void
     */
    private function handleImageUpload($images, $service)
    {
        // Check if we would exceed the 5 image limit
        $currentImageCount = $service->images()->count();
        $newImagesCount = count($images);
        
        // If adding these images would exceed the limit, only add as many as needed
        $allowedNewImages = min($newImagesCount, 5 - $currentImageCount);
        
        if ($allowedNewImages <= 0) {
            return; // Already at the limit
        }
        
        // Only process the allowed number of images
        $imagesToProcess = array_slice($images, 0, $allowedNewImages);
        
        foreach ($imagesToProcess as $image) {
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('services', $fileName, 'public');

            $service->images()->create([
                'image_path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ]);
        }
    }

    /**
     * Gère le téléchargement d'images temporaires pour un service.
     *
     * @param  array  $tempImages
     * @param  \App\Models\Service  $service
     * @return void
     */
    private function handleTempImageUpload($tempImages, $service)
    {
        foreach ($tempImages as $tempImage) {
            // Déplacer l'image temporaire vers le dossier définitif
            $newPath = str_replace('temp/', '', $tempImage['path']);
            Storage::disk('public')->move($tempImage['path'], $newPath);

            $service->images()->create([
                'image_path' => $newPath,
                'original_name' => $tempImage['original_name'],
                'file_size' => $tempImage['size'],
                'mime_type' => $tempImage['mime_type']
            ]);
        }
    }
}
