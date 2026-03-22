<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        $totalVisits = $ambassador->referralVisits()->count();
        $conversions = $ambassador->referralVisits()->where('converted', true)->count();
        $conversionRate = $totalVisits > 0 ? round(($conversions / $totalVisits) * 100, 1) : 0;

        $recentVisits = $ambassador->referralVisits()
            ->with('convertedPrestataire')
            ->latest('visited_at')
            ->take(50)
            ->get();

        return view('ambassador.referral.index', compact('ambassador', 'totalVisits', 'conversions', 'conversionRate', 'recentVisits'));
    }
}
