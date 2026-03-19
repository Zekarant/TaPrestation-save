<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\FoodProduct;
use App\Traits\ChecksPrestatairePrerequisites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FoodProductController extends Controller
{
    use ChecksPrestatairePrerequisites;
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Liste des produits alimentaires du prestataire
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        $query = $prestataire->foodProducts();

        // Filtres
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_available', $request->status === 'available');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('sort_order')->orderBy('name')->paginate(12);
        $categories = FoodProduct::categories();

        return view('prestataire.food-products.index', compact('products', 'categories', 'prestataire'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        $categories = FoodProduct::categories();

        return view('prestataire.food-products.create', compact('categories', 'prestataire'));
    }

    /**
     * Enregistrer un nouveau produit
     */
    public function store(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        // À la validation de cette page (submit), si le prestataire active un paiement en ligne,
        // exiger un compte Stripe connecté/activé.
        $paymentPolicy = function_exists('normalize_payment_policy_for_mode')
            ? normalize_payment_policy_for_mode($request->input('payment_policy', 'cash'))
            : $request->input('payment_policy', 'cash');
        if ($paymentPolicy !== 'cash') {
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.food-products.create'));
            if ($redirect) {
                session(['food_product_draft' => $request->except(['_token', 'image'])]);
                return $redirect;
            }
        }

        $validated = $request->validate($this->foodProductRules());

        // Upload de l'image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('food-products', 'public');
        }

        $validated = $this->normalizeFoodProductData($validated, $request);
        $validated['prestataire_id'] = $prestataire->id;
        
        // Définir l'ordre de tri
        $maxOrder = $prestataire->foodProducts()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        FoodProduct::create($validated);

        return redirect()->route('prestataire.food-products.index')
            ->with('success', 'Produit ajouté avec succès !');
    }

    /**
     * Afficher un produit
     */
    public function show(FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        return view('prestataire.food-products.show', compact('foodProduct'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        $categories = FoodProduct::categories();
        $prestataire = Auth::user()->prestataire;

        return view('prestataire.food-products.edit', compact('foodProduct', 'categories', 'prestataire'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        // Vérifier si paiement en ligne sélectionné et compte Stripe configuré
        $paymentPolicy = function_exists('normalize_payment_policy_for_mode')
            ? normalize_payment_policy_for_mode($request->input('payment_policy', 'cash'))
            : $request->input('payment_policy', 'cash');
        if ($paymentPolicy !== 'cash') {
            $redirect = $this->redirectIfPaymentRequired($paymentPolicy, route('prestataire.food-products.edit', $foodProduct));
            if ($redirect) {
                session(['food_product_edit_draft_' . $foodProduct->id => $request->except(['_token', '_method', 'image'])]);
                return $redirect;
            }
        }

        $validated = $request->validate($this->foodProductRules());

        // Upload de la nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($foodProduct->image) {
                Storage::disk('public')->delete($foodProduct->image);
            }
            $validated['image'] = $request->file('image')->store('food-products', 'public');
        }

        $validated = $this->normalizeFoodProductData($validated, $request);

        $foodProduct->update($validated);

        return redirect()->route('prestataire.food-products.index')
            ->with('success', 'Produit modifié avec succès !');
    }

    protected function foodProductRules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category' => 'required|string|in:' . implode(',', array_keys(FoodProduct::categories())),
            'is_available' => 'boolean',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'stock' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
            'payment_policy' => 'nullable|string|in:cash,deposit,full_prepay',
            'deposit_percent' => 'nullable|integer|min:10|max:100',
        ];

        if (FoodProduct::supportsAdvanceOrder()) {
            $rules['is_preorder_only'] = 'nullable|boolean';
            $rules['min_preorder_days'] = 'nullable|integer|min:2|max:90|required_if:is_preorder_only,1';
        }

        return $rules;
    }

    protected function normalizeFoodProductData(array $validated, Request $request): array
    {
        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['payment_policy'] = function_exists('normalize_payment_policy_for_mode')
            ? normalize_payment_policy_for_mode($validated['payment_policy'] ?? 'cash')
            : ($validated['payment_policy'] ?? 'cash');
        $validated['deposit_percent'] = $validated['payment_policy'] === 'deposit'
            ? (int) ($validated['deposit_percent'] ?? 30)
            : 0;

        if (FoodProduct::supportsAdvanceOrder()) {
            $validated['is_preorder_only'] = $request->boolean('is_preorder_only', false);
            $validated['min_preorder_days'] = $validated['is_preorder_only']
                ? (int) ($validated['min_preorder_days'] ?? 2)
                : null;
        }

        return $validated;
    }

    /**
     * Supprimer un produit
     */
    public function destroy(FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        // Supprimer l'image
        if ($foodProduct->image) {
            Storage::disk('public')->delete($foodProduct->image);
        }

        $foodProduct->delete();

        return redirect()->route('prestataire.food-products.index')
            ->with('success', 'Produit supprimé avec succès !');
    }

    /**
     * Changer la disponibilité
     */
    public function toggleAvailability(FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        $foodProduct->update([
            'is_available' => !$foodProduct->is_available,
        ]);

        // Si requête AJAX, retourner JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_available' => $foodProduct->is_available,
                'message' => $foodProduct->is_available ? 'Produit disponible' : 'Produit indisponible',
            ]);
        }

        // Sinon rediriger avec message
        return redirect()->back()->with('success', $foodProduct->is_available ? 'Produit activé' : 'Produit mis en pause');
    }

    /**
     * Réorganiser les produits
     */
    public function reorder(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'integer|exists:food_products,id',
        ]);

        foreach ($request->products as $index => $productId) {
            FoodProduct::where('id', $productId)
                ->where('prestataire_id', $prestataire->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Dupliquer un produit
     */
    public function duplicate(FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        $newProduct = $foodProduct->replicate();
        $newProduct->name = $foodProduct->name . ' (copie)';
        $newProduct->is_available = false;
        $newProduct->sort_order = $foodProduct->prestataire->foodProducts()->max('sort_order') + 1;
        
        // Copier l'image si elle existe
        if ($foodProduct->image) {
            $extension = pathinfo($foodProduct->image, PATHINFO_EXTENSION);
            $newPath = 'food-products/' . uniqid() . '.' . $extension;
            Storage::disk('public')->copy($foodProduct->image, $newPath);
            $newProduct->image = $newPath;
        }
        
        $newProduct->save();

        return redirect()->route('prestataire.food-products.edit', $newProduct)
            ->with('success', 'Produit dupliqué avec succès !');
    }

    /**
     * Mettre à jour le stock
     */
    public function updateStock(Request $request, FoodProduct $foodProduct)
    {
        $this->authorizeProduct($foodProduct);

        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $foodProduct->update(['stock' => $request->stock]);

        return response()->json([
            'success' => true,
            'stock' => $foodProduct->stock,
        ]);
    }

    /**
     * Vérifier que le produit appartient au prestataire connecté
     */
    protected function authorizeProduct(FoodProduct $foodProduct): void
    {
        $prestataire = Auth::user()->prestataire;

        if (!$prestataire || $foodProduct->prestataire_id !== $prestataire->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce produit.');
        }
    }
}
