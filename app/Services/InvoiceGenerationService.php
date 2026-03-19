<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\UrgentSalePurchase;
use App\Models\PaymentTransaction;
use App\Models\Prestataire;
use App\Support\PaymentMetadataNormalizer;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Log;

/**
 * Service pour générer automatiquement les factures lors des paiements
 */
class InvoiceGenerationService
{
    // Taux Stripe par défaut (surchargés par settings admin si disponibles)
    protected float $stripeRate;
    protected float $stripeFee;

    public function __construct()
    {
        $this->stripeRate = (float) get_setting('stripe_fee_percent', (string) config('finance.stripe_rate', 1.4));
        $this->stripeFee = (float) get_setting('stripe_fee_fixed', (string) config('finance.stripe_fixed_fee', 0.25));
    }

    /**
     * Génère les factures pour une réservation (Booking)
     */
    public function generateForBooking(Booking $booking, PaymentTransaction $transaction = null): array
    {
        // Vérifier si la facture existe déjà
        if ($this->invoiceExists(Booking::class, $booking->id)) {
            Log::info("Invoice already exists for Booking #{$booking->id}");
            return ['exists' => true];
        }

        $booking->load(['user', 'service', 'service.prestataire', 'service.prestataire.user']);

        $amount = (float) ($booking->total_price ?? $booking->price ?? 0);
        if ($amount <= 0) {
            Log::warning("Booking #{$booking->id} has no amount");
            return ['error' => 'No amount'];
        }

        $prestataire = $booking->service?->prestataire;
        $user = $booking->user;

        if (!$prestataire || !$user) {
            Log::warning("Booking #{$booking->id} missing prestataire or user");
            return ['error' => 'Missing prestataire or user'];
        }

        $serviceName = $booking->service->name ?? 'Service';
        $fees = $this->calculateFees($amount, 'service');

        // Facture CLIENT
        $clientInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('client'),
            'type' => 'client',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => Booking::class,
            'invoiceable_id' => $booking->id,
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? null,
            'seller_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'seller_siret' => $prestataire->siret ?? null,
            'description' => "Réservation: {$serviceName}",
            'line_items' => [[
                'description' => $serviceName,
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount,
            ]],
            'subtotal' => $amount,
            'total' => $amount,
            'commission_rate' => $fees['platformRate'],
            'commission_amount' => $fees['platformCommission'],
            'net_amount' => $fees['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        // Facture PRESTATAIRE (Relevé détaillé)
        $prestataireInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('prestataire'),
            'type' => 'prestataire',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => Booking::class,
            'invoiceable_id' => $booking->id,
            'billing_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'billing_email' => $prestataire->user->email ?? null,
            'seller_name' => 'TaPrestation',
            'seller_siret' => config('app.siret', ''),
            'description' => "Vente: {$serviceName} - Client: {$user->name}",
            'line_items' => $this->prestataireLineItems($serviceName, $amount, $fees),
            'subtotal' => $amount,
            'total' => $fees['netAmount'],
            'commission_rate' => $fees['platformRate'],
            'commission_amount' => $fees['platformCommission'],
            'net_amount' => $fees['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe_connect',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
            'notes' => json_encode([
                'stripe_fee' => $fees['stripeCommission'],
                'total_deductions' => $fees['totalDeductions'],
                'breakdown' => [
                    'sale_amount' => $amount,
                    'platform_commission' => $fees['platformCommission'],
                    'stripe_fee' => $fees['stripeCommission'],
                    'net_received' => $fees['netAmount'],
                ]
            ]),
        ]);

        Log::info("Generated invoices for Booking #{$booking->id}", [
            'client_invoice' => $clientInvoice->invoice_number,
            'prestataire_invoice' => $prestataireInvoice->invoice_number,
        ]);

        return [
            'client_invoice' => $clientInvoice,
            'prestataire_invoice' => $prestataireInvoice,
        ];
    }

    /**
     * Génère les factures pour une location d'équipement
     */
    public function generateForEquipmentRental(EquipmentRentalRequest $request, PaymentTransaction $transaction = null): array
    {
        $request->load(['client', 'client.user', 'equipment', 'equipment.prestataire', 'equipment.prestataire.user']);

        $amounts = $this->resolveEquipmentInvoiceAmounts($request, $transaction);
        $paidAmount = $amounts['paid_amount'];
        $paidRentalAmount = $amounts['paid_rental_amount'];
        $paidDepositAmount = $amounts['paid_deposit_amount'];

        if ($paidAmount <= 0) {
            Log::warning("EquipmentRentalRequest #{$request->id} has no amount");
            return ['error' => 'No amount'];
        }

        $prestataire = $request->equipment?->prestataire;
        $user = $request->client?->user;

        if (!$prestataire || !$user) {
            Log::warning("EquipmentRentalRequest #{$request->id} missing prestataire or user");
            return ['error' => 'Missing prestataire or user'];
        }

        $equipmentName = $request->equipment->name ?? 'Équipement';
        $feesClient = $this->calculateFees($paidAmount, 'rental');
        $feesPrestataire = $this->calculateFees($paidRentalAmount, 'rental');
        $duration = $request->duration_days ?? 1;
        $description = "Location: {$equipmentName} ({$duration} jour" . ($duration > 1 ? 's' : '') . ")";
        $paymentStageLabel = $amounts['payment_stage_label'];

        $clientLineItems = [
            [
                'description' => $equipmentName,
                'details' => "Location du {$request->start_date} au {$request->end_date}",
                'quantity' => $duration,
                'unit_price' => (float) ($request->unit_price ?? ($duration > 0 ? ($paidRentalAmount / max(1, $duration)) : $paidRentalAmount)),
                'total' => $paidRentalAmount,
                'type' => 'rental',
            ],
        ];

        if ($paidDepositAmount > 0) {
            $clientLineItems[] = [
                'description' => 'Caution remboursable',
                'details' => 'Montant bloqué en séquestre jusqu\'au retour',
                'quantity' => 1,
                'unit_price' => $paidDepositAmount,
                'total' => $paidDepositAmount,
                'type' => 'deposit',
            ];
        }

        // Vérifier si la facture existe déjà
        if ($this->invoiceExists(EquipmentRentalRequest::class, $request->id)) {
            $this->synchronizeExistingEquipmentRentalInvoices(
                $request,
                $transaction,
                "{$description} - {$paymentStageLabel}",
                $clientLineItems,
                $paidAmount,
                $paidRentalAmount,
                $feesClient,
                $feesPrestataire
            );

            Log::info("Invoice already exists for EquipmentRentalRequest #{$request->id} (synchronized)");
            return ['exists' => true, 'updated' => true];
        }

        // Facture CLIENT
        $clientInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('client'),
            'type' => 'client',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => EquipmentRentalRequest::class,
            'invoiceable_id' => $request->id,
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? null,
            'seller_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'seller_siret' => $prestataire->siret ?? null,
            'description' => "{$description} - {$paymentStageLabel}",
            'line_items' => $clientLineItems,
            'subtotal' => $paidAmount,
            'total' => $paidAmount,
            'commission_rate' => $feesClient['platformRate'],
            'commission_amount' => $feesClient['platformCommission'],
            'net_amount' => $feesClient['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        // Facture PRESTATAIRE
        $prestataireInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('prestataire'),
            'type' => 'prestataire',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => EquipmentRentalRequest::class,
            'invoiceable_id' => $request->id,
            'billing_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'billing_email' => $prestataire->user->email ?? null,
            'seller_name' => 'TaPrestation',
            'seller_siret' => config('app.siret', ''),
            'description' => "Location: {$equipmentName} - {$paymentStageLabel} - Client: {$user->name}",
            'line_items' => $this->prestataireLineItems($description, $paidRentalAmount, $feesPrestataire),
            'subtotal' => $paidRentalAmount,
            'total' => $feesPrestataire['netAmount'],
            'commission_rate' => $feesPrestataire['platformRate'],
            'commission_amount' => $feesPrestataire['platformCommission'],
            'net_amount' => $feesPrestataire['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe_connect',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
            'notes' => json_encode([
                'stripe_fee' => $feesPrestataire['stripeCommission'],
                'total_deductions' => $feesPrestataire['totalDeductions'],
            ]),
        ]);

        Log::info("Generated invoices for EquipmentRentalRequest #{$request->id}", [
            'client_invoice' => $clientInvoice->invoice_number,
            'prestataire_invoice' => $prestataireInvoice->invoice_number,
        ]);

        return [
            'client_invoice' => $clientInvoice,
            'prestataire_invoice' => $prestataireInvoice,
        ];
    }

    /**
     * Génère les factures pour un achat vente flash (UrgentSale)
     */
    public function generateForUrgentSale(UrgentSalePurchase $purchase, PaymentTransaction $transaction = null): array
    {
        // Vérifier si la facture existe déjà
        if ($this->invoiceExists(UrgentSalePurchase::class, $purchase->id)) {
            Log::info("Invoice already exists for UrgentSalePurchase #{$purchase->id}");
            return ['exists' => true];
        }

        $purchase->load(['buyer', 'urgentSale', 'urgentSale.prestataire', 'urgentSale.prestataire.user']);

        $amount = (float) ($purchase->total_amount ?? 0);
        if ($amount <= 0) {
            Log::warning("UrgentSalePurchase #{$purchase->id} has no amount");
            return ['error' => 'No amount'];
        }

        $prestataire = $purchase->urgentSale?->prestataire;
        $user = $purchase->buyer;

        if (!$prestataire || !$user) {
            Log::warning("UrgentSalePurchase #{$purchase->id} missing prestataire or buyer");
            return ['error' => 'Missing prestataire or buyer'];
        }

        $productName = $purchase->urgentSale->title ?? 'Produit';
        $quantity = (int) ($purchase->quantity ?? 1);
        $fees = $this->calculateFees($amount, 'urgent_sale');

        // Facture CLIENT
        $clientInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('client'),
            'type' => 'client',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => UrgentSalePurchase::class,
            'invoiceable_id' => $purchase->id,
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? null,
            'seller_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'seller_siret' => $prestataire->siret ?? null,
            'description' => "Achat: {$productName}",
            'line_items' => [
                [
                    'description' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => (float) ($purchase->unit_price ?? $amount / $quantity),
                    'total' => $amount,
                ]
            ],
            'subtotal' => $amount,
            'total' => $amount,
            'commission_rate' => $fees['platformRate'],
            'commission_amount' => $fees['platformCommission'],
            'net_amount' => $fees['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        // Facture PRESTATAIRE
        $prestataireInvoice = Invoice::systemCreate([
            'invoice_number' => Invoice::generateInvoiceNumber('prestataire'),
            'type' => 'prestataire',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => UrgentSalePurchase::class,
            'invoiceable_id' => $purchase->id,
            'billing_name' => $prestataire->user->name ?? $prestataire->company_name ?? 'Prestataire',
            'billing_email' => $prestataire->user->email ?? null,
            'seller_name' => 'TaPrestation',
            'seller_siret' => config('app.siret', ''),
            'description' => "Vente Flash: {$productName} (x{$quantity}) - Client: {$user->name}",
            'line_items' => $this->prestataireLineItems($productName, $amount, $fees),
            'subtotal' => $amount,
            'total' => $fees['netAmount'],
            'commission_rate' => $fees['platformRate'],
            'commission_amount' => $fees['platformCommission'],
            'net_amount' => $fees['netAmount'],
            'status' => 'paid',
            'payment_method' => 'stripe_connect',
            'payment_reference' => $transaction?->stripe_payment_intent_id,
            'issued_at' => now(),
            'paid_at' => now(),
            'notes' => json_encode([
                'stripe_fee' => $fees['stripeCommission'],
                'total_deductions' => $fees['totalDeductions'],
            ]),
        ]);

        Log::info("Generated invoices for UrgentSalePurchase #{$purchase->id}", [
            'client_invoice' => $clientInvoice->invoice_number,
            'prestataire_invoice' => $prestataireInvoice->invoice_number,
        ]);

        return [
            'client_invoice' => $clientInvoice,
            'prestataire_invoice' => $prestataireInvoice,
        ];
    }

    /**
     * Calcule les frais (commission plateforme + Stripe)
     */
    protected function calculateFees(float $amount, string $type = 'service'): array
    {
        if ($amount <= 0) {
            return [
                'amount' => 0.0,
                'platformRate' => 0.0,
                'platformCommission' => 0.0,
                'stripeCommission' => 0.0,
                'totalDeductions' => 0.0,
                'netAmount' => 0.0,
                'stripeRateUsed' => $this->stripeRate,
                'stripeFixedUsed' => $this->stripeFee,
            ];
        }

        $normalizedType = CommissionService::normalizeType($type);
        $platformRate = CommissionService::ratePercent($normalizedType, 'prestataire');
        $platformCommission = CommissionService::feeAmount($amount, $normalizedType, 'prestataire');
        $stripeCommission = CommissionService::stripeFeesAmount($amount);
        $totalDeductions = $platformCommission + $stripeCommission;
        $netAmount = round($amount - $totalDeductions, 2);

        return [
            'amount' => $amount,
            'platformRate' => $platformRate,
            'platformCommission' => $platformCommission,
            'stripeCommission' => $stripeCommission,
            'totalDeductions' => $totalDeductions,
            'netAmount' => $netAmount,
            'stripeRateUsed' => $this->stripeRate,
            'stripeFixedUsed' => $this->stripeFee,
        ];
    }

    /**
     * Génère les line_items pour la facture prestataire
     */
    protected function prestataireLineItems(string $title, float $amount, array $fees): array
    {
        $platformRate = (float) ($fees['platformRate'] ?? CommissionService::ratePercent('service', 'prestataire'));
        $stripeRate = (float) ($fees['stripeRateUsed'] ?? $this->stripeRate);
        $stripeFixed = (float) ($fees['stripeFixedUsed'] ?? $this->stripeFee);

        return [
            [
                'description' => $title,
                'details' => 'Montant de la vente',
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount,
                'type' => 'sale'
            ],
            [
                'description' => "Commission TaPrestation ({$platformRate}%)",
                'details' => 'Commission plateforme déduite automatiquement',
                'quantity' => 1,
                'unit_price' => -$fees['platformCommission'],
                'total' => -$fees['platformCommission'],
                'type' => 'platform_fee'
            ],
            [
                'description' => "Frais Stripe ({$stripeRate}% + {$stripeFixed}€)",
                'details' => 'Frais de paiement en ligne',
                'quantity' => 1,
                'unit_price' => -$fees['stripeCommission'],
                'total' => -$fees['stripeCommission'],
                'type' => 'stripe_fee'
            ],
            [
                'description' => 'Net à recevoir',
                'details' => 'Montant après déductions',
                'quantity' => 1,
                'unit_price' => $fees['netAmount'],
                'total' => $fees['netAmount'],
                'type' => 'net_amount',
                'is_summary' => true
            ],
        ];
    }

    /**
     * Vérifie si une facture existe déjà pour un invoiceable donné
     */
    protected function invoiceExists(string $type, int $id): bool
    {
        return Invoice::where('invoiceable_type', $type)
            ->where('invoiceable_id', $id)
            ->where('type', 'client')
            ->exists();
    }

    private function resolveEquipmentInvoiceAmounts(EquipmentRentalRequest $request, ?PaymentTransaction $transaction): array
    {
        $rentalAmount = (float) ($request->final_amount ?? $request->total_amount ?? 0);
        $securityDeposit = (float) ($request->equipment?->security_deposit ?? $request->security_deposit ?? 0);
        $depositPercentage = (float) ($request->equipment?->deposit_percentage ?? 30);
        $expectedDepositPart = round($rentalAmount * ($depositPercentage / 100), 2);
        $expectedBalancePart = max(0, $rentalAmount - $expectedDepositPart);

        $metadata = PaymentMetadataNormalizer::normalize((array) ($transaction?->metadata ?? []));
        $rawPaymentType = (string) ($metadata['payment_type'] ?? ($metadata['tx_type'] ?? ($transaction?->type ?? 'full')));
        $paymentType = strtolower($rawPaymentType);
        if (!in_array($paymentType, ['deposit', 'balance', 'full', 'payment'], true)) {
            $paymentType = 'full';
        }
        if ($paymentType === 'payment') {
            $paymentType = 'full';
        }

        $expectedPaidAmount = match ($paymentType) {
            'deposit' => $expectedDepositPart + $securityDeposit,
            'balance' => $expectedBalancePart,
            default => $rentalAmount + $securityDeposit,
        };

        $paidAmount = (float) ($transaction?->amount ?? 0);
        if ($paidAmount <= 0) {
            $paidAmount = $expectedPaidAmount;
        }

        $paidRentalAmount = match ($paymentType) {
            'deposit' => min($paidAmount, $expectedDepositPart),
            'balance' => min($paidAmount, $expectedBalancePart),
            default => min($paidAmount, $rentalAmount),
        };
        $paidRentalAmount = max(0, round($paidRentalAmount, 2));

        $paidDepositAmount = max(0, round($paidAmount - $paidRentalAmount, 2));
        if ($securityDeposit > 0 && $paidDepositAmount > $securityDeposit) {
            $paidDepositAmount = $securityDeposit;
        }

        $paidAmount = round($paidRentalAmount + $paidDepositAmount, 2);
        if ($paidAmount <= 0) {
            $paidAmount = max(0, round($expectedPaidAmount, 2));
        }

        $paymentStageLabel = match ($paymentType) {
            'deposit' => 'Acompte + caution',
            'balance' => 'Paiement du solde',
            default => 'Paiement intégral',
        };

        return [
            'rental_amount' => $rentalAmount,
            'security_deposit' => $securityDeposit,
            'paid_amount' => $paidAmount,
            'paid_rental_amount' => $paidRentalAmount,
            'paid_deposit_amount' => $paidDepositAmount,
            'payment_type' => $paymentType,
            'payment_stage_label' => $paymentStageLabel,
        ];
    }

    private function synchronizeExistingEquipmentRentalInvoices(
        EquipmentRentalRequest $request,
        ?PaymentTransaction $transaction,
        string $description,
        array $clientLineItems,
        float $paidAmount,
        float $paidRentalAmount,
        array $feesClient,
        array $feesPrestataire
    ): void {
        $clientInvoice = Invoice::where('invoiceable_type', EquipmentRentalRequest::class)
            ->where('invoiceable_id', $request->id)
            ->where('type', 'client')
            ->first();

        if ($clientInvoice) {
            $clientInvoice->forceFill([
                'description' => $description,
                'line_items' => $clientLineItems,
                'subtotal' => $paidAmount,
                'total' => $paidAmount,
                'commission_amount' => $feesClient['platformCommission'],
                'net_amount' => $feesClient['netAmount'],
                'payment_reference' => $transaction?->stripe_payment_intent_id ?: $clientInvoice->payment_reference,
                'status' => 'paid',
                'paid_at' => $clientInvoice->paid_at ?? now(),
            ]);
            $clientInvoice->save();
        }

        $prestataireInvoice = Invoice::where('invoiceable_type', EquipmentRentalRequest::class)
            ->where('invoiceable_id', $request->id)
            ->where('type', 'prestataire')
            ->first();

        if ($prestataireInvoice) {
            $prestataireInvoice->forceFill([
                'line_items' => $this->prestataireLineItems($description, $paidRentalAmount, $feesPrestataire),
                'subtotal' => $paidRentalAmount,
                'total' => $feesPrestataire['netAmount'],
                'commission_amount' => $feesPrestataire['platformCommission'],
                'net_amount' => $feesPrestataire['netAmount'],
                'payment_reference' => $transaction?->stripe_payment_intent_id ?: $prestataireInvoice->payment_reference,
                'status' => 'paid',
                'paid_at' => $prestataireInvoice->paid_at ?? now(),
                'notes' => json_encode([
                    'stripe_fee' => $feesPrestataire['stripeCommission'],
                    'total_deductions' => $feesPrestataire['totalDeductions'],
                ]),
            ]);
            $prestataireInvoice->save();
        }
    }
}
