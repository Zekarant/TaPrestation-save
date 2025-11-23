<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceReportController extends Controller
{
    /**
     * Afficher la liste des signalements de services
     */
    public function index(Request $request)
    {
        $query = ServiceReport::with(['service', 'service.prestataire.user'])
            ->orderBy('created_at', 'desc');
        
        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('service', function ($service) use ($search) {
                      $service->where('title', 'like', "%{$search}%")
                             ->orWhereHas('prestataire.user', function ($user) use ($search) {
                                 $user->where('name', 'like', "%{$search}%");
                             });
                  });
            });
        }
        
        $reports = $query->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => ServiceReport::count(),
            'pending' => ServiceReport::where('status', 'pending')->count(),
            'under_review' => ServiceReport::where('status', 'under_review')->count(),
            'resolved' => ServiceReport::where('status', 'resolved')->count(),
            'dismissed' => ServiceReport::where('status', 'dismissed')->count(),
        ];
        
        return view('admin.reports.services.index', compact('reports', 'stats'));
    }
    
    /**
     * Afficher un signalement spécifique
     */
    public function show(ServiceReport $report)
    {
        $report->load(['service', 'service.prestataire.user']);
        
        return view('admin.reports.services.show', compact('report'));
    }
    
    /**
     * Mettre à jour le statut d'un signalement
     */
    public function updateStatus(Request $request, ServiceReport $report)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,investigating,resolved,dismissed,escalated',
            'admin_notes' => 'nullable|string|max:1000'
        ]);
        
        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'resolved_by' => Auth::id(),
            'resolved_at' => $request->status === 'resolved' ? now() : null
        ]);
        
        return back()->with('success', 'Statut du signalement mis à jour avec succès.');
    }
    
    /**
     * Supprimer un signalement
     */
    public function destroy(ServiceReport $report)
    {
        $report->delete();
        
        return redirect()->route('administrateur.reports.services.index')
            ->with('success', 'Signalement supprimé avec succès.');
    }
}