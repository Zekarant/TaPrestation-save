<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\PrestataireVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Afficher le statut de vérification du prestataire
     */
    public function index()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            abort(403, 'Accès non autorisé');
        }

        $verificationRequests = $prestataire->verificationRequests()
            ->orderBy('created_at', 'desc')
            ->get();

        $canSubmitRequest = !$prestataire->hasPendingVerificationRequest() && !$prestataire->isVerified();
        
        $automaticVerificationStatus = [
            'meets_criteria' => $prestataire->meetsAutomaticVerificationCriteria(),
            'positive_reviews' => $prestataire->reviews()->where('rating', '>=', 4)->count(),
            'negative_reviews' => $prestataire->reviews()->where('rating', '<', 3)->count(),
            'average_rating' => $prestataire->rating_average ?? 0,
            'email_verified' => $prestataire->user->email_verified_at !== null,
            'phone_verified' => $prestataire->user->phone_verified_at !== null,
        ];

        return view('prestataire.verification.index', compact(
            'prestataire', 
            'verificationRequests', 
            'canSubmitRequest',
            'automaticVerificationStatus'
        ));
    }

    /**
     * Afficher le formulaire de demande de vérification
     */
    public function create()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $prestataire->hasPendingVerificationRequest() || $prestataire->isVerified()) {
            return redirect()->route('prestataire.verification.index')
                ->with('error', 'Vous ne pouvez pas soumettre de nouvelle demande.');
        }

        return view('prestataire.verification.create');
    }

    /**
     * Soumettre une demande de vérification
     */
    public function store(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $prestataire->hasPendingVerificationRequest() || $prestataire->isVerified()) {
            return redirect()->route('prestataire.verification.index')
                ->with('error', 'Vous ne pouvez pas soumettre de nouvelle demande.');
        }

        $request->validate([
            'document_type' => 'required|in:identity,professional,business',
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $documentPaths = [];
        
        // Upload des documents
        foreach ($request->file('documents') as $document) {
            $path = $document->store('verification-documents', 'public');
            $documentPaths[] = $path;
        }

        // Créer la demande de vérification
        PrestataireVerificationRequest::create([
            'prestataire_id' => $prestataire->id,
            'document_type' => $request->document_type,
            'documents' => $documentPaths,
            'status' => 'pending',
            'submitted_at' => now()
        ]);

        return redirect()->route('prestataire.verification.index')
            ->with('success', 'Votre demande de vérification a été soumise avec succès. Elle sera examinée par notre équipe.');
    }

    /**
     * Afficher les détails d'une demande de vérification
     */
    public function show(PrestataireVerificationRequest $verificationRequest)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $verificationRequest->prestataire_id !== $prestataire->id) {
            abort(403, 'Accès non autorisé');
        }

        return view('prestataire.verification.show', compact('verificationRequest'));
    }

    /**
     * Télécharger un document de la demande
     */
    public function downloadDocument(PrestataireVerificationRequest $verificationRequest, $documentIndex)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire || $verificationRequest->prestataire_id !== $prestataire->id) {
            abort(403, 'Accès non autorisé');
        }

        if (!$verificationRequest->documents || !isset($verificationRequest->documents[$documentIndex])) {
            abort(404, 'Document non trouvé');
        }

        $documentPath = $verificationRequest->documents[$documentIndex];
        
        if (!Storage::disk('public')->exists($documentPath)) {
            abort(404, 'Fichier non trouvé');
        }

        return Storage::disk('public')->download($documentPath);
    }

    /**
     * Vérifier les critères de vérification automatique
     */
    public function checkAutomaticCriteria()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            abort(403, 'Accès non autorisé');
        }

        $meetsCriteria = $prestataire->meetsAutomaticVerificationCriteria();
        
        if ($meetsCriteria && $prestataire->applyAutomaticVerification()) {
            return redirect()->route('prestataire.verification.index')
                ->with('success', 'Félicitations ! Vous avez été automatiquement vérifié.');
        }

        return redirect()->route('prestataire.verification.index')
            ->with('info', 'Vous ne remplissez pas encore tous les critères pour la vérification automatique.');
    }
}