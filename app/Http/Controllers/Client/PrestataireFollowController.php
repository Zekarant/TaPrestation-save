<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestataireFollowController extends Controller
{
    /**
     * Suivre un prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function follow(Request $request, Prestataire $prestataire)
    {
        // Vérifier que le prestataire est approuvé
        if (!$prestataire->is_approved) {
            return redirect()->back()->with('error', 'Ce prestataire n\'est pas disponible.');
        }
        
        // Récupérer le client connecté
        $user = Auth::user();
        if (!$user->client) {
            return redirect()->back()->with('error', 'Vous devez avoir un profil client pour suivre un prestataire.');
        }
        $client = $user->client;
        
        // Vérifier si le client suit déjà ce prestataire
        if ($client->isFollowing($prestataire->id)) {
            return redirect()->back()->with('info', 'Vous suivez déjà ce prestataire.');
        }
        
        // Ajouter le prestataire aux suivis
        $client->followedPrestataires()->attach($prestataire->id);
        
        return redirect()->back()->with('success', 'Vous suivez maintenant ce prestataire.');
    }
    
    /**
     * Ne plus suivre un prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function unfollow(Request $request, Prestataire $prestataire)
    {
        // Récupérer le client connecté
        $user = Auth::user();
        if (!$user->client) {
            return redirect()->back()->with('error', 'Vous devez avoir un profil client pour gérer vos suivis.');
        }
        $client = $user->client;
        
        // Vérifier si le client suit ce prestataire
        if (!$client->isFollowing($prestataire->id)) {
            return redirect()->back()->with('info', 'Vous ne suivez pas ce prestataire.');
        }
        
        // Retirer le prestataire des suivis
        $client->followedPrestataires()->detach($prestataire->id);
        
        return redirect()->back()->with('success', 'Vous ne suivez plus ce prestataire.');
    }
    
    /**
     * Afficher la liste des prestataires suivis par le client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer le client connecté
        $user = Auth::user();
        if (!$user->client) {
            return redirect()->route('client.dashboard')->with('error', 'Vous devez avoir un profil client pour voir vos suivis.');
        }
        $client = $user->client;
        
        $sort = $request->get('sort', 'recent');
        
        $query = $client->followedPrestataires()
            ->with(['user', 'services', 'equipments']);
        
        if ($sort === 'oldest') {
            $query->orderBy('client_prestataire_follows.created_at', 'asc');
        } else {
            $query->orderBy('client_prestataire_follows.created_at', 'desc');
        }
        
        $prestataires = $query->paginate(12);
        
        return view('client.follows.index', compact('prestataires'));
    }
}