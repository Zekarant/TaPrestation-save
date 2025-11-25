<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Afficher le tableau de bord du client
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $user->client;
        

        
        // 2. 📅 Réservations - Données complètes
        $totalBookings = $client ? $client->bookings()->count() : 0;
        $upcomingBookings = $client ? $client->bookings()
            ->where('start_datetime', '>', now())
            ->where('status', 'confirmed')
            ->count() : 0;
        $recentBookings = $client ? $client->bookings()
            ->with(['prestataire.user', 'service'])
            ->orderBy('start_datetime', 'desc')
            ->take(3)
            ->get() : collect();
        
        // 3. 💬 Messagerie - Messages et conversations
        $unreadMessages = \App\Models\Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
        $recentMessages = \App\Models\Message::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->groupBy(function ($message) use ($user) {
                return $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
            })
            ->map(function ($messages) {
                return $messages->first();
            })
            ->take(3);
        
        // 4. ⭐ Prestataires suivis/favoris
        $followedPrestataires = $client ? $client->followedPrestataires()->count() : 0;
        $recentFollowedPrestataires = $client ? $client->followedPrestataires()
            ->with(['user', 'services.categories'])
            ->orderBy('pivot_created_at', 'desc')
            ->take(4)
            ->get() : collect();
        
        // 5. 📊 Historique et statistiques complètes
        $totalReviews = $client ? \App\Models\Review::where('client_id', $client->id)->count() : 0;
        $averageRatingGiven = $client ? \App\Models\Review::where('client_id', $client->id)->avg('rating') ?? 0 : 0;

        

        
        // 7. 🔔 Actualités des prestataires suivis
        $followedPrestatairesIds = $client ? $client->followedPrestataires()->pluck('prestataires.id') : collect();
        $recentServicesFromFollowed = \App\Models\Service::whereIn('prestataire_id', $followedPrestatairesIds)
            ->with(['prestataire.user', 'categories'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Compilation des statistiques pour la vue
        $stats = [
            'bookings_count' => $totalBookings,
            'upcoming_bookings' => $upcomingBookings,
            'favorites_count' => $followedPrestataires,
            'unread_messages' => $unreadMessages,
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRatingGiven, 1),
        ];
        
        return view('client.dashboard', [
            'client' => $client,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'recentMessages' => $recentMessages,
            'recentFollowedPrestataires' => $recentFollowedPrestataires,
            'recentServicesFromFollowed' => $recentServicesFromFollowed
        ]);
    }
    
    /**
     * Afficher le profil du client
     */
    public function profile()
    {
        $user = Auth::user();
        $client = $user->client;
        
        return view('client.profile', compact('user', 'client'));
    }
    
    /**
     * Mettre à jour le profil du client
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'location' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image'],
        ]);
        
        $user = Auth::user();
        $client = $user->client;
        
        // Mettre à jour les informations utilisateur
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        // Mettre à jour les informations client
        if ($client) {
            $clientData = [
                'location' => $request->location,
            ];
            
            // Gérer l'upload de photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($client->photo) {
                    Storage::disk('public')->delete($client->photo);
                }
                
                $clientData['photo'] = $request->file('photo')->store('profile_photos/clients', 'public');
            }
            
            $client->update($clientData);
        } else {
            // Créer le profil client s'il n'existe pas
            $clientData = [
                'user_id' => $user->id,
                'location' => $request->location,
            ];
            
            if ($request->hasFile('photo')) {
                $clientData['photo'] = $request->file('photo')->store('profile_photos/clients', 'public');
            }
            
            Client::create($clientData);
        }
        
        return redirect()->route('client.profile')->with('success', 'Profil mis à jour avec succès.');
    }
    
    /**
     * Afficher les prestataires favoris
     */
    public function favorites()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('client.dashboard')->with('error', 'Profil client non trouvé.');
        }
        
        $favorites = $client->followedPrestataires()
            ->with(['user', 'services', 'reviews'])
            ->get();
        
        return view('client.favorites', compact('favorites'));
    }
    
    /**
     * Afficher les prestataires suivis par le client
     */
    public function follows()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('client.dashboard')->with('error', 'Profil client non trouvé.');
        }
        
        $follows = $client->followedPrestataires()->with('user')->get();
        
        // Récupérer les prestataires suivis avec pagination
        $prestataires = $client->followedPrestataires()->with('user')->paginate(12);
        
        // Récupérer les services récents des prestataires suivis
        $followedPrestatairesIds = $client->followedPrestataires()->pluck('prestataires.id');
        $recentServices = \App\Models\Service::whereIn('prestataire_id', $followedPrestatairesIds)
            ->with('prestataire.user')
            ->with('categories')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('client.follows.index', compact('follows', 'prestataires', 'recentServices'));
    }
    
    /**
     * Ajouter/retirer un prestataire des favoris
     */
    public function toggleFavorite(Prestataire $prestataire)
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return back()->with('error', 'Profil client non trouvé.');
        }
        
        if ($client->isFollowing($prestataire)) {
            $client->followedPrestataires()->detach($prestataire->id);
            $message = 'Prestataire retiré des favoris.';
        } else {
            $client->followedPrestataires()->attach($prestataire->id);
            $message = 'Prestataire ajouté aux favoris.';
        }
        
        return back()->with('success', $message);
    }
}