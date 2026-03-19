<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceGenerationService;
use App\Services\EquipmentRentalPaymentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected InvoiceGenerationService $invoiceService;
    protected EquipmentRentalPaymentSyncService $equipmentRentalPaymentSyncService;

    public function __construct(
        InvoiceGenerationService $invoiceService,
        EquipmentRentalPaymentSyncService $equipmentRentalPaymentSyncService
    )
    {
        $this->invoiceService = $invoiceService;
        $this->equipmentRentalPaymentSyncService = $equipmentRentalPaymentSyncService;
    }

    /**
     * Liste des factures du client
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $this->equipmentRentalPaymentSyncService->syncForClient($user);
        } catch (\Throwable $e) {
            \Log::warning('Client invoice index rental payment sync warning', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $query = Invoice::forClient($user->id)
            ->with(['prestataire', 'prestataire.user'])
            ->latest('issued_at');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('seller_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15);

        // Statistiques
        $stats = [
            'total_count' => Invoice::forClient($user->id)->count(),
            'total_amount' => Invoice::forClient($user->id)->paid()->sum('total'),
            'this_month' => Invoice::forClient($user->id)
                ->where('issued_at', '>=', now()->startOfMonth())
                ->sum('total'),
            'this_year' => Invoice::forClient($user->id)
                ->where('issued_at', '>=', now()->startOfYear())
                ->sum('total'),
        ];

        return view('client.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Afficher une facture
     */
    public function show(Invoice $invoice)
    {
        // Vérifier que la facture appartient au client
        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        $invoice->load(['prestataire', 'prestataire.user', 'invoiceable']);

        return view('client.invoices.show', compact('invoice'));
    }

    /**
     * Télécharger le PDF de la facture
     */
    public function download(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        return view('client.invoices.pdf', compact('invoice'));
    }
}
