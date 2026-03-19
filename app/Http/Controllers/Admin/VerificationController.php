<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestataireVerificationRequest;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PrestataireApprovedNotification;

class VerificationController extends Controller
{
    /**
     * Afficher la liste des demandes de vérification
     */
    public function index(Request $request)
    {
        $query = PrestataireVerificationRequest::with(['prestataire.user', 'reviewedBy'])
            ->orderBy('created_at', 'desc');

        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par nom de prestataire
        if ($request->filled('search')) {
            $query->whereHas('prestataire.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $verificationRequests = $query->paginate(15);

        // Statistiques
        $stats = [
            'total' => PrestataireVerificationRequest::count(),
            'pending' => PrestataireVerificationRequest::where('status', 'pending')->count(),
            'approved' => PrestataireVerificationRequest::where('status', 'approved')->count(),
            'rejected' => PrestataireVerificationRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.verifications.index', compact('verificationRequests', 'stats'));
    }

    /**
     * Afficher les détails d'une demande de vérification
     */
    public function show(PrestataireVerificationRequest $verificationRequest)
    {
        $verificationRequest->load(['prestataire.user', 'reviewedBy']);
        
        return view('admin.verifications.show', compact('verificationRequest'));
    }

    /**
     * Approuver une demande de vérification
     */
    public function approve(Request $request, PrestataireVerificationRequest $verificationRequest)
    {
        $request->validate([
            'admin_comment' => 'nullable|string|max:1000'
        ]);

        $verificationRequest->update([
            'status' => 'approved',
            'admin_comment' => $request->admin_comment,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id()
        ]);

        // Marquer le prestataire comme vérifié
        $verificationRequest->prestataire->update([
            'is_verified' => true,
            'verification_status' => 'verified_manual'
        ]);

        // Envoyer une notification au prestataire
        $verificationRequest->prestataire->user->notify(
            new PrestataireApprovedNotification($verificationRequest->prestataire)
        );

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Demande de vérification approuvée avec succès.');
    }

    /**
     * Rejeter une demande de vérification
     */
    public function reject(Request $request, PrestataireVerificationRequest $verificationRequest)
    {
        $request->validate([
            'admin_comment' => 'required|string|max:1000'
        ]);

        $verificationRequest->update([
            'status' => 'rejected',
            'admin_comment' => $request->admin_comment,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id()
        ]);

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Demande de vérification rejetée.');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument(PrestataireVerificationRequest $verificationRequest, $documentIndex)
    {
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
     * Exécuter la vérification automatique pour tous les prestataires éligibles
     */
    public function runAutomaticVerification()
    {
        $prestataires = Prestataire::where('is_verified', false)
            ->where('is_approved', true)
            ->get();

        $verifiedCount = 0;

        foreach ($prestataires as $prestataire) {
            if ($prestataire->applyAutomaticVerification()) {
                $verifiedCount++;
            }
        }

        return redirect()->route('admin.verifications.index')
            ->with('success', "Vérification automatique terminée. {$verifiedCount} prestataire(s) vérifié(s).");
    }

    /**
     * Révoquer la vérification d'un prestataire
     */
    public function revokeVerification(Prestataire $prestataire)
    {
        $prestataire->update([
            'is_verified' => false,
            'verification_status' => 'revoked'
        ]);

        // Marquer toutes les demandes de vérification comme rejetées
        $prestataire->verificationRequests()
            ->where('status', 'approved')
            ->update([
                'status' => 'rejected',
                'admin_comment' => 'Vérification révoquée par l\'administrateur',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);

        return redirect()->back()
            ->with('success', 'Vérification révoquée avec succès.');
    }
}
