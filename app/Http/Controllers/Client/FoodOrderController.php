<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\FoodOrderItem;
use App\Models\FoodProduct;
use App\Models\Prestataire;
use App\Notifications\NewFoodOrder;
use App\Notifications\FoodOrderConfirmedByClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class FoodOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['menu', 'showProduct']);
    }

    /**
     * Afficher le menu d'un prestataire (page publique)
     */
    public function menu(Prestataire $prestataire)
    {
        // Vérifier que le prestataire a des produits alimentaires
        $products = $prestataire->foodProducts()
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        if ($products->isEmpty()) {
            abort(404, 'Ce prestataire n\'a pas de menu disponible.');
        }

        $categories = FoodProduct::categories();

        return view('client.food-orders.menu', compact('prestataire', 'products', 'categories'));
    }

    /**
     * Afficher un produit
     */
    public function showProduct(Prestataire $prestataire, FoodProduct $product)
    {
        if ($product->prestataire_id !== $prestataire->id) {
            abort(404);
        }

        return view('client.food-orders.product', compact('prestataire', 'product'));
    }

    /**
     * Afficher le panier (stocké en session)
     */
    public function cart(Prestataire $prestataire)
    {
        $cart = session()->get("food_cart.{$prestataire->id}", []);
        $cartItems = $this->getCartItems($prestataire, $cart);
        $totals = $this->calculateTotals($cartItems);
        $scheduleConfig = $this->buildCartScheduleConfig($cartItems);

        return view('client.food-orders.cart', compact('prestataire', 'cartItems', 'totals', 'scheduleConfig'));
    }

    /**
     * Ajouter au panier
     */
    public function addToCart(Request $request, Prestataire $prestataire, FoodProduct $product)
    {
        if ($product->prestataire_id !== $prestataire->id) {
            abort(404);
        }

        if (!$this->isFoodOpen($prestataire)) {
            return $this->restaurantClosedResponse($request, $prestataire);
        }

        if (!$product->is_available || !$product->isInStock()) {
            return back()->with('error', 'Ce produit n\'est pas disponible.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:50',
            'options' => 'nullable|array',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $cartKey = "food_cart.{$prestataire->id}";
        $cart = session()->get($cartKey, []);

        // Générer une clé unique pour cet article (produit + options)
        $itemKey = $product->id . '_' . md5(json_encode($request->options ?? []));

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $request->quantity;
        } else {
            $cart[$itemKey] = [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'options' => $request->options,
                'special_instructions' => $request->special_instructions,
            ];
        }

        session()->put($cartKey, $cart);

        if ($request->wantsJson() || $request->ajax()) {
            // Calculate cart total
            $cartTotal = 0;
            $cartCount = 0;
            foreach ($cart as $item) {
                $itemProduct = FoodProduct::find($item['product_id']);
                if ($itemProduct) {
                    $cartTotal += $itemProduct->price * $item['quantity'];
                    $cartCount += $item['quantity'];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier !',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'cart_url' => route('food.cart', $prestataire),
            ]);
        }

        return back()->with('success', 'Produit ajouté au panier !');
    }

    /**
     * Modifier la quantité dans le panier
     */
    public function updateCart(Request $request, Prestataire $prestataire)
    {
        $request->validate([
            'item_key' => 'required|string',
            'quantity' => 'required|integer|min:0|max:50',
        ]);

        $cartKey = "food_cart.{$prestataire->id}";
        $cart = session()->get($cartKey, []);

        if ($request->quantity === 0) {
            unset($cart[$request->item_key]);
        } elseif (isset($cart[$request->item_key])) {
            $cart[$request->item_key]['quantity'] = $request->quantity;
        }

        session()->put($cartKey, $cart);

        if ($request->wantsJson()) {
            $cartItems = $this->getCartItems($prestataire, $cart);
            $totals = $this->calculateTotals($cartItems);

            return response()->json([
                'success' => true,
                'cart_count' => array_sum(array_column($cart, 'quantity')),
                'totals' => $totals,
            ]);
        }

        return back()->with('success', 'Panier mis à jour !');
    }

    /**
     * Supprimer du panier
     */
    public function removeFromCart(Request $request, Prestataire $prestataire)
    {
        $request->validate([
            'item_key' => 'required|string',
        ]);

        $cartKey = "food_cart.{$prestataire->id}";
        $cart = session()->get($cartKey, []);

        unset($cart[$request->item_key]);

        session()->put($cartKey, $cart);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé du panier !',
                'cart_count' => array_sum(array_column($cart, 'quantity')),
            ]);
        }

        return back()->with('success', 'Produit supprimé du panier !');
    }

    /**
     * Vider le panier
     */
    public function clearCart(Prestataire $prestataire)
    {
        session()->forget("food_cart.{$prestataire->id}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Panier vidé !');
    }

    /**
     * Page de checkout
     */
    public function checkout(Prestataire $prestataire)
    {
        // Bloquer l'auto-commande pour les prestataires
        $user = Auth::user();
        if ($user && $user->role === 'prestataire' && $user->prestataire && $user->prestataire->id === $prestataire->id) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Vous ne pouvez pas commander vos propres produits.');
        }

        if (!$this->isFoodOpen($prestataire)) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Ce restaurant est actuellement fermé. La prise de commande est indisponible.');
        }
        
        $cart = session()->get("food_cart.{$prestataire->id}", []);

        if (empty($cart)) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Votre panier est vide.');
        }

        $cartItems = $this->getCartItems($prestataire, $cart);
        $totals = $this->calculateTotals($cartItems, 'pickup', $prestataire);
        $scheduleConfig = $this->buildCartScheduleConfig($cartItems);

        // Déterminer le payment_policy du panier (prendre le plus restrictif)
        // full_prepay > deposit > cash
        $paymentPolicy = 'cash';
        $depositPercent = 0;
        $foodPaymentsEnabled = function_exists('food_online_payment_enabled')
            ? food_online_payment_enabled()
            : !(function_exists('cash_only_mode') && cash_only_mode());

        if ($foodPaymentsEnabled) {
            foreach ($cartItems as $item) {
                $productPolicy = $item['product']->payment_policy ?? 'cash';
                $productDeposit = $item['product']->deposit_percent ?? 30;
                
                if ($productPolicy === 'full_prepay') {
                    $paymentPolicy = 'full_prepay';
                } elseif ($productPolicy === 'deposit' && $paymentPolicy !== 'full_prepay') {
                    $paymentPolicy = 'deposit';
                    $depositPercent = max($depositPercent, $productDeposit);
                }
            }
        }

        return view('client.food-orders.checkout', compact('prestataire', 'cartItems', 'totals', 'paymentPolicy', 'depositPercent', 'scheduleConfig'));
    }

    /**
     * Créer la commande
     */
    public function placeOrder(Request $request, Prestataire $prestataire)
    {
        // Bloquer l'auto-commande pour les prestataires
        $user = Auth::user();
        if ($user && $user->role === 'prestataire' && $user->prestataire && $user->prestataire->id === $prestataire->id) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Vous ne pouvez pas commander vos propres produits.');
        }

        if (!$this->isFoodOpen($prestataire)) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Ce restaurant est actuellement fermé. La prise de commande est indisponible.');
        }
        
        $cart = session()->get("food_cart.{$prestataire->id}", []);

        if (empty($cart)) {
            return redirect()->route('food.menu', $prestataire)
                ->with('error', 'Votre panier est vide.');
        }

        $cartItems = $this->getCartItems($prestataire, $cart);
        $scheduleConfig = $this->buildCartScheduleConfig($cartItems);

        $requestedAtRule = $scheduleConfig['requires_advance_order']
            ? 'required|date|after_or_equal:' . $scheduleConfig['earliest_date']->format('Y-m-d 00:00:00')
            : 'nullable|date|after_or_equal:now';

        $validated = $request->validate([
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string|max:500',
            'delivery_phone' => 'required_if:delivery_type,delivery|nullable|string|max:30',
            'delivery_contact_name' => 'nullable|string|max:100',
            'delivery_lat' => 'required_if:delivery_type,delivery|nullable|numeric',
            'delivery_lng' => 'required_if:delivery_type,delivery|nullable|numeric',
            'delivery_floor' => 'nullable|string|max:50',
            'delivery_door_code' => 'nullable|string|max:50',
            'delivery_building_info' => 'nullable|string|max:500',
            'requested_at' => $requestedAtRule,
            'notes' => 'nullable|string|max:1000',
        ]);
        $requestedAt = $this->normalizeRequestedAt($validated['requested_at'] ?? null, $scheduleConfig);

        if ($scheduleConfig['requires_advance_order']) {
            if (!$requestedAt) {
                return back()
                    ->withErrors(['requested_at' => 'Choisissez une date disponible pour ce panier sur commande.'])
                    ->withInput();
            }

            $requestedDay = $requestedAt->copy()->startOfDay();
            if ($requestedDay->lt($scheduleConfig['earliest_date'])) {
                return back()
                    ->withErrors([
                        'requested_at' => 'Choisissez une date à partir du ' . $scheduleConfig['earliest_date']->locale('fr')->translatedFormat('d/m/Y') . '.',
                    ])
                    ->withInput();
            }
        }
        
        // Calculer la distance si livraison
        $distance = null;
        if ($validated['delivery_type'] === 'delivery' && 
            !empty($validated['delivery_lat']) && 
            !empty($validated['delivery_lng'])) {
            $origin = $this->resolvePrestataireCoordinates($prestataire);

            if ($origin) {
                $distance = $this->calculateDistance(
                    (float) $validated['delivery_lat'],
                    (float) $validated['delivery_lng'],
                    $origin['lat'],
                    $origin['lng']
                );
            }
        }
        
        $totals = $this->calculateTotals($cartItems, $validated['delivery_type'], $prestataire, $distance);

        // Vérifier le rayon de livraison
        if ($validated['delivery_type'] === 'delivery') {
            if ($distance === null) {
                return back()->with('error', "Impossible de calculer la distance de livraison. Vérifiez l'adresse du prestataire et l'adresse client.");
            }

            $maxRadius = $prestataire->food_delivery_radius_km ?? 5;
            if ($distance > $maxRadius) {
                return back()->with('error', "L'adresse de livraison est à " . number_format($distance, 1) . " km. La zone de livraison est limitée à {$maxRadius} km.");
            }
        }

        // Vérifier le minimum de commande
        if ($validated['delivery_type'] === 'delivery' && $prestataire->food_min_order_delivery) {
            if ($totals['subtotal'] < $prestataire->food_min_order_delivery) {
                return back()->with('error', "Le montant minimum pour la livraison est de " . number_format($prestataire->food_min_order_delivery, 2) . " €.");
            }
        }
        if ($validated['delivery_type'] === 'pickup' && $prestataire->food_min_order_pickup) {
            if ($totals['subtotal'] < $prestataire->food_min_order_pickup) {
                return back()->with('error', "Le montant minimum pour le retrait est de " . number_format($prestataire->food_min_order_pickup, 2) . " €.");
            }
        }

        // Vérifier la disponibilité des produits
        foreach ($cartItems as $item) {
            if (!$item['product']->is_available) {
                return back()->with('error', "Le produit \"{$item['product']->name}\" n'est plus disponible.");
            }
            if (!$item['product']->isInStock()) {
                return back()->with('error', "Le produit \"{$item['product']->name}\" est en rupture de stock.");
            }
        }

        // Déterminer le payment_policy du panier (prendre le plus restrictif)
        $paymentPolicy = 'cash';
        $depositPercent = 0;
        $foodPaymentsEnabled = function_exists('food_online_payment_enabled')
            ? food_online_payment_enabled()
            : !(function_exists('cash_only_mode') && cash_only_mode());

        if ($foodPaymentsEnabled) {
            foreach ($cartItems as $item) {
                $productPolicy = $item['product']->payment_policy ?? 'cash';
                $productDeposit = $item['product']->deposit_percent ?? 30;
                
                if ($productPolicy === 'full_prepay') {
                    $paymentPolicy = 'full_prepay';
                } elseif ($productPolicy === 'deposit' && $paymentPolicy !== 'full_prepay') {
                    $paymentPolicy = 'deposit';
                    $depositPercent = max($depositPercent, $productDeposit);
                }
            }
        }

        try {
            DB::beginTransaction();

            // Déterminer le statut de paiement initial selon le policy
            $paymentStatus = FoodOrder::PAYMENT_PENDING;
            if ($paymentPolicy === 'cash') {
                $paymentStatus = FoodOrder::PAYMENT_PENDING; // Sera payé à la réception
            }
            // Pour deposit et full_prepay, on garde pending - le client devra payer

            // Créer la commande
            $order = FoodOrder::create([
                'client_id' => Auth::id(),
                'prestataire_id' => $prestataire->id,
                'status' => FoodOrder::STATUS_PENDING,
                'subtotal' => $totals['subtotal'],
                'service_fee' => $totals['service_fee'],
                'delivery_fee' => $totals['delivery_fee'],
                'total' => $totals['total'],
                'delivery_type' => $validated['delivery_type'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
                'delivery_contact_name' => $validated['delivery_contact_name'] ?? null,
                'delivery_lat' => $validated['delivery_lat'] ?? null,
                'delivery_lng' => $validated['delivery_lng'] ?? null,
                'delivery_floor' => $validated['delivery_floor'] ?? null,
                'delivery_door_code' => $validated['delivery_door_code'] ?? null,
                'delivery_building_info' => $validated['delivery_building_info'] ?? null,
                'notes' => $validated['notes'] ?? null,
                // Date/heure souhaitée (si vide, commande immédiate)
                'requested_at' => $requestedAt ? $requestedAt->toDateTimeString() : now(),
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentPolicy === 'cash' ? 'cash' : 'online',
            ]);

            // Créer les items
            foreach ($cartItems as $item) {
                FoodOrderItem::create([
                    'food_order_id' => $order->id,
                    'food_product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                    'total_price' => $item['product']->price * $item['quantity'],
                    'options' => $item['options'] ?? null,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);

                // Décrémenter le stock
                $item['product']->decrementStock($item['quantity']);
            }

            // Vider le panier
            session()->forget("food_cart.{$prestataire->id}");

            DB::commit();

            // Si paiement en ligne requis, rediriger vers la page de paiement SANS notifier le prestataire
            // La notification sera envoyée après le paiement
            if ($paymentPolicy !== 'cash') {
                return redirect()->route('food.orders.payment', $order)
                    ->with('info', 'Veuillez procéder au paiement pour confirmer votre commande.');
            }

            // Paiement cash : notifier le prestataire immédiatement
            try {
                $prestataire->user->notify(new NewFoodOrder($order));
            } catch (\Exception $e) {
                \Log::error('Erreur notification NewFoodOrder: ' . $e->getMessage());
            }

            return redirect()->route('food.orders.show', $order)
                ->with('success', 'Votre commande a été passée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création commande: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }

    /**
     * Liste des commandes du client
     */
    public function myOrders(Request $request)
    {
        $statusOptions = FoodOrder::statuses();
        $deliveryTypeOptions = FoodOrder::deliveryTypes();

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
            'delivery_type' => (string) $request->query('delivery_type', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        if (!array_key_exists($filters['status'], $statusOptions)) {
            $filters['status'] = '';
        }

        if (!array_key_exists($filters['delivery_type'], $deliveryTypeOptions)) {
            $filters['delivery_type'] = '';
        }

        $dateFrom = null;
        $dateTo = null;

        if ($filters['date_from'] !== '') {
            try {
                $dateFrom = Carbon::createFromFormat('Y-m-d', $filters['date_from'])->startOfDay();
            } catch (\Throwable $e) {
                $filters['date_from'] = '';
            }
        }

        if ($filters['date_to'] !== '') {
            try {
                $dateTo = Carbon::createFromFormat('Y-m-d', $filters['date_to'])->endOfDay();
            } catch (\Throwable $e) {
                $filters['date_to'] = '';
            }
        }

        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        $baseQuery = FoodOrder::where('client_id', Auth::id());

        $orders = (clone $baseQuery)
            ->with(['prestataire', 'items'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('prestataire', function ($prestataireQuery) use ($search) {
                            $prestataireQuery
                                ->where('company_name', 'like', "%{$search}%")
                                ->orWhere('business_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items', function ($itemsQuery) use ($search) {
                            $itemsQuery->where('product_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['delivery_type'] !== '', function ($query) use ($filters) {
                $query->where('delivery_type', $filters['delivery_type']);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $hasAnyOrders = (clone $baseQuery)->exists();
        $hasActiveFilters = collect($filters)->contains(fn ($value) => $value !== '');
        $activeFiltersCount = collect($filters)->filter(fn ($value) => $value !== '')->count();

        return view('client.food-orders.my-orders', compact(
            'orders',
            'filters',
            'statusOptions',
            'deliveryTypeOptions',
            'hasAnyOrders',
            'hasActiveFilters',
            'activeFiltersCount'
        ));
    }

    /**
     * Afficher une commande
     */
    public function show(FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        $foodOrder->load(['prestataire', 'items.foodProduct']);

        return view('client.food-orders.show', compact('foodOrder'));
    }

    /**
     * Annuler une commande
     */
    public function cancel(Request $request, FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        if (!$foodOrder->canBeCancelled()) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Annulée par le client';
        }

        $breakdown = $foodOrder->getCancellationBreakdown('client');
        $financialMessage = '';

        if ($breakdown['action'] === 'cancel_authorization') {
            $cancelSuccess = $foodOrder->cancelAuthorization("Annulation client: {$reason}");
            if (!$cancelSuccess) {
                return back()->with('error', 'Impossible d’annuler le paiement autorisé pour le moment. Réessayez dans quelques instants.');
            }
            $financialMessage = ' Aucune somme n’a été débitée définitivement (autorisation Stripe annulée).';
        } elseif ($breakdown['action'] === 'refund') {
            $refundAmount = (float) ($breakdown['client_refund_amount'] ?? 0);
            if ($refundAmount > 0) {
                $refundSuccess = $foodOrder->refundPayment("Annulation client: {$reason}", $refundAmount);
                if (!$refundSuccess) {
                    return back()->with('error', 'Impossible de finaliser le remboursement pour le moment. Réessayez dans quelques instants.');
                }
            }

            $amountPaid = number_format((float) ($breakdown['amount_paid'] ?? 0), 2, ',', ' ');
            $stripeFee = number_format((float) ($breakdown['stripe_fee_amount'] ?? 0), 2, ',', ' ');
            $refunded = number_format($refundAmount, 2, ',', ' ');
            $financialMessage = " Montant payé: {$amountPaid} €. Frais Stripe: {$stripeFee} €. Montant remboursé: {$refunded} €.";
        }

        $foodOrder->cancel($reason);

        if (Schema::hasColumn('food_orders', 'cancelled_by')) {
            $foodOrder->forceFill(['cancelled_by' => 'client'])->save();
        }

        return back()->with('success', 'Commande annulée.' . $financialMessage);
    }

    /**
     * Confirmer la réception (côté client)
     */
    public function confirmReception(FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        if (!$foodOrder->isDelivered()) {
            return back()->with('error', 'Cette commande n\'a pas encore été livrée.');
        }

        $foodOrder->confirmByClient();

        // Notifier si les deux ont confirmé
        if ($foodOrder->fresh()->isCompleted()) {
            try {
                $foodOrder->prestataire->user->notify(new FoodOrderConfirmedByClient($foodOrder));
            } catch (\Exception $e) {
                \Log::error('Erreur notification FoodOrderConfirmedByClient: ' . $e->getMessage());
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Réception confirmée !',
            ]);
        }

        return back()->with('success', 'Réception confirmée ! Merci pour votre commande.');
    }

    /**
     * Suivre une commande - page de suivi temps réel avec GPS livreur
     */
    public function track(FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        return view('client.food-orders.track', compact('foodOrder'));
    }

    /**
     * API pour obtenir le statut actuel + position livreur
     */
    public function getStatus(FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        $data = [
            'status' => $foodOrder->status,
            'status_label' => $foodOrder->status_label,
            'status_color' => $foodOrder->status_color,
            'updated_at' => $foodOrder->updated_at->toIso8601String(),
            'ready_at' => $foodOrder->ready_at?->toIso8601String(),
            'delivered_at' => $foodOrder->delivered_at?->toIso8601String(),
            'delivery_code' => in_array($foodOrder->status, ['ready', 'delivered']) ? $foodOrder->delivery_code : null,
        ];

        // Inclure la position du livreur si livraison en cours
        if ($foodOrder->driver_id && $foodOrder->delivery_type === 'delivery'
            && in_array($foodOrder->status, ['ready', 'preparing', 'accepted'])) {
            $driver = $foodOrder->driver;
            if ($driver && $driver->current_lat && $driver->current_lng) {
                $staleSeconds = config('delivery.driver_location_stale_seconds', 60);
                $isOnline = $driver->last_location_update 
                    && $driver->last_location_update->diffInSeconds(now()) < $staleSeconds;

                $data['driver'] = [
                    'lat' => (float) $driver->current_lat,
                    'lng' => (float) $driver->current_lng,
                    'name' => $driver->first_name ?? 'Livreur',
                    'updated_at' => $driver->last_location_update?->toIso8601String(),
                    'is_online' => $isOnline,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * API dédiée position livreur temps réel (polling 5s côté client)
     */
    public function driverLocation(FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        if (!$foodOrder->driver_id || $foodOrder->delivery_type !== 'delivery') {
            return response()->json(['driver' => null]);
        }

        $driver = $foodOrder->driver;
        if (!$driver || !$driver->current_lat || !$driver->current_lng) {
            return response()->json(['driver' => null]);
        }

        $staleSeconds = config('delivery.driver_location_stale_seconds', 60);
        $isOnline = $driver->last_location_update 
            && $driver->last_location_update->diffInSeconds(now()) < $staleSeconds;

        // Points d'intérêt pour le tracé du parcours
        $restaurant = [
            'lat' => (float) ($foodOrder->prestataire->latitude ?? 0),
            'lng' => (float) ($foodOrder->prestataire->longitude ?? 0),
        ];
        $destination = [
            'lat' => (float) ($foodOrder->delivery_lat ?? 0),
            'lng' => (float) ($foodOrder->delivery_lng ?? 0),
        ];

        return response()->json([
            'driver' => [
                'lat' => (float) $driver->current_lat,
                'lng' => (float) $driver->current_lng,
                'name' => $driver->first_name ?? 'Livreur',
                'phone' => $driver->phone ?? null,
                'vehicle_type' => $driver->vehicle_type ?? 'scooter',
                'updated_at' => $driver->last_location_update?->toIso8601String(),
                'is_online' => $isOnline,
            ],
            'restaurant' => $restaurant,
            'destination' => $destination,
            'order_status' => $foodOrder->status,
        ]);
    }

    /**
     * Laisser un avis
     */
    public function rate(Request $request, FoodOrder $foodOrder)
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403);
        }

        if (!$foodOrder->isCompleted()) {
            return back()->with('error', 'Vous pouvez noter uniquement les commandes terminées.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'driver_rating' => 'nullable|integer|min:1|max:5',
            'driver_comment' => 'nullable|string|max:500',
        ]);

        // Créer ou mettre à jour l'avis sur la commande
        $foodOrder->update([
            'rating' => $request->rating,
            'rating_comment' => $request->comment,
            'rated_at' => now(),
        ]);

        // Noter le livreur si livraison et note fournie
        if ($request->filled('driver_rating') && $foodOrder->driver_id && $foodOrder->delivery_type === 'delivery') {
            try {
                $driverRatingData = [
                    'driver_id' => $foodOrder->driver_id,
                    'client_id' => Auth::id(),
                    'food_order_id' => $foodOrder->id,
                    'rating' => $request->driver_rating,
                    'comment' => $request->driver_comment,
                ];

                // Utiliser DriverClientRating si disponible, sinon fallback
                if (class_exists(\App\Models\DriverClientRating::class)) {
                    \App\Models\DriverClientRating::updateOrCreate(
                        ['food_order_id' => $foodOrder->id, 'client_id' => Auth::id()],
                        $driverRatingData
                    );
                }

                // Recalculer la note moyenne du livreur
                $driver = $foodOrder->driver;
                if ($driver && class_exists(\App\Models\DriverClientRating::class)) {
                    $avgRating = \App\Models\DriverClientRating::where('driver_id', $driver->id)->avg('rating');
                    if ($avgRating) {
                        $driver->update(['rating' => round($avgRating, 2)]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Erreur notation livreur: ' . $e->getMessage());
                // On ne bloque pas — la notation de la commande est déjà sauvée
            }
        }

        return back()->with('success', 'Merci pour votre avis !');
    }

    /**
     * Récupérer les items du panier avec les produits
     */
    protected function getCartItems(Prestataire $prestataire, array $cart): array
    {
        $productIds = array_unique(array_column($cart, 'product_id'));
        $products = FoodProduct::whereIn('id', $productIds)
            ->where('prestataire_id', $prestataire->id)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($cart as $key => $item) {
            if (isset($products[$item['product_id']])) {
                $items[$key] = [
                    'key' => $key,
                    'product' => $products[$item['product_id']],
                    'quantity' => $item['quantity'],
                    'options' => $item['options'] ?? null,
                    'special_instructions' => $item['special_instructions'] ?? null,
                    'total' => $products[$item['product_id']]->price * $item['quantity'],
                ];
            }
        }

        return $items;
    }

    protected function buildCartScheduleConfig(array $cartItems): array
    {
        $today = now()->startOfDay();
        $preorderProducts = collect($cartItems)
            ->pluck('product')
            ->filter(function ($product) {
                return $product instanceof FoodProduct
                    && method_exists($product, 'requiresAdvanceOrder')
                    && $product->requiresAdvanceOrder();
            })
            ->values();

        $requiresAdvanceOrder = $preorderProducts->isNotEmpty();
        $minPreorderDays = $requiresAdvanceOrder
            ? (int) $preorderProducts->max(fn (FoodProduct $product) => (int) $product->min_preorder_days)
            : 0;
        $earliestDate = $today->copy()->addDays($minPreorderDays);

        $dateOptions = [];
        if ($requiresAdvanceOrder) {
            for ($offset = $minPreorderDays; $offset <= $minPreorderDays + 30; $offset++) {
                $date = $today->copy()->addDays($offset);
                $dateOptions[] = [
                    'value' => $date->copy()->setTime(12, 0)->format('Y-m-d\TH:i'),
                    'date' => $date->toDateString(),
                    'label' => ucfirst($date->locale('fr')->translatedFormat('l d F Y')),
                    'short_label' => $offset === $minPreorderDays
                        ? 'Première date dispo'
                        : 'J+' . $offset,
                ];
            }
        }

        return [
            'requires_advance_order' => $requiresAdvanceOrder,
            'min_preorder_days' => $minPreorderDays,
            'earliest_date' => $earliestDate,
            'earliest_date_label' => ucfirst($earliestDate->locale('fr')->translatedFormat('l d F Y')),
            'date_options' => $dateOptions,
            'product_names' => $preorderProducts->pluck('name')->unique()->values()->all(),
        ];
    }

    protected function normalizeRequestedAt(?string $requestedAt, array $scheduleConfig): ?Carbon
    {
        if (!$requestedAt) {
            return null;
        }

        try {
            $parsed = Carbon::parse($requestedAt);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$scheduleConfig['requires_advance_order']) {
            return $parsed;
        }

        return $parsed->copy()->startOfDay()->setTime(12, 0);
    }

    /**
     * Calculer la distance en km entre deux points (formule Haversine)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // Rayon de la Terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    /**
     * Retourne les coordonnees du prestataire (profil), pas celles d'une annonce.
     */
    protected function resolvePrestataireCoordinates(Prestataire $prestataire): ?array
    {
        $lat = (float) ($prestataire->latitude ?? 0);
        $lng = (float) ($prestataire->longitude ?? 0);

        if ($lat !== 0.0 && $lng !== 0.0) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        $cityFallbacks = [
            'paris' => [48.8566, 2.3522],
            'marseille' => [43.2965, 5.3698],
            'lyon' => [45.7640, 4.8357],
            'toulouse' => [43.6047, 1.4442],
            'nice' => [43.7102, 7.2620],
            'nantes' => [47.2184, -1.5536],
            'montpellier' => [43.6110, 3.8767],
            'strasbourg' => [48.5734, 7.7521],
            'bordeaux' => [44.8378, -0.5792],
            'lille' => [50.6292, 3.0573],
            'rennes' => [48.1173, -1.6778],
            'saint-etienne' => [45.4397, 4.3872],
            'saint etienne' => [45.4397, 4.3872],
        ];

        $cityKey = strtolower(trim((string) ($prestataire->city ?? '')));
        if ($cityKey !== '' && isset($cityFallbacks[$cityKey])) {
            [$lat, $lng] = $cityFallbacks[$cityKey];
            $this->persistPrestataireCoordinates($prestataire, $lat, $lng);
            return ['lat' => $lat, 'lng' => $lng];
        }

        $addressParts = array_filter([
            trim((string) ($prestataire->address ?? '')),
            trim((string) ($prestataire->postal_code ?? '')),
            trim((string) ($prestataire->city ?? '')),
            trim((string) ($prestataire->country ?: 'France')),
        ], static fn ($part) => $part !== '');

        if (empty($addressParts)) {
            return null;
        }

        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => implode(', ', $addressParts),
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

            $context = stream_context_create([
                'http' => [
                    'timeout' => 4,
                    'user_agent' => 'TaPrestation/1.0 (Food order distance)',
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                return null;
            }

            $data = json_decode($response, true);
            if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
                return null;
            }

            $lat = (float) $data[0]['lat'];
            $lng = (float) $data[0]['lon'];
            if ($lat === 0.0 || $lng === 0.0) {
                return null;
            }

            $this->persistPrestataireCoordinates($prestataire, $lat, $lng);

            return ['lat' => $lat, 'lng' => $lng];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function persistPrestataireCoordinates(Prestataire $prestataire, float $lat, float $lng): void
    {
        try {
            $prestataire->forceFill([
                'latitude' => round($lat, 8),
                'longitude' => round($lng, 8),
            ])->save();
        } catch (\Throwable $e) {
            // Le calcul continue meme si la sauvegarde echoue.
        }
    }

    protected function isFoodOpen(Prestataire $prestataire): bool
    {
        return (bool) ($prestataire->food_is_open ?? true);
    }

    protected function restaurantClosedResponse(Request $request, Prestataire $prestataire)
    {
        $message = 'Ce restaurant est actuellement fermé. La prise de commande est indisponible.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 409);
        }

        return redirect()->route('food.menu', $prestataire)
            ->with('error', $message);
    }

    /**
     * Calculer les totaux
     */
    protected function calculateTotals(array $cartItems, string $deliveryType = 'pickup', ?Prestataire $prestataire = null, ?float $distance = null): array
    {
        $subtotal = array_sum(array_column($cartItems, 'total'));
        
        // Frais de service client - récupéré dynamiquement depuis les paramètres
        $clientFeeRate = (float) get_setting('commission_client_food', '0');
        $serviceFee = $subtotal * ($clientFeeRate / 100);
        
        // Calcul des frais de livraison basé sur les paramètres du prestataire
        $deliveryFee = 0;
        if ($deliveryType === 'delivery' && $prestataire) {
            if ($prestataire->food_delivery_enabled) {
                // Frais de base + frais par km
                $deliveryFee = $prestataire->food_delivery_base_fee ?? 3.00;
                if ($distance && ($prestataire->food_delivery_fee_per_km ?? 0) > 0) {
                    $deliveryFee += $distance * $prestataire->food_delivery_fee_per_km;
                }
                
                // Livraison gratuite au-dessus d'un certain montant
                if ($prestataire->food_free_delivery_above && $subtotal >= $prestataire->food_free_delivery_above) {
                    $deliveryFee = 0;
                }
            }
            // Si la livraison n'est pas activée, pas de frais (le bouton ne devrait pas être visible)
        }

        return [
            'subtotal' => round($subtotal, 2),
            'service_fee' => round($serviceFee, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'total' => round($subtotal + $serviceFee + $deliveryFee, 2),
            'free_delivery_above' => $prestataire->food_free_delivery_above ?? null,
            'min_order_delivery' => $prestataire->food_min_order_delivery ?? null,
            'min_order_pickup' => $prestataire->food_min_order_pickup ?? null,
        ];
    }

    /**
     * Proxy pour OpenRouteService - évite d'exposer la clé API côté client
     */
    public function routeProxy(Request $request)
    {
        $request->validate([
            'start_lng' => 'required|numeric|between:-180,180',
            'start_lat' => 'required|numeric|between:-90,90',
            'end_lng' => 'required|numeric|between:-180,180',
            'end_lat' => 'required|numeric|between:-90,90',
        ]);

        $apiKey = config('delivery.routing.api_key', config('services.openrouteservice.key', ''));
        if (empty($apiKey)) {
            return response()->json(['error' => 'Service de routage non configuré'], 503);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(10)->get('https://api.openrouteservice.org/v2/directions/driving-car', [
                'start' => $request->start_lng . ',' . $request->start_lat,
                'end' => $request->end_lng . ',' . $request->end_lat,
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erreur du service de routage'], 502);
        }
    }
}
