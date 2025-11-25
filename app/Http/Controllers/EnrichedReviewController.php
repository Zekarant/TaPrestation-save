<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\SatisfactionCertificate;
use App\Services\EnrichedReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EnrichedReviewController extends Controller
{
    protected $reviewService;

    public function __construct(EnrichedReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Display a listing of reviews for a prestataire.
     */
    public function index(Request $request, $prestataireId)
    {
        $reviews = Review::where('prestataire_id', $prestataireId)
            ->approved()
            ->with(['client.user', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = $this->reviewService->getPrestataireReviewStats($prestataireId);
        
        return view('reviews.index', compact('reviews', 'stats', 'prestataireId'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request)
    {
        $prestataireId = $request->get('prestataire_id');
        $bookingId = $request->get('booking_id');
        
        return view('reviews.create', compact('prestataireId', 'bookingId'));
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prestataire_id' => 'required|exists:prestataires,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'punctuality_rating' => 'nullable|integer|min:1|max:5',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'prestataire_id', 'booking_id', 'rating', 'punctuality_rating',
            'quality_rating', 'value_rating', 'communication_rating', 'comment'
        ]);
        $data['client_id'] = Auth::id();
        
        $photos = $request->file('photos', []);
        
        try {
            $review = $this->reviewService->createReview($data, $photos);
            
            return redirect()->route('prestataires.show', $data['prestataire_id'])
                ->with('success', 'Votre avis a été soumis avec succès et est en attente de modération.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors de la soumission de votre avis.')
                ->withInput();
        }
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['client.user', 'prestataire', 'booking']);
        
        return view('reviews.show', compact('review'));
    }

    /**
     * Show reviews with photos.
     */
    public function withPhotos($prestataireId)
    {
        $reviews = Review::where('prestataire_id', $prestataireId)
            ->approved()
            ->withPhotos()
            ->with(['client.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
            
        // S'assurer que la relation client.user est correctement chargée pour chaque review
        if ($reviews->isNotEmpty()) {
            $reviews->load('client.user');
        }
            
        return view('reviews.with-photos', compact('reviews', 'prestataireId'));
    }

    /**
     * Display satisfaction certificates for a prestataire.
     */
    public function certificates($prestataireId)
    {
        $certificates = SatisfactionCertificate::where('prestataire_id', $prestataireId)
            ->valid()
            ->orderBy('year', 'desc')
            ->get();
            
        return view('reviews.certificates', compact('certificates', 'prestataireId'));
    }

    /**
     * Download a satisfaction certificate.
     */
    public function downloadCertificate(SatisfactionCertificate $certificate)
    {
        if (!$certificate->file_path) {
            // Générer le PDF s'il n'existe pas
            $this->reviewService->generateCertificatePDF($certificate);
        }
        
        $filePath = storage_path('app/public/' . $certificate->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Certificat non trouvé.');
        }
        
        return response()->download($filePath, "certificat_satisfaction_{$certificate->year}.pdf");
    }

    /**
     * Get review statistics for API.
     */
    public function stats($prestataireId)
    {
        $stats = $this->reviewService->getPrestataireReviewStats($prestataireId);
        
        return response()->json($stats);
    }

    /**
     * Admin: Moderate reviews.
     */
    public function moderate(Request $request, Review $review)
    {
        $this->authorize('moderate', $review);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $this->reviewService->moderateReview(
            $review, 
            $request->status, 
            Auth::id()
        );
        
        return back()->with('success', 'Avis modéré avec succès.');
    }

    /**
     * Admin: List pending reviews.
     */
    public function pending()
    {
        $this->authorize('viewAny', Review::class);
        
        $reviews = Review::where('status', 'pending')
            ->with(['client.user', 'prestataire'])
            ->orderBy('created_at', 'asc')
            ->paginate(20);
            
        // S'assurer que la relation client.user est correctement chargée pour chaque review
        if ($reviews->isNotEmpty()) {
            $reviews->load('client.user');
        }
        
        // Statistiques pour la vue
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
        ];
            
        return view('admin.reviews.pending', compact('reviews', 'stats'));
    }
}