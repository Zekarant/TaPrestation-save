<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrgentSaleReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrgentSaleReportController extends Controller
{
    /**
     * Afficher la liste des signalements d'annonces
     */
    public function index(Request $request)
    {
        $query = UrgentSaleReport::with(['urgentSale', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par raison
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('urgentSale', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $reports = $query->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => UrgentSaleReport::count(),
            'pending' => UrgentSaleReport::where('status', 'pending')->count(),
            'reviewed' => UrgentSaleReport::where('status', 'reviewed')->count(),
            'resolved' => UrgentSaleReport::where('status', 'resolved')->count(),
            'dismissed' => UrgentSaleReport::where('status', 'dismissed')->count(),
        ];
        
        return view('admin.reports.urgent-sales.index', compact('reports', 'stats'));
    }
    
    /**
     * Afficher un signalement spécifique
     */
    public function show(UrgentSaleReport $report)
    {
        $report->load(['urgentSale', 'user']);
        
        return view('admin.reports.urgent-sales.show', compact('report'));
    }
    
    /**
     * Mettre à jour le statut d'un signalement
     */
    public function updateStatus(Request $request, UrgentSaleReport $report)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000'
        ]);
        
        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now()
        ]);
        
        return back()->with('success', 'Statut du signalement mis à jour avec succès.');
    }
    
    /**
     * Supprimer un signalement
     */
    public function destroy(UrgentSaleReport $report)
    {
        $report->delete();
        
        return redirect()->route('administrateur.reports.urgent-sales.index')
            ->with('success', 'Signalement supprimé avec succès.');
    }
}