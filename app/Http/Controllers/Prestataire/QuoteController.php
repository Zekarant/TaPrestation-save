<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Notifications\QuoteSentNotification;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    /**
     * Liste des devis du prestataire
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        $query = Quote::forPrestataire($prestataire->id)
            ->with(['client.user', 'service'])
            ->latest();

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('client.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $quotes = $query->paginate(15);

        // Stats
        $stats = [
            'total' => Quote::forPrestataire($prestataire->id)->count(),
            'draft' => Quote::forPrestataire($prestataire->id)->draft()->count(),
            'pending' => Quote::forPrestataire($prestataire->id)->pending()->count(),
            'accepted' => Quote::forPrestataire($prestataire->id)->accepted()->count(),
            'rejected' => Quote::forPrestataire($prestataire->id)->rejected()->count(),
        ];

        return view('prestataire.quotes.index', [
            'quotes' => $quotes,
            'stats' => $stats,
            'currentStatus' => $request->status,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        // Liste des clients avec qui le prestataire a déjà travaillé
        $clients = Client::whereHas('bookings', function ($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })->with('user')->get();

        // Services du prestataire
        $services = $prestataire->services()->where('status', 'active')->get();

        // Client pré-sélectionné ?
        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::with('user')->find($request->client_id);
        }

        // Service pré-sélectionné ?
        $selectedService = null;
        if ($request->filled('service_id')) {
            $selectedService = Service::find($request->service_id);
        }

        return view('prestataire.quotes.create', [
            'clients' => $clients,
            'services' => $services,
            'selectedClient' => $selectedClient,
            'selectedService' => $selectedService,
            'prestataire' => $prestataire,
        ]);
    }

    /**
     * Enregistrer le devis
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'service_id' => 'nullable|exists:services,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'valid_until' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:5000',
        ]);

        $prestataire = Auth::user()->prestataire;

        DB::beginTransaction();
        try {
            $quote = new Quote([
                'prestataire_id' => $prestataire->id,
                'client_id' => $request->client_id,
                'service_id' => $request->service_id,
                'title' => $request->title,
                'description' => $request->description,
                'items' => $request->items,
                'tax_rate' => $request->tax_rate ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_type' => $request->discount_type ?? 'fixed',
                'valid_until' => $request->valid_until,
                'notes' => $request->notes,
                'terms' => $request->terms ?? $prestataire->default_quote_terms,
                'currency' => 'EUR',
            ]);

            $quote->calculateTotal();
            $quote->save();

            DB::commit();

            // Si envoi direct demandé
            if ($request->boolean('send_immediately')) {
                $quote->send();
                return redirect()
                    ->route('prestataire.quotes.show', $quote)
                    ->with('success', 'Devis créé et envoyé au client avec succès !');
            }

            return redirect()
                ->route('prestataire.quotes.show', $quote)
                ->with('success', 'Devis créé avec succès en brouillon.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la création du devis.']);
        }
    }

    /**
     * Afficher un devis
     */
    public function show(Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['client.user', 'service', 'prestataire.user']);

        return view('prestataire.quotes.show', [
            'quote' => $quote,
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->can_be_edited) {
            return back()->withErrors(['error' => 'Ce devis ne peut plus être modifié.']);
        }

        $prestataire = Auth::user()->prestataire;

        $clients = Client::whereHas('bookings', function ($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })->with('user')->get();

        $services = $prestataire->services()->where('status', 'active')->get();

        return view('prestataire.quotes.edit', [
            'quote' => $quote,
            'clients' => $clients,
            'services' => $services,
            'prestataire' => $prestataire,
        ]);
    }

    /**
     * Mettre à jour le devis
     */
    public function update(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->can_be_edited) {
            return back()->withErrors(['error' => 'Ce devis ne peut plus être modifié.']);
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'service_id' => 'nullable|exists:services,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'valid_until' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:5000',
        ]);

        $quote->fill([
            'client_id' => $request->client_id,
            'service_id' => $request->service_id,
            'title' => $request->title,
            'description' => $request->description,
            'items' => $request->items,
            'tax_rate' => $request->tax_rate ?? 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'discount_type' => $request->discount_type ?? 'fixed',
            'valid_until' => $request->valid_until,
            'notes' => $request->notes,
            'terms' => $request->terms,
        ]);

        $quote->calculateTotal();
        $quote->save();

        return redirect()
            ->route('prestataire.quotes.show', $quote)
            ->with('success', 'Devis mis à jour avec succès.');
    }

    /**
     * Envoyer le devis au client
     */
    public function send(Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->can_be_sent) {
            return back()->withErrors(['error' => 'Ce devis ne peut pas être envoyé.']);
        }

        $quote->send();

        return back()->with('success', 'Devis envoyé au client avec succès !');
    }

    /**
     * Dupliquer un devis
     */
    public function duplicate(Quote $quote)
    {
        $this->authorize('view', $quote);

        $newQuote = $quote->replicate([
            'reference_number',
            'status',
            'sent_at',
            'viewed_at',
            'accepted_at',
            'rejected_at',
            'rejection_reason',
        ]);

        $newQuote->status = Quote::STATUS_DRAFT;
        $newQuote->valid_until = now()->addDays(30);
        $newQuote->save();

        return redirect()
            ->route('prestataire.quotes.edit', $newQuote)
            ->with('success', 'Devis dupliqué avec succès. Vous pouvez le modifier.');
    }

    /**
     * Annuler un devis
     */
    public function cancel(Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->cancel()) {
            return back()->withErrors(['error' => 'Ce devis ne peut pas être annulé.']);
        }

        return back()->with('success', 'Devis annulé.');
    }

    /**
     * Supprimer un devis (brouillon uniquement)
     */
    public function destroy(Quote $quote)
    {
        $this->authorize('delete', $quote);

        if ($quote->status !== Quote::STATUS_DRAFT) {
            return back()->withErrors(['error' => 'Seuls les brouillons peuvent être supprimés.']);
        }

        $quote->delete();

        return redirect()
            ->route('prestataire.quotes.index')
            ->with('success', 'Devis supprimé.');
    }

    /**
     * Télécharger le devis en PDF
     */
    public function downloadPdf(Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['client.user', 'service', 'prestataire.user']);

        $pdf = Pdf::loadView('pdf.quote', compact('quote'));

        return $pdf->download('devis-' . $quote->reference_number . '.pdf');
    }

    /**
     * Rechercher des clients (AJAX)
     */
    public function searchClients(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        $search = $request->get('q', '');

        $clients = Client::whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
        ->whereHas('bookings', function ($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })
        ->with('user')
        ->take(10)
        ->get();

        return response()->json($clients->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->user->name,
                'email' => $client->user->email,
                'avatar' => $client->user->avatar_url,
            ];
        }));
    }
}
