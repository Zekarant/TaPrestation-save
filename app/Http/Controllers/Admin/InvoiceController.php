<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class InvoiceController extends Controller
{
    /**
     * Dashboard des factures admin
     */
    public function index(Request $request)
    {
        // If the new invoices module schema isn't deployed yet, avoid 500s by
        // sending admins to the legacy finance invoices page.
        $requiredColumns = [
            'invoice_number',
            'type',
            'status',
            'issued_at',
            'subtotal',
            'total',
            'commission_amount',
            'commission_rate',
            'billing_name',
            'billing_email',
            // Invoice model uses SoftDeletes
            'deleted_at',
        ];

        if (!TableExistenceCache::has('invoices')) {
            return redirect()
                ->route('admin.finance.invoices')
                ->with('error', 'Module factures non disponible: table invoices introuvable.');
        }

        foreach ($requiredColumns as $col) {
            if (!Schema::hasColumn('invoices', $col)) {
                return redirect()
                    ->route('admin.finance.invoices')
                    ->with('error', 'Module factures non disponible: migrations non à jour (colonne manquante).');
            }
        }

        $query = Invoice::with(['user', 'prestataire', 'prestataire.user'])
            ->latest('issued_at');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('issued_at', today());
                    break;
                case 'week':
                    $query->where('issued_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('issued_at', '>=', now()->startOfMonth());
                    break;
                case 'quarter':
                    $query->where('issued_at', '>=', now()->startOfQuarter());
                    break;
                case 'year':
                    $query->where('issued_at', '>=', now()->startOfYear());
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('billing_name', 'like', "%{$search}%")
                  ->orWhere('seller_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prestataire.user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Tri (whitelist to avoid SQL errors / injection)
        $allowedSorts = ['issued_at', 'paid_at', 'total', 'created_at', 'invoice_number', 'status'];
        $sortBy = $request->get('sort', 'issued_at');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'issued_at';
        }

        $sortDir = strtolower((string) $request->get('dir', 'desc'));
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $query->orderBy($sortBy, $sortDir);

        $invoices = $query->paginate(25);

        // Statistiques globales
        $stats = $this->getStats($request);

        // Liste des prestataires pour le filtre
        $prestataires = Prestataire::with('user')
            ->whereHas('invoices')
            ->get()
            ->sortBy('user.name');

        return view('admin.invoices.index', compact('invoices', 'stats', 'prestataires'));
    }

    /**
     * Obtenir les statistiques
     */
    private function getStats(Request $request): array
    {
        $baseQuery = Invoice::query();
        
        // Appliquer les mêmes filtres de période si présent
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $baseQuery->whereDate('issued_at', today());
                    break;
                case 'week':
                    $baseQuery->where('issued_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $baseQuery->where('issued_at', '>=', now()->startOfMonth());
                    break;
                case 'quarter':
                    $baseQuery->where('issued_at', '>=', now()->startOfQuarter());
                    break;
                case 'year':
                    $baseQuery->where('issued_at', '>=', now()->startOfYear());
                    break;
            }
        }

        return [
            // Totaux généraux
            'total_invoices' => (clone $baseQuery)->where('type', 'client')->count(),
            'total_revenue' => (clone $baseQuery)->where('type', 'client')->paid()->sum('total'),
            'total_commission' => (clone $baseQuery)->where('type', 'client')->paid()->sum('commission_amount'),
            'total_net_prestataires' => (clone $baseQuery)->where('type', 'client')->paid()->sum('net_amount'),
            
            // Ce mois
            'month_revenue' => Invoice::where('type', 'client')
                ->where('issued_at', '>=', now()->startOfMonth())
                ->paid()->sum('total'),
            'month_commission' => Invoice::where('type', 'client')
                ->where('issued_at', '>=', now()->startOfMonth())
                ->paid()->sum('commission_amount'),
            
            // Aujourd'hui
            'today_revenue' => Invoice::where('type', 'client')
                ->whereDate('issued_at', today())
                ->paid()->sum('total'),
            'today_invoices' => Invoice::where('type', 'client')
                ->whereDate('issued_at', today())
                ->count(),
            
            // Par statut
            'paid_count' => (clone $baseQuery)->where('status', 'paid')->count(),
            'pending_count' => (clone $baseQuery)->whereIn('status', ['draft', 'issued'])->count(),
            'cancelled_count' => (clone $baseQuery)->whereIn('status', ['cancelled', 'refunded'])->count(),
            
            // Top prestataires ce mois
            'top_prestataires' => Invoice::where('type', 'client')
                ->where('issued_at', '>=', now()->startOfMonth())
                ->paid()
                ->select('prestataire_id', DB::raw('SUM(total) as total_revenue'), DB::raw('COUNT(*) as invoice_count'))
                ->groupBy('prestataire_id')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->with('prestataire.user')
                ->get(),
        ];
    }

    /**
     * Voir une facture en détail
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'prestataire', 'prestataire.user', 'invoiceable']);

        // Récupérer les factures liées (client + prestataire)
        $relatedInvoices = Invoice::where('invoiceable_type', $invoice->invoiceable_type)
            ->where('invoiceable_id', $invoice->invoiceable_id)
            ->where('id', '!=', $invoice->id)
            ->get();

        return view('admin.invoices.show', compact('invoice', 'relatedInvoices'));
    }

    /**
     * Export CSV
     */
    public function export(Request $request)
    {
        $query = Invoice::with(['user', 'prestataire.user'])
            ->where('type', 'client')
            ->latest('issued_at');

        // Appliquer les mêmes filtres
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'month':
                    $query->where('issued_at', '>=', now()->startOfMonth());
                    break;
                case 'quarter':
                    $query->where('issued_at', '>=', now()->startOfQuarter());
                    break;
                case 'year':
                    $query->where('issued_at', '>=', now()->startOfYear());
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }

        $invoices = $query->get();

        $filename = 'factures_plateforme_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'N° Facture',
                'Date Émission',
                'Date Paiement',
                'Client',
                'Email Client',
                'Prestataire',
                'Description',
                'Montant HT',
                'TVA',
                'Montant TTC',
                'Commission',
                'Net Prestataire',
                'Méthode Paiement',
                'Statut'
            ], ';');

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->issued_at ? $invoice->issued_at->format('d/m/Y H:i') : null,
                    $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : null,
                    $invoice->billing_name,
                    $invoice->billing_email,
                    data_get($invoice, 'prestataire.user.name', $invoice->seller_name),
                    $invoice->description,
                    number_format($invoice->subtotal, 2, ',', ''),
                    number_format($invoice->tax_amount, 2, ',', ''),
                    number_format($invoice->total, 2, ',', ''),
                    number_format($invoice->commission_amount, 2, ',', ''),
                    number_format($invoice->net_amount, 2, ',', ''),
                    $invoice->payment_method,
                    $invoice->status_label,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Rapport des commissions
     */
    public function commissions(Request $request)
    {
        $period = $request->get('period', 'month');

        $startDate = null;
        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'quarter':
                $startDate = now()->startOfQuarter();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            default:
                $startDate = null;
                break;
        }

        // Query de base
        $baseQuery = Invoice::where('type', 'client')->paid();
        
        if ($startDate) {
            $baseQuery->where('issued_at', '>=', $startDate);
        }

        // Filtres supplémentaires
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('issued_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('issued_at', '<=', $request->end_date);
        }
        if ($request->filled('prestataire_id')) {
            $baseQuery->where('prestataire_id', $request->prestataire_id);
        }

        // Stats par prestataire
        $prestataireStats = (clone $baseQuery)
            ->select(
                'prestataire_id',
                DB::raw('COUNT(*) as invoices_count'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('SUM(net_amount) as total_net'),
                DB::raw('AVG(commission_rate) as avg_rate')
            )
            ->groupBy('prestataire_id')
            ->with('prestataire.user')
            ->orderByDesc('total_commission')
            ->get()
            ->map(function ($item) {
                $item->prestataire_name = data_get($item, 'prestataire.user.name', 'Prestataire #' . $item->prestataire_id);
                $item->prestataire_email = data_get($item, 'prestataire.user.email', '');
                return $item;
            });

        // Statistiques globales
        $stats = [
            'total_revenue' => $prestataireStats->sum('total_revenue'),
            'total_commission' => $prestataireStats->sum('total_commission'),
            'total_net' => $prestataireStats->sum('total_net'),
            'avg_rate' => $prestataireStats->avg('avg_rate') ?? 10,
        ];

        // Évolution mensuelle (12 derniers mois)
        $monthlyStats = Invoice::where('type', 'client')
            ->paid()
            ->where('issued_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw("DATE_FORMAT(issued_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(commission_amount) as commission')
            )
            ->groupBy(DB::raw("DATE_FORMAT(issued_at, '%Y-%m')"))
            ->orderBy('month')
            ->get();

        // Top 10 prestataires
        $topPrestataires = $prestataireStats->take(10);

        // Liste des prestataires pour filtre
        $prestataires = Prestataire::with('user')
            ->whereHas('invoices')
            ->get()
            ->sortBy('user.name');

        // Export CSV si demandé
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportCommissions($prestataireStats, $stats);
        }

        return view('admin.invoices.commissions', compact(
            'prestataireStats',
            'monthlyStats',
            'topPrestataires',
            'stats',
            'period',
            'prestataires'
        ));
    }

    /**
     * Export CSV des commissions
     */
    private function exportCommissions($prestataireStats, $stats)
    {
        $filename = 'commissions_plateforme_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($prestataireStats, $stats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Prestataire',
                'Email',
                'Nombre Factures',
                'CA Total',
                'Commission',
                'Net versé',
                'Taux moyen'
            ], ';');

            foreach ($prestataireStats as $stat) {
                fputcsv($file, [
                    $stat->prestataire_name,
                    $stat->prestataire_email,
                    $stat->invoices_count,
                    number_format($stat->total_revenue, 2, ',', ''),
                    number_format($stat->total_commission, 2, ',', ''),
                    number_format($stat->total_net, 2, ',', ''),
                    number_format($stat->avg_rate, 1) . '%',
                ], ';');
            }

            // Ligne total
            fputcsv($file, [
                'TOTAL',
                '',
                $prestataireStats->sum('invoices_count'),
                number_format($stats['total_revenue'], 2, ',', ''),
                number_format($stats['total_commission'], 2, ',', ''),
                number_format($stats['total_net'], 2, ',', ''),
                number_format($stats['avg_rate'], 1) . '%',
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
