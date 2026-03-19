<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\EquipmentRentalRequest;
use App\Models\FoodOrder;
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
     * Liste des factures/relevés du prestataire
     * 
     * Affiche les factures avec le détail des paiements selon le type :
     * - Services : acompte (deposit_percentage) + solde
     * - Équipement : caution (security_deposit) + location
     * - Food : escrow (amount_held, amount_released, amount_refunded)
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        if (!$prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Profil prestataire non trouvé');
        }

        try {
            $this->equipmentRentalPaymentSyncService->syncForPrestataire($prestataire);
        } catch (\Throwable $e) {
            \Log::warning('Prestataire invoice index rental payment sync warning', [
                'prestataire_id' => $prestataire->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $query = Invoice::forPrestataire($prestataire->id)
            ->with(['user', 'invoiceable'])
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
                  ->orWhere('billing_name', 'like', "%{$search}%");
            });
        }

        // Filtre par type de transaction
        if ($request->filled('type')) {
            $type = $request->type;
            $query->where(function ($q) use ($type) {
                switch ($type) {
                    case 'service':
                        $q->where('invoiceable_type', 'like', '%Booking%');
                        break;
                    case 'equipment':
                        $q->where(function ($sub) {
                            $sub->where('invoiceable_type', 'like', '%EquipmentRental%')
                                ->orWhere('invoiceable_type', 'like', '%EquipmentRentalRequest%');
                        });
                        break;
                    case 'food':
                        $q->where('invoiceable_type', 'like', '%FoodOrder%');
                        break;
                }
            });
        }

        // Filtre par statut escrow (pour les commandes food)
        if ($request->filled('escrow')) {
            $escrowStatus = $request->escrow;
            // On filtre via les FoodOrders liées
            $foodOrderIds = FoodOrder::where('prestataire_id', $prestataire->id)
                ->where('escrow_status', $escrowStatus)
                ->pluck('id');
            
            $query->where('invoiceable_type', 'like', '%FoodOrder%')
                  ->whereIn('invoiceable_id', $foodOrderIds);
        }

        $invoices = $query->paginate(15);

        // Statistiques de base
        $stats = [
            'total_count' => Invoice::forPrestataire($prestataire->id)->count(),
            'total_revenue' => Invoice::forPrestataire($prestataire->id)->paid()->sum('total'),
            'total_commission' => Invoice::forPrestataire($prestataire->id)->paid()->sum('commission_amount'),
            'total_net' => Invoice::forPrestataire($prestataire->id)->paid()->sum('net_amount'),
            'this_month_revenue' => Invoice::forPrestataire($prestataire->id)
                ->where('issued_at', '>=', now()->startOfMonth())
                ->sum('total'),
            'this_month_net' => Invoice::forPrestataire($prestataire->id)
                ->where('issued_at', '>=', now()->startOfMonth())
                ->sum('net_amount'),
        ];

        // Statistiques escrow (fonds bloqués Food)
        $stats['escrow_held'] = FoodOrder::where('prestataire_id', $prestataire->id)
            ->where('escrow_status', 'held')
            ->sum('amount_held');

        return view('prestataire.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Afficher un relevé/facture
     */
    public function show(Invoice $invoice)
    {
        $prestataire = Auth::user()->prestataire;

        if ($invoice->prestataire_id !== $prestataire->id) {
            abort(403, 'Accès non autorisé');
        }

        $invoice->load(['user', 'invoiceable']);

        // Resync à l'ouverture pour corriger les anciens relevés équipement
        // (historique: certains relevés utilisaient un taux fixe non aligné).
        if (str_contains((string) $invoice->invoiceable_type, 'EquipmentRental')) {
            try {
                $rentalRequest = EquipmentRentalRequest::find((int) $invoice->invoiceable_id);
                if ($rentalRequest) {
                    $this->equipmentRentalPaymentSyncService->syncForRequest($rentalRequest);
                    $invoice->refresh();
                }
            } catch (\Throwable $e) {
                \Log::warning('Invoice show rental sync warning', [
                    'invoice_id' => $invoice->id ?? null,
                    'invoiceable_id' => $invoice->invoiceable_id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Récupérer aussi la facture client associée
        $clientInvoice = Invoice::where('invoiceable_type', $invoice->invoiceable_type)
            ->where('invoiceable_id', $invoice->invoiceable_id)
            ->where('type', 'client')
            ->first();

        return view('prestataire.invoices.show', compact('invoice', 'clientInvoice'));
    }

    /**
     * Télécharger le PDF
     */
    public function download(Invoice $invoice)
    {
        $prestataire = Auth::user()->prestataire;

        if ($invoice->prestataire_id !== $prestataire->id) {
            abort(403, 'Accès non autorisé');
        }

        return view('prestataire.invoices.pdf', compact('invoice'));
    }

    /**
     * Export CSV des factures
     */
    public function export(Request $request)
    {
        $prestataire = Auth::user()->prestataire;

        $invoices = Invoice::forPrestataire($prestataire->id)
            ->paid()
            ->latest('issued_at')
            ->get();

        $filename = 'factures_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            // Header
            fputcsv($file, [
                'N° Facture',
                'Date',
                'Client',
                'Description',
                'Montant Brut',
                'Commission',
                'Montant Net',
                'Statut'
            ], ';');

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->issued_at?->format('d/m/Y'),
                    $invoice->billing_name,
                    $invoice->description,
                    number_format($invoice->total, 2, ',', ''),
                    number_format($invoice->commission_amount, 2, ',', ''),
                    number_format($invoice->net_amount, 2, ',', ''),
                    $invoice->status_label,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
