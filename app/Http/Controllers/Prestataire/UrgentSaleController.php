<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use App\Models\UrgentSaleContact;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Traits\ChecksPrestatairePrerequisites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UrgentSaleController extends Controller
{
    use ChecksPrestatairePrerequisites;

    /**
     * Afficher la liste des ventes urgentes du prestataire
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $query = $prestataire->urgentSales()
                            ->with(['category', 'contacts'])
                            ->withCount(['contacts', 'reports']);
        
        // Filtres
        if ($request->filled('category')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category)
                  ->orWhereHas('category', function ($q2) use ($request) {
                      $q2->where('parent_id', $request->category);
                  });
            });
        }
        
        if ($request->filled('subcategory')) {
            $query->where('category_id', $request->subcategory);
        }
        
        $urgentSales = $query->latest()->paginate(12);
        
        // Get categories for filter - catégories ventes uniquement
        $categories = Category::ofTypeSale()->whereNull('parent_id')
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get();
        
        // Get subcategories based on selected parent category
        $subcategories = collect();
        if ($request->filled('category')) {
            $subcategories = Category::where('parent_id', $request->category)
                                   ->where('is_active', true)
                                   ->orderBy('name')
                                   ->get();
        }
        
        // Statistiques
        $stats = [
            'total' => $prestataire->urgentSales()->count(),
            'active' => $prestataire->urgentSales()->where('status', 'active')->count(),
            'sold' => $prestataire->urgentSales()->where('status', 'sold')->count(),
            'inactive' => $prestataire->urgentSales()->where('status', 'inactive')->count(),
            'total_views' => $prestataire->urgentSales()->sum('views_count'),
            'total_contacts' => $prestataire->urgentSales()->withCount('contacts')->get()->sum('contacts_count'),
        ];
        
        return view('prestataire.urgent-sales.index', compact('urgentSales', 'categories', 'subcategories', 'stats'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        // Nettoyer les données de session précédentes
        session()->forget('urgent_sale_creation');

        $categories = Category::ofTypeSale()->whereNull('parent_id')
                            ->where('is_active', true)
                            ->with(['children' => function($query) {
                                $query->where('is_active', true)->orderBy('name');
                            }])
                            ->orderBy('name')
                            ->get();
        
        return view('prestataire.urgent-sales.create', compact('categories'));
    }
    
    /**
     * Étape 1 : Informations de base
     */
    public function createStep1()
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        $categories = Category::ofTypeSale()->whereNull('parent_id')
                            ->where('is_active', true)
                            ->with(['children' => function($query) {
                                $query->where('is_active', true)->orderBy('name');
                            }])
                            ->orderBy('name')
                            ->get();
        
        return view('prestataire.urgent-sales.steps.step1', compact('categories'));
    }
    
    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,very_good,good,fair,poor',
            'parent_category_id' => 'required|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'payment_requirement' => 'nullable|in:none,full',
        ]);

        // Default payment_requirement to 'none' if not provided
        $validated['payment_requirement'] = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($validated['payment_requirement'] ?? 'none')
            : ($validated['payment_requirement'] ?? 'none');

        // Bloquer immédiatement le paiement en ligne si Stripe n'est pas configuré.
        if (($validated['payment_requirement'] ?? 'none') === 'full') {
            $redirect = $this->redirectIfPaymentRequired('full_prepay', route('prestataire.urgent-sales.create.step1'));
            if ($redirect) {
                session(['urgent_sale_creation.step1' => $validated]);
                return $redirect;
            }
        }

        // Preserve inventory_item_id if it exists in the session
        $existingData = session('urgent_sale_creation.step1', []);
        if (isset($existingData['inventory_item_id'])) {
            $validated['inventory_item_id'] = $existingData['inventory_item_id'];
        }

        session(['urgent_sale_creation.step1' => $validated]);
        return redirect()->route('prestataire.urgent-sales.create.step2');
    }
    
    /**
     * Étape 2 : Localisation
     */
    public function createStep2()
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        // Vérifier que l'étape 1 est complétée
        if (!session()->has('urgent_sale_creation.step1')) {
            return redirect()->route('prestataire.urgent-sales.create.step1')
                ->with('error', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
        return view('prestataire.urgent-sales.steps.step2');
    }
    
    public function storeStep2(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        session(['urgent_sale_creation.step2' => $validated]);
        return redirect()->route('prestataire.urgent-sales.create.step3');
    }
    
    /**
     * Étape 3 : Description et photos
     */
    public function createStep3()
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        // Vérifier que les étapes précédentes sont complétées
        if (!session()->has('urgent_sale_creation.step1') || !session()->has('urgent_sale_creation.step2')) {
            return redirect()->route('prestataire.urgent-sales.create.step1')
                ->with('error', 'Veuillez compléter les étapes précédentes.');
        }
        
        return view('prestataire.urgent-sales.steps.step3');
    }
    
    public function storeStep3(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Stocker temporairement les images
        $tempImagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $tempImagePaths[] = $photo->store('temp_urgent_sales_photos', 'public');
            }
        }

        session(['urgent_sale_creation.step3' => [
            'description' => $validated['description'],
            'temp_image_paths' => $tempImagePaths
        ]]);
        
        return redirect()->route('prestataire.urgent-sales.create.step4');
    }
    
    /**
     * Étape 4 : Révision et publication
     */
    public function createStep4()
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        // Vérifier que toutes les étapes précédentes sont complétées
        if (!session()->has('urgent_sale_creation.step1') || 
            !session()->has('urgent_sale_creation.step2') || 
            !session()->has('urgent_sale_creation.step3')) {
            return redirect()->route('prestataire.urgent-sales.create.step1')
                ->with('error', 'Veuillez compléter toutes les étapes précédentes.');
        }

        // Récupérer toutes les données pour l'affichage du résumé
        $step1Data = session('urgent_sale_creation.step1');
        $step2Data = session('urgent_sale_creation.step2');
        $step3Data = session('urgent_sale_creation.step3');

        // Récupérer les noms des catégories
        $category = Category::find($step1Data['parent_category_id']);
        $subcategory = $step1Data['category_id'] ? Category::find($step1Data['category_id']) : null;

        return view('prestataire.urgent-sales.steps.step4', [
            'step1Data' => $step1Data,
            'step2Data' => $step2Data,
            'step3Data' => $step3Data,
            'category' => $category,
            'subcategory' => $subcategory
        ]);
    }
    
    /**
     * Enregistrer une nouvelle vente urgente
     */
    public function store(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        // Vérifier si nous utilisons le processus multi-étapes
        if (session()->has('urgent_sale_creation')) {
            return $this->storeFromSession($request);
        }

        // Processus de création classique (pour compatibilité)
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,very_good,good,fair,poor',
            'parent_category_id' => 'required|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'payment_requirement' => 'nullable|in:none,full',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($request->input('payment_requirement', 'none'))
            : $request->input('payment_requirement', 'none');
        if ($paymentRequirement === 'full') {
            $redirect = $this->redirectIfPaymentRequired('full_prepay', route('prestataire.urgent-sales.create'));
            if ($redirect) {
                return $redirect;
            }
        }
        
        // Créer automatiquement un article d'inventaire lié (optionnel si table n'existe pas)
        $inventoryItem = null;
        $inventoryItemId = null;
        try {
            $inventoryItem = InventoryItem::create([
                'user_id' => Auth::id(),
                'name' => $request->title,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'initial_quantity' => $request->quantity,
                'selling_price' => $request->price,
                'category' => $request->category_id ?: $request->parent_category_id,
                'condition' => $request->condition,
                'location' => $request->address,
                'status' => 'available',
                'metadata' => json_encode([
                    'source' => 'urgent_sale',
                    'condition' => $request->condition,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]),
            ]);
            $inventoryItemId = $inventoryItem->id;
        } catch (\Exception $e) {
            // Table inventory n'existe pas encore, on continue sans
        }
        
        $urgentSale = new UrgentSale();
        $urgentSale->prestataire_id = $prestataire->id;
        $urgentSale->inventory_item_id = $inventoryItemId;
        $urgentSale->title = $request->title;
        $urgentSale->slug = Str::slug($request->title . '-' . time());
        $urgentSale->description = $request->description;
        $urgentSale->price = $request->price;
        $urgentSale->condition = $request->condition;
        $urgentSale->category_id = $request->category_id ?: $request->parent_category_id;
        $urgentSale->quantity = $request->quantity;
        $urgentSale->payment_requirement = $paymentRequirement;
        $urgentSale->location = $request->address;
        $urgentSale->latitude = $request->latitude;
        $urgentSale->longitude = $request->longitude;
        $urgentSale->status = 'active';
        
        // Gestion des photos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('urgent_sales_photos', 'public');
                $photos[] = $path;
            }
            $urgentSale->photos = json_encode($photos);
            
            // Mettre à jour l'inventaire avec les photos si disponible
            if ($inventoryItem) {
                try {
                    $inventoryItem->update(['photos' => json_encode($photos)]);
                } catch (\Exception $e) {
                    // Ignorer si erreur
                }
            }
        }
        
        $urgentSale->save();
        
        // Mettre à jour l'inventaire avec l'ID de la vente urgente (liaison bidirectionnelle)
        if ($inventoryItem) {
            try {
                $inventoryItem->update(['urgent_sale_id' => $urgentSale->id]);
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
        
        // Set a flag to indicate that an urgent sale was just created
        session()->flash('urgent_sale_just_created', true);
        
        return redirect()->route('prestataire.urgent-sales.index')
                        ->with('success', 'Vente urgente créée avec succès et synchronisée avec l\'inventaire!');
    }
    
    /**
     * Enregistre une vente urgente à partir des données de session (processus multi-étapes)
     */
    private function storeFromSession(Request $request)
    {
        // Récupérer les données de l'étape 1 pour vérifier le mode de paiement
        $step1Data = session('urgent_sale_creation.step1');
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($step1Data['payment_requirement'] ?? 'none')
            : ($step1Data['payment_requirement'] ?? 'none');

        // Sécurité : si paiement en ligne, exiger Stripe avant validation finale
        if ($paymentRequirement === 'full') {
            $redirect = $this->redirectIfPaymentRequired('full_prepay', route('prestataire.urgent-sales.create.step4'));
            if ($redirect) {
                return $redirect;
            }
        }
        
        // Règles de validation de base
        $rules = [
            'terms' => 'accepted',
            'contact' => 'accepted',
        ];
        
        // Si paiement en ligne requis, exiger le consentement aux conditions de paiement
        if ($paymentRequirement === 'full') {
            $rules['payment_terms_consent'] = 'required|accepted';
        }
        
        $request->validate($rules, [
            'payment_terms_consent.required' => 'Vous devez accepter les conditions de paiement.',
            'payment_terms_consent.accepted' => 'Vous devez accepter les conditions de paiement.',
        ]);

        $prestataire = Auth::user()->prestataire;

        // Récupérer toutes les données des étapes
        $step2Data = session('urgent_sale_creation.step2');
        $step3Data = session('urgent_sale_creation.step3');

        // Créer la vente urgente avec toutes les données
        $urgentSaleData = array_merge($step1Data, $step2Data, [
            'description' => $step3Data['description'],
        ]);
        
        // Ajouter les données de consentement si paiement en ligne
        if ($paymentRequirement === 'full') {
            $urgentSaleData['payment_consent_at'] = now();
            $urgentSaleData['payment_consent_ip'] = $request->ip();
            $urgentSaleData['payment_consent_user_agent'] = $request->userAgent();
        }

        // Créer automatiquement un article d'inventaire lié (sauf si déjà lié)
        $inventoryItemId = $step1Data['inventory_item_id'] ?? null;
        $inventoryItem = null;
        
        if (!$inventoryItemId) {
            try {
                $inventoryItem = InventoryItem::create([
                    'user_id' => Auth::id(),
                    'name' => $urgentSaleData['title'],
                    'description' => $urgentSaleData['description'],
                    'quantity' => $urgentSaleData['quantity'],
                    'initial_quantity' => $urgentSaleData['quantity'],
                    'selling_price' => $urgentSaleData['price'],
                    'category' => $urgentSaleData['category_id'] ?: $urgentSaleData['parent_category_id'],
                    'condition' => $urgentSaleData['condition'],
                    'location' => $urgentSaleData['location'],
                    'status' => 'available',
                    'metadata' => json_encode([
                        'source' => 'urgent_sale',
                        'condition' => $urgentSaleData['condition'],
                        'latitude' => $urgentSaleData['latitude'],
                        'longitude' => $urgentSaleData['longitude'],
                    ]),
                ]);
                $inventoryItemId = $inventoryItem->id;
            } catch (\Exception $e) {
                // Table inventory n'existe pas encore, on continue sans
                $inventoryItemId = null;
            }
        }

        $urgentSale = new UrgentSale();
        $urgentSale->prestataire_id = $prestataire->id;
        $urgentSale->inventory_item_id = $inventoryItemId;
        $urgentSale->title = $urgentSaleData['title'];
        $urgentSale->slug = Str::slug($urgentSaleData['title'] . '-' . time());
        $urgentSale->description = $urgentSaleData['description'];
        $urgentSale->price = $urgentSaleData['price'];
        $urgentSale->condition = $urgentSaleData['condition'];
        $urgentSale->category_id = $urgentSaleData['category_id'] ?: $urgentSaleData['parent_category_id'];
        $urgentSale->quantity = $urgentSaleData['quantity'];
        $urgentSale->location = $urgentSaleData['location'];
        $urgentSale->latitude = $urgentSaleData['latitude'];
        $urgentSale->longitude = $urgentSaleData['longitude'];
        $urgentSale->payment_requirement = $urgentSaleData['payment_requirement'] ?? 'none';
        $urgentSale->status = 'active';
        
        // Enregistrer le consentement aux conditions de paiement (pour escrow)
        if (!empty($urgentSaleData['payment_consent_at'])) {
            $urgentSale->payment_consent_at = $urgentSaleData['payment_consent_at'];
            $urgentSale->payment_consent_ip = $urgentSaleData['payment_consent_ip'] ?? null;
            $urgentSale->payment_consent_user_agent = $urgentSaleData['payment_consent_user_agent'] ?? null;
        }

        // Gérer les images temporaires
        if (!empty($step3Data['temp_image_paths'])) {
            $finalPaths = [];
            foreach ($step3Data['temp_image_paths'] as $tempPath) {
                $finalPath = str_replace('temp_urgent_sales_photos/', 'urgent_sales_photos/', $tempPath);
                
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);
                    $finalPaths[] = $finalPath;
                }
            }
            
            $urgentSale->photos = json_encode($finalPaths);
            
            // Mettre à jour l'inventaire avec les photos si disponible
            if ($inventoryItem) {
                try {
                    $inventoryItem->update(['photos' => json_encode($finalPaths)]);
                } catch (\Exception $e) {
                    // Ignorer si erreur
                }
            }
        }

        $urgentSale->save();
        
        // Mettre à jour l'inventaire avec l'ID de la vente urgente (liaison bidirectionnelle)
        if ($inventoryItem) {
            try {
                $inventoryItem->update(['urgent_sale_id' => $urgentSale->id]);
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }

        // Nettoyer la session
        session()->forget('urgent_sale_creation');

        // Set a flag to indicate that an urgent sale was just created
        session()->flash('urgent_sale_just_created', true);

        return redirect()->route('prestataire.urgent-sales.index')
                        ->with('success', 'Vente urgente créée avec succès et synchronisée avec l\'inventaire!');
    }
    
    /**
     * Afficher une vente urgente spécifique
     */
    public function show(UrgentSale $urgentSale)
    {
        $this->authorize('view', $urgentSale);
        
        $urgentSale->load(['category', 'contacts.user', 'reports']);
        $urgentSale->loadCount('contacts');
        
        // Récupérer les contacts liés à cette vente urgente
        $relatedMessages = $urgentSale->contacts()->with('user')->latest()->get();
        
        return view('prestataire.urgent-sales.show', compact('urgentSale', 'relatedMessages'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit(UrgentSale $urgentSale)
    {
        $this->authorize('update', $urgentSale);
        
        $user = Auth::user();
        
        $categories = Category::ofTypeSale()->whereNull('parent_id')
                            ->where('is_active', true)
                            ->with(['children' => function($query) {
                                $query->where('is_active', true)->orderBy('name');
                            }])
                            ->orderBy('name')
                            ->get();
        
        // Récupérer les articles d'inventaire pour liaison (si table existe)
        $inventoryItems = collect();
        try {
            $inventoryItems = \App\Models\InventoryItem::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            // Table inventory n'existe pas
        }
        
        return view('prestataire.urgent-sales.edit', compact('urgentSale', 'categories', 'inventoryItems'));
    }
    
    /**
     * Mettre à jour une vente urgente
     */
    public function update(Request $request, UrgentSale $urgentSale)
    {
        $this->authorize('update', $urgentSale);

        // Si l'utilisateur active le paiement en ligne, exiger Stripe
        $requestedPaymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($request->input('payment_requirement', $urgentSale->payment_requirement ?? 'none'))
            : $request->input('payment_requirement', $urgentSale->payment_requirement ?? 'none');
        if ($requestedPaymentRequirement === 'full') {
            $redirect = $this->redirectIfPaymentRequired('full_prepay', route('prestataire.urgent-sales.edit', $urgentSale));
            if ($redirect) {
                return $redirect;
            }
        }
        
        // Validate with location fields and photos
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,very_good,good,fair,poor,new,like_new',
            'parent_category_id' => 'required|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'payment_requirement' => 'nullable|in:none,full',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        
        $urgentSale->title = $request->title;
        $urgentSale->slug = Str::slug($request->title . '-' . $urgentSale->id);
        $urgentSale->description = $request->description;
        $urgentSale->price = $request->price;
        $urgentSale->condition = $request->condition;
        $urgentSale->category_id = $request->category_id ?: $request->parent_category_id;
        $urgentSale->quantity = $request->quantity;
        $urgentSale->payment_requirement = $requestedPaymentRequirement;
        $urgentSale->location = $request->address;
        $urgentSale->latitude = $request->latitude;
        $urgentSale->longitude = $request->longitude;
        // Note: inventory_item_id colonne non disponible
        
        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photos = $urgentSale->photos ?? [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('urgent_sales_photos', 'public');
                $photos[] = $path;
            }
            $urgentSale->photos = $photos;
        }
        
        // Synchroniser avec l'inventaire
        $inventoryItem = null;
        $syncToInventory = $request->has('sync_to_inventory') && $request->input('sync_to_inventory');
        
        if ($urgentSale->inventory_item_id) {
            // Mettre à jour l'article d'inventaire existant
            try {
                $inventoryItem = InventoryItem::find($urgentSale->inventory_item_id);
                if ($inventoryItem) {
                    $inventoryItem->update([
                        'name' => $request->title,
                        'description' => $request->description,
                        'quantity' => $request->quantity,
                        'selling_price' => $request->price,
                        'category' => $request->category_id ?: $request->parent_category_id,
                        'condition' => $request->condition,
                        'location' => $request->address,
                        'photos' => $urgentSale->photos,
                        'metadata' => json_encode([
                            'source' => 'urgent_sale',
                            'condition' => $request->condition,
                            'latitude' => $request->latitude,
                            'longitude' => $request->longitude,
                        ]),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur mise à jour inventaire: ' . $e->getMessage());
            }
        } elseif ($syncToInventory) {
            // Créer un nouvel article d'inventaire uniquement si la case est cochée
            try {
                $inventoryItem = InventoryItem::create([
                    'user_id' => Auth::id(),
                    'urgent_sale_id' => $urgentSale->id,
                    'name' => $request->title,
                    'description' => $request->description,
                    'quantity' => $request->quantity,
                    'initial_quantity' => $request->quantity,
                    'selling_price' => $request->price,
                    'category' => $request->category_id ?: $request->parent_category_id,
                    'condition' => $request->condition,
                    'location' => $request->address,
                    'status' => 'available',
                    'photos' => $urgentSale->photos,
                    'metadata' => json_encode([
                        'source' => 'urgent_sale',
                        'condition' => $request->condition,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]),
                ]);
                $urgentSale->inventory_item_id = $inventoryItem->id;
            } catch (\Exception $e) {
                Log::error('Erreur création inventaire depuis vente urgente: ' . $e->getMessage());
            }
        }
        
        $urgentSale->save();
        
        $message = $inventoryItem ? 'Vente urgente mise à jour et synchronisée avec l\'inventaire!' : 'Vente urgente mise à jour avec succès!';
        
        return redirect()->route('prestataire.urgent-sales.show', $urgentSale)
                        ->with('success', $message);
    }
    
    /**
     * Supprimer une vente urgente
     */
    public function destroy(UrgentSale $urgentSale)
    {
        $this->authorize('delete', $urgentSale);
        
        // Supprimer les photos
        if ($urgentSale->photos) {
            foreach ($urgentSale->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        $urgentSale->delete();
        
        return redirect()->route('prestataire.urgent-sales.index')
                        ->with('success', 'Vente urgente supprimée avec succès!');
    }
    
    /**
     * Mettre à jour le statut d'une vente urgente
     */
    public function updateStatus(Request $request, UrgentSale $urgentSale)
    {
        $this->authorize('update', $urgentSale);
        
        $request->validate([
            'status' => 'required|in:active,inactive,sold,expired'
        ]);
        
        $oldStatus = $urgentSale->status;
        $newStatus = $request->status;
        
        $urgentSale->status = $newStatus;
        $urgentSale->save();
        
        // Note: Synchronisation avec l'inventaire désactivée car la colonne inventory_item_id n'existe pas
        // dans la table urgent_sales. Réactiver quand la migration sera appliquée.
        
        return back()->with('success', 'Statut mis à jour avec succès!');
    }
    
    /**
     * Afficher les contacts pour une vente urgente
     */
    public function contacts(UrgentSale $urgentSale)
    {
        $this->authorize('view', $urgentSale);
        
        $contacts = $urgentSale->contacts()
                              ->with('user')
                              ->latest()
                              ->paginate(10);
        
        return view('prestataire.urgent-sales.contacts', compact('urgentSale', 'contacts'));
    }
    
    /**
     * Répondre à un contact
     */
    public function respondToContact(Request $request, UrgentSaleContact $contact)
    {
        $this->authorize('view', $contact->urgentSale);
        
        $request->validate([
            'response' => 'required|string|max:1000'
        ]);
        
        $contact->response = $request->response;
        $contact->responded_at = now();
        $contact->save();
        
        return back()->with('success', 'Réponse envoyée avec succès!');
    }
    
    /**
     * Accepter un contact (confirmer une vente)
     */
    public function acceptContact(Request $request, UrgentSaleContact $contact)
    {
        $this->authorize('view', $contact->urgentSale);
        
        $urgentSale = $contact->urgentSale;
        
        // Quantité à vendre (par défaut 1)
        $quantityToSell = $request->input('quantity', 1);
        
        // Vérifier le stock disponible
        if ($urgentSale->quantity < $quantityToSell) {
            return back()->with('error', 'Stock insuffisant! Il reste ' . $urgentSale->quantity . ' unité(s).');
        }
        
        $contact->status = 'accepted';
        $contact->save();
        
        // Diminuer la quantité en stock
        $urgentSale->quantity -= $quantityToSell;
        
        // Si le stock est épuisé, marquer comme vendu
        if ($urgentSale->quantity <= 0) {
            $urgentSale->quantity = 0;
            $urgentSale->status = 'sold';
            $message = 'Vente confirmée! Stock épuisé - annonce marquée comme vendue.';
        } else {
            $message = 'Vente confirmée! Stock restant: ' . $urgentSale->quantity . ' unité(s).';
        }
        
        $urgentSale->save();
        
        return back()->with('success', $message);
    }
    
    /**
     * Rejeter un contact
     */
    public function rejectContact(UrgentSaleContact $contact)
    {
        $this->authorize('view', $contact->urgentSale);
        
        $contact->status = 'rejected';
        $contact->save();
        
        return back()->with('success', 'Contact rejeté!');
    }
    
    /**
     * Récupérer les sous-catégories d'une catégorie parent
     */
    public function getSubcategories($categoryId)
    {
        $subcategories = Category::where('parent_id', $categoryId)
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get(['id', 'name']);
        
        return response()->json($subcategories);
    }

    /**
     * Afficher la sélection depuis l'inventaire/équipements
     */
    public function selectFromInventory()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        // Récupérer les articles de l'inventaire disponibles (table inventory utilise user_id)
        $inventoryItems = collect();
        try {
            $inventoryItems = \App\Models\InventoryItem::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            // Table inventory n'existe pas
        }
        
        // Récupérer les équipements disponibles à la vente
        $equipments = \App\Models\Equipment::where('prestataire_id', $prestataire->id)
            ->where('status', 'active')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();
        
        return view('prestataire.urgent-sales.select-inventory', compact('inventoryItems', 'equipments'));
    }

    /**
     * Créer une vente depuis un article d'inventaire
     */
    public function createFromInventoryItem($inventoryItemId)
    {
        $user = Auth::user();
        
        try {
            $item = \App\Models\InventoryItem::where('user_id', $user->id)
                ->findOrFail($inventoryItemId);
        } catch (\Exception $e) {
            return redirect()->route('prestataire.urgent-sales.create.step1')
                ->with('error', 'Article d\'inventaire non trouvé ou fonctionnalité non disponible.');
        }
        
        // Pré-remplir les données de session
        session(['urgent_sale_creation.step1' => [
            'title' => $item->name,
            'description' => $item->description,
            'price' => $item->selling_price ?? $item->cost_per_unit,
            'quantity' => $item->quantity,
            'condition' => 'good',
            'inventory_item_id' => $item->id,
            'source_type' => 'inventory'
        ]]);
        
        // Pré-remplir les photos si disponibles (metadata peut contenir des images)
        if ($item->metadata && isset($item->metadata['photos']) && count($item->metadata['photos']) > 0) {
            session(['urgent_sale_creation.step3' => [
                'description' => $item->description ?? '',
                'temp_image_paths' => $item->metadata['photos']
            ]]);
        }
        
        return redirect()->route('prestataire.urgent-sales.create.step1')
            ->with('info', 'Article importé depuis l\'inventaire. Complétez les informations manquantes.');
    }

    /**
     * Créer une vente depuis un équipement
     */
    public function createFromEquipment($equipmentId)
    {
        $prestataire = Auth::user()->prestataire;
        $equipment = \App\Models\Equipment::where('prestataire_id', $prestataire->id)
            ->findOrFail($equipmentId);
        
        // Calculer un prix de vente suggéré (ex: prix journalier x 30)
        $suggestedPrice = $equipment->price_per_day ? $equipment->price_per_day * 30 : 0;
        
        // Pré-remplir les données de session
        session(['urgent_sale_creation.step1' => [
            'title' => $equipment->name,
            'description' => $equipment->description,
            'price' => $suggestedPrice,
            'quantity' => 1,
            'condition' => $equipment->condition ?? 'good',
            'equipment_id' => $equipment->id,
            'category_id' => $equipment->category_id,
            'source_type' => 'equipment'
        ]]);
        
        // Pré-remplir la localisation
        if ($equipment->address) {
            session(['urgent_sale_creation.step2' => [
                'location' => $equipment->address . ', ' . $equipment->city,
                'latitude' => $equipment->latitude ?? 0,
                'longitude' => $equipment->longitude ?? 0
            ]]);
        }
        
        // Pré-remplir les photos si disponibles
        $photos = $equipment->photos ?? [];
        if ($equipment->main_photo) {
            array_unshift($photos, $equipment->main_photo);
        }
        if (count($photos) > 0) {
            session(['urgent_sale_creation.step3' => [
                'description' => $equipment->description ?? '',
                'temp_image_paths' => $photos
            ]]);
        }
        
        return redirect()->route('prestataire.urgent-sales.create.step1')
            ->with('info', 'Équipement importé. Complétez les informations manquantes.');
    }

    /**
     * API pour obtenir les articles de l'inventaire (pour AJAX)
     */
    public function getInventoryItems()
    {
        $user = Auth::user();
        
        try {
            $items = \App\Models\InventoryItem::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->select('id', 'name', 'quantity', 'selling_price', 'metadata', 'category')
                ->orderBy('name')
                ->get()
                ->map(function($item) {
                $metadata = is_string($item->metadata) ? json_decode($item->metadata, true) : $item->metadata;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->selling_price,
                    'photo' => isset($metadata['photos']) && count($metadata['photos']) > 0 ? $metadata['photos'][0] : null,
                    'category' => $item->category,
                    'type' => 'inventory'
                ];
            });
        
            return response()->json($items);
        } catch (\Exception $e) {
            // Table inventory n'existe pas
            return response()->json([]);
        }
    }

    /**
     * API pour obtenir les équipements (pour AJAX)
     */
    public function getEquipments()
    {
        $prestataire = Auth::user()->prestataire;
        
        $equipments = \App\Models\Equipment::where('prestataire_id', $prestataire->id)
            ->where('status', 'active')
            ->select('id', 'name', 'price_per_day', 'main_photo', 'condition')
            ->orderBy('name')
            ->get()
            ->map(function($eq) {
                return [
                    'id' => $eq->id,
                    'name' => $eq->name,
                    'price' => $eq->price_per_day * 30,
                    'photo' => $eq->main_photo,
                    'condition' => $eq->condition,
                    'type' => 'equipment'
                ];
            });
        
        return response()->json($equipments);
    }
}
