<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use App\Models\Prestataire;
use App\Notifications\NewReviewNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews.
     */
    public function index(Request $request): View
    {
        // Default to null if no prestataire ID is provided
        $prestataireId = $request->query('prestataire_id');
        
        $reviews = Review::with(['client', 'prestataire.user']);
        
        // Filter by prestataire if ID is provided
        if ($prestataireId) {
            $reviews->where('prestataire_id', $prestataireId);
        }
        
        $reviews = $reviews->latest()->paginate(10);
        
        // Statistiques pour les avis
        $stats = [
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating') ?: 0,
            'reviews_with_photos' => Review::whereRaw("JSON_LENGTH(IFNULL(photos, '[]')) > 0")->count(),
            'detailed_averages' => [
                'punctuality' => Review::avg('punctuality_rating') ?: 0,
                'quality' => Review::avg('quality_rating') ?: 0,
                'value' => Review::avg('value_rating') ?: 0,
                'communication' => Review::avg('communication_rating') ?: 0
            ]
        ];
            
        return view('reviews.index', compact('reviews', 'stats', 'prestataireId'));
    }
    
    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request): View
    {
        $prestataireId = $request->query('prestataire');
        $bookingId = $request->query('booking');
        
        // Fetch additional information if needed
        $prestataire = null;
        if ($prestataireId) {
            $prestataire = \App\Models\Prestataire::find($prestataireId);
        }

        return view('reviews.create', compact('prestataireId', 'bookingId', 'prestataire'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'punctuality_rating' => 'nullable|integer|min:1|max:5',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'prestataire_id' => 'required|exists:prestataires,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Handle photo uploads
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $filename = 'review_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $photo->storeAs('reviews/photos', $filename, 'public');
                    $photos[] = 'reviews/photos/' . $filename;
                }
            }
        }

        // Get service_id from booking if available
        $serviceId = null;
        if ($request->booking_id) {
            $booking = Booking::find($request->booking_id);
            $serviceId = $booking ? $booking->service_id : null;
        }

        $review = Review::create([
            'client_id' => Auth::user()->id,
            'prestataire_id' => $request->prestataire_id,
            'service_id' => $serviceId,
            'booking_id' => $request->booking_id,
            'rating' => $request->rating,
            'punctuality_rating' => $request->punctuality_rating,
            'quality_rating' => $request->quality_rating,
            'value_rating' => $request->value_rating,
            'communication_rating' => $request->communication_rating,
            'comment' => $request->comment,
            'photos' => $photos,
            'status' => 'approved', // Default status
        ]);

        // Update prestataire rating average
        $this->updatePrestataireRating($request->prestataire_id);

        // Envoyer notification au prestataire
        $prestataire = Prestataire::find($request->prestataire_id);
        if ($prestataire && $prestataire->user) {
            $prestataire->user->notify(new NewReviewNotification($review));
        }

        return redirect()->route('client.dashboard')->with('success', 'Merci pour votre avis ! Votre évaluation a été enregistrée avec succès.');
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review): View
    {
        return view('reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);
        
        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Avis modifié avec succès.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        
        $review->delete();

        return redirect()->back()->with('success', 'Avis supprimé avec succès.');
    }
    
    /**
     * Display reviews with photos.
     */
    public function withPhotos(Request $request): View
    {
        // Default to null if no prestataire ID is provided
        $prestataireId = $request->query('prestataire_id');
        
        $reviews = Review::with(['client', 'prestataire.user'])
            ->whereRaw("JSON_LENGTH(IFNULL(photos, '[]')) > 0");
            
        // Filter by prestataire if ID is provided
        if ($prestataireId) {
            $reviews->where('prestataire_id', $prestataireId);
        }
        
        $reviews = $reviews->latest()->paginate(10);
        
        // Statistiques pour les avis
        $stats = [
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating') ?: 0,
            'reviews_with_photos' => Review::whereRaw("JSON_LENGTH(IFNULL(photos, '[]')) > 0")->count(),
            'detailed_averages' => [
                'punctuality' => Review::avg('punctuality_rating') ?: 0,
                'quality' => Review::avg('quality_rating') ?: 0,
                'value' => Review::avg('value_rating') ?: 0,
                'communication' => Review::avg('communication_rating') ?: 0
            ]
        ];
            
        return view('reviews.index', compact('reviews', 'stats', 'prestataireId'));
    }
    
    /**
     * Display reviews with satisfaction certificates.
     */
    public function certificates(Request $request): View
    {
        // Default to null if no prestataire ID is provided
        $prestataireId = $request->query('prestataire_id');
        
        // Assuming certificates are reviews with high ratings (4 or 5)
        $reviews = Review::with(['client', 'prestataire.user'])
            ->where('rating', '>=', 4);
            
        // Filter by prestataire if ID is provided
        if ($prestataireId) {
            $reviews->where('prestataire_id', $prestataireId);
        }
        
        $reviews = $reviews->latest()->paginate(10);
        
        // Statistiques pour les avis
        $stats = [
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating') ?: 0,
            'reviews_with_photos' => Review::whereRaw("JSON_LENGTH(IFNULL(photos, '[]')) > 0")->count(),
            'detailed_averages' => [
                'punctuality' => Review::avg('punctuality_rating') ?: 0,
                'quality' => Review::avg('quality_rating') ?: 0,
                'value' => Review::avg('value_rating') ?: 0,
                'communication' => Review::avg('communication_rating') ?: 0
            ]
        ];
            
        return view('reviews.index', compact('reviews', 'stats', 'prestataireId'));
    }
    
    /**
     * Update prestataire rating average based on all approved reviews
     */
    private function updatePrestataireRating($prestataireId)
    {
        $prestataire = Prestataire::find($prestataireId);
        if ($prestataire) {
            $averageRating = Review::where('prestataire_id', $prestataireId)
                ->where('status', 'approved')
                ->avg('rating');
            
            $prestataire->update([
                'rating_average' => $averageRating ?: 0,
                'total_reviews' => Review::where('prestataire_id', $prestataireId)
                    ->where('status', 'approved')
                    ->count()
            ]);
        }
    }
}