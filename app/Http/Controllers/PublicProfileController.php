<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientReview;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    /**
     * Display a public profile for any user (client or prestataire)
     * Useful for messaging context where you want to see who you're talking to
     */
    public function show(User $user)
    {
        // Si l'utilisateur est un prestataire, rediriger vers sa page prestataire
        if ($user->prestataire) {
            return redirect()->route('prestataires.show', $user->prestataire);
        }

        // Pour les clients, afficher leur profil public avec avis
        $client = Client::where('user_id', $user->id)->first();

        // Récupérer les avis laissés SUR ce client (par les prestataires)
        $reviewsReceived = collect();
        if ($client) {
            $reviewsReceived = ClientReview::where('client_id', $client->id)
                ->with(['prestataire.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Calculer la note moyenne reçue
        $averageRating = $reviewsReceived->avg('rating') ?? 0;
        $totalReviews = $reviewsReceived->count();

        // Statistiques du client
        $stats = [];
        if ($client) {
            $stats['total_bookings'] = Booking::where('client_id', $client->id)->count();
            $stats['completed_bookings'] = Booking::where('client_id', $client->id)
                ->where('status', 'completed')
                ->count();
            $stats['member_since'] = $user->created_at;
        } else {
            $stats['total_bookings'] = 0;
            $stats['completed_bookings'] = 0;
            $stats['member_since'] = $user->created_at;
        }

        return view('users.public-profile', compact(
            'user',
            'client',
            'reviewsReceived',
            'averageRating',
            'totalReviews',
            'stats'
        ));
    }
}
