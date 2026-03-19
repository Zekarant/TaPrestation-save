<?php

namespace App\Services;

use App\Models\FoodOrder;
use Illuminate\Support\Facades\Log;

/**
 * Audit 4.1: Logique financière d'annulation extraite de FoodOrderController.
 *
 * Gère les remboursements clients, les frais Stripe imputés au prestataire,
 * et les rollbacks en cas d'échec.
 */
class FoodOrderCancellationService
{
    /**
     * Traite toute la logique financière d'une annulation par le prestataire.
     */
    public function handleCancellationFinance(FoodOrder $foodOrder, string $reason): array
    {
        $breakdown = $foodOrder->getCancellationBreakdown('prestataire');
        $action = (string) ($breakdown['action'] ?? 'none');

        if ($action === 'none') {
            return ['success' => true, 'message' => 'Aucun remboursement carte nécessaire.'];
        }

        if ($action === 'cancel_authorization') {
            $ok = $foodOrder->cancelAuthorization($reason);
            if (!$ok) {
                Log::error("Échec annulation autorisation commande #{$foodOrder->id} (prestataire)");
                return ['success' => false, 'message' => 'Impossible d\'annuler l\'autorisation Stripe pour le moment.'];
            }
            return ['success' => true, 'message' => 'Autorisation Stripe annulée, aucun débit final client.'];
        }

        if ($action !== 'refund') {
            return ['success' => true, 'message' => ''];
        }

        $refundAmount = round((float) ($breakdown['client_refund_amount'] ?? 0), 2);
        $stripeFee = round((float) ($breakdown['stripe_fee_amount'] ?? 0), 2);
        $amountPaid = round((float) ($breakdown['amount_paid'] ?? 0), 2);

        $feeCharge = null;

        if ($stripeFee > 0) {
            $feeCharge = $this->chargeStripeFeeToPrestataire($foodOrder, $stripeFee, $reason);
            if (!($feeCharge['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => (string) ($feeCharge['message'] ?? 'Impossible d\'imputer les frais Stripe au prestataire.'),
                ];
            }
        }

        $refundOk = $refundAmount > 0 ? $foodOrder->refundPayment($reason, $refundAmount) : true;
        if (!$refundOk) {
            if (is_array($feeCharge)) {
                $this->rollbackStripeFee($feeCharge, $stripeFee,
                    "Rollback frais Stripe commande #{$foodOrder->id}: remboursement client échoué");
            }
            Log::error("Échec remboursement client commande #{$foodOrder->id} (prestataire)");
            return ['success' => false, 'message' => 'Le remboursement client a échoué.'];
        }

        return [
            'success' => true,
            'message' => 'Client remboursé ' . number_format($refundAmount, 2, ',', ' ')
                . ' €. Frais Stripe débités au prestataire: '
                . number_format($stripeFee, 2, ',', ' ')
                . ' € (montant payé: ' . number_format($amountPaid, 2, ',', ' ') . ' €).',
        ];
    }

    /**
     * Débite les frais Stripe sur le compte connecté du prestataire.
     */
    public function chargeStripeFeeToPrestataire(FoodOrder $foodOrder, float $feeAmount, string $reason): array
    {
        $feeAmount = round(max(0, $feeAmount), 2);
        if ($feeAmount <= 0) {
            return ['success' => true, 'fee_amount' => 0.0];
        }

        try {
            $foodOrder->loadMissing('prestataire');
            $stripeAccountId = trim((string) ($foodOrder->prestataire->stripe_account_id ?? ''));

            if ($stripeAccountId === '') {
                return [
                    'success' => false,
                    'message' => 'Annulation bloquée: compte Stripe prestataire manquant pour imputer les frais.',
                ];
            }

            $stripeService = app(StripePaymentService::class);
            $debit = $stripeService->debitConnectedAccountBalanceToPlatform(
                $stripeAccountId,
                $feeAmount,
                "Frais Stripe annulation food #{$foodOrder->order_number}",
                [
                    'food_order_id' => (string) $foodOrder->id,
                    'order_number' => (string) $foodOrder->order_number,
                    'type' => 'food_cancellation_fee',
                    'reason' => $reason,
                ]
            );

            return [
                'success' => true,
                'fee_amount' => $feeAmount,
                'stripe_account_id' => $stripeAccountId,
                'stripe_debit_id' => (string) ($debit->id ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::error("Impossible de débiter les frais Stripe d'annulation (food_order #{$foodOrder->id}): " . $e->getMessage());
            return ['success' => false, 'message' => "Débit des frais Stripe impossible: {$e->getMessage()}"];
        }
    }

    /**
     * Rembourse le débit de frais Stripe si le remboursement client échoue.
     */
    public function rollbackStripeFee(array $chargeContext, float $feeAmount, string $reason): void
    {
        $feeAmount = round(max(0, $feeAmount), 2);
        $debitId = trim((string) ($chargeContext['stripe_debit_id'] ?? ''));
        if ($feeAmount <= 0 || $debitId === '') {
            return;
        }

        try {
            $stripeService = app(StripePaymentService::class);
            $stripeService->refundConnectedAccountDebit(
                $debitId,
                $feeAmount,
                $reason,
                [
                    'type' => 'food_cancellation_fee_reversal',
                    'stripe_account_id' => (string) ($chargeContext['stripe_account_id'] ?? ''),
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Rollback des frais Stripe impossible (debit {$debitId}): " . $e->getMessage());
        }
    }
}
