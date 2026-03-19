<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Mail\InvoiceMail;
use Exception;
use App\Services\StripePaymentService;

class AdminFinanceController extends Controller
{
    /**
     * 38. Tableau de bord financier
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        try {
            // Regrouper les stats transactions en une seule requête
            $txStats = DB::table('transactions')
                ->where('status', 'completed')
                ->selectRaw('SUM(amount) as total_revenue')
                ->selectRaw('SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END) as revenue_today', [$today])
                ->selectRaw('SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as revenue_month', [$startOfMonth])
                ->selectRaw('SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as revenue_year', [$startOfYear])
                ->selectRaw('SUM(commission) as total_commissions')
                ->first();

            $stats = [
                'total_revenue' => $txStats->total_revenue ?? 0,
                'revenue_today' => $txStats->revenue_today ?? 0,
                'revenue_month' => $txStats->revenue_month ?? 0,
                'revenue_year' => $txStats->revenue_year ?? 0,
                'pending_withdrawals' => DB::table('withdrawals')->where('status', 'pending')->sum('amount') ?? 0,
                'pending_refunds' => DB::table('refunds')->where('status', 'pending')->sum('amount') ?? 0,
                'total_commissions' => $txStats->total_commissions ?? 0,
                'total_prestataires_balance' => DB::table('prestataires')->sum('balance') ?? 0,
                'escrow_held' => DB::table('escrow_transactions')->where('status', 'held')->sum('total_amount') ?? 0,
            ];
        } catch (\Exception $e) {
            $stats = [
                'total_revenue' => 0,
                'revenue_today' => 0,
                'revenue_month' => 0,
                'revenue_year' => 0,
                'pending_withdrawals' => 0,
                'pending_refunds' => 0,
                'total_commissions' => 0,
                'total_prestataires_balance' => 0,
                'escrow_held' => 0,
            ];
        }

        // Stats Escrow
        try {
            // Regrouper les stats escrow en une seule requête
            $escrowRaw = DB::table('escrow_transactions')
                ->selectRaw("SUM(CASE WHEN status = 'held' THEN 1 ELSE 0 END) as held_count")
                ->selectRaw("SUM(CASE WHEN status = 'held' THEN total_amount ELSE 0 END) as held_amount")
                ->selectRaw("SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial_count")
                ->selectRaw("SUM(CASE WHEN status = 'partial' THEN total_amount ELSE 0 END) as partial_amount")
                ->selectRaw("SUM(CASE WHEN status = 'released' THEN 1 ELSE 0 END) as released_count")
                ->selectRaw("SUM(CASE WHEN status = 'released' THEN total_amount ELSE 0 END) as released_amount")
                ->selectRaw("SUM(CASE WHEN status IN ('partial','released') AND stripe_transfer_id IS NULL THEN 1 ELSE 0 END) as pending_transfer_count")
                ->selectRaw("SUM(CASE WHEN status IN ('partial','released') AND stripe_transfer_id IS NULL THEN prestataire_amount ELSE 0 END) as pending_transfer_amount")
                ->first();

            $escrowStats = [
                'held_count' => (int) ($escrowRaw->held_count ?? 0),
                'held_amount' => $escrowRaw->held_amount ?? 0,
                'partial_count' => (int) ($escrowRaw->partial_count ?? 0),
                'partial_amount' => $escrowRaw->partial_amount ?? 0,
                'released_count' => (int) ($escrowRaw->released_count ?? 0),
                'released_amount' => $escrowRaw->released_amount ?? 0,
                'pending_transfer_count' => (int) ($escrowRaw->pending_transfer_count ?? 0),
                'pending_transfer_amount' => $escrowRaw->pending_transfer_amount ?? 0,
            ];
        } catch (\Exception $e) {
            $escrowStats = [
                'held_count' => 0,
                'held_amount' => 0,
                'partial_count' => 0,
                'partial_amount' => 0,
                'released_count' => 0,
                'released_amount' => 0,
                'pending_transfer_count' => 0,
                'pending_transfer_amount' => 0,
            ];
        }

        // Escrows récents
        try {
            $recentEscrows = DB::table('escrow_transactions')
                ->leftJoin('users', 'escrow_transactions.client_id', '=', 'users.id')
                ->leftJoin('prestataires', 'escrow_transactions.prestataire_id', '=', 'prestataires.id')
                ->leftJoin('users as presta_users', 'prestataires.user_id', '=', 'presta_users.id')
                ->select(
                    'escrow_transactions.*',
                    'users.name as client_name',
                    'presta_users.name as prestataire_name'
                )
                ->orderBy('escrow_transactions.created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            $recentEscrows = collect([]);
        }

        // Graphique des revenus mensuels
        try {
            $monthlyRevenue = DB::table('transactions')
                ->where('status', 'completed')
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } catch (\Exception $e) {
            $monthlyRevenue = collect([]);
        }

        // Transactions récentes
        try {
            $recentTransactions = DB::table('transactions')
                ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                ->select('transactions.*', 'users.name as user_name')
                ->orderBy('transactions.created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $recentTransactions = collect([]);
        }

        return view('admin.finance.dashboard', compact('stats', 'monthlyRevenue', 'recentTransactions', 'escrowStats', 'recentEscrows'));
    }

    /**
     * 39. Gestion des transactions
     */
    public function transactions(Request $request)
    {
        $query = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email');

        // Filtres
        if ($request->filled('status')) {
            $query->where('transactions.status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('transactions.type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transactions.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transactions.created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transactions.reference', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')->paginate(20);

        return view('admin.finance.transactions', compact('transactions'));
    }

    public function transactionDetails($id)
    {
        $transaction = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
            ->where('transactions.id', $id)
            ->first();

        $logs = DB::table('transaction_logs')
            ->where('transaction_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.finance.transaction-details', compact('transaction', 'logs'));
    }

    /**
     * 40. Gestion des retraits
     */
    public function withdrawals(Request $request)
    {
        $query = DB::table('withdrawals')
            ->leftJoin('users', 'withdrawals.user_id', '=', 'users.id')
            ->leftJoin('prestataires', 'users.id', '=', 'prestataires.user_id')
            ->select('withdrawals.*', 'users.name as user_name', 'users.email as user_email', 'prestataires.balance');

        if ($request->filled('status')) {
            $query->where('withdrawals.status', $request->status);
        }

        $withdrawals = $query->orderBy('withdrawals.created_at', 'desc')->paginate(20);

        $stats = [
            'pending' => DB::table('withdrawals')->where('status', 'pending')->count(),
            'pending_amount' => DB::table('withdrawals')->where('status', 'pending')->sum('amount'),
            'processed_this_month' => DB::table('withdrawals')
                ->where('status', 'completed')
                ->where('processed_at', '>=', Carbon::now()->startOfMonth())
                ->sum('amount'),
        ];

        return view('admin.finance.withdrawals', compact('withdrawals', 'stats'));
    }

    public function processWithdrawal(Request $request, $id)
    {
        $withdrawal = DB::table('withdrawals')->where('id', $id)->first();

        if (!$withdrawal || $withdrawal->status !== 'pending') {
            return back()->with('error', 'Retrait introuvable ou déjà traité.');
        }

        $status = $request->action === 'approve' ? 'completed' : 'rejected';

        DB::table('withdrawals')->where('id', $id)->update([
            'status' => $status,
            'processed_at' => now(),
            'processed_by' => auth()->user()->id,
            'admin_notes' => $request->notes,
            'transaction_reference' => $request->transaction_reference,
            'updated_at' => now(),
        ]);

        if ($status === 'rejected') {
            // Recréditer le solde
            DB::table('prestataires')
                ->where('user_id', $withdrawal->user_id)
                ->increment('balance', $withdrawal->amount);
        }

        return back()->with('success', 'Retrait ' . ($status === 'completed' ? 'approuvé' : 'rejeté') . '.');
    }

    public function bulkProcessWithdrawals(Request $request)
    {
        $ids = $request->withdrawal_ids;
        $action = $request->action;

        foreach ($ids as $id) {
            $withdrawal = DB::table('withdrawals')->where('id', $id)->where('status', 'pending')->first();
            if ($withdrawal) {
                $status = $action === 'approve' ? 'completed' : 'rejected';

                DB::table('withdrawals')->where('id', $id)->update([
                    'status' => $status,
                    'processed_at' => now(),
                    'processed_by' => auth()->user()->id,
                    'updated_at' => now(),
                ]);

                if ($status === 'rejected') {
                    DB::table('prestataires')
                        ->where('user_id', $withdrawal->user_id)
                        ->increment('balance', $withdrawal->amount);
                }
            }
        }

        return back()->with('success', count($ids) . ' retraits traités.');
    }

    /**
     * 41. Gestion des remboursements
     */
    public function refunds(Request $request)
    {
        $query = DB::table('refunds')
            ->leftJoin('users', 'refunds.user_id', '=', 'users.id')
            ->leftJoin('transactions', 'refunds.transaction_id', '=', 'transactions.id')
            ->leftJoin('bookings', 'refunds.booking_id', '=', 'bookings.id')
            ->select('refunds.*', 'users.name as user_name', 'transactions.reference as transaction_ref');

        if ($request->filled('status')) {
            $query->where('refunds.status', $request->status);
        }

        $refunds = $query->orderBy('refunds.created_at', 'desc')->paginate(20);

        return view('admin.finance.refunds', compact('refunds'));
    }

    public function createRefund(Request $request)
    {
        $transaction = DB::table('transactions')->where('id', $request->transaction_id)->first();

        if (!$transaction) {
            return back()->with('error', 'Transaction introuvable.');
        }

        // Vérifier les remboursements existants pour éviter le sur-remboursement
        $existingRefundsTotal = (float) DB::table('refunds')
            ->where('transaction_id', $transaction->id)
            ->whereIn('status', ['pending', 'completed', 'approved'])
            ->sum('amount');
        $maxRefundable = max(0, (float) $transaction->amount - $existingRefundsTotal);

        if ($maxRefundable <= 0) {
            return back()->with('error', 'Cette transaction a déjà été entièrement remboursée.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $maxRefundable,
            'reason' => 'required|string',
        ]);

        DB::table('refunds')->insert([
            'transaction_id' => $transaction->id,
            'booking_id' => $transaction->booking_id,
            'user_id' => $transaction->user_id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_by' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Demande de remboursement créée.');
    }

    public function processRefund(Request $request, $id)
    {
        $refund = DB::table('refunds')->where('id', $id)->first();

        if (!$refund || $refund->status !== 'pending') {
            return back()->with('error', 'Remboursement introuvable ou déjà traité.');
        }

        $action = $request->get('action');
        $status = $action === 'approve' ? 'completed' : 'rejected';

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            DB::table('refunds')->where('id', $id)->update([
                'status' => $status,
                'processed_at' => now(),
                'processed_by' => auth()->user()->id,
                'admin_notes' => $request->notes,
                'updated_at' => now(),
            ]);

            if ($status === 'completed') {
                // Marquer la transaction comme remboursée et ajuster les soldes si nécessaire
                $transaction = DB::table('transactions')->where('id', $refund->transaction_id)->first();
                if ($transaction) {
                    DB::table('transactions')->where('id', $transaction->id)->update(['status' => 'refunded', 'updated_at' => now()]);
                }

                // Si un prestataire a été payé, décrémenter/ajuster son solde selon la logique métier
                if (!empty($refund->prestataire_id)) {
                    DB::table('prestataires')->where('id', $refund->prestataire_id)->decrement('balance', $refund->amount);
                }

                // Ajouter un log de transaction/refund
                DB::table('transaction_logs')->insert([
                    'transaction_id' => $refund->transaction_id,
                    'type' => 'refund',
                    'amount' => $refund->amount,
                    'notes' => $request->notes,
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Remboursement ' . ($status === 'completed' ? 'effectué' : 'rejeté') . '.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Erreur lors du traitement du remboursement.');
        }
    }

    /**
     * 42. Gestion des factures
     */
    public function invoices(Request $request)
    {
        try {
            $query = DB::table('invoices')
                ->leftJoin('users', 'invoices.user_id', '=', 'users.id')
                ->select('invoices.*', 'users.name as user_name', 'users.email as user_email');

            if ($request->filled('status')) {
                $query->where('invoices.status', $request->status);
            }
            if ($request->filled('type')) {
                $query->where('invoices.type', $request->type);
            }

            $invoices = $query->orderBy('invoices.created_at', 'desc')->paginate(20);
        } catch (\Throwable $e) {
            report($e);

            $invoices = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                20,
                max(1, (int) $request->get('page', 1)),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $viewName = view()->exists('admin.finance.invoices')
            ? 'admin.finance.invoices'
            : (view()->exists('admin.invoices') ? 'admin.invoices' : null);

        if (!$viewName) {
            abort(500, 'View invoices introuvable (admin.finance.invoices).');
        }

        return view($viewName, compact('invoices'));
    }

    public function generateInvoice($id)
    {
        $invoice = DB::table('invoices')
            ->leftJoin('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.name', 'users.email', 'users.phone')
            ->where('invoices.id', $id)
            ->first();

        $items = DB::table('invoice_items')->where('invoice_id', $id)->get();

        // Générer le PDF
        $pdf = PDF::loadView('admin.finance.invoice-pdf', compact('invoice', 'items'));

        return $pdf->download('facture-' . $invoice->number . '.pdf');
    }

    public function sendInvoice($id)
    {
        $invoice = DB::table('invoices')
            ->leftJoin('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.email')
            ->where('invoices.id', $id)
            ->first();

        if (!$invoice) {
            return back()->with('error', 'Facture introuvable.');
        }

        try {
            $items = DB::table('invoice_items')->where('invoice_id', $id)->get();

            // Générer le PDF en mémoire
            $pdf = PDF::loadView('admin.finance.invoice-pdf', ['invoice' => $invoice, 'items' => $items]);
            $filename = 'invoices/facture-' . ($invoice->number ?? $invoice->id) . '.pdf';

            // Stocker le pdf dans storage/app/invoices
            Storage::disk('local')->put($filename, $pdf->output());

            // Envoyer par email avec pièce jointe
            Mail::to($invoice->email)->send(new InvoiceMail($invoice, storage_path('app/' . $filename)));

            DB::table('invoices')->where('id', $id)->update([
                'sent_at' => now(),
                'status' => 'sent',
                'pdf_path' => $filename,
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Facture envoyée.');
        } catch (Exception $e) {
            report($e);
            return back()->with('error', 'Erreur lors de l\'envoi de la facture.');
        }
    }

    /**
     * 43. Rapport des commissions
     */
    public function commissions(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $commissions = DB::table('transactions')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount, SUM(commission) as total_commission, COUNT(*) as transaction_count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $totals = [
            'total_transactions' => $commissions->sum('transaction_count'),
            'total_amount' => $commissions->sum('total_amount'),
            'total_commission' => $commissions->sum('total_commission'),
        ];

        // Commissions par prestataire
        $byPrestataire = DB::table('transactions')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->join('prestataires', 'transactions.prestataire_id', '=', 'prestataires.id')
            ->join('users', 'prestataires.user_id', '=', 'users.id')
            ->selectRaw('prestataires.id, users.name, SUM(transactions.commission) as total_commission, COUNT(*) as transaction_count')
            ->groupBy('prestataires.id', 'users.name')
            ->orderBy('total_commission', 'desc')
            ->limit(20)
            ->get();

        return view('admin.finance.commissions', compact('commissions', 'totals', 'byPrestataire', 'dateFrom', 'dateTo'));
    }

    /**
     * 44. Gestion des versements prestataires
     */
    public function payouts(Request $request)
    {
        $query = DB::table('payouts')
            ->leftJoin('prestataires', 'payouts.prestataire_id', '=', 'prestataires.id')
            ->leftJoin('users', 'prestataires.user_id', '=', 'users.id')
            ->select('payouts.*', 'users.name as prestataire_name', 'users.email as prestataire_email');

        if ($request->filled('status')) {
            $query->where('payouts.status', $request->status);
        }

        $payouts = $query->orderBy('payouts.created_at', 'desc')->paginate(20);

        // Prestataires avec solde à verser
        $prestatairesWithBalance = DB::table('prestataires')
            ->join('users', 'prestataires.user_id', '=', 'users.id')
            ->where('prestataires.balance', '>', 0)
            ->select('prestataires.*', 'users.name', 'users.email')
            ->orderBy('prestataires.balance', 'desc')
            ->get();

        return view('admin.finance.payouts', compact('payouts', 'prestatairesWithBalance'));
    }

    public function createPayout(Request $request)
    {
        $request->validate([
            'prestataire_id' => 'required|exists:prestataires,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $prestataire = DB::table('prestataires')->where('id', $request->prestataire_id)->first();

        if ($request->amount > $prestataire->balance) {
            return back()->with('error', 'Montant supérieur au solde disponible.');
        }

        DB::beginTransaction();
        try {
            DB::table('payouts')->insert([
                'prestataire_id' => $request->prestataire_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'status' => 'pending',
                'notes' => $request->notes,
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If the payout method is not Stripe, decrement immediately.
            // For Stripe we decrement only after transfer confirmation.
            if (($request->method ?? '') !== 'stripe') {
                DB::table('prestataires')
                    ->where('id', $request->prestataire_id)
                    ->decrement('balance', $request->amount);
            }

            DB::commit();
            return back()->with('success', 'Versement créé.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Erreur lors de la création du versement.');
        }
    }

    public function processPayout(Request $request, $id)
    {
        $action = $request->get('action');
        $status = $action === 'complete' ? 'completed' : 'cancelled';

        $validator = Validator::make($request->all(), [
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            DB::table('payouts')->where('id', $id)->update([
                'status' => $status,
                'processed_at' => now(),
                'processed_by' => auth()->user()->id,
                'transaction_reference' => $request->transaction_reference,
                'admin_notes' => $request->notes,
                'updated_at' => now(),
            ]);

            // If completing a payout and method is stripe, attempt to create a test transfer
            if ($status === 'completed') {
                $payout = DB::table('payouts')->where('id', $id)->first();
                if ($payout && ($payout->method ?? '') === 'stripe') {
                    // Audit 4.4: utilise StripePaymentService au lieu du service dupliqué
                    $prest = DB::table('prestataires')->where('id', $payout->prestataire_id)->first();
                    if (!$prest || empty($prest->stripe_account_id)) {
                        throw new Exception('Prestataire Stripe account not configured.');
                    }
                    $stripeService = app(StripePaymentService::class);
                    $transfer = $stripeService->transferToConnectedAccount(
                        $prest->stripe_account_id,
                        (float) $payout->amount,
                        'Platform payout #' . ($payout->id ?? 'unknown'),
                        ['payout_id' => (string) ($payout->id ?? '')]
                    );
                    if (!empty($transfer->id)) {
                        DB::table('payouts')->where('id', $id)->update(['transaction_reference' => $transfer->id]);
                        DB::table('prestataires')
                            ->where('id', $payout->prestataire_id)
                            ->decrement('balance', $payout->amount);
                    } else {
                        throw new Exception('Stripe transfert failed: empty id');
                    }
                }
            }

            if ($status === 'cancelled') {
                $payout = DB::table('payouts')->where('id', $id)->first();
                // Only re-credit if the payout had already decremented the balance at creation (non-stripe methods)
                if (($payout->method ?? '') !== 'stripe') {
                    DB::table('prestataires')
                        ->where('id', $payout->prestataire_id)
                        ->increment('balance', $payout->amount);
                }
            }

            DB::commit();
            return back()->with('success', 'Versement ' . ($status === 'completed' ? 'complété' : 'annulé') . '.');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Erreur lors du traitement du versement.');
        }
    }

    /**
     * Synchroniser les paiements de ventes urgentes avec le système escrow
     */
    public function syncEscrowPayments(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $force = (bool) $request->get('force', false);

        $escrowService = app(\App\Services\EscrowService::class);

        $results = [
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => [],
        ];

        try {
            // Trouver les achats de ventes urgentes sans escrow
            $purchases = \App\Models\UrgentSalePurchase::with(['urgentSale.prestataire', 'buyer.client', 'paymentTransaction'])
                ->where('created_at', '>=', now()->subDays($days))
                ->where('status', 'paid')
                ->when(!$force, function ($q) {
                    $q->whereNull('escrow_id');
                })
                ->get();

            foreach ($purchases as $purchase) {
                $urgentSale = $purchase->urgentSale;
                $buyer = $purchase->buyer;
                $transaction = $purchase->paymentTransaction;

                if (!$urgentSale || !$buyer || !$buyer->client) {
                    $results['errors']++;
                    $results['details'][] = "Achat #{$purchase->id}: données manquantes";
                    continue;
                }

                $clientId = $buyer->client->id;
                $prestataireId = $urgentSale->prestataire_id;

                if (!$prestataireId) {
                    $results['errors']++;
                    $results['details'][] = "Achat #{$purchase->id}: prestataire manquant";
                    continue;
                }

                // Vérifier si un escrow existe déjà
                $existingEscrow = DB::table('escrow_transactions')
                    ->where('escrowable_type', 'like', '%UrgentSalePurchase%')
                    ->where('escrowable_id', $purchase->id)
                    ->first();

                if ($existingEscrow && !$force) {
                    $results['skipped']++;
                    $results['details'][] = "Achat #{$purchase->id}: escrow #{$existingEscrow->id} existe déjà";
                    continue;
                }

                // Calculer les montants
                $amount = (float) $purchase->total_amount;
                $stripePaymentIntentId = $transaction?->stripe_payment_intent_id;

                // Récupérer les métadonnées du PaymentIntent
                $platformFee = null;
                $metadata = [];

                if ($stripePaymentIntentId && class_exists('\Stripe\Stripe')) {
                    try {
                        \Stripe\Stripe::setApiKey(config('stripe.secret'));
                        $pi = \Stripe\PaymentIntent::retrieve($stripePaymentIntentId);
                        $metadata = $pi->metadata?->toArray() ?? [];

                        $clientFee = (float) ($metadata['client_fee_total'] ?? 0);
                        $prestaFee = (float) ($metadata['prestataire_fee_total'] ?? 0);
                        $stripeFee = (float) ($metadata['stripe_fee_total'] ?? 0);
                        $platformFee = round($clientFee + $prestaFee + $stripeFee, 2);
                    } catch (\Exception $e) {
                        // Continuer sans les métadonnées Stripe
                    }
                }

                // Supprimer l'ancien escrow si force
                if ($existingEscrow && $force) {
                    DB::table('escrow_transactions')->where('id', $existingEscrow->id)->delete();
                }

                // Créer l'escrow
                $escrow = $escrowService->createEscrow(
                    escrowable: $purchase,
                    clientId: $clientId,
                    prestataireId: $prestataireId,
                    amount: $amount,
                    depositAmount: 0,
                    stripePaymentIntentId: $stripePaymentIntentId,
                    platformFeeOverride: $platformFee,
                    metadata: array_merge($metadata, [
                        'synced_at' => now()->toISOString(),
                        'purchase_id' => (string) $purchase->id,
                        'urgent_sale_id' => (string) $urgentSale->id,
                    ])
                );

                if ($escrow) {
                    DB::table('urgent_sale_purchases')
                        ->where('id', $purchase->id)
                        ->update(['escrow_id' => $escrow->id, 'updated_at' => now()]);

                    $results['created']++;
                    $results['details'][] = "Achat #{$purchase->id}: escrow #{$escrow->id} créé ({$amount}€)";
                } else {
                    $results['errors']++;
                    $results['details'][] = "Achat #{$purchase->id}: échec création escrow";
                }
            }

            $message = "Synchronisation terminée: {$results['created']} créé(s), {$results['skipped']} ignoré(s), {$results['errors']} erreur(s)";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => $results,
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            report($e);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la synchronisation des paiements sécurisés.',
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la synchronisation des paiements sécurisés.');
        }
    }

    /**
     * 45. Export financier
     */
    public function exportTransactions(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $transactions = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
            ->orderBy('transactions.created_at', 'desc')
            ->get();

        $filename = 'transactions_' . $dateFrom . '_' . $dateTo . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['ID', 'Référence', 'Utilisateur', 'Email', 'Montant', 'Commission', 'Type', 'Statut', 'Date']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->reference ?? '',
                    $t->user_name,
                    $t->user_email,
                    $t->amount,
                    $t->commission ?? 0,
                    $t->type ?? '',
                    $t->status,
                    $t->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
