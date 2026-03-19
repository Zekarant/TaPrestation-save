<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Service;
use App\Models\Booking;

use App\Models\Review;
use App\Models\Message;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Affiche le tableau de bord des rapports.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Statistiques générales
        $generalStats = [
            'total_users' => User::count(),
            'total_prestataires' => Prestataire::count(),
            'total_clients' => Client::count(),
            'total_services' => Service::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
        ];
        
        // Évolution mensuelle
        $monthlyEvolution = $this->getMonthlyEvolution();
        
        // Top catégories
        $topCategories = $this->getTopCategories();
        
        // Statistiques de conversion
        $conversionStats = $this->getConversionStats();
        
        return view('admin.reports.index', [
            'generalStats' => $generalStats,
            'monthlyEvolution' => $monthlyEvolution,
            'topCategories' => $topCategories,
            'conversionStats' => $conversionStats,
        ]);
    }
    
    /**
     * Affiche le tableau de bord principal des rapports.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Statistiques générales
        $generalStats = [
            'total_users' => User::count(),
            'total_prestataires' => Prestataire::count(),
            'total_clients' => Client::count(),
            'total_services' => Service::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
        ];
        
        // Évolution mensuelle
        $monthlyEvolution = $this->getMonthlyEvolution();
        
        // Top catégories
        $topCategories = $this->getTopCategories();
        
        // Statistiques de conversion
        $conversionStats = $this->getConversionStats();
        
        return view('admin.reports.dashboard', [
            'generalStats' => $generalStats,
            'monthlyEvolution' => $monthlyEvolution,
            'topCategories' => $topCategories,
            'conversionStats' => $conversionStats,
        ]);
    }
    
    /**
     * Affiche le tableau de bord moderne des rapports.
     *
     * @return \Illuminate\View\View
     */
    public function dashboardModern()
    {
        // Statistiques globales pour la vue moderne
        $globalStats = [
            'total_users' => User::count(),
            'total_services' => Service::where('status', 'active')->count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
            'users_growth' => $this->calculateGrowthPercentage('users'),
            'services_growth' => $this->calculateGrowthPercentage('services'),
            'bookings_growth' => $this->calculateGrowthPercentage('bookings'),
            'revenue_growth' => $this->calculateGrowthPercentage('revenue'),
        ];
        
        // Statistiques utilisateurs détaillées
        $userStats = [
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'clients_percentage' => $this->calculateUserTypePercentage('client'),
            'prestataires_percentage' => $this->calculateUserTypePercentage('prestataire'),
            'admins_percentage' => $this->calculateUserTypePercentage('admin'),
        ];
        
        // Statistiques services
        $serviceStats = [
            'total_services' => Service::count(),
            'avg_price' => Service::avg('price') ?? 0,
            'most_popular_service' => $this->getMostPopularService(),
            'most_popular_count' => $this->getMostPopularServiceCount(),
        ];
        
        // Statistiques réservations
        $bookingStats = [
            'pending_percentage' => $this->calculateBookingStatusPercentage('pending'),
            'completed_percentage' => $this->calculateBookingStatusPercentage('completed'),
            'top_prestataire' => $this->getTopPrestataire(),
            'top_prestataire_bookings' => $this->getTopPrestataireBookings(),
        ];
        
        return view('admin.reports.dashboard-modern', [
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'serviceStats' => $serviceStats,
            'bookingStats' => $bookingStats,
        ]);
    }
    
    /**
     * Rapport des utilisateurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function users(Request $request)
    {
        $period = $request->get('period', '30'); // Derniers 30 jours par défaut
        $startDate = now()->subDays($period);
        
        // Nouvelles inscriptions
        $newUsers = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Répartition par rôle
        $usersByRole = [
            'clients' => Client::whereHas('user', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->count(),
            'prestataires' => Prestataire::whereHas('user', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->count(),
        ];
        
        // Utilisateurs actifs
        $activeUsers = User::where('last_login_at', '>=', $startDate)->count();
        
        // Top villes
        $topCities = User::select('city', DB::raw('count(*) as count'))
            ->whereNotNull('city')
            ->where('created_at', '>=', $startDate)
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.reports.users', [
            'newUsers' => $newUsers,
            'usersByRole' => $usersByRole,
            'activeUsers' => $activeUsers,
            'topCities' => $topCities,
            'period' => $period,
        ]);
    }
    
    /**
     * Rapport des services.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function services(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        // Services par catégorie
        $servicesByCategory = Service::join('categories', 'services.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->where('services.created_at', '>=', $startDate)
            ->groupBy('categories.name')
            ->orderBy('count', 'desc')
            ->get();
        
        // Services les plus populaires
        $popularServices = Service::withCount(['bookings' => function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }])
            ->orderBy('bookings_count', 'desc')
            ->take(10)
            ->get();
        
        // Prix moyens par catégorie
        $avgPricesByCategory = Service::join('categories', 'services.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('AVG(services.price) as avg_price'))
            ->where('services.created_at', '>=', $startDate)
            ->groupBy('categories.name')
            ->get();
        
        // Évolution des créations de services
        $serviceCreation = Service::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.reports.services', [
            'servicesByCategory' => $servicesByCategory,
            'popularServices' => $popularServices,
            'avgPricesByCategory' => $avgPricesByCategory,
            'serviceCreation' => $serviceCreation,
            'period' => $period,
        ]);
    }
    
    /**
     * Rapport des réservations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function bookings(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        // Réservations par statut
        $bookingsByStatus = Booking::where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // Évolution des réservations
        $bookingEvolution = Booking::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Revenus par mois
        $monthlyRevenue = Booking::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_price) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Top prestataires par revenus
        $topPrestataires = Prestataire::join('services', 'prestataires.id', '=', 'services.prestataire_id')
            ->join('bookings', 'services.id', '=', 'bookings.service_id')
            ->join('users', 'prestataires.user_id', '=', 'users.id')
            ->where('bookings.status', 'completed')
            ->where('bookings.created_at', '>=', $startDate)
            ->select('users.name', DB::raw('SUM(bookings.total_price) as total_revenue'))
            ->groupBy('prestataires.id', 'users.name')
            ->orderBy('total_revenue', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.reports.bookings', [
            'bookingsByStatus' => $bookingsByStatus,
            'bookingEvolution' => $bookingEvolution,
            'monthlyRevenue' => $monthlyRevenue,
            'topPrestataires' => $topPrestataires,
            'period' => $period,
        ]);
    }
    
    /**
     * Rapport financier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function financial(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        // Revenus totaux
        $totalRevenue = Booking::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('total_price');
        
        // Commission de la plateforme (supposons 10%)
        $platformCommission = $totalRevenue * 0.10;
        
        // Revenus par catégorie
        $revenueByCategory = Booking::join('services', 'bookings.service_id', '=', 'services.id')
            ->join('categories', 'services.category_id', '=', 'categories.id')
            ->where('bookings.status', 'completed')
            ->where('bookings.created_at', '>=', $startDate)
            ->select('categories.name', DB::raw('SUM(bookings.total_price) as revenue'))
            ->groupBy('categories.name')
            ->orderBy('revenue', 'desc')
            ->get();
        
        // Évolution quotidienne des revenus
        $dailyRevenue = Booking::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Panier moyen
        $averageOrderValue = Booking::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->avg('total_price');
        
        return view('admin.reports.financial', [
            'totalRevenue' => $totalRevenue,
            'platformCommission' => $platformCommission,
            'revenueByCategory' => $revenueByCategory,
            'dailyRevenue' => $dailyRevenue,
            'averageOrderValue' => $averageOrderValue,
            'period' => $period,
        ]);
    }
    
    /**
     * Export d'un rapport.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request, $type)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        $filename = "rapport_{$type}_" . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($type, $startDate) {
            $file = fopen('php://output', 'w');
            
            switch ($type) {
                case 'users':
                    $this->exportUsersReport($file, $startDate);
                    break;
                case 'services':
                    $this->exportServicesReport($file, $startDate);
                    break;
                case 'bookings':
                    $this->exportBookingsReport($file, $startDate);
                    break;
                case 'financial':
                    $this->exportFinancialReport($file, $startDate);
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtient l'évolution mensuelle.
     *
     * @return array
     */
    private function getMonthlyEvolution()
    {
        $evolution = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $evolution[] = [
                'month' => $date->format('M Y'),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'bookings' => Booking::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'revenue' => Booking::where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_price'),
            ];
        }
        
        return $evolution;
    }
    
    /**
     * Obtient les top catégories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTopCategories()
    {
        return Category::withCount('services')
            ->orderBy('services_count', 'desc')
            ->take(5)
            ->get();
    }
    
    /**
     * Obtient les statistiques de conversion.
     *
     * @return array
     */
    private function getConversionStats()
    {
        $totalServices = Service::count();
        $completedBookings = Booking::where('status', 'completed')->count();
        
        return [
            'booking_completion' => Booking::count() > 0 ? round(($completedBookings / Booking::count()) * 100, 1) : 0,
        ];
    }
    
    /**
     * Calcule le pourcentage de croissance pour une métrique donnée.
     *
     * @param string $metric
     * @return float
     */
    private function calculateGrowthPercentage($metric)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $previousMonth = now()->subMonth()->month;
        $previousYear = now()->subMonth()->year;
        
        switch ($metric) {
            case 'users':
                $current = User::whereMonth('created_at', $currentMonth)
                              ->whereYear('created_at', $currentYear)
                              ->count();
                $previous = User::whereMonth('created_at', $previousMonth)
                               ->whereYear('created_at', $previousYear)
                               ->count();
                break;
            case 'services':
                $current = Service::whereMonth('created_at', $currentMonth)
                                 ->whereYear('created_at', $currentYear)
                                 ->count();
                $previous = Service::whereMonth('created_at', $previousMonth)
                                  ->whereYear('created_at', $previousYear)
                                  ->count();
                break;
            case 'bookings':
                $current = Booking::whereMonth('created_at', $currentMonth)
                                 ->whereYear('created_at', $currentYear)
                                 ->count();
                $previous = Booking::whereMonth('created_at', $previousMonth)
                                  ->whereYear('created_at', $previousYear)
                                  ->count();
                break;
            case 'revenue':
                $current = Booking::where('status', 'completed')
                                 ->whereMonth('created_at', $currentMonth)
                                 ->whereYear('created_at', $currentYear)
                                 ->sum('total_price');
                $previous = Booking::where('status', 'completed')
                                  ->whereMonth('created_at', $previousMonth)
                                  ->whereYear('created_at', $previousYear)
                                  ->sum('total_price');
                break;
            default:
                return 0;
        }
        
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    /**
     * Calcule le pourcentage d'un type d'utilisateur.
     *
     * @param string $type
     * @return float
     */
    private function calculateUserTypePercentage($type)
    {
        $totalUsers = User::count();
        
        if ($totalUsers == 0) {
            return 0;
        }
        
        switch ($type) {
            case 'client':
                $count = Client::count();
                break;
            case 'prestataire':
                $count = Prestataire::count();
                break;
            case 'admin':
                $count = User::where('role', 'admin')->count();
                break;
            default:
                return 0;
        }
        
        return round(($count / $totalUsers) * 100, 1);
    }
    
    /**
     * Obtient le service le plus populaire.
     *
     * @return string|null
     */
    private function getMostPopularService()
    {
        $service = Service::withCount('bookings')
                         ->orderBy('bookings_count', 'desc')
                         ->first();
        
        return $service ? $service->title : null;
    }
    
    /**
     * Obtient le nombre de réservations du service le plus populaire.
     *
     * @return int
     */
    private function getMostPopularServiceCount()
    {
        $service = Service::withCount('bookings')
                         ->orderBy('bookings_count', 'desc')
                         ->first();
        
        return $service ? $service->bookings_count : 0;
    }
    
    /**
     * Calcule le pourcentage d'un statut de réservation.
     *
     * @param string $status
     * @return float
     */
    private function calculateBookingStatusPercentage($status)
    {
        $totalBookings = Booking::count();
        
        if ($totalBookings == 0) {
            return 0;
        }
        
        $statusCount = Booking::where('status', $status)->count();
        
        return round(($statusCount / $totalBookings) * 100, 1);
    }
    
    /**
     * Obtient le prestataire avec le plus de réservations.
     *
     * @return string|null
     */
    private function getTopPrestataire()
    {
        $prestataire = Prestataire::withCount('bookings')
                                 ->orderBy('bookings_count', 'desc')
                                 ->with('user')
                                 ->first();
        
        return $prestataire ? $prestataire->user->name : null;
    }
    
    /**
     * Obtient le nombre de réservations du top prestataire.
     *
     * @return int
     */
    private function getTopPrestataireBookings()
    {
        $prestataire = Prestataire::withCount('bookings')
                                 ->orderBy('bookings_count', 'desc')
                                 ->first();
        
        return $prestataire ? $prestataire->bookings_count : 0;
    }
    
    /**
     * Exporte le rapport des utilisateurs.
     *
     * @param  resource  $file
     * @param  \Carbon\Carbon  $startDate
     */
    private function exportUsersReport($file, $startDate)
    {
        fputcsv($file, ['Date', 'Nouveaux Utilisateurs', 'Nouveaux Clients', 'Nouveaux Prestataires']);
        
        $data = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        foreach ($data as $row) {
            $clients = Client::whereHas('user', function($q) use ($row) {
                $q->whereDate('created_at', $row->date);
            })->count();
            
            $prestataires = Prestataire::whereHas('user', function($q) use ($row) {
                $q->whereDate('created_at', $row->date);
            })->count();
            
            fputcsv($file, [$row->date, $row->total, $clients, $prestataires]);
        }
    }
    
    /**
     * Exporte le rapport des services.
     *
     * @param  resource  $file
     * @param  \Carbon\Carbon  $startDate
     */
    private function exportServicesReport($file, $startDate)
    {
        fputcsv($file, ['Catégorie', 'Nombre de Services', 'Prix Moyen']);
        
        $data = Service::join('categories', 'services.category_id', '=', 'categories.id')
            ->where('services.created_at', '>=', $startDate)
            ->select('categories.name', DB::raw('COUNT(*) as count'), DB::raw('AVG(services.price) as avg_price'))
            ->groupBy('categories.name')
            ->get();
        
        foreach ($data as $row) {
            fputcsv($file, [$row->name, $row->count, round($row->avg_price, 2)]);
        }
    }
    
    /**
     * Exporte le rapport des réservations.
     *
     * @param  resource  $file
     * @param  \Carbon\Carbon  $startDate
     */
    private function exportBookingsReport($file, $startDate)
    {
        fputcsv($file, ['Date', 'Nombre de Réservations', 'Revenus']);
        
        $data = Booking::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        foreach ($data as $row) {
            fputcsv($file, [$row->date, $row->count, round($row->revenue, 2)]);
        }
    }
    
    /**
     * Exporte le rapport financier.
     *
     * @param  resource  $file
     * @param  \Carbon\Carbon  $startDate
     */
    private function exportFinancialReport($file, $startDate)
    {
        fputcsv($file, ['Catégorie', 'Revenus', 'Commission Plateforme (10%)']);
        
        $data = Booking::join('services', 'bookings.service_id', '=', 'services.id')
            ->join('categories', 'services.category_id', '=', 'categories.id')
            ->where('bookings.status', 'completed')
            ->where('bookings.created_at', '>=', $startDate)
            ->select('categories.name', DB::raw('SUM(bookings.total_price) as revenue'))
            ->groupBy('categories.name')
            ->get();
        
        foreach ($data as $row) {
            $commission = $row->revenue * 0.10;
            fputcsv($file, [$row->name, round($row->revenue, 2), round($commission, 2)]);
        }
    }
}