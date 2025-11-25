<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'administrateur.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // --- Statistiques des cartes ---
        $totalUsersCount = User::count();
        $approvedPrestatairesCount = Prestataire::where('is_approved', true)->count();
        $pendingPrestatairesCount = Prestataire::where('is_approved', false)->count();
        $activeServicesCount = \App\Models\Service::where('status', 'active')->count();

        // Calcul des variations (exemple pour le dernier mois)
        $totalUsersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $totalUsersPreviousMonth = User::whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();
        $userChange = $totalUsersPreviousMonth > 0 ? (($totalUsersLastMonth - $totalUsersPreviousMonth) / $totalUsersPreviousMonth) * 100 : 0;

        $approvedPrestatairesLastMonth = Prestataire::where('is_approved', true)->where('created_at', '>=', now()->subMonth())->count();
        $approvedPrestatairesPreviousMonth = Prestataire::where('is_approved', true)->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();
        $prestataireChange = $approvedPrestatairesPreviousMonth > 0 ? (($approvedPrestatairesLastMonth - $approvedPrestatairesPreviousMonth) / $approvedPrestatairesPreviousMonth) * 100 : 0;
        
        $pendingPrestatairesLastMonth = Prestataire::where('is_approved', false)->where('created_at', '>=', now()->subMonth())->count();
        $pendingPrestatairesPreviousMonth = Prestataire::where('is_approved', false)->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();
        $pendingChange = $pendingPrestatairesPreviousMonth > 0 ? (($pendingPrestatairesLastMonth - $pendingPrestatairesPreviousMonth) / $pendingPrestatairesPreviousMonth) * 100 : 0;

        $activeServicesLastMonth = \App\Models\Service::where('status', 'active')->where('created_at', '>=', now()->subMonth())->count();
        $activeServicesPreviousMonth = \App\Models\Service::where('status', 'active')->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();
        $serviceChange = $activeServicesPreviousMonth > 0 ? (($activeServicesLastMonth - $activeServicesPreviousMonth) / $activeServicesPreviousMonth) * 100 : 0;


        // --- Statistiques Détaillées ---
        $totalServices = \App\Models\Service::count();
        $totalMessages = \App\Models\Message::count();
        $satisfactionRate = \App\Models\Review::avg('rating'); // Supposant une colonne 'rating'
        $satisfactionRate = $satisfactionRate ? round($satisfactionRate / 5 * 100) : 0; // Converti en pourcentage

        // --- Données pour les tableaux ---
        $pendingPrestataires = Prestataire::with('user')->where('is_approved', false)->latest()->paginate(5);
        $recentUsers = User::latest()->take(5)->get();

        // --- Données initiales pour le graphique (par défaut : 1 an) ---
        $chartData = $this->getChartDataForPeriod('1an');

        return view('admin.dashboard-modern', compact(
            'totalUsersCount',
            'approvedPrestatairesCount',
            'pendingPrestatairesCount',
            'activeServicesCount',
            'userChange',
            'prestataireChange',
            'pendingChange',
            'serviceChange',
            'totalServices',
            'totalMessages',
            'satisfactionRate',
            'pendingPrestataires',
            'recentUsers',
            'chartData'
        ));
    }

    public function getChartData(Request $request)
    {
        $period = $request->input('period', '1an'); // 7j, 30j, 1an
        $data = $this->getChartDataForPeriod($period);
        return response()->json($data);
    }

    private function getChartDataForPeriod($period)
    {
        $endDate = now();
        $labels = [];
        $usersData = [];
        $prestatairesData = [];

        switch ($period) {
            case '7j':
                $startDate = now()->subDays(6);
                $format = 'D'; // Jour de la semaine
                $step = '1 day';
                break;
            case '30j':
                $startDate = now()->subDays(29);
                $format = 'd M'; // Jour et mois
                $step = '1 day';
                break;
            case '1an':
            default:
                $startDate = now()->subMonths(11);
                $format = 'M'; // Mois
                $step = '1 month';
                break;
        }

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format($format);
            $range = $this->getDateRangeForStep($currentDate, $step);

            $usersData[] = User::whereBetween('created_at', $range)->count();
            $prestatairesData[] = Prestataire::whereBetween('created_at', $range)->count();

            if ($step === '1 day') {
                $currentDate->addDay();
            } else {
                $currentDate->addMonthNoOverflow();
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Utilisateurs',
                    'data' => $usersData,
                    'borderColor' => '#4A90E2',
                    'backgroundColor' => 'rgba(74, 144, 226, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Prestataires',
                    'data' => $prestatairesData,
                    'borderColor' => '#50E3C2',
                    'backgroundColor' => 'rgba(80, 227, 194, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    private function getDateRangeForStep($date, $step)
    {
        if ($step === '1 day') {
            return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        }
        return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
    }
    
    /**
     * Affiche la liste des utilisateurs.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        $users = User::with(['client', 'prestataire'])->paginate(15);
        
        return view('admin.users.index-modern', [
            'users' => $users,
        ]);
    }
    
    /**
     * Affiche la liste des prestataires.
     *
     * @return \Illuminate\View\View
     */
    public function prestataires()
    {
        $prestataires = Prestataire::with('user')->paginate(15);
        
        return view('admin.prestataires.index-modern', [
            'prestataires' => $prestataires,
        ]);
    }
    
    /**
     * Affiche les prestataires en attente d'approbation.
     *
     * @return \Illuminate\View\View
     */
    public function pendingPrestataires()
    {
        $pendingPrestataires = Prestataire::with('user')
            ->where('is_approved', false)
            ->paginate(15);
        
        return view('admin.prestataires.pending', [
            'pendingPrestataires' => $pendingPrestataires,
        ]);
    }
    
    /**
     * Approuve un prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approvePrestataire($id)
    {
        $prestataire = Prestataire::findOrFail($id);
        $prestataire->is_approved = true;
        $prestataire->save();
        
        // Ici, on pourrait envoyer un email de notification au prestataire
        
        return redirect()->route('administrateur.prestataires.pending')
            ->with('success', 'Le prestataire a été approuvé avec succès.');
    }
    
    /**
     * Rejette un prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectPrestataire($id)
    {
        $prestataire = Prestataire::findOrFail($id);
        $prestataire->is_approved = false;
        $prestataire->save();
        
        // Ici, on pourrait envoyer un email de notification au prestataire
        
        return redirect()->route('administrateur.prestataires.pending')
            ->with('success', 'Le prestataire a été rejeté.');
    }
    
    /**
     * Supprime un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::id()) {
            return redirect()->route('administrateur.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();
        
        return redirect()->route('administrateur.users.index')
                ->with('success', 'L\'utilisateur a été supprimé avec succès.');
    }
    
    /**
     * Affiche la liste des clients.
     *
     * @return \Illuminate\View\View
     */
    public function clients()
    {
        $clients = Client::with('user')->paginate(15);
        
        return view('admin.clients.index-modern', [
            'clients' => $clients,
        ]);
    }
}