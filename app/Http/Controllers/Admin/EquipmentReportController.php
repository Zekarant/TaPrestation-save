<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentReportController extends Controller
{
    /**
     * Afficher la liste des signalements d'équipements
     */
    public function index(Request $request)
    {
        $query = EquipmentReport::with(['equipment'])
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
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $reports = $query->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => EquipmentReport::count(),
            'pending' => EquipmentReport::where('status', 'pending')->count(),
            'under_review' => EquipmentReport::where('status', 'under_review')->count(),
            'resolved' => EquipmentReport::where('status', 'resolved')->count(),
            'dismissed' => EquipmentReport::where('status', 'dismissed')->count(),
        ];
        
        return view('admin.reports.equipments.index', compact('reports', 'stats'));
    }
    
    /**
     * Afficher un signalement spécifique
     */
    public function show(EquipmentReport $report)
    {
        $report->load(['equipment']);
        
        return view('admin.reports.equipments.show', compact('report'));
    }
    
    /**
     * Mettre à jour le statut d'un signalement
     */
    public function updateStatus(Request $request, EquipmentReport $report)
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
    public function destroy(EquipmentReport $report)
    {
        $report->delete();
        
        return redirect()->route('administrateur.reports.equipments.index')
            ->with('success', 'Signalement supprimé avec succès.');
    }
}