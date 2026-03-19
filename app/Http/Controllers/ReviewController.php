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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the reviews.
     */
    public function index(Request $request): View
    {
        try {
            // Default to null if no prestataire ID is provided
            $prestataireId = $request->query('prestataire_id');

            $reviews = Review::with(['client', 'prestataire.user']);

            // Filter by prestataire if ID is provided
            if ($prestataireId) {
                $reviews->where('prestataire_id', $prestataireId);
            }

            $reviews = $reviews->latest()->paginate(10);

            // Statistiques pour les avis avec gestion des colonnes manquantes
            $totalReviews = Review::count();
            $avgRating = Review::avg('rating') ?: 0;

            // Vérifier si les colonnes de photos existent
            $reviewsWithPhotos = 0;
            try {
                $reviewsWithPhotos = Review::whereNotNull('photos')
                    ->where('photos', '!=', '[]')
                    ->where('photos', '!=', '')
                    ->count();
            } catch (\Exception $e) {
                $reviewsWithPhotos = 0;
            }

            // Moyennes détaillées avec gestion des erreurs
            $detailedAverages = [
                'punctuality' => 0,
                'quality' => 0,
                'value' => 0,
                'communication' => 0
            ];

            try {
                $detailedAverages = [
                    'punctuality' => Review::avg('punctuality_rating') ?: 0,
                    'quality' => Review::avg('quality_rating') ?: 0,
                    'value' => Review::avg('value_rating') ?: 0,
                    'communication' => Review::avg('communication_rating') ?: 0
                ];
            } catch (\Exception $e) {
                // Les colonnes n'existent peut-être pas
            }

            $stats = [
                'total_reviews' => $totalReviews,
                'average_rating' => $avgRating,
                'reviews_with_photos' => $reviewsWithPhotos,
                'detailed_averages' => $detailedAverages
            ];

            return view('reviews.index', compact('reviews', 'stats', 'prestataireId'));
        } catch (\Exception $e) {
            Log::error('Erreur reviews index: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            abort(500, 'Erreur lors du chargement des avis.');
        }
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            $prestataireId = $request->query('prestataire');
            $bookingId = $request->query('booking');

            // Fetch additional information if needed
            $prestataire = null;
            if ($prestataireId) {
                $prestataire = \App\Models\Prestataire::with('user')->find($prestataireId);
            }

            // Récupérer le booking si fourni
            $booking = null;
            if ($bookingId) {
                $booking = \App\Models\Booking::with(['service', 'prestataire.user'])->find($bookingId);
                if ($booking) {
                    $ownsBooking = (int) ($booking->user_id ?? 0) === (int) Auth::id()
                        || (int) ($booking->client_id ?? 0) === (int) Auth::id()
                        || (int) ($booking->client?->user_id ?? 0) === (int) Auth::id();

                    if (!$ownsBooking) {
                        abort(403, 'Vous ne pouvez noter que vos propres réservations.');
                    }
                }

                if ($booking && !$prestataire) {
                    $prestataire = $booking->prestataire;
                    $prestataireId = $prestataire ? $prestataire->id : null;
                }
            }

            return view('reviews.create', compact('prestataireId', 'bookingId', 'prestataire', 'booking'));
        } catch (\Exception $e) {
            Log::error('Erreur reviews create: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Vous devez être connecté pour laisser un avis.');
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'punctuality_rating' => 'nullable|integer|min:1|max:5',
                'quality_rating' => 'nullable|integer|min:1|max:5',
                'value_rating' => 'nullable|integer|min:1|max:5',
                'communication_rating' => 'nullable|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'prestataire_id' => 'nullable|exists:prestataires,id',
                'booking_id' => 'required|exists:bookings,id',
                'photos' => 'nullable|array|max:5',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'video' => 'nullable|mimes:mp4,mov,ogg,qt,webm|max:51200',
            ]);

            $booking = null;
            $serviceId = null;
            $prestataireId = null;

            $booking = Booking::with(['client', 'prestataire'])->findOrFail($validated['booking_id']);

            $ownsBooking = (int) ($booking->user_id ?? 0) === (int) Auth::id()
                || (int) ($booking->client_id ?? 0) === (int) Auth::id()
                || (int) ($booking->client?->user_id ?? 0) === (int) Auth::id();

            if (!$ownsBooking) {
                return back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'Vous ne pouvez noter que vos propres réservations.']);
            }

            if (!in_array((string) ($booking->status ?? ''), ['completed', 'confirmed'], true)) {
                return back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'Vous ne pouvez laisser un avis qu\'après une réservation finalisée.']);
            }

            $prestataireId = (int) ($booking->prestataire_id ?? 0);
            if ($prestataireId <= 0) {
                return back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'Cette réservation n\'est liée à aucun prestataire.']);
            }

            if (!empty($validated['prestataire_id']) && $prestataireId !== (int) $validated['prestataire_id']) {
                return back()
                    ->withInput()
                    ->withErrors(['prestataire_id' => 'Le prestataire sélectionné ne correspond pas à cette réservation.']);
            }

            if (Review::where('booking_id', $booking->id)->where('client_id', Auth::id())->exists()) {
                return back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'Vous avez déjà laissé un avis pour cette réservation.']);
            }

            $serviceId = $booking->service_id;

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

            $videoPath = null;
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                if ($video->isValid()) {
                    $filename = 'review_video_' . time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                    $video->storeAs('reviews/videos', $filename, 'public');
                    $videoPath = 'reviews/videos/' . $filename;
                }
            }

            $review = Review::create([
                'client_id' => Auth::user()->id,
                'prestataire_id' => $prestataireId,
                'service_id' => $serviceId,
                'booking_id' => $validated['booking_id'] ?? null,
                'rating' => $validated['rating'],
                'punctuality_rating' => $validated['punctuality_rating'] ?? null,
                'quality_rating' => $validated['quality_rating'] ?? null,
                'value_rating' => $validated['value_rating'] ?? null,
                'communication_rating' => $validated['communication_rating'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'photos' => $photos,
                'video' => $videoPath,
                'status' => 'approved',
            ]);

            $this->updatePrestataireRating($prestataireId);

            try {
                $prestataire = Prestataire::find($prestataireId);
                if ($prestataire && $prestataire->user) {
                    $prestataire->user->notify(new NewReviewNotification($review));
                }
            } catch (\Exception $e) {
                Log::error('Erreur notification review: ' . $e->getMessage());
            }

            return redirect()->route('client.dashboard')->with('success', 'Merci pour votre avis ! Votre évaluation a été enregistrée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur store review: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Erreur lors de l\'enregistrement de l\'avis.')->withInput();
        }
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
