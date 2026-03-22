<?php

namespace App\Services;

use App\Models\Ambassador;
use App\Models\AmbassadorActivityLog;
use App\Models\AmbassadorCommission;
use App\Models\PrestataireAmbassadorAssignment;
use Illuminate\Support\Facades\Log;

class AmbassadorCommissionService
{
    /**
     * Record ambassador commission if the prestataire is assigned to an active ambassador.
     * Called after a successful escrow release — non-intrusive, additive only.
     */
    public static function recordIfApplicable(
        int $prestataireId,
        float $baseAmount,
        float $platformCommission,
        float $commissionRate,
        string $orderType,
        ?int $escrowTransactionId = null,
        ?int $bookingId = null
    ): ?AmbassadorCommission {
        try {
            // Check if prestataire is assigned to an ambassador
            $assignment = PrestataireAmbassadorAssignment::where('prestataire_id', $prestataireId)->first();
            if (!$assignment) {
                return null;
            }

            $ambassador = Ambassador::where('id', $assignment->ambassador_id)
                ->where('status', 'active')
                ->first();
            if (!$ambassador) {
                return null;
            }

            // Idempotency: don't create duplicate commission for same escrow
            if ($escrowTransactionId) {
                $exists = AmbassadorCommission::where('escrow_transaction_id', $escrowTransactionId)
                    ->where('ambassador_id', $ambassador->id)
                    ->exists();
                if ($exists) {
                    return null;
                }
            }

            if ($platformCommission <= 0) {
                return null;
            }

            $commission = AmbassadorCommission::create([
                'ambassador_id' => $ambassador->id,
                'prestataire_id' => $prestataireId,
                'escrow_transaction_id' => $escrowTransactionId,
                'booking_id' => $bookingId,
                'order_type' => CommissionService::normalizeType($orderType),
                'base_amount' => $baseAmount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $platformCommission,
                'status' => 'pending',
            ]);

            // Update denormalized counter
            $ambassador->increment('total_commission_earned', $platformCommission);

            // Activity log
            AmbassadorActivityLog::create([
                'ambassador_id' => $ambassador->id,
                'type' => 'commission_earned',
                'description' => "Commission de {$platformCommission}€ sur une transaction {$orderType} de {$baseAmount}€",
                'metadata' => [
                    'commission_id' => $commission->id,
                    'prestataire_id' => $prestataireId,
                    'order_type' => $orderType,
                ],
                'created_at' => now(),
            ]);

            return $commission;
        } catch (\Exception $e) {
            Log::error('AmbassadorCommissionService::recordIfApplicable failed', [
                'prestataire_id' => $prestataireId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cancel ambassador commission when an escrow is refunded.
     */
    public static function cancelForEscrow(int $escrowTransactionId): void
    {
        try {
            $commissions = AmbassadorCommission::where('escrow_transaction_id', $escrowTransactionId)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            foreach ($commissions as $commission) {
                $commission->update(['status' => 'cancelled']);

                // Decrement denormalized counter
                $ambassador = Ambassador::find($commission->ambassador_id);
                if ($ambassador) {
                    $ambassador->decrement('total_commission_earned', $commission->commission_amount);

                    AmbassadorActivityLog::create([
                        'ambassador_id' => $ambassador->id,
                        'type' => 'commission_cancelled',
                        'description' => "Commission de {$commission->commission_amount}€ annulée (remboursement escrow #{$escrowTransactionId})",
                        'metadata' => [
                            'commission_id' => $commission->id,
                            'escrow_transaction_id' => $escrowTransactionId,
                        ],
                        'created_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('AmbassadorCommissionService::cancelForEscrow failed', [
                'escrow_transaction_id' => $escrowTransactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
