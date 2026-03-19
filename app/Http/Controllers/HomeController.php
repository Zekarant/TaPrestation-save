<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index()
    {
        // Si l'utilisateur est connecté, rediriger vers son dashboard
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->role === 'prestataire') {
                return redirect()->route('prestataire.dashboard');
            } elseif ($user->role === 'client') {
                return redirect()->route('client.dashboard');
            } elseif ($user->role === 'admin' || $user->role === 'administrateur') {
                return redirect()->route('administrateur.dashboard');
            }
        }
        
        // Articles supprimés - fonctionnalité désactivée
        $recentArticles = collect();
        
        // Récupérer les catégories principales pour l'affichage (uniquement services)
        $categories = Category::ofTypeService()->whereNull('parent_id')
            ->withCount('services')
            ->orderBy('services_count', 'desc')
            ->limit(6)
            ->get();
        
        // Récupérer quelques prestataires en vedette avec leurs vraies notes et avis
        $featuredPrestataires = Prestataire::where('is_approved', true)
            ->with(['user', 'services', 'reviews'])
            ->withCount('reviews as total_reviews')
            ->withAvg('reviews as rating_average', 'rating')
            ->inRandomOrder()
            ->limit(6)
            ->get();
        
        // Récupérer les avis clients approuvés pour la section témoignages
        // $clientReviews = Review::approved()
        //     ->with(['client', 'prestataire.user'])
        //     ->where('rating', '>=', 4) // Afficher uniquement les avis positifs (4 étoiles ou plus)
        //     ->latest()
        //     ->limit(3)
        //     ->get();
        $clientReviews = [];
        
        // Statistiques générales - dynamiques
        $stats = [
            'total_prestataires' => Prestataire::where('is_approved', true)->count(),
            'total_services' => Service::where('status', 'active')->count(),
            'total_categories' => Category::count(),
            'avg_rating' => Review::avg('rating') ?? 4.9
        ];
        
        return view('home', compact(
            'recentArticles',
            'categories', // Now an empty array
            'featuredPrestataires', // Now an empty array
            'clientReviews',
            'stats'
        ));
    }
    

}