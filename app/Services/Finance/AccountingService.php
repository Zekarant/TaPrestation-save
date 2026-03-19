<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Services\CommissionService;
use Exception;

class AccountingService
{
    /**
     * Record a client payment transaction, calculate commission and update prestataire balance.
     * $data must contain: user_id, prestataire_id, amount, reference (optional), type (optional)
     */
    public function recordClientPayment(array $data): array
    {
        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new Exception('Invalid amount');
        }

        // Use CommissionService for type-aware commission rates
        $type = $data['type'] ?? 'service';
        $prestataire = null;
        if (!empty($data['prestataire_id'])) {
            $prestataire = \App\Models\Prestataire::find($data['prestataire_id']);
        }
        $commission = CommissionService::feeAmount($amount, $type, 'prestataire', null, $prestataire);
        $prestataireShare = round($amount - $commission, 2);

        DB::beginTransaction();
        try {
            // Insert into transactions table (legacy schema expected)
            $txId = DB::table('transactions')->insertGetId([
                'user_id' => $data['user_id'] ?? null,
                'prestataire_id' => $data['prestataire_id'] ?? null,
                'amount' => $amount,
                'commission' => $commission,
                'type' => $data['type'] ?? 'payment',
                'reference' => $data['reference'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update prestataire balance (add net share)
            if (!empty($data['prestataire_id'])) {
                $before = DB::table('prestataires')->where('id', $data['prestataire_id'])->value('balance') ?: 0;
                DB::table('prestataires')->where('id', $data['prestataire_id'])->increment('balance', $prestataireShare);
                $after = DB::table('prestataires')->where('id', $data['prestataire_id'])->value('balance');

                // Insert ledger entry
                DB::table('finance_ledger')->insert([
                    'type' => 'payment',
                    'reference_id' => $txId,
                    'user_id' => $data['user_id'] ?? null,
                    'prestataire_id' => $data['prestataire_id'] ?? null,
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'notes' => 'Payment received, commission ' . $commission,
                    'meta' => json_encode(['commission' => $commission, 'prestataire_share' => $prestataireShare]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return ['transaction_id' => $txId, 'commission' => $commission, 'prestataire_share' => $prestataireShare];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
