<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\UrgentSale;
use App\Models\Equipment;
use App\Models\Review;

class AdminAnalyticsController extends Controller
{
    /**
     * 11. Dashboard analytique avancé
     */
    public function dashboard()
    {
        $period = request('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $stats = [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
            'period_revenue' => Booking::where('status', 'completed')->where('created_at', '>=', $startDate)->sum('total_price'),
            'total_bookings' => Booking::count(),
            'period_bookings' => Booking::where('created_at', '>=', $startDate)->count(),
            'conversion_rate' => $this->calculateConversionRate($startDate),
        ];

        $charts = [
            'revenue_chart' => $this->getRevenueChart($period),
            'users_chart' => $this->getUsersChart($period),
            'bookings_chart' => $this->getBookingsChart($period),
        ];

        return view('admin.analytics.dashboard', compact('stats', 'charts', 'period'));
    }

    /**
     * 12. Analyse des revenus détaillée
     */
    public function revenue(Request $request)
    {
        $totalRevenue = 0;
        $monthlyRevenue = 0;
        $averageOrder = 0;
        $totalTransactions = 0;
        $monthlyData = [];
        $subscriptionRevenue = 0;
        $commissionRevenue = 0;

        try {
            $totalRevenue = Booking::where('status', 'completed')->sum('total_price') ?? 0;
            $monthlyRevenue = Booking::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total_price') ?? 0;
            $totalTransactions = Booking::where('status', 'completed')->count();
            $averageOrder = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

            // Get last 6 months data
            $maxAmount = 1;
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $amount = Booking::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price') ?? 0;
                if ($amount > $maxAmount) $maxAmount = $amount;
                $monthlyData[] = [
                    'label' => $date->translatedFormat('M Y'),
                    'amount' => $amount,
                    'percentage' => 0,
                ];
            }
            foreach ($monthlyData as &$month) {
                $month['percentage'] = $maxAmount > 0 ? ($month['amount'] / $maxAmount) * 100 : 0;
            }
        } catch (\Exception $e) {
            // Handle errors gracefully
        }

        return view('admin.analytics.revenue', compact(
            'totalRevenue', 'monthlyRevenue', 'averageOrder', 'totalTransactions',
            'monthlyData', 'subscriptionRevenue', 'commissionRevenue'
        ));
    }

    /**
     * 13. Analyse des utilisateurs
     */
    public function users(Request $request)
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))->count();
        $retentionRate = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
        
        $clientCount = 0;
        $prestaCount = 0;
        $adminCount = User::where('role', 'admin')->count();
        
        try {
            $clientCount = Client::count();
            $prestaCount = Prestataire::count();
        } catch (\Exception $e) {
            // Models might not exist
        }

        $registrationsByMonth = [];
        $maxCount = 1;
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            if ($count > $maxCount) $maxCount = $count;
            $registrationsByMonth[] = [
                'label' => $date->translatedFormat('M Y'),
                'count' => $count,
                'percentage' => 0,
            ];
        }
        foreach ($registrationsByMonth as &$month) {
            $month['percentage'] = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
        }

        $recentUsers = User::orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.analytics.users', compact(
            'totalUsers', 'newUsersThisMonth', 'activeUsers', 'retentionRate',
            'clientCount', 'prestaCount', 'adminCount', 'registrationsByMonth', 'recentUsers'
        ));
    }

    /**
     * 14. Analyse des services
     */
    public function services(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = Carbon::now()->subDays($period);

        $topServices = Service::withCount(['bookings' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
            ->withSum(['bookings' => function ($query) use ($startDate) {
                $query->where('status', 'completed')->where('created_at', '>=', $startDate);
            }], 'total_price')
            ->orderByDesc('bookings_count')
            ->limit(20)
            ->get();

        $servicesByCategory = DB::table('services')
            ->join('categories', 'services.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COUNT(*) as count')
            ->groupBy('categories.name')
            ->get();

        $averagePrice = Service::avg('price');
        $priceDistribution = Service::selectRaw('
            CASE 
                WHEN price < 50 THEN "0-50€"
                WHEN price < 100 THEN "50-100€"
                WHEN price < 200 THEN "100-200€"
                WHEN price < 500 THEN "200-500€"
                ELSE "500€+"
            END as price_range,
            COUNT(*) as count
        ')->groupBy('price_range')->get();

        return view('admin.analytics.services', compact('topServices', 'servicesByCategory', 'averagePrice', 'priceDistribution'));
    }

    /**
     * 15. Analyse géographique
     */
    public function geographic()
    {
        $topCities = [];
        $topDepartments = [];
        $totalCities = 0;
        $totalDepartments = 0;
        $totalRegions = 0;
        $averagePerCity = 0;

        try {
            $usersByCity = DB::table('users')
                ->leftJoin('prestataires', 'users.id', '=', 'prestataires.user_id')
                ->leftJoin('clients', 'users.id', '=', 'clients.user_id')
                ->selectRaw('COALESCE(prestataires.city, clients.city, "Non renseigné") as city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $maxCount = $usersByCity->max('count') ?: 1;
            foreach ($usersByCity as $index => $city) {
                $topCities[] = [
                    'name' => $city->city,
                    'count' => $city->count,
                    'percentage' => ($city->count / $maxCount) * 100,
                ];
            }

            $totalCities = DB::table('prestataires')->distinct('city')->count('city');
            $totalUsers = User::count();
            $averagePerCity = $totalCities > 0 ? round($totalUsers / $totalCities, 1) : 0;
        } catch (\Exception $e) {
            // Tables might not exist with expected columns
        }

        return view('admin.analytics.geographic', compact('topCities', 'topDepartments', 'totalCities', 'totalDepartments', 'totalRegions', 'averagePerCity'));
    }

    /**
     * 16. Analyse des avis et notes
     */
    public function reviews()
    {
        $averageRating = Review::avg('rating');
        
        $ratingDistribution = Review::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        $reviewsTrend = Review::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(rating) as avg_rating, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topRatedPrestataires = Prestataire::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->having('reviews_count', '>=', 5)
            ->orderByDesc('reviews_avg_rating')
            ->limit(10)
            ->get();

        $lowRatedPrestataires = Prestataire::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->having('reviews_count', '>=', 3)
            ->orderBy('reviews_avg_rating')
            ->limit(10)
            ->get();

        return view('admin.analytics.reviews', compact('averageRating', 'ratingDistribution', 'reviewsTrend', 'topRatedPrestataires', 'lowRatedPrestataires'));
    }

    /**
     * 17. Rapport de performance
     */
    public function performance()
    {
        $pageViews = 0;
        $uniqueVisitors = 0;
        $averageSessionTime = '0:00';
        $bounceRate = 0;
        $topPages = [];
        $trafficSources = [];
        $phpVersion = phpversion();
        $laravelVersion = app()->version();
        $diskSpace = 'N/A';
        $uptime = 'N/A';

        try {
            // Try to get disk space info
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            if ($freeSpace && $totalSpace) {
                $usedPercentage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1);
                $diskSpace = $usedPercentage . '% utilisé';
            }
        } catch (\Exception $e) {
            // Unable to get disk info on some hosts
        }

        return view('admin.analytics.performance', compact(
            'pageViews', 'uniqueVisitors', 'averageSessionTime', 'bounceRate',
            'topPages', 'trafficSources', 'phpVersion', 'laravelVersion', 'diskSpace', 'uptime'
        ));
    }

    /**
     * 18. Export des rapports
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $format = $request->get('format', 'csv');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now());

        $data = $this->getExportData($type, $startDate, $endDate);

        if ($format === 'csv') {
            return $this->exportToCsv($data, $type);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($data, $type);
        }

        return response()->json($data);
    }

    // Méthodes privées d'aide
    private function calculateConversionRate($startDate)
    {
        try {
            $totalVisits = DB::table('page_views')->where('created_at', '>=', $startDate)->count() ?: 1;
        } catch (\Exception $e) {
            $totalVisits = 1;
        }
        $bookings = Booking::where('created_at', '>=', $startDate)->count();
        return round(($bookings / $totalVisits) * 100, 2);
    }

    private function getRevenueChart($days)
    {
        return Booking::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getUsersChart($days)
    {
        return User::where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getBookingsChart($days)
    {
        return Booking::where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function calculateRetention($days)
    {
        // Calcul simplifié de rétention
        $startDate = Carbon::now()->subDays($days * 2);
        $midDate = Carbon::now()->subDays($days);
        
        $oldUsers = User::whereBetween('created_at', [$startDate, $midDate])->pluck('id');
        $retainedUsers = User::whereIn('id', $oldUsers)->where('last_login_at', '>=', $midDate)->count();
        
        return $oldUsers->count() > 0 ? round(($retainedUsers / $oldUsers->count()) * 100, 2) : 0;
    }

    private function getCompletionRate()
    {
        $total = Booking::count() ?: 1;
        $completed = Booking::where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }

    private function getAverageResponseTime()
    {
        // Temps moyen de réponse en heures
        return DB::table('bookings')
            ->whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
    }

    private function getCancellationRate()
    {
        $total = Booking::count() ?: 1;
        $cancelled = Booking::where('status', 'cancelled')->count();
        return round(($cancelled / $total) * 100, 2);
    }

    private function getExportData($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'revenue':
                return Booking::where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with(['client', 'prestataire', 'service'])
                    ->get();
            case 'users':
                return User::whereBetween('created_at', [$startDate, $endDate])->get();
            case 'bookings':
                return Booking::whereBetween('created_at', [$startDate, $endDate])
                    ->with(['client', 'prestataire', 'service'])
                    ->get();
            default:
                return collect();
        }
    }

    private function exportToCsv($data, $type)
    {
        $filename = $type . '_export_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()->toArray()));
                foreach ($data as $row) {
                    fputcsv($file, $row->toArray());
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToPdf($data, $type)
    {
        // Retourne JSON si PDF non configuré
        return response()->json(['message' => 'PDF export - data', 'count' => $data->count()]);
    }
}
