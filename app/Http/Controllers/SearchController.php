<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use App\Models\Service;
use App\Models\Category;
use App\Models\Skill;
use App\Models\ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Recherche de prestataires avec géolocalisation
     */
    public function searchPrestataires(Request $request)
    {
        $query = Prestataire::with(['user', 'skills', 'services'])
            ->where('is_approved', true);

        // Recherche par mot-clé
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->whereHas('user', function($userQuery) use ($keyword) {
                    $userQuery->where('name', 'like', '%' . $keyword . '%');
                })
                ->orWhere('description', 'like', '%' . $keyword . '%')
                ->orWhere('secteur_activite', 'like', '%' . $keyword . '%')
                ->orWhereHas('skills', function($skillQuery) use ($keyword) {
                    $skillQuery->where('name', 'like', '%' . $keyword . '%');
                });
            });
        }

        // Filtrage par catégorie
        if ($request->filled('category_id')) {
            $query->whereHas('services.categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filtrage par compétences
        if ($request->filled('skills')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->whereIn('skills.id', $request->skills);
            });
        }

        // Filtres de prix supprimés pour des raisons de confidentialité

        // Filtrage par note moyenne
        if ($request->filled('min_rating')) {
            $query->whereHas('reviews', function($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->min_rating]);
            });
        }

        // Recherche géolocalisée
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->filled('radius') ? $request->radius : 50; // 50km par défaut

            // Utilisation de la formule de Haversine pour calculer la distance
            $query->selectRaw(
                'prestataires.*, 
                ( 6371 * acos( cos( radians(?) ) * 
                  cos( radians( latitude ) ) * 
                  cos( radians( longitude ) - radians(?) ) + 
                  sin( radians(?) ) * 
                  sin( radians( latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');
        }

        // Tri
        $sortBy = $request->get('sort_by', 'relevance');
        switch ($sortBy) {
            // Tri par prix supprimé pour des raisons de confidentialité
            case 'rating':
                $query->withAvg('reviews', 'rating')
                      ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                // Tri par pertinence (distance si géolocalisation, sinon par date)
                if (!$request->filled('latitude')) {
                    $query->orderBy('created_at', 'desc');
                }
        }

        $prestataires = $query->paginate(12)->appends($request->all());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('search.partials.prestataires-results', compact('prestataires'))->render(),
                'pagination' => $prestataires->links()->render()
            ]);
        }

        $categories = Category::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        return view('search.prestataires', compact('prestataires', 'categories', 'skills'));
    }

    /**
     * Recherche de services avec géolocalisation
     */
    public function searchServices(Request $request)
    {
        $query = Service::with(['prestataire.user', 'categories'])
            ->whereHas('prestataire', function($q) {
                $q->where('is_approved', true);
            });

        // Recherche par mot-clé
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%')
                  ->orWhereHas('categories', function($catQuery) use ($keyword) {
                      $catQuery->where('name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        // Filtrage par catégorie
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filtres de prix supprimés pour des raisons de confidentialité

        // Recherche géolocalisée
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->filled('radius') ? $request->radius : 50;

            $query->whereHas('prestataire', function($q) use ($latitude, $longitude, $radius) {
                $q->selectRaw(
                    '*, ( 6371 * acos( cos( radians(?) ) * 
                      cos( radians( latitude ) ) * 
                      cos( radians( longitude ) - radians(?) ) + 
                      sin( radians(?) ) * 
                      sin( radians( latitude ) ) ) ) AS distance',
                    [$latitude, $longitude, $latitude]
                )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->havingRaw('distance <= ?', [$radius]);
            });
        }

        $services = $query->paginate(12)->appends($request->all());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('search.partials.services-results', compact('services'))->render(),
                'pagination' => $services->links()->render()
            ]);
        }

        $categories = Category::orderBy('name')->get();

        return view('search.services', compact('services', 'categories'));
    }



    /**
     * Autocomplétion pour la recherche
     */
    public function autocomplete(Request $request)
    {
        $term = $request->get('term', '');
        $results = [];
        
        if (strlen($term) >= 2) {
            // Recherche dans les catégories
            $categories = Category::where('name', 'like', '%' . $term . '%')
                ->take(5)
                ->get();
            
            foreach ($categories as $category) {
                $results[] = [
                    'label' => $category->name,
                    'value' => $category->name,
                    'type' => 'category',
                    'id' => $category->id
                ];
            }
            
            // Recherche dans les compétences
            $skills = Skill::where('name', 'like', '%' . $term . '%')
                ->take(5)
                ->get();
            
            foreach ($skills as $skill) {
                $results[] = [
                    'label' => $skill->name,
                    'value' => $skill->name,
                    'type' => 'skill',
                    'id' => $skill->id
                ];
            }
            
            // Recherche dans les noms de prestataires
            $prestataires = Prestataire::whereHas('user', function($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%');
            })
            ->where('is_approved', true)
            ->with('user')
            ->take(5)
            ->get();
            
            foreach ($prestataires as $prestataire) {
                $results[] = [
                    'label' => $prestataire->user->name,
                    'value' => $prestataire->user->name,
                    'type' => 'prestataire',
                    'id' => $prestataire->id
                ];
            }
        }
        
        return response()->json($results);
    }
}