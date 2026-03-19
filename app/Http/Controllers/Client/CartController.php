<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\EquipmentRentalRequest;
use App\Models\UrgentSale;
use App\Services\CartPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Support\TableExistenceCache;
class CartController extends Controller
{
    /**
     * Resolve CartPricingService lazily to avoid constructor DI failures
     */
    private function pricing(): CartPricingService
    {
        return app(CartPricingService::class);
    }

    public function index()
    {
        $emptyData = [
            'cart' => null,
            'totals' => ['deposit' => 0, 'full' => 0, 'count' => 0],
            'canPayOnline' => false,
        ];

        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                return view('cart.index', $emptyData);
            }

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart) {
                return view('cart.index', $emptyData);
            }

            // Charger les items avec gestion d'erreur
            try {
                $cart->load('items.purchasable');
            } catch (\Throwable $e) {
                Log::warning('Cart: erreur chargement items', ['error' => $e->getMessage()]);
                $cart->setRelation('items', collect());
            }

            // Filtrer les items dont le purchasable a été supprimé
            $validItems = $cart->items->filter(fn ($item) => $item->purchasable !== null);
            $cart->setRelation('items', $validItems);

            $totals = [
                'deposit' => $validItems->sum(fn (CartItem $i) => (float) ($i->line_deposit ?? 0)),
                'full'    => $validItems->sum(fn (CartItem $i) => (float) ($i->line_total ?? 0)),
                'count'   => $validItems->count(),
            ];

            // Vérifier si les articles du panier supportent le paiement en ligne
            // Même logique que sur la page annonce : payment_requirement=full + moyen de paiement configuré
            $canPayOnline = false;
            try {
                if ($validItems->isNotEmpty()) {
                    $canPayOnline = $validItems->every(function ($item) {
                        $p = $item->purchasable;
                        if (!$p) return false;

                        if ($p instanceof UrgentSale) {
                            $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                                ? normalize_payment_requirement_for_mode($p->payment_requirement ?? 'none')
                                : ($p->payment_requirement ?? 'none');
                            $hasOnlinePayment = $paymentRequirement === 'full';
                            $presta = $p->prestataire;
                            $hasAnyPaymentMethod = $presta && !empty($presta->stripe_account_id);
                            return $hasOnlinePayment && $hasAnyPaymentMethod;
                        }
                        if ($p instanceof Booking) {
                            $payReq = function_exists('normalize_payment_requirement_for_mode')
                                ? normalize_payment_requirement_for_mode($p->service?->payment_requirement ?? 'none')
                                : ($p->service?->payment_requirement ?? 'none');
                            if ($payReq === 'none') return false;
                            $presta = $p->prestataire ?? ($p->service?->prestataire ?? null);
                            return $presta && !empty($presta->stripe_account_id);
                        }
                        if ($p instanceof EquipmentRentalRequest) {
                            $payReq = function_exists('normalize_payment_requirement_for_mode')
                                ? normalize_payment_requirement_for_mode($p->equipment?->payment_requirement ?? 'none')
                                : ($p->equipment?->payment_requirement ?? 'none');
                            if ($payReq === 'none') return false;
                            $presta = $p->equipment?->prestataire ?? null;
                            return $presta && !empty($presta->stripe_account_id);
                        }
                        return false;
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Cart: erreur calcul canPayOnline', ['error' => $e->getMessage()]);
                $canPayOnline = false;
            }

            return view('cart.index', compact('cart', 'totals', 'canPayOnline'));
        } catch (\Throwable $e) {
            Log::error('Cart index erreur: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Tenter de rendre la vue vide, sinon réponse HTML brute
            try {
                return view('cart.index', $emptyData);
            } catch (\Throwable $e2) {
                Log::error('Cart index vue fallback erreur: ' . $e2->getMessage());
                return response('<html><body><h2>Panier</h2><p>Votre panier est vide.</p><a href="/">Retour à l\'accueil</a></body></html>', 200);
            }
        }
    }

    public function addBooking(Booking $booking)
    {
        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $this->authorize('view', $booking);

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $pricing = $this->pricing()->pricingFor($booking, 1);

            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_type' => $booking->getMorphClass(),
                    'purchasable_id' => $booking->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $pricing['unit_price'],
                    'line_total' => $pricing['line_total'],
                    'line_deposit' => $pricing['line_deposit'],
                    'currency' => $pricing['currency'],
                ]
            );

            return redirect()->route('client.cart.index')->with('success', 'Réservation ajoutée au panier.');
        } catch (\Throwable $e) {
            Log::error('addBooking erreur: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'ajout au panier.');
        }
    }

    public function addRentalRequest(EquipmentRentalRequest $request)
    {
        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $clientId = auth()->user()?->client?->id;
            if (!$clientId || (int) $request->client_id !== (int) $clientId) {
                abort(403);
            }

            // Vérifier que le paiement en ligne est possible
            $eqPayReq = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($request->equipment?->payment_requirement ?? 'none')
                : ($request->equipment?->payment_requirement ?? 'none');
            if ($eqPayReq === 'none') {
                return back()->with('error', 'Cet équipement n\'est pas configuré pour le paiement en ligne.');
            }
            if (empty($request->equipment?->prestataire?->stripe_account_id)) {
                return back()->with('error', 'Le prestataire n\'a pas configuré le paiement en ligne.');
            }

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $pricing = $this->pricing()->pricingFor($request, 1);

            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_type' => $request->getMorphClass(),
                    'purchasable_id' => $request->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $pricing['unit_price'],
                    'line_total' => $pricing['line_total'],
                    'line_deposit' => $pricing['line_deposit'],
                    'currency' => $pricing['currency'],
                ]
            );

            return redirect()->route('client.cart.index')->with('success', 'Location ajoutée au panier.');
        } catch (\Throwable $e) {
            Log::error('addRentalRequest erreur: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'ajout au panier.');
        }
    }

    public function addUrgentSale(Request $httpRequest, UrgentSale $urgentSale)
    {
        $isAjax = $httpRequest->expectsJson();

        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                $msg = 'Le système de panier n\'est pas encore configuré.';
                return $isAjax ? response()->json(['message' => $msg], 500) : back()->with('error', $msg);
            }

            $httpRequest->validate([
                'quantity' => 'nullable|integer|min:1',
            ]);

            $quantity = (int) ($httpRequest->input('quantity') ?? 1);

            if (($urgentSale->status ?? 'active') !== 'active') {
                $msg = 'Cette vente urgente n\'est plus disponible.';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            // Vente urgente: paiement en ligne possible uniquement si explicitement activé.
            $urgentSalePaymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($urgentSale->payment_requirement ?? 'none')
                : ($urgentSale->payment_requirement ?? 'none');

            if ($urgentSalePaymentRequirement !== 'full') {
                $msg = 'Cette annonce est en mode contact vendeur (pas de paiement en ligne).';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            $sellerUserId = (int) ($urgentSale->prestataire?->user_id ?? 0);
            if ($sellerUserId > 0 && $sellerUserId === (int) auth()->id()) {
                $msg = 'Vous ne pouvez pas acheter votre propre annonce.';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            if (empty($urgentSale->prestataire?->stripe_account_id)) {
                $msg = 'Le vendeur n\'a pas activé le paiement en ligne.';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            if ((int) $urgentSale->quantity < $quantity) {
                $msg = 'Quantité insuffisante pour cette vente urgente.';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart) {
                $msg = 'Le système de panier n\'est pas encore configuré.';
                return $isAjax ? response()->json(['message' => $msg], 500) : back()->with('error', $msg);
            }

            $existing = $cart->items()
                ->where('purchasable_type', $urgentSale->getMorphClass())
                ->where('purchasable_id', $urgentSale->id)
                ->first();

            $newQty = $existing ? ($existing->quantity + $quantity) : $quantity;

            if ((int) $urgentSale->quantity < $newQty) {
                $msg = 'Quantité insuffisante (panier + ajout).';
                return $isAjax ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
            }

            $pricing = $this->pricing()->pricingFor($urgentSale, $newQty);

            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_type' => $urgentSale->getMorphClass(),
                    'purchasable_id' => $urgentSale->id,
                ],
                [
                    'quantity' => $newQty,
                    'unit_price' => $pricing['unit_price'],
                    'line_total' => $pricing['line_total'],
                    'line_deposit' => $pricing['line_deposit'],
                    'currency' => $pricing['currency'],
                ]
            );

            if ($isAjax) {
                $cartCount = $cart->items()->sum('quantity');
                return response()->json([
                    'success' => true,
                    'message' => 'Produit ajouté au panier.',
                    'cartCount' => $cartCount,
                ]);
            }

            return redirect()->route('client.cart.index')->with('success', 'Produit ajouté au panier.');
        } catch (\Throwable $e) {
            Log::error('addUrgentSale erreur: ' . $e->getMessage(), [
                'urgentSale' => $urgentSale->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Une erreur est survenue lors de l\'ajout au panier.';
            return $isAjax ? response()->json(['message' => $msg], 500) : back()->with('error', $msg);
        }
    }


    public function updateItem(Request $request, CartItem $cartItem)
    {
        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart || $cartItem->cart_id !== $cart->id) {
                abort(403);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem->load('purchasable');
            $purchasable = $cartItem->purchasable;

            if (!$purchasable instanceof UrgentSale) {
                return back()->with('error', 'La quantité ne peut pas être modifiée pour cet article.');
            }

            $quantity = (int) $request->input('quantity');

            if ((int) $purchasable->quantity < $quantity) {
                return back()->with('error', 'Quantité insuffisante pour cette vente urgente.');
            }

            $pricing = $this->pricing()->pricingFor($purchasable, $quantity);

            $cartItem->update([
                'quantity' => $quantity,
                'unit_price' => $pricing['unit_price'],
                'line_total' => $pricing['line_total'],
                'line_deposit' => $pricing['line_deposit'],
                'currency' => $pricing['currency'],
            ]);

            return redirect()->route('client.cart.index')->with('success', 'Panier mis à jour.');
        } catch (\Throwable $e) {
            Log::error('updateItem erreur: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    public function removeItem(CartItem $cartItem)
    {
        try {
            if (!TableExistenceCache::has('carts') || !TableExistenceCache::has('cart_items')) {
                return back()->with('error', 'Le système de panier n\'est pas encore configuré.');
            }

            $cart = Cart::forUserActive(auth()->id());

            if (!$cart || $cartItem->cart_id !== $cart->id) {
                abort(403);
            }

            $cartItem->delete();

            return redirect()->route('client.cart.index')->with('success', 'Article supprimé du panier.');
        } catch (\Throwable $e) {
            Log::error('removeItem erreur: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}
