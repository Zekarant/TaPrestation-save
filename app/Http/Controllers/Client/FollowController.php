<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prestataire;
use App\Models\Service;

class FollowController extends Controller
{
    /**
     * Affiche la liste des prestataires suivis par le client.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $client = Auth::user()->client;
        $followedPrestataires = $client->followedPrestataires()->with('user', 'skills', 'services')->paginate(9);
        
        // Récupérer les services récents des prestataires suivis
        $followedPrestatairesIds = $client->followedPrestataires()->pluck('prestataires.id');
        $recentServices = Service::whereIn('prestataire_id', $followedPrestatairesIds)
            ->with('prestataire.user')
            ->with('categories')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('client.follows.index', [
            'prestataires' => $followedPrestataires,
            'recentServices' => $recentServices
        ]);
    }

    /**
     * Suit un prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'prestataire_id' => 'required|exists:prestataires,id'
        ]);
        
        $client = Auth::user()->client;
        $prestataire = Prestataire::findOrFail($request->prestataire_id);
        
        // Vérifier que le prestataire est approuvé
        if (!$prestataire->is_approved) {
            return back()->with('error', 'Vous ne pouvez pas suivre un prestataire non approuvé.');
        }
        
        // Vérifier si le client suit déjà ce prestataire
        if (!$client->isFollowing($prestataire->id)) {
            $client->followedPrestataires()->attach($prestataire->id);
            return back()->with('success', 'Vous suivez maintenant ce prestataire.');
        }
        
        return back()->with('info', 'Vous suivez déjà ce prestataire.');
    }

    /**
     * Arrête de suivre un prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $client = Auth::user()->client;
        $prestataire = Prestataire::findOrFail($id);
        
        $client->followedPrestataires()->detach($prestataire->id);
        
        return back()->with('success', 'Vous ne suivez plus ce prestataire.');
    }
}
