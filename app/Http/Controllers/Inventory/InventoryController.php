<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
class InventoryController extends Controller
{
    /**
     * Show all inventory items
     */
    public function index()
    {
        $items = auth()->user()->inventory()
            ->with('category')
            ->paginate(20);

        $analytics = $this->getAnalytics();

        return view('inventory.index', compact('items', 'analytics'));
    }

    /**
     * Store a new inventory item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:20',
            'cost_per_unit' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50|unique:inventory,sku',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:100',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Valeurs par défaut
        $validated['unit'] = $validated['unit'] ?? 'unité';
        $validated['reorder_level'] = $validated['reorder_level'] ?? 5;
        $validated['user_id'] = auth()->id();
        $validated['initial_quantity'] = $validated['quantity'];

        // Gérer les photos - utiliser un chemin uniforme
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $path = $photo->store('inventory/' . auth()->id(), 'public');
                    $photos[] = $path;
                }
            }
        }
        $validated['photos'] = !empty($photos) ? $photos : null;

        $item = InventoryItem::create($validated);

        // Si c'est une requête AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'message' => 'Article ajouté à l\'inventaire',
            ]);
        }

        return redirect()->route('prestataire.inventory.index')
            ->with('success', 'Article ajouté à votre inventaire avec succès !');
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, InventoryItem $item)
    {
        \Log::info('Inventory Update Request', [
            'item_id' => $item->id,
            'user_id' => auth()->id(),
            'item_user_id' => $item->user_id,
        ]);

        // Vérifier que l'item appartient à l'utilisateur
        if ($item->user_id !== auth()->id()) {
            \Log::warning('Inventory Update FORBIDDEN - user mismatch');
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'category' => 'nullable|string|max:50',
                'quantity' => 'required|integer|min:0',
                'unit' => 'nullable|string|max:20',
                'cost_per_unit' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'reorder_level' => 'nullable|integer|min:0',
                'supplier' => 'nullable|string|max:100',
                'sku' => 'nullable|string|max:50|unique:inventory,sku,' . $item->id,
                'description' => 'nullable|string|max:1000',
                'location' => 'nullable|string|max:255',
                'existing_photos' => 'nullable|array',
                'existing_photos.*' => 'nullable|string',
                'new_photos' => 'nullable|array',
                'new_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Valeurs par défaut
            $validated['unit'] = $validated['unit'] ?? 'unité';
            $validated['reorder_level'] = $validated['reorder_level'] ?? 5;

            // === GESTION DES PHOTOS ===
            $photos = [];
            
            // 1. Garder les photos existantes qui n'ont pas été supprimées
            if ($request->has('existing_photos')) {
                $photos = array_filter($request->input('existing_photos', []));
            }
            
            // 2. Ajouter les nouvelles photos uploadées
            if ($request->hasFile('new_photos')) {
                foreach ($request->file('new_photos') as $photo) {
                    if ($photo->isValid()) {
                        $path = $photo->store('inventory/' . auth()->id(), 'public');
                        $photos[] = $path;
                    }
                }
            }
            
            // 3. Supprimer les anciennes photos qui ne sont plus dans la liste
            $oldPhotos = is_array($item->photos) ? $item->photos : json_decode($item->photos ?? '[]', true);
            if ($oldPhotos) {
                foreach ($oldPhotos as $oldPhoto) {
                    if (!in_array($oldPhoto, $photos) && \Storage::disk('public')->exists($oldPhoto)) {
                        \Storage::disk('public')->delete($oldPhoto);
                        \Log::info('Photo supprimée', ['path' => $oldPhoto]);
                    }
                }
            }
            
            // Mettre à jour les photos dans les données validées
            $validated['photos'] = !empty($photos) ? $photos : null;
            
            // Retirer les clés non nécessaires pour l'update
            unset($validated['existing_photos']);
            unset($validated['new_photos']);

            $item->update($validated);

            \Log::info('Inventory Update SUCCESS', ['item_id' => $item->id, 'new_name' => $item->name, 'photos_count' => count($photos)]);

            // Si c'est une requête AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'item' => $item,
                    'message' => 'Article mis à jour avec succès',
                ]);
            }

            return redirect()->route('prestataire.inventory.index')
                ->with('success', 'Article mis à jour avec succès !');
                
        } catch (\Exception $e) {
            \Log::error('Inventory Update FAILED', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de l\'article.',
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'article.');
        }
    }

    /**
     * Delete inventory item
     */
    public function destroy(InventoryItem $item)
    {
        \Log::info('Inventory destroy called', [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'user_id' => auth()->id(),
        ]);

        // Vérifier que l'item appartient à l'utilisateur
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }

        $item->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Article supprimé',
            ]);
        }

        return redirect()->route('prestataire.inventory.index')
            ->with('success', 'Article supprimé de l\'inventaire');
    }

    /**
     * Adjust stock (add or remove)
     */
    public function adjustStock(Request $request, InventoryItem $item)
    {
        // Vérifier que l'item appartient à l'utilisateur
        if ($item->user_id !== auth()->id()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Non autorisé'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:add,remove',
            'reason' => 'nullable|string|max:200',
        ]);

        if ($validated['action'] === 'add') {
            $item->increment('quantity', $validated['quantity']);
            $message = '+' . $validated['quantity'] . ' ajouté(s) au stock';
        } else {
            // Ne pas permettre un stock négatif
            if ($item->quantity < $validated['quantity']) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'error' => 'Stock insuffisant pour cette opération']);
                }
                return redirect()->back()->with('error', 'Stock insuffisant pour cette opération');
            }
            $item->decrement('quantity', $validated['quantity']);
            $message = '-' . $validated['quantity'] . ' retiré(s) du stock';
        }

        // Mettre à jour la date de réapprovisionnement si ajout
        if ($validated['action'] === 'add') {
            $item->update(['last_restocked_at' => now()]);
        }

        // Retourner JSON si requête AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message . ' pour "' . $item->name . '"',
                'new_quantity' => $item->fresh()->quantity
            ]);
        }

        return redirect()->route('prestataire.inventory.index')
            ->with('success', $message . ' pour "' . $item->name . '"');
    }

    /**
     * Decrease stock
     */
    public function decreaseStock(Request $request, InventoryItem $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:200',
        ]);

        $item->decreaseStock($validated['quantity']);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Stock diminué avec succès.',
        ]);
    }

    /**
     * Increase stock
     */
    public function increaseStock(Request $request, InventoryItem $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:200',
        ]);

        $item->increaseStock($validated['quantity']);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Stock augmenté avec succès.',
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock()
    {
        $items = auth()->user()->inventory()
            ->whereRaw('quantity <= reorder_level')
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    /**
     * Get analytics data as array
     */
    public function getAnalytics()
    {
        $user = auth()->user();

        return [
            'total_items' => $user->inventory()->count(),
            'total_value' => $user->inventory()->sum(\DB::raw('quantity * cost_per_unit')),
            'low_stock_count' => $user->inventory()->whereRaw('quantity <= reorder_level')->count(),
            'average_profit_margin' => round(
                $user->inventory()->avg(\DB::raw('((selling_price - cost_per_unit) / cost_per_unit) * 100')) ?? 0,
                2
            ),
            'items_by_category' => $user->inventory()
                ->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->get()
                ->pluck('count', 'category'),
        ];
    }

    /**
     * Prestataire analytics view
     */
    public function prestataireAnalytics()
    {
        if (!TableExistenceCache::has('inventory')) {
            return view('prestataire.inventory.analytics', [
                'stats' => [
                    'total_items' => 0,
                    'total_value' => 0,
                    'selling_value' => 0,
                    'profit' => 0,
                    'margin' => 0,
                    'low_stock_count' => 0,
                    'out_of_stock_count' => 0,
                    'items_by_category' => collect([]),
                    'recent_items' => collect([]),
                    'low_stock_items' => collect([]),
                ],
                'tableNotExists' => true,
            ]);
        }
        
        $user = auth()->user();
        
        try {
            $totalItems = $user->inventory()->count();
            $totalCost = $user->inventory()->sum(\DB::raw('quantity * COALESCE(cost_per_unit, 0)'));
            $totalSellingValue = $user->inventory()->sum(\DB::raw('quantity * COALESCE(selling_price, 0)'));
            $profit = $totalSellingValue - $totalCost;
            $margin = $totalCost > 0 ? round((($totalSellingValue - $totalCost) / $totalCost) * 100, 2) : 0;
            
            $stats = [
                'total_items' => $totalItems,
                'total_value' => $totalCost,
                'selling_value' => $totalSellingValue,
                'profit' => $profit,
                'margin' => $margin,
                'low_stock_count' => $user->inventory()
                    ->whereRaw('quantity <= COALESCE(reorder_level, 5) AND quantity > 0')
                    ->count(),
                'out_of_stock_count' => $user->inventory()
                    ->where('quantity', '<=', 0)
                    ->count(),
                'items_by_category' => $user->inventory()
                    ->groupBy('category')
                    ->selectRaw('category, count(*) as count, SUM(quantity * COALESCE(selling_price, 0)) as value')
                    ->get(),
                'recent_items' => $user->inventory()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'low_stock_items' => $user->inventory()
                    ->whereRaw('quantity <= COALESCE(reorder_level, 5)')
                    ->orderBy('quantity', 'asc')
                    ->limit(10)
                    ->get(),
            ];
            
            return view('prestataire.inventory.analytics', compact('stats'));
        } catch (\Exception $e) {
            return view('prestataire.inventory.analytics', [
                'stats' => [
                    'total_items' => 0,
                    'total_value' => 0,
                    'selling_value' => 0,
                    'profit' => 0,
                    'margin' => 0,
                    'low_stock_count' => 0,
                    'out_of_stock_count' => 0,
                    'items_by_category' => collect([]),
                    'recent_items' => collect([]),
                    'low_stock_items' => collect([]),
                ],
                'error' => 'Erreur lors du chargement des analytics: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Export inventory as CSV
     */
    public function export()
    {
        $items = auth()->user()->inventory()->get();

        $csv = "Name,Category,Quantity,Unit,Cost,Price,Margin,Location\n";

        foreach ($items as $item) {
            $margin = round((($item->selling_price - $item->cost_per_unit) / $item->cost_per_unit) * 100, 2);
            $csv .= "{$item->name},{$item->category},{$item->quantity},{$item->unit},";
            $csv .= "{$item->cost_per_unit},{$item->selling_price},{$margin}%,{$item->location}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_' . date('Y-m-d') . '.csv"',
        ]);
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client inventory index (purchased items, rentals)
     */
    public function clientIndex()
    {
        $user = auth()->user();
        
        // Items achetés/loués par le client
        $rentals = \App\Models\EquipmentRental::where('client_id', $user->client?->id)
            ->with(['equipment'])
            ->latest()
            ->paginate(15);

        $stats = [
            'active_rentals' => \App\Models\EquipmentRental::where('client_id', $user->client?->id)
                ->where('status', 'active')->count(),
            'total_spent' => \App\Models\EquipmentRental::where('client_id', $user->client?->id)
                ->sum('total_price'),
        ];

        return view('client.inventory.index', compact('rentals', 'stats'));
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire inventory index
     */
    public function prestataireIndex(Request $request)
    {
        // Vérifier si la table existe
        if (!TableExistenceCache::has('inventory')) {
            // Créer un paginateur vide
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // items
                0,  // total
                20, // perPage
                1,  // currentPage
                ['path' => request()->url()]
            );
            
            return view('prestataire.inventory.index', [
                'items' => $emptyPaginator,
                'totalItems' => 0,
                'availableItems' => 0,
                'lowStockItems' => 0,
                'totalValue' => 0,
                'totalCost' => 0,
                'totalSellingValue' => 0,
                'totalProfit' => 0,
                'averageMargin' => 0,
                'tableNotExists' => true,
            ]);
        }
        
        $user = auth()->user();
        
        try {
            // Requête de base avec filtres et relations pour les réservations
            $query = InventoryItem::where('user_id', $user->id)
                ->with(['urgentSales', 'urgentSale']);
            
            // Filtre recherche
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('supplier', 'like', "%{$search}%");
                });
            }
            
            // Filtre catégorie
            if ($request->filled('category')) {
                $query->where('category', $request->input('category'));
            }
            
            // Filtre statut
            if ($request->filled('status')) {
                switch ($request->input('status')) {
                    case 'available':
                        $query->whereRaw('quantity > COALESCE(reorder_level, 5)');
                        break;
                    case 'low_stock':
                        $query->whereRaw('quantity <= COALESCE(reorder_level, 5) AND quantity > 0');
                        break;
                    case 'out_of_stock':
                        $query->where('quantity', '<=', 0);
                        break;
                }
            }
            
            $items = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

            // Statistiques de base (calculées sur TOUS les items, pas les filtrés)
            $totalItems = InventoryItem::where('user_id', $user->id)->count();
            
            // Valeur d'achat totale (coût)
            $totalCost = InventoryItem::where('user_id', $user->id)
                ->sum(\DB::raw('quantity * COALESCE(cost_per_unit, 0)'));
            
            // Valeur de vente totale
            $totalSellingValue = InventoryItem::where('user_id', $user->id)
                ->sum(\DB::raw('quantity * COALESCE(selling_price, 0)'));
            
            // Bénéfice potentiel
            $totalProfit = $totalSellingValue - $totalCost;
            
            // Marge moyenne
            $averageMargin = 0;
            if ($totalCost > 0) {
                $averageMargin = (($totalSellingValue - $totalCost) / $totalCost) * 100;
            }
            
            // Articles en stock faible
            $lowStockItems = InventoryItem::where('user_id', $user->id)
                ->whereRaw('quantity <= COALESCE(reorder_level, 5)')
                ->where('quantity', '>', 0)
                ->count();
            
            // Articles disponibles (stock > 0)
            $availableItems = InventoryItem::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->count();
            
            // Valeur totale (prix de vente × quantité)
            $totalValue = $totalSellingValue;
            
        } catch (\Exception $e) {
            // Créer un paginateur vide en cas d'erreur
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // items
                0,  // total
                20, // perPage
                1,  // currentPage
                ['path' => request()->url()]
            );
            
            return view('prestataire.inventory.index', [
                'items' => $emptyPaginator,
                'totalItems' => 0,
                'availableItems' => 0,
                'lowStockItems' => 0,
                'totalValue' => 0,
                'totalCost' => 0,
                'totalSellingValue' => 0,
                'totalProfit' => 0,
                'averageMargin' => 0,
                'tableNotExists' => true,
            ]);
        }

        return view('prestataire.inventory.index', compact(
            'items', 
            'totalItems',
            'availableItems',
            'lowStockItems',
            'totalValue',
            'totalCost',
            'totalSellingValue',
            'totalProfit',
            'averageMargin'
        ));
    }

    /**
     * Show creation form for prestataire inventory item
     */
    public function create()
    {
        return view('prestataire.inventory.create');
    }

    /**
     * Show detail page for prestataire inventory item
     */
    public function show(InventoryItem $item)
    {
        // Vérifier que l'item appartient à l'utilisateur
        if ($item->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cet article.');
        }
        
        // Charger les relations nécessaires
        $item->load(['urgentSales.reservations.client', 'urgentSale']);
        
        // Calculer les statistiques de réservation
        $reservedQty = 0;
        $soldQty = 0;
        $pendingReservations = collect();
        $confirmedReservations = collect();
        $completedReservations = collect();
        
        if ($item->urgentSales && $item->urgentSales->count() > 0) {
            foreach ($item->urgentSales as $urgentSale) {
                $reservedQty += $urgentSale->reserved_quantity ?? 0;
                $soldQty += $urgentSale->sold_quantity ?? 0;
                
                if ($urgentSale->reservations) {
                    $pendingReservations = $pendingReservations->merge(
                        $urgentSale->reservations->where('status', 'pending')
                    );
                    $confirmedReservations = $confirmedReservations->merge(
                        $urgentSale->reservations->where('status', 'confirmed')
                    );
                    $completedReservations = $completedReservations->merge(
                        $urgentSale->reservations->where('status', 'completed')
                    );
                }
            }
        } elseif ($item->urgentSale) {
            $reservedQty = $item->urgentSale->reserved_quantity ?? 0;
            $soldQty = $item->urgentSale->sold_quantity ?? 0;
            
            if ($item->urgentSale->reservations) {
                $pendingReservations = $item->urgentSale->reservations->where('status', 'pending');
                $confirmedReservations = $item->urgentSale->reservations->where('status', 'confirmed');
                $completedReservations = $item->urgentSale->reservations->where('status', 'completed');
            }
        }
        
        $availableStock = $item->quantity - $reservedQty - $soldQty;
        
        // Statistiques
        $stats = [
            'total_stock' => $item->quantity,
            'reserved' => $reservedQty,
            'sold' => $soldQty,
            'available' => max(0, $availableStock),
            'pending_count' => $pendingReservations->count(),
            'confirmed_count' => $confirmedReservations->count(),
            'completed_count' => $completedReservations->count(),
        ];
        
        return view('prestataire.inventory.show', compact(
            'item', 
            'stats', 
            'pendingReservations', 
            'confirmedReservations', 
            'completedReservations'
        ));
    }

    /**
     * Show edit form for prestataire inventory item
     */
    public function edit(InventoryItem $item)
    {
        // Vérifier que l'item appartient à l'utilisateur
        if ($item->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cet article.');
        }
        return view('prestataire.inventory.edit', compact('item'));
    }

    /**
     * Client analytics view wrapper
     */
    public function analytics()
    {
        $stats = $this->getAnalytics();
        return view('client.inventory.analytics', compact('stats'));
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin inventory index
     */
    public function adminIndex()
    {
        // Vérifier si la table inventory existe
        if (!TableExistenceCache::has('inventory')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.inventory.index', [
                'items' => $emptyPaginator,
                'stats' => [
                    'total_items' => 0,
                    'total_value' => 0,
                    'low_stock' => 0,
                    'users_with_inventory' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }

        try {
            $items = InventoryItem::with(['user'])
                ->latest()
                ->paginate(30);

            $stats = [
                'total_items' => InventoryItem::count(),
                'total_value' => InventoryItem::sum(\DB::raw('quantity * cost_per_unit')),
                'low_stock' => InventoryItem::whereRaw('quantity <= reorder_level')->count(),
                'users_with_inventory' => InventoryItem::distinct('user_id')->count('user_id'),
            ];

            return view('admin.inventory.index', compact('items', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.inventory.index', [
                'items' => $emptyPaginator,
                'stats' => [
                    'total_items' => 0,
                    'total_value' => 0,
                    'low_stock' => 0,
                    'users_with_inventory' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin all items
     */
    public function adminAllItems()
    {
        // Vérifier si la table inventory existe
        if (!TableExistenceCache::has('inventory')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 50, 1, ['path' => request()->url()]
            );
            return view('admin.inventory.all-items', [
                'items' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $items = InventoryItem::with(['user'])->paginate(50);
            return view('admin.inventory.all-items', compact('items'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 50, 1, ['path' => request()->url()]
            );
            return view('admin.inventory.all-items', [
                'items' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin destroy item
     */
    public function adminDestroy(InventoryItem $item)
    {
        $item->delete();
        return back()->with('success', 'Article supprimé');
    }

    /**
     * Admin analytics
     */
    public function adminAnalytics()
    {
        // Vérifier si la table inventory existe
        if (!TableExistenceCache::has('inventory')) {
            return view('admin.inventory.analytics', [
                'categoryStats' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $categoryStats = InventoryItem::groupBy('category')
                ->selectRaw('category, COUNT(*) as count, SUM(quantity * cost_per_unit) as value')
                ->get();

            return view('admin.inventory.analytics', compact('categoryStats'));
        } catch (\Exception $e) {
            return view('admin.inventory.analytics', [
                'categoryStats' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin export
     */
    public function adminExport()
    {
        // Vérifier si la table inventory existe
        if (!TableExistenceCache::has('inventory')) {
            return response("User,Name,Category,Quantity,Unit,Cost,Price,Location\n", 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="all_inventory_' . date('Y-m-d') . '.csv"',
            ]);
        }

        try {
            $items = InventoryItem::with('user')->get();

            $csv = "User,Name,Category,Quantity,Unit,Cost,Price,Location\n";

            foreach ($items as $item) {
                $csv .= "{$item->user?->name},{$item->name},{$item->category},{$item->quantity},";
                $csv .= "{$item->unit},{$item->cost_per_unit},{$item->selling_price},{$item->location}\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="all_inventory_' . date('Y-m-d') . '.csv"',
            ]);
        } catch (\Exception $e) {
            return response("User,Name,Category,Quantity,Unit,Cost,Price,Location\n", 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="all_inventory_' . date('Y-m-d') . '.csv"',
            ]);
        }
    }

    /**
     * Bulk action
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,export,update_category',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:inventory,id',
            'category' => 'required_if:action,update_category|string|max:50',
        ]);

        switch ($validated['action']) {
            case 'delete':
                InventoryItem::whereIn('id', $validated['item_ids'])->delete();
                return back()->with('success', count($validated['item_ids']) . ' articles supprimés');
            
            case 'update_category':
                InventoryItem::whereIn('id', $validated['item_ids'])
                    ->update(['category' => $validated['category']]);
                return back()->with('success', 'Catégorie mise à jour');
        }

        return back();
    }
}
