<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrgentSaleReport;
use App\Models\EquipmentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AllReportsController extends Controller
{
    /**
     * Afficher tous les signalements
     */
    public function index(Request $request)
    {
        // Récupérer les signalements d'annonces
        $urgentSaleReports = UrgentSaleReport::with(['urgentSale', 'user'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type') && $request->type !== 'urgent_sales', function ($query) {
                $query->whereRaw('1 = 0'); // Exclure si le type ne correspond pas
            })
            ->get()
            ->map(function ($report) {
                $report->report_type = 'urgent_sale';
                $report->item_title = $report->urgentSale->title ?? 'Vente supprimée';
                $report->item_url = $report->urgentSale ? route('urgent-sales.show', $report->urgent_sale_id) : '#';
                return $report;
            });
        
        // Récupérer les signalements d'équipements
        $equipmentReports = EquipmentReport::with(['equipment'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type') && $request->type !== 'equipments', function ($query) {
                $query->whereRaw('1 = 0'); // Exclure si le type ne correspond pas
            })
            ->get()
            ->map(function ($report) {
                $report->report_type = 'equipment';
                $report->item_title = $report->equipment->name ?? 'Équipement supprimé';
                $report->item_url = $report->equipment ? route('equipment.show', $report->equipment_id) : '#';
                return $report;
            });
        
        // Fusionner et trier tous les signalements
        $allReports = $urgentSaleReports->concat($equipmentReports)
            ->sortByDesc('created_at')
            ->when($request->filled('search'), function ($collection) use ($request) {
                $search = strtolower($request->search);
                return $collection->filter(function ($report) use ($search) {
                    $userName = '';
                    $userEmail = '';
                    
                    if ($report->report_type === 'urgent_sale' && $report->user) {
                        $userName = $report->user->name ?? '';
                        $userEmail = $report->user->email ?? '';
                    }
                    
                    return str_contains(strtolower($report->item_title), $search) ||
                           str_contains(strtolower($userName), $search) ||
                           str_contains(strtolower($userEmail), $search) ||
                           str_contains(strtolower($report->description ?? ''), $search);
                });
            });
        
        // Pagination manuelle
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedReports = $allReports->slice($offset, $perPage);
        
        // Statistiques globales
        $stats = [
            'total' => UrgentSaleReport::count() + EquipmentReport::count(),
            'pending' => UrgentSaleReport::where('status', 'pending')->count() + EquipmentReport::where('status', 'pending')->count(),
            'under_review' => UrgentSaleReport::where('status', 'reviewed')->count() + EquipmentReport::where('status', 'under_review')->count(),
            'resolved' => UrgentSaleReport::where('status', 'resolved')->count() + EquipmentReport::where('status', 'resolved')->count(),
            'dismissed' => UrgentSaleReport::where('status', 'dismissed')->count() + EquipmentReport::where('status', 'dismissed')->count(),
            'urgent_sales' => UrgentSaleReport::count(),
            'equipments' => EquipmentReport::count(),
        ];
        
        return view('admin.reports.all.index', compact('paginatedReports', 'stats', 'allReports'));
    }
}