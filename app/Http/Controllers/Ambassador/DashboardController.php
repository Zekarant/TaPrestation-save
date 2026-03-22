<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();
        $ambassador->load(['assignments.prestataire', 'commissions']);

        $stats = [
            'total_prestataires' => $ambassador->assignments()->count(),
            'total_earned' => $ambassador->total_commission_earned,
            'total_paid' => $ambassador->total_commission_paid,
            'unpaid' => $ambassador->unpaid_commission,
            'pending_commissions' => $ambassador->commissions()->pending()->sum('commission_amount'),
            'referral_visits' => $ambassador->referralVisits()->count(),
            'referral_visits_month' => $ambassador->referralVisits()->where('visited_at', '>=', now()->startOfMonth())->count(),
            'conversions_month' => $ambassador->referralVisits()->where('converted', true)->where('visited_at', '>=', now()->startOfMonth())->count(),
        ];

        $recentCommissions = $ambassador->commissions()
            ->with('prestataire')
            ->latest()
            ->take(5)
            ->get();

        $recentActivity = $ambassador->activityLogs()
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('ambassador.dashboard', compact('ambassador', 'stats', 'recentCommissions', 'recentActivity'));
    }
}
