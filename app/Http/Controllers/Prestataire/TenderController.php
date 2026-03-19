<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\TenderRequest;
use App\Models\TenderResponse;
use App\Models\TenderInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class TenderController extends Controller
{
    /**
     * Liste des appels d'offres disponibles pour ce prestataire
     */
    public function index(Request $request)
    {
        $prestataire = auth()->user()->prestataire;
        
        // Vérifier que le prestataire existe et a des catégories
        if (!$prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Profil prestataire non trouvé.');
        }
        
        // Vérifier si les tables tender existent
        if (!TableExistenceCache::has('tender_requests') || !TableExistenceCache::has('tender_responses')) {
            return view('prestataire.tenders.index', [
                'tenders' => collect(),
                'invitations' => collect(),
                'stats' => [
                    'available' => 0,
                    'responded' => 0,
                    'shortlisted' => 0,
                    'accepted' => 0,
                ],
                'tableNotExists' => true,
                'prestataireCategories' => [],
                'categories' => \App\Models\Category::orderBy('name')->get(),
            ]);
        }
        
        $prestataireCategories = $prestataire->categories ? $prestataire->categories->pluck('id')->toArray() : [];

        // Note: On affiche TOUS les appels d'offres, même si le prestataire n'a pas de catégories
        // Le prestataire peut utiliser les filtres pour trouver ce qui l'intéresse

        try {
            // Tous les appels d'offres publiés et actifs
            $query = TenderRequest::query()
                ->published()
                ->notExpired()
                ->with(['client.user', 'categories', 'responses'])
                ->withCount('responses');

            // Filtres
            if ($request->filled('city')) {
                $query->forCity($request->city);
            }

            if ($request->filled('category')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('categories.id', $request->category);
                });
            }

            if ($request->filled('budget_min')) {
                $query->where('budget_min', '>=', $request->budget_min);
            }

            if ($request->filled('budget_max')) {
                $query->where('budget_max', '<=', $request->budget_max);
            }

            if ($request->filled('urgency')) {
                $query->where('urgency', $request->urgency);
            }

            // Tri
            $sortBy = $request->get('sort', 'match');
            switch ($sortBy) {
                case 'recent':
                    $query->latest('published_at');
                    break;
                case 'deadline':
                    $query->orderBy('expires_at');
                    break;
                case 'budget':
                    $query->orderByDesc('budget_max');
                    break;
                case 'match':
                default:
                    // Tri par score de matching sera fait après
                    $query->latest('published_at');
                    break;
            }

            $tenders = $query->paginate(12);

            // Calculer le score de matching pour chaque appel d'offre
            foreach ($tenders as $tender) {
                $matchResult = $tender->calculateMatchScore($prestataire);
                $tender->match_score = $matchResult['score'] ?? 0;
                $tender->match_reasons = $matchResult['reasons'] ?? [];
            }

            // Si tri par match, réordonner
            if ($sortBy === 'match') {
                $sortedItems = $tenders->getCollection()->sortByDesc('match_score')->values();
                $tenders->setCollection($sortedItems);
            }

            // Statistiques
            $stats = [
                'available' => TenderRequest::published()->notExpired()->count(),
                'responded' => TenderResponse::where('prestataire_id', $prestataire->id)->count(),
                'shortlisted' => TenderResponse::where('prestataire_id', $prestataire->id)
                    ->where('status', 'shortlisted')->count(),
                'accepted' => TenderResponse::where('prestataire_id', $prestataire->id)
                    ->where('status', 'accepted')->count(),
            ];

            // Mes invitations non lues
            $invitations = collect();
            if (TableExistenceCache::has('tender_invitations')) {
                $invitations = TenderInvitation::where('prestataire_id', $prestataire->id)
                    ->whereNull('read_at')
                    ->with('tender')
                    ->latest()
                    ->take(5)
                    ->get();
            }

            // Ajouter les catégories pour le filtre
            $categories = \App\Models\Category::orderBy('name')->get();

            return view('prestataire.tenders.index', compact('tenders', 'stats', 'invitations', 'prestataireCategories', 'categories'));
        } catch (\Exception $e) {
            return view('prestataire.tenders.index', [
                'tenders' => collect(),
                'invitations' => collect(),
                'stats' => [
                    'available' => 0,
                    'responded' => 0,
                    'shortlisted' => 0,
                    'accepted' => 0,
                ],
                'tableNotExists' => true,
                'prestataireCategories' => [],
                'categories' => \App\Models\Category::orderBy('name')->get(),
            ]);
        }
    }

    /**
     * Mes propositions envoyées
     */
    public function myResponses(Request $request)
    {
        $prestataire = auth()->user()->prestataire;

        // Vérifier si la table tender_responses existe
        if (!TableExistenceCache::has('tender_responses')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.tenders.my-responses', [
                'responses' => $emptyPaginator,
                'stats' => [
                    'total' => 0,
                    'pending' => 0,
                    'shortlisted' => 0,
                    'accepted' => 0,
                    'rejected' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }

        try {
            $query = TenderResponse::where('prestataire_id', $prestataire->id)
                ->with(['tenderRequest.client.user', 'tenderRequest.categories']);

            // Filtre par statut (tabs dans la vue)
            if ($request->filled('status')) {
                $allowedStatuses = ['pending', 'viewed', 'shortlisted', 'accepted', 'rejected', 'withdrawn'];
                if (in_array($request->status, $allowedStatuses, true)) {
                    $query->where('status', $request->status);
                }
            }

            $responses = $query->latest()->paginate(15)->withQueryString();

            $stats = [
                'total' => TenderResponse::where('prestataire_id', $prestataire->id)->count(),
                'pending' => TenderResponse::where('prestataire_id', $prestataire->id)->where('status', 'pending')->count(),
                'shortlisted' => TenderResponse::where('prestataire_id', $prestataire->id)->where('status', 'shortlisted')->count(),
                'accepted' => TenderResponse::where('prestataire_id', $prestataire->id)->where('status', 'accepted')->count(),
                'rejected' => TenderResponse::where('prestataire_id', $prestataire->id)->where('status', 'rejected')->count(),
            ];

            return view('prestataire.tenders.my-responses', compact('responses', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.tenders.my-responses', [
                'responses' => $emptyPaginator,
                'stats' => [
                    'total' => 0,
                    'pending' => 0,
                    'shortlisted' => 0,
                    'accepted' => 0,
                    'rejected' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Afficher un appel d'offre
     */
    public function show(TenderRequest $tender)
    {
        $prestataire = auth()->user()->prestataire;

        // Vérifier que l'appel d'offre est visible
        if (!$tender->public_visibility && !$tender->hasInvitation($prestataire)) {
            abort(403, 'Cet appel d\'offre n\'est pas accessible.');
        }

        // Marquer l'invitation comme lue si existante
        $invitation = TenderInvitation::where('tender_request_id', $tender->id)
            ->where('prestataire_id', $prestataire->id)
            ->first();
        
        if ($invitation && !$invitation->read_at) {
            $invitation->markAsRead();
        }

        // Vérifier si le prestataire a déjà répondu
        // IMPORTANT: une réponse "withdrawn" (retirée) ne doit pas bloquer la possibilité de répondre à nouveau.
        $existingResponse = TenderResponse::where('tender_request_id', $tender->id)
            ->where('prestataire_id', $prestataire->id)
            ->where('status', '!=', 'withdrawn')
            ->first();

        $matchResult = $tender->calculateMatchScore($prestataire);
        $matchScore = $matchResult['score'] ?? 0;
        $canRespond = $tender->canReceiveResponse($prestataire);
        
        $tender->load(['client.user', 'categories']);

        return view('prestataire.tenders.show', compact('tender', 'matchScore', 'existingResponse', 'invitation', 'canRespond'));
    }

    /**
     * Formulaire de proposition
     */
    public function createResponse(TenderRequest $tender)
    {
        $prestataire = auth()->user()->prestataire;

        // Vérifier si peut encore répondre
        if (!$tender->canReceiveResponse($prestataire)) {
            return back()->with('error', 'Cet appel d\'offre n\'accepte plus de propositions.');
        }

        // Vérifier si a déjà répondu (une réponse "withdrawn" peut être réactivée)
        $existingResponse = TenderResponse::withTrashed()
            ->where('tender_request_id', $tender->id)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        // Si une ancienne proposition a été soft-deleted, on la restaure et on la considère comme "withdrawn"
        // afin de permettre une nouvelle soumission tout en respectant l'unicité (tender_request_id, prestataire_id).
        if ($existingResponse && method_exists($existingResponse, 'trashed') && $existingResponse->trashed()) {
            $existingResponse->restore();
            if ($existingResponse->status !== 'withdrawn') {
                $existingResponse->status = 'withdrawn';
                $existingResponse->save();
            }
        }

        if ($existingResponse) {
            if ($existingResponse->status === 'withdrawn') {
                return redirect()
                    ->route('prestataire.tenders.edit-response', [$tender, $existingResponse])
                    ->with('info', 'Vous aviez retiré votre proposition. Vous pouvez la soumettre à nouveau.');
            }

            return redirect()->route('prestataire.tenders.show', $tender)
                ->with('error', 'Vous avez déjà soumis une proposition.');
        }

        $matchResult = $tender->calculateMatchScore($prestataire);
        $matchScore = $matchResult['score'] ?? 0;
        $tender->load(['client.user', 'categories']);

        return view('prestataire.tenders.respond', compact('tender', 'matchScore'));
    }

    /**
     * Soumettre une proposition
     */
    public function storeResponse(Request $request, TenderRequest $tender)
    {
        $prestataire = auth()->user()->prestataire;

        // Vérifications
        if (!$tender->canReceiveResponse($prestataire)) {
            return back()->with('error', 'Cet appel d\'offre n\'accepte plus de propositions.');
        }

        $existingResponse = TenderResponse::withTrashed()
            ->where('tender_request_id', $tender->id)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if ($existingResponse && method_exists($existingResponse, 'trashed') && $existingResponse->trashed()) {
            $existingResponse->restore();
            if ($existingResponse->status !== 'withdrawn') {
                $existingResponse->status = 'withdrawn';
                $existingResponse->save();
            }
        }

        if ($existingResponse && $existingResponse->status !== 'withdrawn') {
            return back()->with('error', 'Vous avez déjà soumis une proposition.');
        }

        $validated = $request->validate([
            'proposed_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,hourly,daily,negotiable',
            'message' => 'required|string|min:50|max:2000',
            'availability_start' => 'required|date|after_or_equal:today',
            'availability_end' => 'nullable|date|after:availability_start',
            'estimated_duration' => 'nullable|string|max:100',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,png',
        ]);

        // Upload des pièces jointes
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tender-responses/' . $tender->id, 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // Calculer le score
        $matchResult = $tender->calculateMatchScore($prestataire);
        $matchScore = $matchResult['score'] ?? 0;

        // Créer OU réactiver une proposition retirée (withdrawn)
        if ($existingResponse && $existingResponse->status === 'withdrawn') {
            $updateData = [
                'proposed_price' => $validated['proposed_price'],
                'price_type' => $validated['price_type'],
                'cover_letter' => $validated['message'] ?? '',
                'match_score' => $matchScore,
                'status' => 'pending',
                'viewed_at' => null,
                'responded_at' => null,
                'client_message' => null,
                'rejection_reason' => null,
            ];

            // Compatibilité schéma: certaines installs utilisent availability_* / estimated_duration
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_start')) {
                    $updateData['availability_start'] = $validated['availability_start'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_end')) {
                    $updateData['availability_end'] = $validated['availability_end'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration')) {
                    $updateData['estimated_duration'] = $validated['estimated_duration'] ?? null;
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_start_date')) {
                    $updateData['proposed_start_date'] = $validated['availability_start'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_end_date')) {
                    $updateData['proposed_end_date'] = $validated['availability_end'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration_hours')) {
                    // Si on reçoit un texte, on ne force pas une conversion risquée.
                    // Garder null si ce n'est pas un entier.
                    $duration = $validated['estimated_duration'] ?? null;
                    $updateData['estimated_duration_hours'] = is_numeric($duration) ? (int) $duration : null;
                }
            } catch (\Throwable $e) {
                // Si Schema n'est pas disponible pour une raison quelconque, on évite de casser la soumission.
            }

            // Pièces jointes
            if (!empty($attachments)) {
                $updateData['attachments'] = $attachments;
            }

            $existingResponse->update($updateData);
            $response = $existingResponse;
        } else {
            $createData = [
                'tender_request_id' => $tender->id,
                'prestataire_id' => $prestataire->id,
                'proposed_price' => $validated['proposed_price'],
                'price_type' => $validated['price_type'],
                'cover_letter' => $validated['message'] ?? '',
                'match_score' => $matchScore,
                'status' => 'pending',
            ];

            // Champs optionnels selon schéma
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_start')) {
                    $createData['availability_start'] = $validated['availability_start'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_end')) {
                    $createData['availability_end'] = $validated['availability_end'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration')) {
                    $createData['estimated_duration'] = $validated['estimated_duration'] ?? null;
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_start_date')) {
                    $createData['proposed_start_date'] = $validated['availability_start'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_end_date')) {
                    $createData['proposed_end_date'] = $validated['availability_end'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration_hours')) {
                    $duration = $validated['estimated_duration'] ?? null;
                    $createData['estimated_duration_hours'] = is_numeric($duration) ? (int) $duration : null;
                }
            } catch (\Throwable $e) {
            }

            if (!empty($attachments)) {
                $createData['attachments'] = $attachments;
            }

            $response = TenderResponse::create($createData);
        }

        // Marquer l'invitation comme répondue
        $invitation = TenderInvitation::where('tender_request_id', $tender->id)
            ->where('prestataire_id', $prestataire->id)
            ->first();
        
        if ($invitation) {
            $invitation->respond('interested');
        }

        // Notifier le client (en try-catch pour ne pas bloquer)
        try {
            if ($tender->client && $tender->client->user) {
                $tender->client->user->notify(new \App\Notifications\NewTenderResponseNotification($response));
            }
        } catch (\Exception $e) {
            \Log::error('Erreur notification tender response: ' . $e->getMessage());
        }

        return redirect()->route('prestataire.tenders.my-responses')
            ->with('success', 'Votre proposition a été envoyée avec succès !');
    }

    /**
     * Modifier une proposition
     */
    public function editResponse(TenderRequest $tender, TenderResponse $response)
    {
        $prestataire = auth()->user()->prestataire;

        if ((int) $response->tender_request_id !== (int) $tender->id) {
            abort(404);
        }

        if ($response->prestataire_id !== $prestataire->id) {
            abort(403);
        }

        if (!in_array($response->status, ['pending', 'viewed', 'withdrawn'])) {
            return redirect()
                ->route('prestataire.tenders.my-responses')
                ->with('error', 'Cette proposition ne peut plus être modifiée.');
        }

        $matchScore = $tender->calculateMatchScore($prestataire);
        $tender->load(['client.user', 'categories']);

        return view('prestataire.tenders.edit-response', compact('tender', 'response', 'matchScore'));
    }

    /**
     * Mettre à jour une proposition
     */
    public function updateResponse(Request $request, TenderRequest $tender, TenderResponse $response)
    {
        $prestataire = auth()->user()->prestataire;

        if ((int) $response->tender_request_id !== (int) $tender->id) {
            abort(404);
        }

        if ($response->prestataire_id !== $prestataire->id) {
            abort(403);
        }

        if (!in_array($response->status, ['pending', 'viewed', 'withdrawn'])) {
            return redirect()
                ->route('prestataire.tenders.my-responses')
                ->with('error', 'Cette proposition ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'proposed_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,hourly,daily,negotiable',
            'message' => 'required|string|min:50|max:2000',
            'availability_start' => 'required|date|after_or_equal:today',
            'availability_end' => 'nullable|date|after:availability_start',
            'estimated_duration' => 'nullable|string|max:100',
        ]);

        // Mettre à jour avec compatibilité schéma + réactiver si withdrawn
        $updateData = [
            'proposed_price' => $validated['proposed_price'],
            'price_type' => $validated['price_type'],
            'cover_letter' => $validated['message'] ?? '',
        ];

        // Réactivation si la proposition avait été retirée
        if ($response->status === 'withdrawn') {
            $updateData['status'] = 'pending';
            $updateData['viewed_at'] = null;
            $updateData['responded_at'] = null;
            $updateData['client_message'] = null;
            $updateData['rejection_reason'] = null;
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_start')) {
                $updateData['availability_start'] = $validated['availability_start'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'availability_end')) {
                $updateData['availability_end'] = $validated['availability_end'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration')) {
                $updateData['estimated_duration'] = $validated['estimated_duration'] ?? null;
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_start_date')) {
                $updateData['proposed_start_date'] = $validated['availability_start'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'proposed_end_date')) {
                $updateData['proposed_end_date'] = $validated['availability_end'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tender_responses', 'estimated_duration_hours')) {
                $duration = $validated['estimated_duration'] ?? null;
                $updateData['estimated_duration_hours'] = is_numeric($duration) ? (int) $duration : null;
            }
        } catch (\Throwable $e) {
        }

        $response->update($updateData);

        return redirect()->route('prestataire.tenders.my-responses')
            ->with('success', 'Votre proposition a été mise à jour.');
    }

    /**
     * Retirer une proposition
     */
    public function withdrawResponse(TenderResponse $response)
    {
        $prestataire = auth()->user()->prestataire;

        if ($response->prestataire_id !== $prestataire->id) {
            abort(403);
        }

        if (!in_array($response->status, ['pending', 'viewed', 'shortlisted'])) {
            return back()->with('error', 'Cette proposition ne peut plus être retirée.');
        }

        $response->withdraw();

        return back()->with('success', 'Votre proposition a été retirée.');
    }

    /**
     * Mes invitations
     */
    public function invitations()
    {
        $prestataire = auth()->user()->prestataire;

        $unreadCount = TenderInvitation::where('prestataire_id', $prestataire->id)
            ->whereNull('read_at')
            ->count();

        $invitations = TenderInvitation::where('prestataire_id', $prestataire->id)
            ->with(['tender.client.user', 'tender.categories'])
            ->latest()
            ->paginate(15);

        return view('prestataire.tenders.invitations', compact('invitations', 'unreadCount'));
    }

    /**
     * Marquer invitation comme lue
     */
    public function markInvitationRead(TenderInvitation $invitation)
    {
        $prestataire = auth()->user()->prestataire;

        if ($invitation->prestataire_id !== $prestataire->id) {
            abort(403);
        }

        $invitation->markAsRead();

        return response()->json(['success' => true]);
    }
}
