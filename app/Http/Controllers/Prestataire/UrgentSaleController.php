<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use App\Models\UrgentSaleContact;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UrgentSaleController extends Controller
{
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
        
        // Get categories for filter
        $categories = Category::whereNull('parent_id')
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
    public function create()
    {
        // Check if an urgent sale was just created to prevent duplicate submissions
        if (session()->has('urgent_sale_just_created')) {
            return redirect()->route('prestataire.urgent-sales.index')
                ->with('info', 'Vous avez déjà créé une annonce. Créez-en une nouvelle si nécessaire.');
        }
        
        // Nettoyer les données de session précédentes
        session()->forget('urgent_sale_creation');
        
        $categories = Category::whereNull('parent_id')
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
        
        $categories = Category::whereNull('parent_id')
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
        ]);

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
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        
        $urgentSale = new UrgentSale();
        $urgentSale->prestataire_id = $prestataire->id;
        $urgentSale->title = $request->title;
        $urgentSale->slug = Str::slug($request->title . '-' . time());
        $urgentSale->description = $request->description;
        $urgentSale->price = $request->price;
        $urgentSale->condition = $request->condition;
        $urgentSale->category_id = $request->category_id ?: $request->parent_category_id;
        $urgentSale->quantity = $request->quantity;
        $urgentSale->location = $request->address;
        $urgentSale->latitude = $request->latitude;
        $urgentSale->longitude = $request->longitude;
        $urgentSale->status = 'active';
        
        // Gestion des photos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('urgent-sales', 'public');
                $photos[] = $path;
            }
            $urgentSale->photos = json_encode($photos);
        }
        
        $urgentSale->save();
        
        // Set a flag to indicate that an urgent sale was just created
        session()->flash('urgent_sale_just_created', true);
        
        return redirect()->route('prestataire.urgent-sales.index')
                        ->with('success', 'Vente urgente créée avec succès!');
    }
    
    /**
     * Enregistre une vente urgente à partir des données de session (processus multi-étapes)
     */
    private function storeFromSession(Request $request)
    {
        $request->validate([
            'terms' => 'accepted',
            'contact' => 'accepted',
        ]);

        $prestataire = Auth::user()->prestataire;

        // Récupérer toutes les données des étapes
        $step1Data = session('urgent_sale_creation.step1');
        $step2Data = session('urgent_sale_creation.step2');
        $step3Data = session('urgent_sale_creation.step3');

        // Créer la vente urgente avec toutes les données
        $urgentSaleData = array_merge($step1Data, $step2Data, [
            'description' => $step3Data['description'],
        ]);

        $urgentSale = new UrgentSale();
        $urgentSale->prestataire_id = $prestataire->id;
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
        $urgentSale->status = 'active';

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
        }

        $urgentSale->save();

        // Nettoyer la session
        session()->forget('urgent_sale_creation');

        // Set a flag to indicate that an urgent sale was just created
        session()->flash('urgent_sale_just_created', true);

        return redirect()->route('prestataire.urgent-sales.index')
                        ->with('success', 'Vente urgente créée avec succès!');
    }
    
    /**
     * Afficher une vente urgente spécifique
     */
    public function show(UrgentSale $urgentSale)
    {
        $this->authorize('view', $urgentSale);
        
        $urgentSale->load(['category', 'contacts.user', 'reports']);
        
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
        
        $categories = Category::whereNull('parent_id')
                            ->where('is_active', true)
                            ->with(['children' => function($query) {
                                $query->where('is_active', true)->orderBy('name');
                            }])
                            ->orderBy('name')
                            ->get();
        
        return view('prestataire.urgent-sales.edit', compact('urgentSale', 'categories'));
    }
    
    /**
     * Mettre à jour une vente urgente
     */
    public function update(Request $request, UrgentSale $urgentSale)
    {
        $this->authorize('update', $urgentSale);
        
        // Validate with location fields and photos
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,very_good,good,fair,poor',
            'parent_category_id' => 'required|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|integer|min:1',
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
        $urgentSale->location = $request->address;
        $urgentSale->latitude = $request->latitude;
        $urgentSale->longitude = $request->longitude;
        
        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photos = $urgentSale->photos ?? [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('urgent-sales', 'public');
                $photos[] = $path;
            }
            $urgentSale->photos = $photos;
        }
        
        $urgentSale->save();
        
        return redirect()->route('prestataire.urgent-sales.show', $urgentSale)
                        ->with('success', 'Vente urgente mise à jour avec succès!');
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
        
        $urgentSale->status = $request->status;
        $urgentSale->save();
        
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
     * Accepter un contact
     */
    public function acceptContact(UrgentSaleContact $contact)
    {
        $this->authorize('view', $contact->urgentSale);
        
        $contact->status = 'accepted';
        $contact->save();
        
        return back()->with('success', 'Contact accepté!');
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
}