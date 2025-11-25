<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Afficher la page des actualités pour le client
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;
        
        // Récupérer les prestataires suivis par le client
        $followedProviders = $client->followedPrestataires()->with('user')->get();
        $followedProviderIds = $followedProviders->pluck('id');
        
        // Récupérer les nouveaux services des prestataires suivis
        $recentServices = Service::whereIn('prestataire_id', $followedProviderIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['prestataire.user', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Récupérer les prestataires récemment vérifiés
        $recentlyVerifiedProviders = Prestataire::where('verified_at', '>=', now()->subDays(7))
            ->with('user')
            ->orderBy('verified_at', 'desc')
            ->limit(5)
            ->get();

        // Statistiques
        $stats = [
            'followed_providers_count' => $followedProviders->count(),
            'new_services_count' => $recentServices->count(),
            'recently_verified_count' => $recentlyVerifiedProviders->count(),
        ];
        
        return view('client.news.index', compact(
            'recentServices',
            'recentlyVerifiedProviders',
            'stats'
        ));
    }
}