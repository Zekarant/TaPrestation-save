<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TenderRequest;
use App\Models\TenderResponse;
use App\Models\Category;
use App\Jobs\ProcessTenderMatching;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Support\TableExistenceCache;
class TenderController extends Controller
{
    /**
     * Liste des appels d'offres du client
     */
    public function index(Request $request)
    {
        $client = auth()->user()->client;

        // Si l'utilisateur n'a pas de profil client (prestataire pur), rediriger
        if (!$client) {
            return redirect()->route('prestataire.tenders.index')
                ->with('info', 'En tant que prestataire, consultez les appels d\'offres disponibles.');
        }

        // Vérifier si la table tender_requests existe
        if (!TableExistenceCache::has('tender_requests')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                1,
                ['path' => request()->url()]
            );
            return view('client.tenders.index', [
                'tenders' => $emptyPaginator,
                'stats' => [
                    'total' => 0,
                    'published' => 0,
                    'in_progress' => 0,
                    'completed' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }

        try {
            $query = TenderRequest::where('client_id', $client->id)
                ->with(['categories', 'responses.prestataire.user', 'awardedPrestataire.user']);

            // Filtre par statut
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filtre par recherche texte
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
            }

            // Filtre par urgence
            if ($request->filled('urgency') && $request->urgency !== 'all') {
                $query->where('urgency', $request->urgency);
            }

            $tenders = $query->latest()->paginate(10)->withQueryString();

            $stats = [
                'total' => TenderRequest::where('client_id', $client->id)->count(),
                'published' => TenderRequest::where('client_id', $client->id)->where('status', 'published')->count(),
                'in_progress' => TenderRequest::where('client_id', $client->id)->where('status', 'in_progress')->count(),
                'completed' => TenderRequest::where('client_id', $client->id)->where('status', 'completed')->count(),
            ];

            return view('client.tenders.index', compact('tenders', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                1,
                ['path' => request()->url()]
            );
            return view('client.tenders.index', [
                'tenders' => $emptyPaginator,
                'stats' => [
                    'total' => 0,
                    'published' => 0,
                    'in_progress' => 0,
                    'completed' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Formulaire de création - Étape 1 (Infos de base)
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $tender = null;
        $draft = null;

        // Vérifier si la table tender_requests existe avant de chercher les brouillons
        if (TableExistenceCache::has('tender_requests')) {
            try {
                $client = auth()->user()->client;
                if ($client) {
                    $draft = TenderRequest::where('client_id', $client->id)
                        ->where('status', 'draft')
                        ->latest()
                        ->first();
                }
            } catch (\Exception $e) {
                $draft = null;
            }
        }

        return view('client.tenders.create', compact('categories', 'draft'));
    }

    /**
     * Sauvegarde d'une étape du formulaire
     */
    public function storeStep(Request $request, $step)
    {
        // Check if tender tables exist
        if (!TableExistenceCache::has('tender_requests')) {
            return response()->json([
                'success' => false,
                'message' => 'Le système d\'appels d\'offres n\'est pas encore configuré.',
            ], 503);
        }

        $client = auth()->user()->client;
        $tenderId = $request->input('tender_id');

        // Récupérer ou créer l'appel d'offre
        if ($tenderId) {
            $tender = TenderRequest::where('id', $tenderId)
                ->where('client_id', $client->id)
                ->where('status', 'draft')
                ->firstOrFail();
        } else {
            $tender = new TenderRequest(['client_id' => $client->id, 'status' => 'draft']);
        }

        switch ($step) {
            case 1:
                $validated = $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|min:50',
                    'categories' => 'required|array|min:1',
                    'categories.*' => 'exists:categories,id',
                    'urgency' => 'required|in:low,normal,high,urgent',
                ]);

                $tender->fill([
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'urgency' => $validated['urgency'],
                    'form_step' => max($tender->form_step ?? 1, 2),
                ]);
                $tender->save();
                $tender->categories()->sync($validated['categories']);
                break;

            case 2:
                $validated = $request->validate([
                    'city' => 'required|string|max:100',
                    'address' => 'nullable|string|max:255',
                    'postal_code' => 'nullable|string|max:10',
                    'radius_km' => 'required|integer|min:1|max:200',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                ]);

                $tender->fill($validated);
                $tender->form_step = max($tender->form_step, 3);
                $tender->save();
                break;

            case 3:
                $validated = $request->validate([
                    'start_date' => 'required|date|after_or_equal:today',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'preferred_time_start' => 'nullable|date_format:H:i',
                    'preferred_time_end' => 'nullable|date_format:H:i|after:preferred_time_start',
                    'flexible_dates' => 'boolean',
                ]);

                $tender->fill($validated);
                $tender->form_step = max($tender->form_step, 4);
                $tender->save();
                break;

            case 4:
                $validated = $request->validate([
                    'budget_min' => 'nullable|numeric|min:0',
                    'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
                    'budget_type' => 'required|in:fixed,hourly,daily,negotiable',
                    'budget_visible' => 'boolean',
                ]);

                $tender->fill($validated);
                $tender->form_step = max($tender->form_step, 5);
                $tender->save();
                break;

            case 5:
                // Validation des médias uploadés
                $request->validate([
                    'photos' => 'nullable|array|max:10',
                    'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                    'videos' => 'nullable|array|max:3',
                    'videos.*' => 'file|mimes:mp4,mov,avi,webm|max:102400',
                    'documents' => 'nullable|array|max:5',
                    'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
                ]);

                // Upload des médias
                $photos = $tender->photos ?? [];
                $videos = $tender->videos ?? [];
                $documents = $tender->documents ?? [];

                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $path = $photo->store('tenders/' . $tender->id . '/photos', 'public');
                        $photos[] = $path;
                    }
                }

                if ($request->hasFile('videos')) {
                    foreach ($request->file('videos') as $video) {
                        $path = $video->store('tenders/' . $tender->id . '/videos', 'public');
                        $videos[] = $path;
                    }
                }

                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $doc) {
                        $path = $doc->store('tenders/' . $tender->id . '/documents', 'public');
                        $documents[] = $path;
                    }
                }

                $tender->photos = $photos;
                $tender->videos = $videos;
                $tender->documents = $documents;
                $tender->form_step = max($tender->form_step, 6);
                $tender->save();
                break;

            case 6:
                $validated = $request->validate([
                    'access_instructions' => 'nullable|string',
                    'contact_name' => 'nullable|string|max:100',
                    'contact_phone' => 'nullable|string|max:20',
                    'contact_email' => 'nullable|email',
                    'contact_preference' => 'required|in:phone,email,messaging,any',
                ]);

                $tender->fill($validated);
                $tender->form_step = max($tender->form_step, 7);
                $tender->save();
                break;

            case 7:
                $validated = $request->validate([
                    'max_responses' => 'required|integer|min:1|max:50',
                    'auto_match' => 'boolean',
                    'public_visibility' => 'boolean',
                    'expires_days' => 'required|integer|min:7|max:90',
                ]);

                $tender->fill([
                    'max_responses' => $validated['max_responses'],
                    'auto_match' => $validated['auto_match'] ?? true,
                    'public_visibility' => $validated['public_visibility'] ?? true,
                    'expires_at' => now()->addDays($validated['expires_days']),
                ]);
                $tender->save();
                break;
        }

        return response()->json([
            'success' => true,
            'tender_id' => $tender->id,
            'step' => $tender->form_step,
            'message' => 'Étape sauvegardée',
        ]);
    }

    /**
     * Publier l'appel d'offre
     */
    public function publish(Request $request, TenderRequest $tender)
    {
        $this->authorize('update', $tender);

        // Vérifier que toutes les étapes sont complètes
        if ($tender->form_step < 7) {
            return back()->with('error', 'Veuillez compléter toutes les étapes avant de publier.');
        }

        $tender->publish();

        return redirect()->route('client.tenders.show', $tender)
            ->with('success', 'Votre appel d\'offre a été publié avec succès !');
    }

    /**
     * Afficher un appel d'offre
     */
    public function show(TenderRequest $tender)
    {
        $this->authorize('view', $tender);

        $tender->load([
            'categories',
            'responses' => function ($query) {
                $query->with('prestataire.user')->orderByDesc('match_score');
            },
            'invitations' => function ($query) {
                $query->with('prestataire.user')->orderByDesc('match_score');
            },
            'awardedPrestataire.user',
        ]);

        return view('client.tenders.show', compact('tender'));
    }

    /**
     * Répondre à une proposition
     */
    public function respondToProposal(Request $request, TenderRequest $tender, TenderResponse $response)
    {
        $this->authorize('update', $tender);

        $validated = $request->validate([
            'action' => 'required|in:shortlist,reject,accept',
            'message' => 'nullable|string',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        $response->markAsViewed();

        switch ($validated['action']) {
            case 'shortlist':
                $response->shortlist();
                $message = 'Proposition présélectionnée.';
                break;
            case 'reject':
                $response->reject($validated['rejection_reason'] ?? null);
                $message = 'Proposition rejetée.';
                break;
            case 'accept':
                $tender->awardTo($response->prestataire);
                $message = 'Appel d\'offre attribué avec succès !';
                break;
        }

        if ($validated['message']) {
            $response->update(['client_message' => $validated['message']]);
        }

        return back()->with('success', $message);
    }

    /**
     * Annuler un appel d'offre
     */
    public function cancel(TenderRequest $tender)
    {
        $this->authorize('update', $tender);

        if (!in_array($tender->status, ['draft', 'published', 'in_progress'])) {
            return back()->with('error', 'Cet appel d\'offre ne peut pas être annulé.');
        }

        $tender->update(['status' => 'cancelled']);

        return redirect()->route('client.tenders.index')
            ->with('success', 'Appel d\'offre annulé.');
    }

    /**
     * Supprimer un média
     */
    public function deleteMedia(Request $request, TenderRequest $tender)
    {
        $this->authorize('update', $tender);

        $validated = $request->validate([
            'type' => 'required|in:photos,videos,documents',
            'path' => 'required|string',
        ]);

        $media = $tender->{$validated['type']} ?? [];
        $media = array_filter($media, fn($p) => $p !== $validated['path']);
        $tender->{$validated['type']} = array_values($media);
        $tender->save();

        // Supprimer le fichier
        Storage::disk('public')->delete($validated['path']);

        return response()->json(['success' => true]);
    }

    /**
     * Supprimer un appel d'offre
     */
    public function destroy(TenderRequest $tender)
    {
        $this->authorize('delete', $tender);

        // Supprimer les médias associés
        if ($tender->photos) {
            foreach ($tender->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        if ($tender->videos) {
            foreach ($tender->videos as $video) {
                Storage::disk('public')->delete($video);
            }
        }
        if ($tender->documents) {
            foreach ($tender->documents as $doc) {
                Storage::disk('public')->delete($doc);
            }
        }

        $tender->delete();

        return redirect()->route('client.tenders.index')
            ->with('success', 'L\'appel d\'offre a été supprimé avec succès.');
    }

    /**
     * Création rapide d'un appel d'offre (formulaire simplifié)
     */
    public function quickCreate(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être connecté pour créer une demande.',
                ], 401);
            }

            $client = $user->client;
            if (!$client) {
                // Créer automatiquement le profil client s'il n'existe pas
                $client = \App\Models\Client::create([
                    'user_id' => $user->id,
                    'phone' => $user->phone ?? '',
                ]);
            }

            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:50',
                'categories' => 'required|array|min:1',
                'categories.*' => 'exists:categories,id',
                'city' => 'required|string|min:2|max:100',
                'postal_code' => 'nullable|string|max:10',
                'address' => 'nullable|string|max:255',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'urgency' => 'required|in:low,normal,urgent',
                'budget_type' => 'required|in:defined,negotiable',
                'budget_min' => 'nullable|numeric|min:0',
                'budget_max' => 'nullable|numeric|min:0',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200',
            ]);

            // Générer une référence unique
            $reference = 'AO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

            // Insérer directement via DB pour éviter le boot du modèle
            $tenderId = \Illuminate\Support\Facades\DB::table('tender_requests')->insertGetId([
                'client_id' => $client->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'reference' => $reference,
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'] ?? null,
                'address' => $request->input('address') ?: null,
                'start_date' => !empty($validated['start_date']) ? $validated['start_date'] : now()->addDays(1)->format('Y-m-d'),
                'end_date' => $request->input('end_date') ?: null,
                'urgency' => $validated['urgency'],
                'budget_type' => $validated['budget_type'] === 'defined' ? 'fixed' : 'negotiable',
                'budget_min' => isset($validated['budget_min']) ? (float) $validated['budget_min'] : null,
                'budget_max' => isset($validated['budget_max']) ? (float) $validated['budget_max'] : null,
                'budget_visible' => true,
                'contact_name' => $user->name,
                'contact_email' => $user->email,
                'contact_preference' => 'messaging',
                'max_responses' => 10,
                'auto_match' => true,
                'public_visibility' => true,
                'expires_at' => now()->addDays(30),
                'status' => 'published',
                'form_step' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Sync categories
            $categoryIds = array_map('intval', $validated['categories']);
            foreach ($categoryIds as $catId) {
                \Illuminate\Support\Facades\DB::table('tender_request_categories')->insert([
                    'tender_request_id' => $tenderId,
                    'category_id' => $catId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Upload des photos
            if ($request->hasFile('photos')) {
                $photoPaths = [];
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('tenders/' . $tenderId . '/photos', 'public');
                    $photoPaths[] = $path;
                }
                \Illuminate\Support\Facades\DB::table('tender_requests')
                    ->where('id', $tenderId)
                    ->update(['photos' => json_encode($photoPaths)]);
            }

            // Upload de la vidéo
            if ($request->hasFile('video')) {
                $videoFile = $request->file('video');
                $path = $videoFile->store('tenders/' . $tenderId . '/videos', 'public');
                \Illuminate\Support\Facades\DB::table('tender_requests')
                    ->where('id', $tenderId)
                    ->update(['videos' => json_encode([$path])]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votre demande a été publiée avec succès !',
                'tender_id' => $tenderId,
                'redirect' => '/client/tenders/' . $tenderId,
            ]);

        } catch (\Exception $e) {
            \Log::error('Tender creation error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage() . ' (fichier: ' . basename($e->getFile()) . ', ligne: ' . $e->getLine() . ')',
            ], 500);
        }
    }
}
