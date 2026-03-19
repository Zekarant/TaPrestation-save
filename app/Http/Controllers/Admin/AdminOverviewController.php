<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AdminOverviewController extends Controller
{
    public function general()
    {
        $stats = [
            'users' => User::count(),
            'prestataires' => Prestataire::where('verified', true)->count(),
            'transactions' => Transaction::count(),
            'revenue' => Transaction::sum('amount'),
            'last_user' => User::latest()->first()?->name,
            'last_transaction' => Transaction::latest()->first()?->id,
            'last_verified' => Prestataire::where('verified', true)->latest()->first()?->name,
            'labels' => [],
            'revenue_trend' => [],
        ];
        // Example: fill labels and revenue_trend for chart
        $monthly = DB::table('transactions')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $stats['labels'] = $monthly->pluck('month')->toArray();
        $stats['revenue_trend'] = $monthly->pluck('total')->toArray();
        return view('admin.overview.general', compact('stats'));
    }
}
