<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Add this import
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PrestatairesExport;
use Illuminate\Support\Facades\Auth;

class PrestataireController extends Controller
{
    /**
     * Affiche la liste des prestataires.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Prestataire::with(['user', 'approvedBy']);
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        // Filtrage par email
        if ($request->has('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        
        // Filtrage par secteur d'activité
        if ($request->has('sector')) {
            $query->where('sector', 'like', '%' . $request->sector . '%');
        }
        
        // Filtrage par statut d'approbation
        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }
        
        $prestataires = $query->paginate(15);
        
        $pendingPrestataires = Prestataire::where('is_approved', false)
            ->with('user')
            ->get();
            
        $approvedPrestataires = Prestataire::where('is_approved', true)
            ->with(['user', 'approvedBy'])
            ->get();
            
        // Récupérer toutes les catégories pour le filtre
        $categories = \App\Models\Category::orderBy('name')->get();
        
        // Calculer les statistiques réelles depuis la base de données
        $stats = [
            'total' => Prestataire::count(),
            'approved' => Prestataire::where('is_approved', true)->count(),
            'pending' => Prestataire::where('is_approved', false)->count(),
            'new_this_month' => Prestataire::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count()
        ];
            
        return view('admin.prestataires.index-modern', compact('pendingPrestataires', 'approvedPrestataires', 'prestataires', 'categories', 'stats'));
    }

    /**
     * Affiche les détails d'un prestataire.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\View\View
     */
    public function show(Prestataire $prestataire)
    {
        $prestataire->load('user', 'services', 'reviews');
        
        // Statistiques financières
        $stats = [];
        
        // Revenus depuis les bookings
        try {
            $stats['total_revenue'] = \App\Models\Booking::where('prestataire_id', $prestataire->id)
                ->where('status', 'completed')
                ->sum('total_price') ?? 0;
            
            $stats['revenue_this_month'] = \App\Models\Booking::where('prestataire_id', $prestataire->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_price') ?? 0;
            
            $stats['total_bookings'] = \App\Models\Booking::where('prestataire_id', $prestataire->id)->count();
            $stats['completed_bookings'] = \App\Models\Booking::where('prestataire_id', $prestataire->id)
                ->where('status', 'completed')->count();
            $stats['pending_bookings'] = \App\Models\Booking::where('prestataire_id', $prestataire->id)
                ->whereIn('status', ['pending', 'confirmed'])->count();
        } catch (\Exception $e) {
            $stats['total_revenue'] = 0;
            $stats['revenue_this_month'] = 0;
            $stats['total_bookings'] = 0;
            $stats['completed_bookings'] = 0;
            $stats['pending_bookings'] = 0;
        }
        
        // Équipements
        try {
            $equipments = \App\Models\Equipment::where('prestataire_id', $prestataire->id)->get();
        } catch (\Exception $e) {
            $equipments = collect();
        }
        
        // Ventes urgentes
        try {
            $urgentSales = \App\Models\UrgentSale::where('prestataire_id', $prestataire->id)->get();
        } catch (\Exception $e) {
            $urgentSales = collect();
        }
        
        // Réservations récentes
        try {
            $recentBookings = \App\Models\Booking::where('prestataire_id', $prestataire->id)
                ->with('client')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $recentBookings = collect();
        }
        
        // Abonnement actuel
        try {
            $subscription = \App\Models\UserSubscription::where('user_id', $prestataire->user_id)
                ->where('status', 'active')
                ->first();
        } catch (\Exception $e) {
            $subscription = null;
        }
        
        // Balance du prestataire
        $balance = $prestataire->balance ?? 0;

        return view('admin.prestataires.show', compact(
            'prestataire', 
            'stats', 
            'equipments', 
            'urgentSales', 
            'recentBookings',
            'subscription',
            'balance'
        ));
    }



    /**
     * Affiche les prestataires en attente d'approbation.
     *
     * @return \Illuminate\View\View
     */
    public function pending(Request $request)
    {
        $query = Prestataire::with('user')
            ->where('is_approved', false);
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        // Filtrage par email
        if ($request->has('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        
        // Filtrage par secteur d'activité
        if ($request->has('sector')) {
            $query->where('sector', 'like', '%' . $request->sector . '%');
        }
        
        $pendingPrestataires = $query->paginate(15);
        
        return view('admin.prestataires.pending', [
            'prestataires' => $pendingPrestataires,
        ]);
    }

    /**
     * Approuve un prestataire.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Prestataire $prestataire)
    {
        $prestataire->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('administrateur.prestataires.index')
            ->with('success', 'Le prestataire a été supprimé avec succès.');
    }

    /**
     * Révoque l'approbation d'un prestataire.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revoke(Prestataire $prestataire)
    {
        $prestataire->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return redirect()->route('administrateur.prestataires.index')
            ->with('success', 'L\'approbation du prestataire a été révoquée avec succès.');
    }
    
    /**
     * Bloque ou débloque un prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function toggleBlock($id, Request $request)
    {
        $prestataire = Prestataire::findOrFail($id);
        $user = User::findOrFail($prestataire->user_id);
        
        $action = $request->input('action');
        
        if ($action === 'block') {
            $user->blocked_at = now();
        } elseif ($action === 'unblock') {
            $user->blocked_at = null;
        } else {
            $user->blocked_at = $user->blocked_at ? null : now();
        }
        
        $user->save();
        
        $status = $user->blocked_at ? 'bloqué' : 'débloqué';
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Le prestataire a été {$status} avec succès.",
                'status' => $user->blocked_at ? 'blocked' : 'active'
            ]);
        }
        
        return redirect()->route('administrateur.prestataires.index')
            ->with('success', "Le prestataire a été {$status} avec succès.");
    }

    /**
     * Supprime un prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        try {
            $prestataire = Prestataire::findOrFail($id);
            $user = User::findOrFail($prestataire->user_id);
            
            // Supprimer d'abord les relations qui pourraient causer des contraintes
            DB::beginTransaction();
            
            // Supprimer les vidéos du prestataire
            if ($prestataire->videos) {
                foreach ($prestataire->videos as $video) {
                    if ($video->video_path) {
                        Storage::disk('public')->delete($video->video_path);
                    }
                    $video->delete();
                }
            }
            
            // Supprimer les services
            if ($prestataire->services) {
                $prestataire->services()->delete();
            }
            
            // Supprimer les équipements
            if (method_exists($prestataire, 'equipments') && $prestataire->equipments) {
                $prestataire->equipments()->delete();
            }
            
            // Supprimer les ventes urgentes
            if (method_exists($prestataire, 'urgentSales') && $prestataire->urgentSales) {
                $prestataire->urgentSales()->delete();
            }
            
            // Supprimer le prestataire
            $prestataire->delete();
            
            // Archiver les infos Stripe avant suppression (éviter doublons si réinscription)
            \App\Models\DeletedStripeAccount::archiveFromUser($user);
            
            // Supprimer l'utilisateur
            $user->delete();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Le prestataire a été supprimé avec succès.'
                ]);
            }
            
            return redirect()->route('administrateur.prestataires.index')
                ->with('success', 'Le prestataire a été supprimé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du prestataire.'
                ], 500);
            }
            
            return redirect()->route('administrateur.prestataires.index')
                ->with('error', 'Erreur lors de la suppression du prestataire.');
        }
    }
    
    /**
     * Débloque plusieurs prestataires en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUnblock(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:prestataires,id'
        ]);
        
        $prestataires = Prestataire::whereIn('id', $request->ids)->get();
        $userIds = $prestataires->pluck('user_id');
        
        User::whereIn('id', $userIds)->update(['blocked_at' => null]);
        
        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' prestataire(s) débloqué(s) avec succès.'
        ]);
    }
    
    /**
     * Bloque plusieurs prestataires en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkBlock(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:prestataires,id'
        ]);
        
        $prestataires = Prestataire::whereIn('id', $request->ids)->get();
        $userIds = $prestataires->pluck('user_id');
        
        User::whereIn('id', $userIds)->update(['blocked_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' prestataire(s) bloqué(s) avec succès.'
        ]);
    }
    
    /**
     * Supprime plusieurs prestataires en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:prestataires,id'
        ]);
        
        $prestataires = Prestataire::whereIn('id', $request->ids)->get();
        $userIds = $prestataires->pluck('user_id');
        
        // Suppression des utilisateurs (cascade sur les prestataires)
        User::whereIn('id', $userIds)->delete();
        
        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' prestataire(s) supprimé(s) avec succès.'
        ]);
    }
    
    /**
     * Exporte les prestataires au format CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Prestataire::with(['user', 'category', 'services'])->withCount(['services']);
        
        // Appliquer les mêmes filtres que dans la méthode index
        if ($request->has('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        if ($request->has('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereHas('user', function($q) {
                    $q->where('is_blocked', false);
                });
            } elseif ($request->status === 'blocked') {
                $query->whereHas('user', function($q) {
                    $q->where('is_blocked', true);
                });
            }
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['name', 'email'])) {
            $query->join('users', 'prestataires.user_id', '=', 'users.id')
                  ->orderBy('users.' . $sortField, $sortDirection)
                  ->select('prestataires.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $prestataires = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="prestataires.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($prestataires) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Email', 'Téléphone', 'Catégorie', 'Services', 'Note', 'Statut', 'Date d\'inscription']);
            
            foreach ($prestataires as $prestataire) {
                fputcsv($file, [
                    $prestataire->id,
                    $prestataire->user->name,
                    $prestataire->user->email,
                    $prestataire->user->phone ?? 'Non renseigné',
                    $prestataire->category ? $prestataire->category->name : 'Non définie',
                    $prestataire->services_count,
                    number_format($prestataire->rating ?? 0, 1),
                    $prestataire->user->is_blocked ? 'Bloqué' : 'Actif',
                    $prestataire->created_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }










}
