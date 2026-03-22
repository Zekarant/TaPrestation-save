<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        $query = $ambassador->commissions()->with('prestataire')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('order_type', $request->type);
        }

        $commissions = $query->paginate(20);

        $stats = [
            'total_earned' => $ambassador->total_commission_earned,
            'total_paid' => $ambassador->total_commission_paid,
            'pending' => $ambassador->commissions()->pending()->sum('commission_amount'),
            'unpaid' => $ambassador->unpaid_commission,
        ];

        return view('ambassador.commissions.index', compact('ambassador', 'commissions', 'stats'));
    }

    public function payouts()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        $payouts = $ambassador->payoutBatches()
            ->latest()
            ->paginate(20);

        return view('ambassador.commissions.payouts', compact('ambassador', 'payouts'));
    }
}
