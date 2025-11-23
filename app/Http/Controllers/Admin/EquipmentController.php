<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\EquipmentReport;
use App\Models\EquipmentReview;
use App\Models\EquipmentRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Tableau de bord des équipements
     */
    public function dashboard()
    {
        // Statistiques générales
        $stats = [
            'total_equipment' => Equipment::count(),
            'active_equipment' => Equipment::active()->count(),
            'pending_reports' => EquipmentReport::pending()->count(),
            'total_rentals' => EquipmentRental::count(),
            'total_revenue' => EquipmentRental::where('payment_status', 'paid')->sum('final_amount'),
            'average_rating' => EquipmentReview::avg('overall_rating')
        ];
        
        // Équipements récemment ajoutés
        $recentEquipment = Equipment::with(['prestataire.user', 'category', 'subcategory'])
                                  ->latest()
                                  ->limit(5)
                                  ->get();
        
        // Signalements récents
        $recentReports = EquipmentReport::with(['equipment.prestataire.user'])
                                       ->latest()
                                       ->limit(5)
                                       ->get();
        
        // Équipements les plus loués
        $topRentedEquipment = Equipment::with(['prestataire.user'])
                                     ->orderBy('total_rentals', 'desc')
                                     ->limit(5)
                                     ->get();
        
        // Évolution mensuelle
        $monthlyStats = Equipment::select(
                                    DB::raw('YEAR(created_at) as year'),
                                    DB::raw('MONTH(created_at) as month'),
                                    DB::raw('count(*) as count')
                                )
                                ->where('created_at', '>=', now()->subMonths(12))
                                ->groupBy('year', 'month')
                                ->orderBy('year')
                                ->orderBy('month')
                                ->get();
        
        return view('admin.dashboard-modern', compact(
            'stats',
            'recentEquipment',
            'recentReports',
            'topRentedEquipment',
            'monthlyStats'
        ));
    }
    
    /**
     * Liste des équipements
     */
    public function index(Request $request)
    {
        $query = Equipment::with(['prestataire.user', 'category', 'subcategory']);
        
        // Filtres
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->inactive();
            }
        }
        
        if ($request->filled('category')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category)
                  ->orWhere('subcategory_id', $request->category);
            });
        }
        
        if ($request->filled('prestataire')) {
            $query->where('prestataire_id', $request->prestataire);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        
        if ($request->filled('reported')) {
            if ($request->reported === 'yes') {
                $query->whereHas('reports', function ($q) {
                    $q->where('status', 'pending');
                });
            }
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('prestataire.user', function ($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'prestataire':
                $query->join('prestataires', 'equipment.prestataire_id', '=', 'prestataires.id')
                      ->join('users', 'prestataires.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder)
                      ->select('equipment.*');
                break;
            case 'price':
                $query->orderBy('price_per_day', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('average_rating', $sortOrder);
                break;
            case 'rentals':
                $query->orderBy('total_rentals', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $equipment = $query->paginate(20)->withQueryString();
        
        // Données pour les filtres
        $categories = \App\Models\Category::whereNull('parent_id')->get(['id', 'name']);
        
        return view('admin.equipments.index-modern', compact('equipment', 'categories'));
    }
    
    /**
     * Détails d'un équipement
     */
    public function show(Equipment $equipment)
    {
        $equipment->load([
            'prestataire.user',
            'category',
            'subcategory',
            'reviews' => function ($query) {
                $query->latest()->limit(10);
            },
            'rentals' => function ($query) {
                $query->latest()->limit(10);
            },
            'reports' => function ($query) {
                $query->latest();
            }
        ]);
        
        // Statistiques de l'équipement
        $stats = [
            'total_rentals' => $equipment->rentals()->count(),
            'total_revenue' => $equipment->rentals()->where('payment_status', 'paid')->sum('final_amount'),
            'average_rating' => $equipment->reviews()->avg('overall_rating'),
            'total_reviews' => $equipment->reviews()->count(),
            'pending_reports' => $equipment->reports()->pending()->count()
        ];
        
        return view('admin.equipments.show', compact('equipment', 'stats'));
    }
    
    /**
     * Suspend un équipement
     */
    public function suspend(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365' // jours
        ]);
        
        $suspendedUntil = $validated['duration'] ? now()->addDays($validated['duration']) : null;
        
        $equipment->update([
            'is_active' => false,
            'status' => 'suspended',
            'suspension_reason' => $validated['reason'],
            'suspended_at' => now(),
            'suspended_until' => $suspendedUntil,
            'suspended_by' => auth()->id()
        ]);
        
        // TODO: Envoyer notification au prestataire
        
        return back()->with('success', 'Équipement suspendu avec succès.');
    }
    
    /**
     * Réactive un équipement suspendu
     */
    public function reactivate(Equipment $equipment)
    {
        if ($equipment->status !== 'suspended') {
            return back()->with('error', 'Cet équipement n\'est pas suspendu.');
        }
        
        $equipment->update([
            'is_active' => true,
            'status' => 'available',
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspended_until' => null,
            'suspended_by' => null,
            'reactivated_at' => now(),
            'reactivated_by' => auth()->id()
        ]);
        
        // TODO: Envoyer notification au prestataire
        
        return back()->with('success', 'Équipement réactivé avec succès.');
    }
    
    /**
     * Supprime définitivement un équipement
     */
    public function destroy(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'confirm' => 'required|accepted'
        ]);
        
        // Vérifier qu'il n'y a pas de locations actives
        $activeRentals = $equipment->rentals()
                                  ->whereIn('rental_status', ['confirmed', 'in_preparation', 'delivered', 'in_progress'])
                                  ->count();
        
        if ($activeRentals > 0) {
            return back()->with('error', 'Impossible de supprimer un équipement avec des locations actives.');
        }
        
        // Supprimer les photos
        if ($equipment->photos) {
            foreach ($equipment->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        // Log de suppression
        DB::table('equipment_deletions')->insert([
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'prestataire_id' => $equipment->prestataire_id,
            'reason' => $validated['reason'],
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
            'equipment_data' => json_encode($equipment->toArray())
        ]);
        
        $equipment->delete();
        
        // TODO: Envoyer notification au prestataire
        
        return redirect()->route('administrateur.equipment.index')
                        ->with('success', 'Équipement supprimé définitivement.');
    }
    
    /**
     * Gestion des signalements
     */
    public function reports(Request $request)
    {
        $query = EquipmentReport::with(['equipment.prestataire.user']);
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'priority':
                $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
                break;
            case 'equipment':
                $query->join('equipment', 'equipment_reports.equipment_id', '=', 'equipment.id')
                      ->orderBy('equipment.name', $sortOrder)
                      ->select('equipment_reports.*');
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $reports = $query->paginate(15)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => EquipmentReport::count(),
            'pending' => EquipmentReport::pending()->count(),
            'urgent' => EquipmentReport::where('priority', 'urgent')->where('status', 'pending')->count(),
            'resolved' => EquipmentReport::resolved()->count()
        ];
        
        return view('admin.reports.equipments.index', compact('reports', 'stats'));
    }
    
    /**
     * Détails d'un signalement
     */
    public function showReport(EquipmentReport $report)
    {
        $report->load([
            'equipment.prestataire.user',
            'equipment.category',
            'equipment.subcategory'
        ]);
        
        return view('admin.reports.equipments.show', compact('report'));
    }
    
    /**
     * Traite un signalement
     */
    public function processReport(Request $request, EquipmentReport $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,warn_prestataire,suspend_equipment,delete_equipment',
            'admin_notes' => 'required|string|max:1000',
            'resolution_details' => 'nullable|string|max:1000'
        ]);
        
        DB::transaction(function () use ($request, $report, $validated) {
            // Mettre à jour le signalement
            $report->update([
                'status' => 'resolved',
                'admin_notes' => $validated['admin_notes'],
                'resolution_details' => $validated['resolution_details'],
                'resolved_at' => now(),
                'resolved_by' => auth()->id()
            ]);
            
            // Appliquer l'action
            switch ($validated['action']) {
                case 'warn_prestataire':
                    // TODO: Envoyer avertissement au prestataire
                    break;
                    
                case 'suspend_equipment':
                    $report->equipment->update([
                        'is_active' => false,
                        'status' => 'suspended',
                        'suspension_reason' => 'Signalement validé: ' . $report->reason,
                        'suspended_at' => now(),
                        'suspended_by' => auth()->id()
                    ]);
                    break;
                    
                case 'delete_equipment':
                    // Marquer pour suppression (nécessite confirmation séparée)
                    $report->equipment->update([
                        'is_active' => false,
                        'status' => 'pending_deletion',
                        'deletion_reason' => 'Signalement grave: ' . $report->reason
                    ]);
                    break;
            }
        });
        
        return back()->with('success', 'Signalement traité avec succès.');
    }
    
    /**
     * Rejette un signalement
     */
    public function dismissReport(Request $request, EquipmentReport $report)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000'
        ]);
        
        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $validated['admin_notes'],
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);
        
        return back()->with('success', 'Signalement rejeté.');
    }
    
    /**
     * Statistiques globales
     */
    public function statistics(Request $request)
    {
        $period = $request->get('period', '12months');
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            'year' => now()->subYear(),
            default => now()->subMonths(12)
        };
        
        // Statistiques générales
        $generalStats = [
            'total_equipment' => Equipment::count(),
            'active_equipment' => Equipment::active()->count(),
            'total_prestataires' => Equipment::distinct('prestataire_id')->count(),
            'total_categories' => \App\Models\Category::count(),
            'total_rentals' => EquipmentRental::count(),
            'total_revenue' => EquipmentRental::where('payment_status', 'paid')->sum('final_amount'),
            'average_rating' => EquipmentReview::avg('overall_rating'),
            'total_reviews' => EquipmentReview::count()
        ];
        
        // Évolution temporelle
        $timeStats = [
            'equipment' => Equipment::where('created_at', '>=', $startDate)
                                  ->select(
                                      DB::raw('DATE(created_at) as date'),
                                      DB::raw('count(*) as count')
                                  )
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get(),
            'rentals' => EquipmentRental::where('created_at', '>=', $startDate)
                                      ->select(
                                          DB::raw('DATE(created_at) as date'),
                                          DB::raw('count(*) as count'),
                                          DB::raw('sum(final_amount) as revenue')
                                      )
                                      ->groupBy('date')
                                      ->orderBy('date')
                                      ->get()
        ];
        
        // Top catégories
        $topCategories = \App\Models\Category::whereNull('parent_id')
                                             ->withCount(['equipmentsAsCategory', 'equipmentsAsSubcategory'])
                                             ->get()
                                             ->map(function ($category) {
                                                 $category->equipment_count = $category->equipments_as_category_count + $category->equipments_as_subcategory_count;
                                                 return $category;
                                             })
                                             ->sortByDesc('equipment_count')
                                             ->take(10)
                                             ->values();
        
        // Top prestataires
        $topPrestataires = DB::table('prestataires')
                            ->join('users', 'prestataires.user_id', '=', 'users.id')
                            ->join('equipment', 'prestataires.id', '=', 'equipment.prestataire_id')
                            ->select(
                                'users.name',
                                DB::raw('count(equipment.id) as equipment_count'),
                                DB::raw('avg(equipment.average_rating) as avg_rating'),
                                DB::raw('sum(equipment.total_rentals) as total_rentals')
                            )
                            ->groupBy('prestataires.id', 'users.name')
                            ->orderBy('equipment_count', 'desc')
                            ->limit(10)
                            ->get();
        
        // Répartition géographique
        $cityStats = Equipment::select('city', DB::raw('count(*) as count'))
                             ->whereNotNull('city')
                             ->groupBy('city')
                             ->orderBy('count', 'desc')
                             ->limit(15)
                             ->get();
        
        return view('admin.dashboard-modern', compact(
            'generalStats',
            'timeStats',
            'topCategories',
            'topPrestataires',
            'cityStats',
            'period'
        ));
    }
    
    /**
     * Export des données
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'equipment');
        
        switch ($type) {
            case 'equipment':
                return $this->exportEquipment($request);
            case 'reports':
                return $this->exportReports($request);
            case 'rentals':
                return $this->exportRentals($request);
            default:
                return back()->with('error', 'Type d\'export invalide.');
        }
    }
    
    private function exportEquipment(Request $request)
    {
        $equipment = Equipment::with(['prestataire.user', 'category', 'subcategory'])->get();
        
        $filename = 'equipements_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($equipment) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Nom',
                'Prestataire',
                'Catégories',
                'Prix/jour',
                'Ville',
                'Statut',
                'Note moyenne',
                'Locations totales',
                'Date création'
            ]);
            
            foreach ($equipment as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->prestataire->user->name,
                    $item->categories->pluck('name')->implode(', '),
                    number_format($item->price_per_day, 2) . ' €',
                    $item->city,
                    $item->is_active ? 'Actif' : 'Inactif',
                    number_format($item->average_rating, 1),
                    $item->total_rentals,
                    $item->created_at->format('d/m/Y')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportReports(Request $request)
    {
        $reports = EquipmentReport::with(['equipment.prestataire.user'])->get();
        
        $filename = 'signalements_equipements_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Équipement',
                'Prestataire',
                'Catégorie',
                'Raison',
                'Priorité',
                'Statut',
                'Date signalement',
                'Date résolution'
            ]);
            
            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->id,
                    $report->equipment->name,
                    $report->equipment->prestataire->user->name,
                    ucfirst($report->category),
                    $report->reason,
                    ucfirst($report->priority),
                    ucfirst($report->status),
                    $report->created_at->format('d/m/Y H:i'),
                    $report->resolved_at ? $report->resolved_at->format('d/m/Y H:i') : ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportRentals(Request $request)
    {
        $rentals = EquipmentRental::with(['equipment.prestataire.user', 'client.user'])->get();
        
        $filename = 'locations_equipements_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($rentals) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Numéro',
                'Équipement',
                'Prestataire',
                'Client',
                'Date début',
                'Date fin',
                'Durée',
                'Montant',
                'Statut',
                'Date création'
            ]);
            
            foreach ($rentals as $rental) {
                fputcsv($file, [
                    $rental->rental_number,
                    $rental->equipment->name,
                    $rental->equipment->prestataire->user->name,
                    $rental->client->user->name,
                    $rental->start_date,
                    $rental->end_date,
                    $rental->duration_days . ' jours',
                    number_format($rental->final_amount, 2) . ' €',
                    ucfirst($rental->rental_status),
                    $rental->created_at->format('d/m/Y')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche le formulaire d'édition d'un équipement
     */
    public function edit(Equipment $equipment)
    {
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('admin.equipments.edit', compact('equipment', 'categories'));
    }

    /**
     * Met à jour un équipement
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'technical_specifications' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            
            // Détails techniques
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'power_requirements' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            
            // Tarification
            'price_per_hour' => 'nullable|numeric|min:0',
            'daily_rate' => 'required|numeric|min:1',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            
            // État et disponibilité
            'condition' => 'required|in:new,excellent,very_good,good,fair,poor',
            'status' => 'required|in:active,inactive,maintenance,rented',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            
            // Localisation
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Conditions de location
            'minimum_rental_days' => 'nullable|integer|min:1',
            'maximum_rental_days' => 'nullable|integer|min:1',
            'age_restriction' => 'nullable|integer|min:16|max:99',
            'experience_required' => 'boolean',
            'insurance_required' => 'boolean',
            'license_required' => 'boolean',
            'rental_conditions' => 'nullable|string',
            
            // Instructions et accessoires
            'usage_instructions' => 'nullable|string',
            'safety_instructions' => 'nullable|string',
            'accessories' => 'nullable|string',
        ]);
        
        // Gestion de la photo principale
        if ($request->hasFile('main_photo')) {
            // Supprimer l'ancienne photo
            if ($equipment->main_photo) {
                Storage::disk('public')->delete($equipment->main_photo);
            }
            $validated['main_photo'] = $request->file('main_photo')
                ->store('equipment_photos', 'public');
        }
        
        // Mise à jour de l'équipement
        $equipment->update($validated);
        
        return redirect()->route('admin.equipments.show', $equipment)
                        ->with('success', 'Équipement mis à jour avec succès!');
    }
}