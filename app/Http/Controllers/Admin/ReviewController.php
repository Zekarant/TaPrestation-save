<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Affiche la liste des avis pour modération.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Review::with(['client', 'prestataire', 'prestataire.user', 'moderator', 'service']);
        
        // Filtrage par note
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        // Filtrage par client
        if ($request->has('client')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->client . '%');
            });
        }
        
        // Filtrage par prestataire
        if ($request->has('prestataire')) {
            $query->whereHas('prestataire.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->prestataire . '%');
            });
        }
        
        // Filtrage par statut de modération
        if ($request->has('moderated')) {
            if ($request->moderated === 'yes') {
                $query->whereNotNull('moderated_by');
            } else {
                $query->whereNull('moderated_by');
            }
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $reviews = $query->paginate(15);
        
        return view('admin.reviews.index-modern', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Affiche les détails d'un avis.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $review = Review::with(['client', 'prestataire', 'prestataire.user', 'moderator', 'service'])->findOrFail($id);
        
        return view('admin.reviews.show', [
            'review' => $review,
        ]);
    }

    /**
     * Modère un avis (marque comme modéré).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moderate($id)
    {
        $review = Review::findOrFail($id);
        $review->moderated_by = Auth::id();
        $review->save();
        
        return redirect()->route('administrateur.reviews.index')
            ->with('success', 'L\'avis a été marqué comme modéré.');
    }

    /**
     * Supprime un avis.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        
        return redirect()->route('administrateur.reviews.index')
            ->with('success', 'L\'avis a été supprimé avec succès.');
    }
}