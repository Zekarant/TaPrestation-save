<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClientUrgentSaleController extends Controller
{
    /**
     * Limite d'annonces pour les clients non-prestataires
     */
    const MAX_CLIENT_LISTINGS = 5;

    /**
     * Afficher la liste des annonces du client
     */
    public function index()
    {
        $user = Auth::user();
        
        try {
            // Compter les annonces actives
            $activeCount = UrgentSale::where('user_id', $user->id)
                ->whereNull('prestataire_id')
                ->where('status', 'active')
                ->count();
            
            // Toutes les annonces du client
            $urgentSales = UrgentSale::where('user_id', $user->id)
                ->whereNull('prestataire_id')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            // Si la colonne user_id n'existe pas encore
            $activeCount = 0;
            $urgentSales = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        }
        
        $remainingSlots = max(0, self::MAX_CLIENT_LISTINGS - $activeCount);
        
        return view('client.my-urgent-sales.index', compact(
            'urgentSales',
            'activeCount',
            'remainingSlots'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        
        try {
            // Vérifier la limite
            $activeCount = UrgentSale::where('user_id', $user->id)
                ->whereNull('prestataire_id')
                ->where('status', 'active')
                ->count();
        } catch (\Exception $e) {
            // Si la colonne user_id n'existe pas encore, rediriger avec message
            return redirect()->route('client.dashboard')
                ->with('error', 'Cette fonctionnalité est en cours de déploiement. Veuillez réessayer plus tard.');
        }
        
        if ($activeCount >= self::MAX_CLIENT_LISTINGS) {
            return redirect()->route('client.my-urgent-sales.index')
                ->with('error', 'Vous avez atteint la limite de ' . self::MAX_CLIENT_LISTINGS . ' annonces. Pour publier plus, devenez prestataire !');
        }
        
        $categories = Category::orderBy('name')->get();
        
        $remainingSlots = self::MAX_CLIENT_LISTINGS - $activeCount;
        
        return view('client.my-urgent-sales.create', compact('categories', 'remainingSlots'));
    }

    /**
     * Enregistrer une nouvelle annonce
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Vérifier la limite
            $activeCount = UrgentSale::where('user_id', $user->id)
                ->whereNull('prestataire_id')
                ->where('status', 'active')
                ->count();
        } catch (\Exception $e) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Cette fonctionnalité est en cours de déploiement. Veuillez réessayer plus tard.');
        }
        
        if ($activeCount >= self::MAX_CLIENT_LISTINGS) {
            return redirect()->route('client.my-urgent-sales.index')
                ->with('error', 'Limite atteinte ! Devenez prestataire pour publier plus d\'annonces.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0.01|max:10000',
            'condition' => 'required|in:new,good,used,fair',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string|max:255',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.min' => 'Le prix minimum est 0.01€.',
            'condition.required' => 'L\'état est obligatoire.',
            'category_id.required' => 'La catégorie est obligatoire.',
            'location.required' => 'La localisation est obligatoire.',
            'photos.max' => 'Maximum 5 photos.',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Traiter les photos
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('urgent-sales/' . $user->id, 'public');
                $photos[] = $path;
            }
        }
        
        // Créer l'annonce
        $urgentSale = UrgentSale::create([
            'user_id' => $user->id,
            'prestataire_id' => null,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'condition' => $request->condition,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'photos' => $photos,
            'quantity' => 1,
            'status' => 'active',
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'payment_requirement' => 'none', // Clients: contact direct uniquement (pas de paiement en ligne)
        ]);
        
        return redirect()->route('client.my-urgent-sales.index')
            ->with('success', 'Votre annonce a été publiée avec succès !');
    }

    /**
     * Afficher une annonce
     */
    public function show($id)
    {
        $urgentSale = UrgentSale::findOrFail($id);
        $this->authorizeClient($urgentSale);
        
        return view('client.my-urgent-sales.show', compact('urgentSale'));
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $urgentSale = UrgentSale::findOrFail($id);
        $this->authorizeClient($urgentSale);
        
        $categories = Category::orderBy('name')->get();
        
        return view('client.my-urgent-sales.edit', compact('urgentSale', 'categories'));
    }

    /**
     * Mettre à jour une annonce
     */
    public function update(Request $request, $id)
    {
        $urgentSale = UrgentSale::findOrFail($id);
        $this->authorizeClient($urgentSale);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0.01|max:10000',
            'condition' => 'required|in:new,good,used,fair',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string|max:255',
            'new_photos' => 'nullable|array|max:5',
            'new_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Gérer les photos
        $photos = $urgentSale->photos ?? [];
        
        if ($request->has('delete_photos')) {
            foreach ($request->delete_photos as $photoToDelete) {
                if (($key = array_search($photoToDelete, $photos)) !== false) {
                    Storage::disk('public')->delete($photoToDelete);
                    unset($photos[$key]);
                }
            }
            $photos = array_values($photos);
        }
        
        if ($request->hasFile('new_photos')) {
            foreach ($request->file('new_photos') as $photo) {
                if (count($photos) < 5) {
                    $path = $photo->store('urgent-sales/' . Auth::id(), 'public');
                    $photos[] = $path;
                }
            }
        }
        
        $urgentSale->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'condition' => $request->condition,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'photos' => $photos,
        ]);
        
        return redirect()->route('client.my-urgent-sales.index')
            ->with('success', 'Votre annonce a été mise à jour !');
    }

    /**
     * Marquer comme vendu
     */
    public function markSold($id)
    {
        $urgentSale = UrgentSale::findOrFail($id);
        $this->authorizeClient($urgentSale);
        
        $urgentSale->update(['status' => 'sold']);
        
        return redirect()->route('client.my-urgent-sales.index')
            ->with('success', 'Annonce marquée comme vendue !');
    }

    /**
     * Supprimer une annonce
     */
    public function destroy($id)
    {
        $urgentSale = UrgentSale::findOrFail($id);
        $this->authorizeClient($urgentSale);
        
        if ($urgentSale->photos) {
            foreach ($urgentSale->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        $urgentSale->delete();
        
        return redirect()->route('client.my-urgent-sales.index')
            ->with('success', 'Annonce supprimée !');
    }

    /**
     * Migrer une annonce client vers le contexte prestataire
     * (associe `prestataire_id` et crée un `InventoryItem` si possible)
     */
    public function migrateToPrestataire($id)
    {
        $user = Auth::user();
        $urgentSale = UrgentSale::findOrFail($id);

        // Vérifications basiques
        if ($urgentSale->user_id !== $user->id || !empty($urgentSale->prestataire_id)) {
            abort(403, 'Impossible de migrer cette annonce.');
        }

        // Doit avoir un prestataire actif
        if (!isset($user->prestataire) || !($user->prestataire->is_active ?? false)) {
            return redirect()->route('client.my-urgent-sales.index')
                ->with('error', 'Vous devez devenir prestataire actif pour utiliser les outils prestataire.');
        }

        $prestataire = $user->prestataire;

        \Log::info('Migration d\'une annonce client vers prestataire', [
            'urgent_sale_id' => $urgentSale->id,
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
        ]);

        // Tenter de créer un article d'inventaire lié
        $inventoryItemId = null;
        try {
            $inventoryItem = \App\Models\InventoryItem::create([
                'user_id' => $user->id,
                'name' => $urgentSale->title,
                'description' => $urgentSale->description,
                'quantity' => $urgentSale->quantity ?? 1,
                'initial_quantity' => $urgentSale->quantity ?? 1,
                'selling_price' => $urgentSale->price ?? 0,
                'category' => $urgentSale->category_id,
                'condition' => $urgentSale->condition ?? null,
                'location' => $urgentSale->location ?? null,
                'status' => 'available',
                'metadata' => json_encode(['source' => 'migrated_from_client'])
            ]);
            $inventoryItemId = $inventoryItem->id;
            \Log::info('InventoryItem créé avec succès', ['inventory_item_id' => $inventoryItemId]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création d\'un InventoryItem : ' . $e->getMessage());
        }

        // Associer l'annonce au prestataire
        $urgentSale->prestataire_id = $prestataire->id;
        if ($inventoryItemId) {
            $urgentSale->inventory_item_id = $inventoryItemId;
            // Lien inverse pour l’inventaire
            $inventoryItem->urgent_sale_id = $urgentSale->id;
            $inventoryItem->save();
        }
        $urgentSale->save();

        \Log::info('Annonce migrée avec succès', [
            'urgent_sale_id' => $urgentSale->id,
            'prestataire_id' => $prestataire->id,
            'inventory_item_id' => $inventoryItemId,
        ]);

        // Rediriger vers l'édition prestataire si disponible
        if (\Illuminate\Support\Facades\Route::has('prestataire.urgent-sales.edit')) {
            return redirect()->route('prestataire.urgent-sales.edit', $urgentSale)
                ->with('success', 'Annonce migrée vers votre espace prestataire.');
        }

        return redirect()->route('client.my-urgent-sales.index')
            ->with('success', 'Annonce migrée. Vous pouvez maintenant la gérer depuis votre espace prestataire.');
    }

    /**
     * Vérifier que l'annonce appartient au client
     */
    private function authorizeClient(UrgentSale $urgentSale)
    {
        if ($urgentSale->user_id !== Auth::id() || !empty($urgentSale->prestataire_id)) {
            abort(403, 'Cette annonce ne vous appartient pas.');
        }
    }
}
