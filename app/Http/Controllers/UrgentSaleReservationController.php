<?php

namespace App\Http\Controllers;

use App\Models\UrgentSale;
use App\Models\UrgentSaleReservation;
use App\Models\Cart;
use App\Models\CartItem;
use App\Notifications\NewUrgentSaleReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrgentSaleReservationController extends Controller
{
    /**
     * Créer une demande de réservation
     */
    public function store(Request $request, UrgentSale $urgentSale)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $urgentSale->available_quantity,
            'message' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'utilisateur n'est pas le propriétaire
        if (Auth::id() === $urgentSale->prestataire->user_id) {
            return back()->with('error', 'Vous ne pouvez pas réserver votre propre annonce.');
        }

        // Vérifier la disponibilité
        if ($request->quantity > $urgentSale->available_quantity) {
            return back()->with('error', 'La quantité demandée n\'est plus disponible.');
        }

        // Vérifier si une réservation existe déjà pour cet utilisateur et cette annonce
        $existingReservation = UrgentSaleReservation::where('urgent_sale_id', $urgentSale->id)
            ->where('client_id', Auth::id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingReservation) {
            return back()->with('error', 'Vous avez déjà une réservation en cours pour cette annonce.');
        }

        // Créer la réservation
        $reservation = UrgentSaleReservation::create([
            'urgent_sale_id' => $urgentSale->id,
            'client_id' => Auth::id(),
            'quantity' => $request->quantity,
            'message' => $request->message,
            'status' => UrgentSaleReservation::STATUS_PENDING,
        ]);

        // Notifier le prestataire
        try {
            $urgentSale->prestataire->user->notify(new NewUrgentSaleReservationNotification($reservation));
        } catch (\Exception $e) {
            \Log::error('Failed to send reservation notification: ' . $e->getMessage());
        }

        // Supprimer l'article du panier si présent
        try {
            $cart = Cart::forUserActive(Auth::id());
            if ($cart) {
                CartItem::where('cart_id', $cart->id)
                    ->where('purchasable_type', UrgentSale::class)
                    ->where('purchasable_id', $urgentSale->id)
                    ->delete();
            }
        } catch (\Throwable $e) {
            \Log::warning('Impossible de retirer l\'article du panier après réservation: ' . $e->getMessage());
        }

        return back()->with('success', 'Votre demande de réservation a été envoyée au vendeur ! Il vous contactera pour finaliser la transaction.');
    }

    /**
     * Liste des réservations pour un client
     */
    public function clientIndex()
    {
        try {
            $reservations = UrgentSaleReservation::where('client_id', Auth::id())
                ->with(['urgentSale.prestataire.user'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            \Log::error('Erreur my-reservations: ' . $e->getMessage());
            $reservations = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        return view('client.reservations.index', compact('reservations'));
    }

    /**
     * Annuler une réservation (client)
     */
    public function clientCancel(UrgentSaleReservation $reservation)
    {
        // Vérifier que c'est bien la réservation du client
        if ($reservation->client_id !== Auth::id()) {
            abort(403);
        }

        // On ne peut annuler que les réservations pending ou confirmed
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Cette réservation ne peut plus être annulée.');
        }

        $reservation->cancel();

        return back()->with('success', 'Votre réservation a été annulée.');
    }

    /**
     * Le client note le prestataire/vendeur après une vente complétée
     */
    public function rateSeller(Request $request, UrgentSaleReservation $reservation)
    {
        if ($reservation->client_id !== Auth::id()) {
            abort(403);
        }

        if ($reservation->status !== UrgentSaleReservation::STATUS_COMPLETED) {
            return back()->with('error', 'Vous ne pouvez noter que les ventes finalisées.');
        }

        if ($reservation->seller_rated_at) {
            return back()->with('error', 'Vous avez déjà noté ce vendeur.');
        }

        $request->validate([
            'seller_rating' => 'required|integer|min:1|max:5',
            'seller_rating_comment' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'seller_rating' => $request->seller_rating,
            'seller_rating_comment' => $request->seller_rating_comment,
            'seller_rated_at' => now(),
        ]);

        return back()->with('success', 'Merci pour votre évaluation !');
    }
}
