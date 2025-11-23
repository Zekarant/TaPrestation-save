<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Review;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index()
    {
        // Articles supprimés - fonctionnalité désactivée
        $recentArticles = collect();
        
        // Récupérer les catégories principales pour l'affichage
        $categories = Category::whereNull('parent_id')
            ->withCount('services')
            ->orderBy('services_count', 'desc')
            ->limit(6)
            ->get();
        
        // Récupérer quelques prestataires en vedette
        $featuredPrestataires = Prestataire::where('is_approved', true)
            ->with(['user', 'services'])
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
        
        // Statistiques générales
        $stats = [
            // 'total_prestataires' => Prestataire::where('is_approved', true)->count(),
            // 'total_services' => Service::where('status', 'active')->count(),
            // 'total_categories' => Category::count()
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