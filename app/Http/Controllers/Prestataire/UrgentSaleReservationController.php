<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use App\Models\UrgentSaleReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrgentSaleReservationController extends Controller
{
    /**
     * Liste des réservations pour les annonces du prestataire
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            abort(403, 'Vous devez être prestataire pour accéder à cette page.');
        }

        $status = $request->get('status', 'all');
        
        $query = UrgentSaleReservation::whereHas('urgentSale', function ($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })->with(['urgentSale', 'client']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats
        $stats = [
            'pending' => UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))->pending()->count(),
            'confirmed' => UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))->confirmed()->count(),
            'completed' => UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))->completed()->count(),
        ];

        return view('prestataire.urgent-sales.reservations.index', compact('reservations', 'stats', 'status'));
    }

    /**
     * Confirmer une réservation (réserver le stock)
     */
    public function confirm(Request $request, UrgentSaleReservation $reservation)
    {
        // Vérifier que c'est bien une annonce du prestataire
        $this->authorizeReservation($reservation);

        if ($reservation->status !== UrgentSaleReservation::STATUS_PENDING) {
            return back()->with('error', 'Cette réservation ne peut pas être confirmée.');
        }

        // Vérifier la disponibilité du stock
        if ($reservation->quantity > $reservation->urgentSale->available_quantity) {
            return back()->with('error', 'Stock insuffisant pour confirmer cette réservation.');
        }

        $reservation->confirm($request->get('notes'));

        // Notifier le client (optionnel)
        // $reservation->client->notify(new ReservationConfirmed($reservation));

        return back()->with('success', 'Réservation confirmée ! Le stock a été réservé.');
    }

    /**
     * Refuser/Annuler une réservation
     */
    public function cancel(UrgentSaleReservation $reservation)
    {
        $this->authorizeReservation($reservation);

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $reservation->cancel();

        // Notifier le client (optionnel)
        // $reservation->client->notify(new ReservationCancelled($reservation));

        return back()->with('success', 'Réservation annulée.');
    }

    /**
     * Marquer comme vendu/complété
     */
    public function complete(UrgentSaleReservation $reservation)
    {
        $this->authorizeReservation($reservation);

        if ($reservation->status !== UrgentSaleReservation::STATUS_CONFIRMED) {
            return back()->with('error', 'Seules les réservations confirmées peuvent être marquées comme vendues.');
        }

        $reservation->complete();

        // Notifier le client (optionnel)
        // $reservation->client->notify(new ReservationCompleted($reservation));

        return back()->with('success', 'Vente finalisée ! Le stock a été mis à jour.');
    }

    /**
     * Afficher les stats d'inventaire d'une annonce
     */
    public function showInventory(UrgentSale $urgentSale)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $urgentSale->prestataire_id !== $prestataire->id) {
            abort(403);
        }

        $reservations = $urgentSale->reservations()
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('prestataire.urgent-sales.reservations.inventory', compact('urgentSale', 'reservations'));
    }

    /**
     * Noter le client après une vente complétée
     */
    public function rateClient(Request $request, UrgentSaleReservation $reservation)
    {
        $this->authorizeReservation($reservation);

        if ($reservation->status !== UrgentSaleReservation::STATUS_COMPLETED) {
            return back()->with('error', 'Vous ne pouvez noter que les ventes finalisées.');
        }

        if ($reservation->client_rated_at) {
            return back()->with('error', 'Vous avez déjà noté ce client.');
        }

        $request->validate([
            'client_rating' => 'required|integer|min:1|max:5',
            'client_rating_comment' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'client_rating' => $request->client_rating,
            'client_rating_comment' => $request->client_rating_comment,
            'client_rated_at' => now(),
        ]);

        return back()->with('success', 'Merci pour votre évaluation du client !');
    }

    /**
     * Vérifier que le prestataire peut gérer cette réservation
     */
    private function authorizeReservation(UrgentSaleReservation $reservation)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $reservation->urgentSale->prestataire_id !== $prestataire->id) {
            abort(403, 'Vous n\'êtes pas autorisé à gérer cette réservation.');
        }
    }
}
