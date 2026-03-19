<?php

namespace App\Jobs;

use App\Models\TenderRequest;
use App\Models\TenderInvitation;
use App\Models\Prestataire;
use App\Notifications\NewTenderMatchNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTenderMatching implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;
    public int $backoff = 30;

    protected TenderRequest $tender;
    protected int $maxInvitations;

    /**
     * Create a new job instance.
     */
    public function __construct(TenderRequest $tender, int $maxInvitations = 20)
    {
        $this->tender = $tender;
        $this->maxInvitations = $maxInvitations;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Processing tender matching for: {$this->tender->reference}");

        // Récupérer les catégories de l'appel d'offre
        $tenderCategoryIds = $this->tender->categories()->pluck('categories.id')->toArray();

        if (empty($tenderCategoryIds)) {
            Log::warning("No categories found for tender: {$this->tender->reference}");
            return;
        }

        // Trouver les prestataires correspondants
        $prestataires = Prestataire::query()
            ->where('is_approved', true)
            ->whereHas('services', function ($query) use ($tenderCategoryIds) {
                $query->whereHas('categories', function ($q) use ($tenderCategoryIds) {
                    $q->whereIn('categories.id', $tenderCategoryIds);
                });
            })
            ->with(['user', 'services.categories'])
            ->get();

        Log::info("Found {$prestataires->count()} potential prestataires");

        $matchResults = [];

        /** @var \App\Models\Prestataire $prestataire */
        foreach ($prestataires as $prestataire) {
            // Calculer le score de matching
            $matchData = $this->tender->calculateMatchScore($prestataire);

            if ($matchData['score'] >= 20) { // Score minimum
                $matchResults[] = [
                    'prestataire' => $prestataire,
                    'score' => $matchData['score'],
                    'reasons' => $matchData['reasons'],
                ];
            }
        }

        // Trier par score décroissant
        usort($matchResults, fn($a, $b) => $b['score'] <=> $a['score']);

        // Limiter le nombre d'invitations
        $matchResults = array_slice($matchResults, 0, $this->maxInvitations);

        Log::info("Creating {$matchResults} invitations");

        // Créer les invitations
        foreach ($matchResults as $match) {
            $invitation = TenderInvitation::updateOrCreate(
                [
                    'tender_request_id' => $this->tender->id,
                    'prestataire_id' => $match['prestataire']->id,
                ],
                [
                    'type' => 'auto_match',
                    'match_score' => $match['score'],
                    'match_reasons' => $match['reasons'],
                ]
            );

            // Envoyer une notification
            try {
                if ($match['prestataire']->user) {
                    $match['prestataire']->user->notify(new NewTenderMatchNotification($this->tender, $match['score']));
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification: " . $e->getMessage());
            }
        }

        Log::info("Tender matching completed for: {$this->tender->reference}");
    }
}
