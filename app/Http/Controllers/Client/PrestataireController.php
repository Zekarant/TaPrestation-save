<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prestataire;
use App\Models\Category;
use App\Models\Service;
use App\Models\Review;

class PrestataireController extends Controller
{
    /**
     * Display a listing of prestataires for browsing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Prestataire::with(['user', 'services.category', 'reviews'])
            ->where('is_approved', true)
            ->where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            })->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by sector
        if ($request->filled('sector')) {
            $query->where('secteur_activite', $request->sector);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where(function($q) use ($request) {
                $q->where('city', 'like', "%{$request->location}%")
                  ->orWhere('postal_code', 'like', "%{$request->location}%")
                  ->orWhere('address', 'like', "%{$request->location}%");
            });
        }

        // Filter by rating
        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $query->having('reviews_avg_rating', '>=', $minRating);
        }

        // Sort results
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Apply sorting
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('reviews_avg_rating', $sortOrder);
                break;
            case 'name':
                $query->join('users', 'prestataires.user_id', '=', 'users.id')
                      ->orderBy('users.nom', $sortOrder)
                      ->select('prestataires.*');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $prestataires = $query->paginate(12)->withQueryString();

        // Add average rating and reviews count to each prestataire
        $prestataires->getCollection()->transform(function ($prestataire) {
            $prestataire->average_rating = $prestataire->reviews()->avg('rating') ?? 0;
            $prestataire->reviews_count = $prestataire->reviews()->count();
            return $prestataire;
        });

        // Get filter options
        $categories = Category::whereHas('services.prestataire', function ($q) {
            $q->where('is_approved', true)->where('is_active', true);
        })->get();

        // Get popular locations
        $locations = Prestataire::where('is_approved', true)
            ->where('is_active', true)
            ->whereNotNull('city')
            ->select('city')
            ->distinct()
            ->limit(10)
            ->get()
            ->pluck('city');

        // Get some statistics
        $stats = [
            'total_prestataires' => Prestataire::where('is_approved', true)->where('is_active', true)->count(),
            'total_services' => Service::whereHas('prestataire', function ($q) {
                $q->where('is_approved', true)->where('is_active', true);
            })->count(),
        ];

        // Get sectors (empty for now since no sector column exists)
        $sectors = collect();

        // Get current filters for form state
        $filters = $request->only(['sector', 'category', 'subcategory', 'region', 'city', 'location', 'min_rating', 'min_price', 'max_price', 'user_location', 'user_latitude', 'user_longitude', 'radius']);

        return view('client.prestataires.index', compact(
            'prestataires',
            'categories',
            'locations',
            'stats',
            'sectors',
            'filters'
        ));
    }

    /**
     * Display the specified prestataire.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\View\View
     */
    public function show(Prestataire $prestataire)
    {
        if (!$prestataire->is_approved || !$prestataire->is_active) {
            abort(404);
        }

        $prestataire->load([
            'user',
            'services.category',
            'services.images',
            'reviews.client',
            'availabilities'
        ])
        ->loadCount('reviews')
        ->loadAvg('reviews', 'rating');

        // Check if the current user follows this prestataire
        $isFollowed = false;
        if (Auth::check() && Auth::user()->isClient() && Auth::user()->client) {
            $isFollowed = Auth::user()->client->isFollowing($prestataire->id);
        }

        return view('client.prestataires.show', [
            'prestataire' => $prestataire,
            'isFollowed' => $isFollowed
        ]);
    }
}