<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Prestataire;
use App\Models\SatisfactionCertificate;
use App\Notifications\NewReviewNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class EnrichedReviewService
{
    /**
     * Create a new enriched review.
     */
    public function createReview(array $data, array $photos = [])
    {
        // Upload photos if provided
        $photoUrls = [];
        if (!empty($photos)) {
            $photoUrls = $this->uploadReviewPhotos($photos);
        }

        // Create the review
        $review = Review::create([
            'client_id' => $data['client_id'],
            'prestataire_id' => $data['prestataire_id'],
            'booking_id' => $data['booking_id'] ?? null,
            'rating' => $data['rating'],
            'punctuality_rating' => $data['punctuality_rating'] ?? null,
            'quality_rating' => $data['quality_rating'] ?? null,
            'value_rating' => $data['value_rating'] ?? null,
            'communication_rating' => $data['communication_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'photos' => $photoUrls,
            'verified' => $data['verified'] ?? false,
            'status' => 'pending', // Par défaut en attente de modération
        ]);

        // Check if we need to generate/update satisfaction certificate
        $this->checkAndUpdateCertificate($data['prestataire_id']);

        return $review;
    }

    /**
     * Upload review photos.
     */
    private function uploadReviewPhotos(array $photos)
    {
        $photoUrls = [];
        
        foreach ($photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $path = $photo->store('reviews/photos', 'public');
                $photoUrls[] = $path;
            }
        }
        
        return $photoUrls;
    }

    /**
     * Calculate satisfaction statistics for a prestataire.
     */
    public function calculateSatisfactionStats($prestataireId, $year = null)
    {
        $year = $year ?? now()->year;
        
        $reviews = Review::where('prestataire_id', $prestataireId)
            ->approved()
            ->whereYear('created_at', $year)
            ->get();

        if ($reviews->isEmpty()) {
            return null;
        }

        $totalReviews = $reviews->count();
        $satisfiedReviews = $reviews->where('rating', '>=', 4)->count();
        $satisfactionRate = ($satisfiedReviews / $totalReviews) * 100;

        return [
            'total_reviews' => $totalReviews,
            'satisfied_reviews' => $satisfiedReviews,
            'satisfaction_rate' => round($satisfactionRate, 2),
            'average_rating' => round($reviews->avg('rating'), 2),
            'detailed_averages' => [
                'punctuality' => round($reviews->whereNotNull('punctuality_rating')->avg('punctuality_rating'), 2),
                'quality' => round($reviews->whereNotNull('quality_rating')->avg('quality_rating'), 2),
                'value' => round($reviews->whereNotNull('value_rating')->avg('value_rating'), 2),
                'communication' => round($reviews->whereNotNull('communication_rating')->avg('communication_rating'), 2),
            ]
        ];
    }

    /**
     * Check and update satisfaction certificate for a prestataire.
     */
    public function checkAndUpdateCertificate($prestataireId)
    {
        $currentYear = now()->year;
        $stats = $this->calculateSatisfactionStats($prestataireId, $currentYear);
        
        if (!$stats || $stats['total_reviews'] < 5) {
            return null; // Minimum 5 avis requis
        }

        // Seuil de satisfaction pour obtenir un certificat (90%)
        if ($stats['satisfaction_rate'] >= 90) {
            $certificate = SatisfactionCertificate::updateOrCreate(
                [
                    'prestataire_id' => $prestataireId,
                    'year' => $currentYear
                ],
                [
                    'satisfaction_rate' => $stats['satisfaction_rate'],
                    'total_reviews' => $stats['total_reviews'],
                    'certificate_number' => SatisfactionCertificate::generateCertificateNumber($prestataireId, $currentYear),
                    'issued_at' => now(),
                    'expires_at' => Carbon::create($currentYear + 1, 12, 31), // Expire fin de l'année suivante
                ]
            );

            return $certificate;
        }

        return null;
    }

    /**
     * Generate PDF certificate.
     */
    public function generateCertificatePDF(SatisfactionCertificate $certificate)
    {
        // TODO: Implémenter la génération PDF avec une librairie comme DomPDF
        // Pour l'instant, on retourne juste le chemin où le PDF devrait être stocké
        $filename = "certificate_{$certificate->certificate_number}.pdf";
        $path = "certificates/{$filename}";
        
        // Ici on générerait le PDF et le sauvegarderait
        // $pdf = PDF::loadView('certificates.template', compact('certificate'));
        // Storage::disk('public')->put($path, $pdf->output());
        
        $certificate->update(['file_path' => $path]);
        
        return $path;
    }

    /**
     * Get prestataire's review statistics.
     */
    public function getPrestataireReviewStats($prestataireId)
    {
        $reviews = Review::where('prestataire_id', $prestataireId)
            ->approved()
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'detailed_averages' => [
                    'punctuality' => 0,
                    'quality' => 0,
                    'value' => 0,
                    'communication' => 0,
                ],
                'photos_count' => 0,
                'verified_count' => 0,
            ];
        }

        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $reviews->where('rating', $i)->count();
        }

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 2),
            'rating_distribution' => $ratingDistribution,
            'detailed_averages' => [
                'punctuality' => round($reviews->whereNotNull('punctuality_rating')->avg('punctuality_rating'), 2),
                'quality' => round($reviews->whereNotNull('quality_rating')->avg('quality_rating'), 2),
                'value' => round($reviews->whereNotNull('value_rating')->avg('value_rating'), 2),
                'communication' => round($reviews->whereNotNull('communication_rating')->avg('communication_rating'), 2),
            ],
            'photos_count' => $reviews->sum('photos_count'),
            'verified_count' => $reviews->where('verified', true)->count(),
        ];
    }

    /**
     * Moderate a review.
     */
    public function moderateReview(Review $review, string $status, $moderatorId)
    {
        $review->update([
            'status' => $status,
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);

        // Si approuvé, vérifier les certificats et envoyer notification
        if ($status === 'approved') {
            $this->checkAndUpdateCertificate($review->prestataire_id);
            
            // Envoyer notification au prestataire
            if ($review->prestataire && $review->prestataire->user) {
                $review->prestataire->user->notify(new NewReviewNotification($review));
            }
        }

        return $review;
    }
}