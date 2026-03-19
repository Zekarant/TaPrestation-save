<?php

namespace App\Http\Controllers;

use App\Models\ClientReview;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:prestataire']);
    }

    /**
     * Afficher le formulaire pour noter un client après une prestation
     */
    public function create(Booking $booking)
    {
        // Vérifier que l'utilisateur est le prestataire de cette réservation
        $user = Auth::user();
        if (!$user->isPrestataire() || $booking->prestataire_id !== $user->prestataire->id) {
            abort(403, 'Vous n\'êtes pas autorisé à noter ce client.');
        }

        // Vérifier que la réservation est terminée
        if ($booking->status !== 'completed') {
            return back()->with('error', 'Vous ne pouvez noter un client qu\'après avoir terminé la prestation.');
        }

        // Vérifier si un avis existe déjà
        $existingReview = ClientReview::where('prestataire_id', $user->id)
            ->where('booking_id', $booking->id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'Vous avez déjà noté ce client pour cette prestation.');
        }

        // Vérifier que le client existe
        if (!$booking->client) {
            return back()->with('error', 'Le client de cette réservation n\'existe plus.');
        }

        // Récupérer le client (user associé au client)
        $client = $booking->client->user ?? null;
        
        if (!$client) {
            // Si pas d'utilisateur associé, créer un objet factice avec les infos du client
            $client = (object) [
                'id' => $booking->client->user_id ?? 0,
                'name' => trim(($booking->client->first_name ?? '') . ' ' . ($booking->client->last_name ?? '')) ?: 'Client',
                'email' => $booking->client->email ?? '',
                'profile_photo_url' => null,
            ];
        }

        return view('client-reviews.create', compact('booking', 'client'));
    }

    /**
     * Enregistrer l'avis sur un client
     */
    public function store(Request $request, Booking $booking)
    {
        try {
            $user = Auth::user();
            
            // Vérifications
            if (!$user->isPrestataire() || !$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
                abort(403, 'Vous n\'êtes pas autorisé à noter ce client.');
            }

            if ($booking->status !== 'completed') {
                return back()->with('error', 'La prestation doit être terminée pour noter le client.');
            }

            // Vérifier que le client existe
            if (!$booking->client) {
                return back()->with('error', 'Le client de cette réservation n\'existe plus.');
            }

            // Vérifier si un avis existe déjà
            $existingReview = ClientReview::where('prestataire_id', $user->prestataire->id)
                ->where('booking_id', $booking->id)
                ->first();

            if ($existingReview) {
                return redirect()->route('prestataire.bookings.show', $booking)
                    ->with('error', 'Vous avez déjà noté ce client pour cette prestation.');
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'punctuality' => 'nullable|in:excellent,good,average,poor',
                'communication' => 'nullable|in:excellent,good,average,poor',
                'respect' => 'nullable|in:excellent,good,average,poor',
                'would_work_again' => 'nullable',
            ]);

            // Récupérer le client_id (id du client dans la table clients)
            $clientId = $booking->client->id ?? null;
            
            if (!$clientId) {
                return back()->with('error', 'Impossible de trouver l\'identifiant du client.');
            }

            // Créer l'avis
            ClientReview::create([
                'client_id' => $clientId,
                'prestataire_id' => $user->prestataire->id,
                'booking_id' => $booking->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'punctuality' => $validated['punctuality'] ?? null,
                'communication' => $validated['communication'] ?? null,
                'respect' => $validated['respect'] ?? null,
                'would_work_again' => $request->has('would_work_again') ? true : false,
            ]);

            return redirect()->route('prestataire.bookings.show', $booking)
                ->with('success', 'Merci pour votre évaluation du client !');
                
        } catch (\Exception $e) {
            \Log::error('Erreur store client review: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return back()->with('error', 'Erreur lors de l\'enregistrement de l\'avis.');
        }
    }

    /**
     * Afficher les avis d'un client (visible par les prestataires)
     */
    public function showClientReviews($userId)
    {
        try {
            // Récupérer l'utilisateur
            $user = User::find($userId);
            
            // Si pas trouvé en tant que User, chercher en tant que Client
            if (!$user) {
                $client = \App\Models\Client::find($userId);
                if ($client && $client->user_id) {
                    $user = User::find($client->user_id);
                }
            }
            
            if (!$user) {
                abort(404, 'Utilisateur non trouvé');
            }

            // Récupérer le client associé pour trouver les avis
            $clientId = $user->client ? $user->client->id : null;
            
            if ($clientId) {
                // Récupérer les avis via client_id
                $reviews = ClientReview::where('client_id', $clientId)
                    ->with(['prestataireUser', 'booking'])
                    ->latest()
                    ->paginate(10);
            } else {
                // Pas de profil client, pas d'avis
                $reviews = collect()->paginate(10);
            }

            $stats = [
                'average_rating' => $user->client_rating ?? null,
                'total_reviews' => $user->client_reviews_count ?? 0,
                'would_work_again_percentage' => $user->would_work_again_percentage ?? null,
            ];

            return view('client-reviews.show', compact('user', 'reviews', 'stats'));
        } catch (\Exception $e) {
            \Log::error('Erreur showClientReviews: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            abort(500, 'Erreur lors du chargement des avis client.');
        }
    }

    /**
     * API: Obtenir la note moyenne d'un client
     */
    public function getClientRating(User $user)
    {
        if (!$user->isClient()) {
            return response()->json(['error' => 'Not a client'], 404);
        }

        return response()->json([
            'rating' => $user->client_rating,
            'reviews_count' => $user->client_reviews_count,
            'would_work_again_percentage' => $user->would_work_again_percentage,
        ]);
    }
}
