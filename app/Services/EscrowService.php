<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EquipmentRentalRequest;
use App\Models\UrgentSalePurchase;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;
use Carbon\Carbon;

/**
 * Service de gestion des paiements sécurisés (Escrow)
 * 
 * ⚠️ SYSTÈME 100% AUTOMATIQUE - PAS D'INTERVENTION ADMIN
 * Les règles sont définies et exécutées automatiquement.
 * 
 * RÈGLES MÉTIER:
 * 
 * 🎯 SERVICES:
 * - Client paie → Argent bloqué
 * - Service rendu → Client confirme OU 48h → Presta payé (auto)
 * - Service non réalisé (déclaré client) → Remboursement total auto (commission rendue)
 * - Annulation dans les délais → Remboursement selon règles auto
 * - Annulation hors délai → PAS de remboursement
 * 
 * 🛠️ ÉQUIPEMENT (Location):
 * - Client paie location + caution → Tout bloqué
 * - Retour en bon état → Location → Presta, Caution → Client (auto)
 * - Dégât constaté → Presta garde tout/partie de la caution (action presta)
 * - Annulation dans les délais → Remboursement total auto
 * - Annulation hors délai → Selon % configuré auto
 * 
 * 📦 VENTE URGENTE (ANNONCES):
 * - Client paie → Argent bloqué (escrow plateforme)
 * - Client confirme réception conforme → Presta payé immédiatement
 * - Client ne fait rien après livraison → Après 48h → Presta payé auto
 * - Non-conformité signalée → Litige ouvert automatiquement
 * - Litige sans accord J+7 → Split automatique 40% client / 60% vendeur (commission rendue)
 * - Retour reçu (accord vendeur) → Remboursement total auto (commission rendue)
 * 
 * 💰 COMMISSION:
 * - Prélevée lors de la libération normale
 * - RENDUE automatiquement en cas de litige/remboursement
 * 
 * ⭐ Notes mutuelles: Client note presta + Presta note client
 */
class EscrowService
{
    private ?string $lastError = null;
    private array $enumCache = [];

    private function setLastError(?string $message): void
    {
        $this->lastError = $message;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function getMysqlEnumValues(string $table, string $column): array
    {
        $cacheKey = $table . '.' . $column;
        if (array_key_exists($cacheKey, $this->enumCache)) {
            return $this->enumCache[$cacheKey];
        }

        try {
            if (DB::getDriverName() !== 'mysql') {
                return $this->enumCache[$cacheKey] = [];
            }

            $row = DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
            if (!$row) {
                return $this->enumCache[$cacheKey] = [];
            }

            $type = (string) (($row->Type ?? $row->type ?? ''));
            if (!preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
                return $this->enumCache[$cacheKey] = [];
            }

            $values = [];
            if (preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $matches[1], $valueMatches)) {
                $values = array_map(static fn (string $v): string => stripslashes($v), $valueMatches[1]);
            }

            return $this->enumCache[$cacheKey] = $values;
        } catch (\Throwable $e) {
            Log::warning("Impossible de lire l'ENUM {$table}.{$column}: " . $e->getMessage());
            return $this->enumCache[$cacheKey] = [];
        }
    }

    private function getCompatibleRequestDepositStatus(string $wantedStatus): ?string
    {
        $status = strtolower(trim($wantedStatus));
        $enumValues = $this->getMysqlEnumValues('equipment_rental_requests', 'deposit_status');

        if (empty($enumValues)) {
            // Sécurité: si l'ENUM est inconnu (ou non lisible), ne pas écrire une valeur potentiellement invalide.
            return null;
        }

        if (in_array($status, $enumValues, true)) {
            return $status;
        }

        $fallbacks = match ($status) {
            'returned' => ['released', 'refunded', 'completed', 'done', 'paid'],
            'partial' => ['partial', 'partially_refunded', 'partially_returned', 'mixed'],
            'retained' => ['retained', 'withheld', 'kept'],
            'pending' => ['pending', 'held'],
            default => [],
        };

        foreach ($fallbacks as $candidate) {
            if (in_array($candidate, $enumValues, true)) {
                return $candidate;
            }
        }

        Log::warning(
            "Aucune valeur compatible pour deposit_status='{$status}' sur equipment_rental_requests (ENUM: " .
            implode(',', $enumValues) . ')'
        );

        return null;
    }

    private function updateEquipmentRentalRequestWithFallback(int $requestId, array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        try {
            DB::table('equipment_rental_requests')
                ->where('id', $requestId)
                ->update($updates);
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strtolower($e->getMessage());
            $hasDepositStatus = array_key_exists('deposit_status', $updates);
            $isDepositStatusTruncate = $hasDepositStatus
                && str_contains($message, 'deposit_status')
                && (str_contains($message, '1265') || str_contains($message, 'data truncated'));

            if (!$isDepositStatusTruncate) {
                throw $e;
            }

            // Fallback final: retirer deposit_status et conserver les autres champs (timestamps, état, etc.).
            unset($updates['deposit_status']);
            if (empty($updates)) {
                return;
            }

            DB::table('equipment_rental_requests')
                ->where('id', $requestId)
                ->update($updates);
        }
    }

    private function isUrgentSaleEscrow(object $escrow): bool
    {
        return isset($escrow->escrowable_type) && str_contains($escrow->escrowable_type, 'UrgentSale');
    }

    private function isServiceEscrow(object $escrow): bool
    {
        return isset($escrow->escrowable_type) && str_contains($escrow->escrowable_type, 'Booking');
    }

    private function decodeMetadata($metadata): array
    {
        if (empty($metadata)) {
            return [];
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_object($metadata) && method_exists($metadata, 'toArray')) {
            return (array) $metadata->toArray();
        }

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $metadata;
    }

    private function updateEscrowDepositMetadata(
        int $escrowId,
        float $depositAmount,
        float $depositRetained,
        float $depositReturned,
        string $depositStatus,
        ?string $reason = null
    ): void {
        try {
            $row = DB::table('escrow_transactions')->where('id', $escrowId)->select('id', 'metadata')->first();
            if (!$row) {
                return;
            }

            $meta = $this->decodeMetadata($row->metadata ?? null);
            $meta['deposit_status'] = $depositStatus;
            $meta['deposit_amount'] = round($depositAmount, 2);
            $meta['deposit_retained'] = round($depositRetained, 2);
            $meta['deposit_returned'] = round($depositReturned, 2);
            $meta['deposit_processed_at'] = now()->toIso8601String();
            if (!empty($reason)) {
                $meta['deposit_retention_reason'] = $reason;
            } else {
                unset($meta['deposit_retention_reason']);
            }

            DB::table('escrow_transactions')->where('id', $escrowId)->update([
                'metadata' => json_encode($meta),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Impossible d'enregistrer le détail caution dans metadata escrow #{$escrowId}: " . $e->getMessage());
        }
    }

    private function normalizeDepositStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));
        if (in_array($normalized, ['pending', 'held', 'returned', 'partial', 'retained'], true)) {
            return $normalized;
        }

        if (in_array($normalized, ['released', 'refunded', 'completed', 'done', 'paid'], true)) {
            return 'returned';
        }

        if (in_array($normalized, ['partially_refunded', 'partially_returned', 'mixed'], true)) {
            return 'partial';
        }

        if (in_array($normalized, ['withheld', 'kept'], true)) {
            return 'retained';
        }

        return 'pending';
    }

    private function getEquipmentDepositState(object $escrow): array
    {
        $meta = $this->decodeMetadata($escrow->metadata ?? null);
        $requestRow = null;

        if (
            !empty($escrow->escrowable_id)
            && str_contains((string) ($escrow->escrowable_type ?? ''), 'EquipmentRental')
            && TableExistenceCache::has('equipment_rental_requests')
        ) {
            $selectColumns = [];
            foreach (['deposit_status', 'deposit_retained', 'deposit_retention_reason', 'equipment_returned_at'] as $column) {
                if (Schema::hasColumn('equipment_rental_requests', $column)) {
                    $selectColumns[] = $column;
                }
            }

            if (!empty($selectColumns)) {
                $requestRow = DB::table('equipment_rental_requests')
                    ->where('id', (int) $escrow->escrowable_id)
                    ->select($selectColumns)
                    ->first();
            }
        }

        $depositAmount = $this->resolveEscrowDepositAmount($escrow);
        $status = $this->normalizeDepositStatus(
            (string) (($requestRow->deposit_status ?? null) ?: ($meta['deposit_status'] ?? 'pending'))
        );
        $retained = (float) (($requestRow->deposit_retained ?? null) ?? ($meta['deposit_retained'] ?? 0));
        $returned = array_key_exists('deposit_returned', $meta)
            ? (float) $meta['deposit_returned']
            : max(0, $depositAmount - $retained);
        $reason = trim((string) (($requestRow->deposit_retention_reason ?? null) ?: ($meta['deposit_retention_reason'] ?? '')));
        $statusProcessed = in_array($status, ['returned', 'partial', 'retained'], true);
        $processed = $statusProcessed
            || !empty($meta['deposit_processed_at'])
            || !empty($requestRow->equipment_returned_at ?? null)
            || array_key_exists('deposit_retained', $meta)
            || array_key_exists('deposit_returned', $meta)
            || ((isset($requestRow) && property_exists($requestRow, 'deposit_retained')) ? $requestRow->deposit_retained !== null : false);

        if (!$statusProcessed && $processed) {
            if ($retained <= 0) {
                $status = 'returned';
            } elseif ($depositAmount > 0 && $retained >= $depositAmount) {
                $status = 'retained';
            } else {
                $status = 'partial';
            }
        }

        return [
            'status' => $status,
            'retained' => max(0, $retained),
            'returned' => max(0, $returned),
            'reason' => $reason,
            'processed' => $processed,
            'request_row' => $requestRow,
            'meta' => $meta,
        ];
    }

    private function resolveEscrowDepositAmount(object $escrow): float
    {
        $direct = (float) ($escrow->deposit_amount ?? 0);
        if ($direct > 0) {
            return round($direct, 2);
        }

        $meta = $this->decodeMetadata($escrow->metadata ?? null);
        $candidates = [
            $meta['deposit_amount'] ?? null,
            $meta['security_deposit'] ?? null,
            $meta['deposit'] ?? null,
            $meta['caution'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $amount = (float) $candidate;
            if ($amount > 0) {
                return round($amount, 2);
            }
        }

        return 0.0;
    }

    /**
     * Détermine le montant de location (hors caution) pour un escrow équipement.
     * Priorité: demande.total_amount > metadata.rental_amount > metadata.breakdown.client_pays > escrow.total_amount.
     */
    private function resolveEquipmentRentalBaseAmount(object $escrow, ?array $metadata = null): float
    {
        $meta = $metadata ?? $this->decodeMetadata($escrow->metadata ?? null);

        $requestTotalAmount = 0.0;
        $requestFinalAmount = 0.0;

        if (TableExistenceCache::has('equipment_rental_requests') && !empty($escrow->escrowable_id)) {
            $requestColumns = [];
            if (Schema::hasColumn('equipment_rental_requests', 'total_amount')) {
                $requestColumns[] = 'total_amount';
            }
            if (Schema::hasColumn('equipment_rental_requests', 'final_amount')) {
                $requestColumns[] = 'final_amount';
            }

            if (!empty($requestColumns)) {
                $requestRow = DB::table('equipment_rental_requests')
                    ->where('id', (int) $escrow->escrowable_id)
                    ->select($requestColumns)
                    ->first();

                if ($requestRow) {
                    $requestTotalAmount = (float) ($requestRow->total_amount ?? 0);
                    $requestFinalAmount = (float) ($requestRow->final_amount ?? 0);
                }
            }
        }

        if ($requestTotalAmount > 0) {
            return round($requestTotalAmount, 2);
        }

        $metaRentalAmount = (float) ($meta['rental_amount'] ?? 0);
        if ($metaRentalAmount > 0) {
            return round($metaRentalAmount, 2);
        }

        $metaBreakdownClientPays = (float) (($meta['breakdown']['client_pays'] ?? null) ?: 0);
        if ($metaBreakdownClientPays > 0) {
            return round($metaBreakdownClientPays, 2);
        }

        $escrowTotalAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
        if ($escrowTotalAmount > 0) {
            return round($escrowTotalAmount, 2);
        }

        if ($requestFinalAmount > 0) {
            return round($requestFinalAmount, 2);
        }

        return 0.0;
    }

    /**
     * Effectue le remboursement Stripe réel de la caution au client (si applicable).
     * Idempotence forte: 1 remboursement max par escrow via un scope dédié.
     */
    private function refundEquipmentDepositToClient(object $escrow, float $depositToReturn): bool
    {
        $amount = round(max(0, $depositToReturn), 2);
        if ($amount <= 0) {
            return true;
        }

        $meta = $this->decodeMetadata($escrow->metadata ?? null);
        $alreadyRefunded = (float) ($meta['deposit_refunded_amount'] ?? 0);
        $refundStatus = strtolower((string) ($meta['deposit_refund_status'] ?? ''));
        if ($refundStatus === 'succeeded' && $alreadyRefunded >= ($amount - 0.01)) {
            return true;
        }

        $paymentIntentId = trim((string) ($escrow->stripe_payment_intent_id ?? ''));
        if ($paymentIntentId === '') {
            $this->setLastError('Aucun paiement Stripe associé: remboursement caution impossible.');
            return false;
        }

        try {
            $refundReason = 'Remboursement caution location #' . (int) $escrow->id;
            $idempotencyScope = 'equipment_deposit_refund_escrow_' . (int) $escrow->id;

            $refund = app(StripePaymentService::class)->refundPaymentIntent(
                $paymentIntentId,
                $amount,
                $refundReason,
                $idempotencyScope
            );

            $updatedMeta = $this->decodeMetadata($escrow->metadata ?? null);
            $updatedMeta['deposit_refund_status'] = 'succeeded';
            $updatedMeta['deposit_refunded_amount'] = round($amount, 2);
            $updatedMeta['deposit_refund_id'] = (string) ($refund->id ?? '');
            $updatedMeta['deposit_refunded_at'] = now()->toIso8601String();
            $updatedMeta['deposit_refund_reason'] = $refundReason;

            DB::table('escrow_transactions')->where('id', (int) $escrow->id)->update([
                'metadata' => json_encode($updatedMeta),
                'updated_at' => now(),
            ]);

            // Maintenir l'objet local à jour pour les traitements suivants dans la même requête.
            $escrow->metadata = json_encode($updatedMeta);

            Log::info("Remboursement caution Stripe effectué pour escrow #{$escrow->id}", [
                'amount' => $amount,
                'payment_intent_id' => $paymentIntentId,
                'refund_id' => (string) ($refund->id ?? ''),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Remboursement caution Stripe échoué pour escrow #{$escrow->id}: " . $e->getMessage());
            $this->setLastError('Remboursement caution échoué: ' . $e->getMessage());
            return false;
        }
    }

    private function recordDepositLedgerEntries(
        object $escrow,
        float $depositReturned,
        float $depositRetained,
        ?string $reason = null
    ): void {
        if ($depositReturned > 0) {
            DB::table('finance_ledger')->insert([
                'type' => 'deposit_returned',
                'reference_id' => $escrow->id,
                'user_id' => $escrow->client_id,
                'prestataire_id' => $escrow->prestataire_id,
                'amount' => -round($depositReturned, 2),
                'notes' => 'Remboursement caution location équipement',
                'meta' => json_encode([
                    'escrow_id' => $escrow->id,
                    'deposit_returned' => round($depositReturned, 2),
                    'reason' => $reason,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($depositRetained > 0) {
            DB::table('prestataires')->where('id', $escrow->prestataire_id)->increment('balance', $depositRetained);
            DB::table('finance_ledger')->insert([
                'type' => 'deposit_retained',
                'reference_id' => $escrow->id,
                'user_id' => $escrow->client_id,
                'prestataire_id' => $escrow->prestataire_id,
                'amount' => round($depositRetained, 2),
                'notes' => !empty($reason) ? "Caution retenue: {$reason}" : 'Caution retenue (retour équipement)',
                'meta' => json_encode([
                    'escrow_id' => $escrow->id,
                    'deposit_retained' => round($depositRetained, 2),
                    'reason' => $reason,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function findShipmentForEscrow(int $escrowId): ?object
    {
        try {
            if (!TableExistenceCache::has('shipments') || !Schema::hasColumn('shipments', 'escrow_id')) {
                return null;
            }

            return DB::table('shipments')
                ->where('escrow_id', $escrowId)
                ->first();
        } catch (\Throwable $e) {
            Log::warning("Impossible de charger l'expedition pour escrow #{$escrowId}: " . $e->getMessage());
            return null;
        }
    }

    private function escrowDisputesQuery(int $escrowId)
    {
        try {
            if (!TableExistenceCache::has('escrow_disputes')) {
                return null;
            }

            $query = DB::table('escrow_disputes');

            if (Schema::hasColumn('escrow_disputes', 'escrow_id')) {
                return $query->where('escrow_id', $escrowId);
            }

            if (Schema::hasColumn('escrow_disputes', 'escrow_transaction_id')) {
                return $query->where('escrow_transaction_id', $escrowId);
            }
        } catch (\Throwable $e) {
            Log::warning("Impossible de charger les litiges pour escrow #{$escrowId}: " . $e->getMessage());
        }

        return null;
    }

    private function insertEscrowDispute(
        int $escrowId,
        int $userId,
        string $openedBy,
        string $reason,
        string $description,
        ?array $evidence
    ): ?int {
        $evidenceJson = $evidence ? json_encode($evidence) : null;

        // Schéma "Laravel migration" (escrow_id, opened_by userId)
        try {
            return DB::table('escrow_disputes')->insertGetId([
                'escrow_id' => $escrowId,
                'opened_by' => $userId,
                'reason' => $reason,
                'description' => $description,
                'evidence' => $evidenceJson,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // fallback ci-dessous
        }

        // Schéma "SQL phpMyAdmin" (escrow_transaction_id, opened_by enum, opener_user_id)
        try {
            return DB::table('escrow_disputes')->insertGetId([
                'escrow_transaction_id' => $escrowId,
                'opened_by' => $openedBy,
                'opener_user_id' => $userId,
                'reason' => $reason,
                'description' => $description,
                'evidence' => $evidenceJson,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur insert escrow_dispute (fallback): ' . $e->getMessage());
            return null;
        }
    }

    private function closeDisputeAsAutoRefund(int $disputeId, float $refundAmount, string $notes): void
    {
        // Schéma "Laravel migration"
        try {
            DB::table('escrow_disputes')->where('id', $disputeId)->update([
                'status' => 'closed',
                'refund_amount' => $refundAmount,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        } catch (\Exception $e) {
            // fallback
        }

        // Schéma "SQL phpMyAdmin"
        try {
            DB::table('escrow_disputes')->where('id', $disputeId)->update([
                'status' => 'closed',
                'resolution' => 'refund_full',
                'resolution_amount' => $refundAmount,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur close dispute auto-refund (fallback): ' . $e->getMessage());
        }
    }

    /**
     * Créer une transaction escrow (bloquer les fonds)
     * 
     * @param string|null $type Type de transaction: 'service', 'rental', 'food', 'urgent_sale' (auto-détecté si null)
     */
    public function createEscrow(
        $escrowable,
        int $clientId,
        int $prestataireId,
        float $amount,
        float $depositAmount = 0,
        ?string $stripePaymentIntentId = null,
        ?float $platformFeeOverride = null,
        array $metadata = [],
        ?string $type = null
    ): ?object {
        try {
            $autoReleaseHours = (int) get_setting('escrow_auto_release_hours', 48);
            
            // Auto-détection du type si non fourni
            if ($type === null) {
                $className = get_class($escrowable);
                if (str_contains($className, 'FoodOrder')) {
                    $type = 'food';
                } elseif (str_contains($className, 'UrgentSale')) {
                    $type = 'urgent_sale';
                } elseif (str_contains($className, 'Equipment') || str_contains($className, 'Rental')) {
                    $type = 'rental';
                } else {
                    $type = 'service';
                }
            }
            
            // Utiliser CommissionService pour calculer le montant net (après Stripe + commission plateforme)
            $breakdown = CommissionService::netAmountForPrestataire($amount, $type);
            
            $stripeFees = $breakdown['stripe_fees'];
            $commissionRate = $breakdown['platform_commission_rate'];
            $commissionAmount = $platformFeeOverride !== null
                ? (float) $platformFeeOverride
                : $breakdown['platform_commission'];
            
            // Le prestataire reçoit: montant - frais Stripe - commission plateforme
            $prestataireAmount = $platformFeeOverride !== null
                ? round($amount - $stripeFees - $platformFeeOverride, 2)
                : $breakdown['prestataire_receives'];
            
            Log::info("Escrow: Type={$type}, Montant={$amount}€, FraisStripe={$stripeFees}€, Commission={$commissionRate}%={$commissionAmount}€, Presta={$prestataireAmount}€");

            $metadataJson = !empty($metadata) ? json_encode($metadata) : null;

            // Ajouter les frais Stripe dans les metadata
            $metadataArray = !empty($metadata) ? $metadata : [];
            $metadataArray['stripe_fees'] = $stripeFees;
            $metadataArray['breakdown'] = [
                'client_pays' => $amount,
                'stripe_fees' => $stripeFees,
                'platform_commission' => $commissionAmount,
                'prestataire_receives' => $prestataireAmount,
            ];
            $metadataJson = json_encode($metadataArray);

            $insert = [
                'client_id' => $clientId,
                'prestataire_id' => $prestataireId,
                'escrowable_type' => get_class($escrowable),
                'escrowable_id' => $escrowable->id,
                'currency' => 'EUR',
                // Statut "pending" = fonds sécurisés/bloqués (en attente de confirmation client ou auto-release)
                'status' => 'pending',
                'stripe_payment_intent_id' => $stripePaymentIntentId,
                'auto_release_at' => now()->addHours($autoReleaseHours),
                'metadata' => $metadataJson,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Compatibilité schémas escrow (ancien/nouveau)
            if (Schema::hasColumn('escrow_transactions', 'total_amount')) {
                $insert['total_amount'] = $amount;
            } elseif (Schema::hasColumn('escrow_transactions', 'amount')) {
                $insert['amount'] = $amount;
            }

            if (Schema::hasColumn('escrow_transactions', 'deposit_amount')) {
                $insert['deposit_amount'] = $depositAmount;
            }
            if (Schema::hasColumn('escrow_transactions', 'remaining_amount')) {
                $insert['remaining_amount'] = $prestataireAmount;
            }
            if (Schema::hasColumn('escrow_transactions', 'released_amount')) {
                $insert['released_amount'] = 0;
            }
            if (Schema::hasColumn('escrow_transactions', 'security_deposit')) {
                $insert['security_deposit'] = 0;
            }
            if (Schema::hasColumn('escrow_transactions', 'commission_rate')) {
                $insert['commission_rate'] = $commissionRate;
            }
            if (Schema::hasColumn('escrow_transactions', 'commission_amount')) {
                $insert['commission_amount'] = $commissionAmount;
            }
            if (Schema::hasColumn('escrow_transactions', 'platform_fee')) {
                $insert['platform_fee'] = $commissionAmount;
            }
            if (Schema::hasColumn('escrow_transactions', 'prestataire_amount')) {
                $insert['prestataire_amount'] = $prestataireAmount;
            }
            if (Schema::hasColumn('escrow_transactions', 'stripe_fees')) {
                $insert['stripe_fees'] = $stripeFees;
            }
            if (Schema::hasColumn('escrow_transactions', 'paid_at')) {
                $insert['paid_at'] = now();
            }
            if (Schema::hasColumn('escrow_transactions', 'held_at')) {
                $insert['held_at'] = now();
            }
            if (Schema::hasColumn('escrow_transactions', 'client_confirmed')) {
                $insert['client_confirmed'] = false;
            }
            if (Schema::hasColumn('escrow_transactions', 'prestataire_confirmed')) {
                $insert['prestataire_confirmed'] = false;
            }

            $escrow = DB::table('escrow_transactions')->insertGetId($insert);

            Log::info("Escrow créé #{$escrow} pour " . get_class($escrowable) . " #{$escrowable->id}");

            return DB::table('escrow_transactions')->find($escrow);
        } catch (\Exception $e) {
            Log::error("Erreur création escrow: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Client confirme la réception/prestation
     */
    public function clientConfirm(int $escrowId): bool
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['pending', 'held'])) {
                Log::warning("clientConfirm: escrow #{$escrowId} non trouvé ou statut invalide (status: " . ($escrow->status ?? 'null') . ")");
                return false;
            }

            // Mettre à jour - utiliser uniquement client_confirmed_at (pas de colonne client_confirmed)
            DB::table('escrow_transactions')->where('id', $escrowId)->update([
                'client_confirmed_at' => now(),
                'updated_at' => now(),
            ]);

            // Déterminer le type
            $escrowType = $escrow->escrowable_type ?? '';
            $isBooking = str_contains($escrowType, 'Booking');
            $isUrgentSale = str_contains($escrowType, 'UrgentSale');

            // Si le presta a aussi confirmé OU c'est un service/vente urgente, on libère.
            // Si la libération échoue, on remonte false pour éviter un faux message "paiement libéré".
            $prestataireConfirmed = !empty($escrow->prestataire_confirmed_at);
            if ($prestataireConfirmed || $isBooking || $isUrgentSale) {
                $released = $this->releaseToPrestataire($escrowId);
                if (!$released) {
                    Log::warning("clientConfirm: libération échouée pour escrow #{$escrowId} après confirmation client");
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur confirmation client escrow #{$escrowId}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Prestataire confirme (pour équipement retourné en bon état)
     */
    public function prestataireConfirm(int $escrowId, string $condition = 'good', float $retainAmount = 0): bool
    {
        try {
            $this->setLastError(null);
            $escrow = DB::table('escrow_transactions')->find($escrowId);

            if (!$escrow) {
                $this->setLastError("Escrow #{$escrowId} introuvable.");
                return false;
            }

            $escrowStatus = strtolower((string) ($escrow->status ?? ''));
            $escrowType = (string) ($escrow->escrowable_type ?? '');
            $isEquipmentEscrow = str_contains($escrowType, 'EquipmentRental');

            if (in_array($escrowStatus, ['pending', 'held', 'partial'], true)) {
                // Libérer d'abord les fonds. Si ça échoue, on ne marque pas la validation prestataire.
                $released = $this->releaseToPrestataire($escrowId);
                if (!$released) {
                    // Tolérance concurrence: si un autre flux a libéré entre temps, continuer.
                    $freshEscrow = DB::table('escrow_transactions')->find($escrowId);
                    $freshStatus = strtolower((string) ($freshEscrow->status ?? ''));
                    if (!($isEquipmentEscrow && $freshStatus === 'released')) {
                        Log::warning("prestataireConfirm: libération échouée pour escrow #{$escrowId}");
                        $this->setLastError("La libération des fonds a échoué (statut actuel: {$freshStatus}).");
                        return false;
                    }
                }
            } elseif (!($isEquipmentEscrow && $escrowStatus === 'released')) {
                $this->setLastError("Statut escrow non éligible pour validation: {$escrowStatus}.");
                return false;
            }

            $depositAmount = $this->resolveEscrowDepositAmount($escrow);

            // Gérer la caution pour équipement (y compris si l'escrow est déjà released).
            if ($depositAmount > 0 && $isEquipmentEscrow) {
                $depositHandled = $this->handleDepositReturn($escrowId, $condition, $retainAmount);
                if (!$depositHandled) {
                    Log::warning("prestataireConfirm: gestion caution échouée pour escrow #{$escrowId}");
                    $detail = $this->getLastError();
                    $this->setLastError("Traitement caution échoué." . ($detail ? " {$detail}" : ''));
                    return false;
                }
            }

            // Marquer la validation prestataire seulement après succès complet.
            if (empty($escrow->prestataire_confirmed_at)) {
                DB::table('escrow_transactions')->where('id', $escrowId)->update([
                    'prestataire_confirmed_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur confirmation presta escrow: " . $e->getMessage());
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Libérer les fonds au prestataire
     * 
     * Cette méthode effectue un Transfer Stripe réel vers le compte connecté du prestataire
     * si un paiement Stripe est associé à l'escrow.
     */
    public function releaseToPrestataire(int $escrowId, ?float $partialAmount = null): bool
    {
        try {
            $this->setLastError(null);
            // ========================================================
            // SÉCURITÉ C4: Utiliser lockForUpdate pour éviter les
            // race conditions (double Transfer Stripe)
            // ========================================================
            DB::beginTransaction();

            $escrow = DB::table('escrow_transactions')
                ->where('id', $escrowId)
                ->lockForUpdate()
                ->first();
            
            if (!$escrow) {
                DB::rollBack();
                $this->setLastError("Escrow #{$escrowId} introuvable.");
                return false;
            }

            // Idempotence: déjà libéré = succès logique (évite les faux "échecs" UI).
            if ((string) $escrow->status === 'released') {
                DB::rollBack();
                return true;
            }

            if ((string) $escrow->status === 'refunded') {
                DB::rollBack();
                $this->setLastError("Escrow #{$escrowId} déjà remboursé.");
                return false;
            }

            // Utiliser les bonnes colonnes selon la structure de la table
            $totalAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
            $commissionAmount = (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0);

            // Récupérer les frais Stripe depuis metadata
            $metadata = $this->decodeMetadata($escrow->metadata ?? null);
            $stripeFees = (float) ($metadata['stripe_fees'] ?? 0);

            $isEquipmentEscrow = str_contains((string) ($escrow->escrowable_type ?? ''), 'EquipmentRental');
            $rentalBaseAmount = $totalAmount;

            // Garde-fou anti-surversement: la base location doit rester hors caution.
            if ($isEquipmentEscrow) {
                $rentalBaseAmount = $this->resolveEquipmentRentalBaseAmount($escrow, $metadata);
            }

            if ($rentalBaseAmount <= 0) {
                $rentalBaseAmount = $totalAmount;
            }

            if ($stripeFees <= 0) {
                $stripeFees = (float) CommissionService::stripeFeesAmount($rentalBaseAmount);
            }

            $maxNetReleasable = max(0, round($rentalBaseAmount - $stripeFees - $commissionAmount, 2));

            // prestataire_amount peut être faux dans des enregistrements legacy; on le borne toujours.
            $storedPrestataireAmount = (float) ($escrow->prestataire_amount ?? $maxNetReleasable);
            $prestataireAmount = min(max(0, $storedPrestataireAmount), $maxNetReleasable);

            $storedRemaining = (float) ($escrow->remaining_amount ?? $prestataireAmount);
            $remainingAmount = min(max(0, $storedRemaining), $maxNetReleasable);

            $requestedAmount = $partialAmount ?? $prestataireAmount;
            $amountToRelease = min(max(0, $requestedAmount), $remainingAmount, $maxNetReleasable);
            $newRemainingAmount = max(0, $remainingAmount - $amountToRelease);

            $newStatus = ($newRemainingAmount <= 0) ? 'released' : 'partial';

            // Récupérer le compte Stripe Connect du prestataire
            $prestataire = DB::table('prestataires')->find($escrow->prestataire_id);
            $stripeAccountId = $prestataire?->stripe_account_id ?? null;
            $stripeTransferId = null;

            // Si paiement Stripe en mode escrow, faire le Transfer réel
            if ($escrow->stripe_payment_intent_id && $stripeAccountId && $amountToRelease > 0) {
                try {
                    $stripeService = app(StripePaymentService::class);
                    
                    // Vérifier que le compte peut recevoir des transferts
                    if ($stripeService->canReceiveTransfers($stripeAccountId)) {
                        $transfer = $stripeService->transferToConnectedAccount(
                            $stripeAccountId,
                            $amountToRelease,
                            "Libération escrow #{$escrowId}",
                            [
                                'escrow_id' => (string) $escrowId,
                                'escrowable_type' => $escrow->escrowable_type ?? '',
                                'escrowable_id' => (string) ($escrow->escrowable_id ?? ''),
                            ],
                            $escrow->stripe_payment_intent_id
                        );
                        $stripeTransferId = $transfer->id;
                        Log::info("Transfer Stripe {$stripeTransferId} créé pour escrow #{$escrowId}");
                    } else {
                        Log::warning("Compte Stripe {$stripeAccountId} ne peut pas recevoir de transfers, escrow #{$escrowId}");
                    }
                } catch (\Exception $stripeError) {
                    Log::error("Erreur Transfer Stripe pour escrow #{$escrowId}: " . $stripeError->getMessage());
                    // IMPORTANT: Si le transfer Stripe échoue, on ne libère PAS l'escrow
                    // pour éviter de créditer le prestataire sans que l'argent soit transféré
                    DB::rollBack();
                    $this->setLastError("Erreur Stripe transfer: " . $stripeError->getMessage());
                    return false;
                }
            }

            // Mettre à jour l'escrow avec le Transfer ID si disponible
            $updateData = [
                'remaining_amount' => $newRemainingAmount,
                'status' => $newStatus,
                'released_at' => $newStatus === 'released' ? now() : null,
                'updated_at' => now(),
            ];
            if ($stripeTransferId) {
                $updateData['stripe_transfer_id'] = $stripeTransferId;
            }
            DB::table('escrow_transactions')->where('id', $escrowId)->update($updateData);

            // Créditer le prestataire (balance interne pour suivi)
            DB::table('prestataires')->where('id', $escrow->prestataire_id)->increment('balance', $amountToRelease);

            // Enregistrer dans le ledger
            DB::table('finance_ledger')->insert([
                'type' => 'escrow_release',
                'reference_id' => $escrowId,
                'user_id' => $escrow->client_id,
                'prestataire_id' => $escrow->prestataire_id,
                'amount' => $amountToRelease,
                'notes' => 'Libération escrow #' . $escrowId . ($stripeTransferId ? " (Transfer: {$stripeTransferId})" : ''),
                'meta' => json_encode([
                    'escrow_id' => $escrowId,
                    'status' => $newStatus,
                    'stripe_transfer_id' => $stripeTransferId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Record ambassador commission if applicable (after commit, non-blocking)
            $escrowType = str_contains((string) ($escrow->escrowable_type ?? ''), 'EquipmentRental') ? 'rental'
                : (str_contains((string) ($escrow->escrowable_type ?? ''), 'FoodOrder') ? 'food'
                : (str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale') ? 'urgent_sale' : 'service'));
            $commissionRateUsed = $totalAmount > 0 ? round(($commissionAmount / $totalAmount) * 100, 2) : 0;
            AmbassadorCommissionService::recordIfApplicable(
                $escrow->prestataire_id,
                $totalAmount,
                $commissionAmount,
                $commissionRateUsed,
                $escrowType,
                $escrowId,
                $escrow->booking_id ?? null
            );

            Log::info("Escrow #{$escrowId} libéré: {$amountToRelease}€ au presta #{$escrow->prestataire_id}" . ($stripeTransferId ? " (Transfer: {$stripeTransferId})" : ''));

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur libération escrow: " . $e->getMessage());
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Rembourser le client
     */
    public function refundClient(int $escrowId, float $amount, string $reason = '', bool $allowFullEscrowRefund = false): bool
    {
        try {
            DB::beginTransaction();

            // Lock pour éviter les double-remboursements concurrents
            $escrow = DB::table('escrow_transactions')->where('id', $escrowId)->lockForUpdate()->first();

            if (!$escrow || $escrow->status === 'released' || $escrow->status === 'refunded') {
                DB::rollBack();
                return false;
            }

            // Utiliser les bonnes colonnes
            $totalAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
            $remainingAmount = (float) ($escrow->remaining_amount ?? $totalAmount);
            $refundableAmount = $allowFullEscrowRefund
                ? max($remainingAmount, $totalAmount)
                : $remainingAmount;
            $actualRefund = min($amount, $refundableAmount);

            // Si c'est un remboursement Stripe, faire le refund Stripe
            if ($escrow->stripe_payment_intent_id && class_exists('\Stripe\Stripe')) {
                try {
                    \Stripe\Stripe::setApiKey(config('stripe.secret'));
                    // SÉCURITÉ C5: Clé d'idempotence pour le refund escrow
                    $refundIdempotencyKey = 'escrow_refund_' . $escrowId . '_' . md5((string) $actualRefund);
                    \Stripe\Refund::create([
                        'payment_intent' => $escrow->stripe_payment_intent_id,
                        'amount' => (int)($actualRefund * 100),
                    ], [
                        'idempotency_key' => $refundIdempotencyKey,
                    ]);
                } catch (\Exception $stripeError) {
                    Log::error("Stripe refund error for escrow #{$escrowId}: " . $stripeError->getMessage());
                    // ========================================================
                    // SÉCURITÉ H3: Si le refund Stripe ÉCHOUE, on NE marque
                    // PAS l'escrow comme remboursé. Le client n'a pas reçu
                    // son argent, on ne doit pas mentir dans nos données.
                    // ========================================================
                    DB::rollBack();
                    return false;
                }
            }

            $newRemainingAmount = max(0, $remainingAmount - $actualRefund);
            $newStatus = ($newRemainingAmount <= 0) ? 'refunded' : 'partial';

            DB::table('escrow_transactions')->where('id', $escrowId)->update([
                'status' => $newStatus,
                'remaining_amount' => $newRemainingAmount,
                'refunded_at' => $newStatus === 'refunded' ? now() : null,
                'notes' => $reason,
                'updated_at' => now(),
            ]);

            // Enregistrer dans le ledger
            DB::table('finance_ledger')->insert([
                'type' => 'escrow_refund',
                'reference_id' => $escrowId,
                'user_id' => $escrow->client_id,
                'prestataire_id' => $escrow->prestataire_id,
                'amount' => -$actualRefund,
                'notes' => 'Remboursement escrow #' . $escrowId . ' - ' . $reason,
                'meta' => json_encode(['escrow_id' => $escrowId, 'reason' => $reason]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Cancel ambassador commission if applicable (after commit, non-blocking)
            AmbassadorCommissionService::cancelForEscrow($escrowId);

            Log::info("Escrow #{$escrowId} remboursé: {$actualRefund}€ au client #{$escrow->client_id}");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur remboursement escrow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gérer le retour de la caution (équipement)
     */
    public function handleDepositReturn(int $escrowId, string $condition, float $retainAmount = 0): bool
    {
        try {
            $this->setLastError(null);
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow) {
                $this->setLastError("Escrow #{$escrowId} introuvable.");
                return false;
            }

            $depositAmount = $this->resolveEscrowDepositAmount($escrow);
            if ($depositAmount <= 0) {
                $this->setLastError("Aucune caution à traiter pour escrow #{$escrowId}.");
                return false;
            }

            $retainAmount = max(0, min((float) $retainAmount, (float) $depositAmount));
            $existingState = $this->getEquipmentDepositState($escrow);
            if ($existingState['processed']) {
                $alreadyRefunded = strtolower((string) ($existingState['meta']['deposit_refund_status'] ?? '')) === 'succeeded';
                $returnedAmount = (float) ($existingState['returned'] ?? 0);
                if ($returnedAmount > 0 && !$alreadyRefunded) {
                    $depositRefunded = $this->refundEquipmentDepositToClient($escrow, $returnedAmount);
                    if (!$depositRefunded) {
                        return false;
                    }
                }

                $this->updateEscrowDepositMetadata(
                    $escrowId,
                    (float) $depositAmount,
                    (float) $existingState['retained'],
                    (float) $existingState['returned'],
                    (string) $existingState['status'],
                    $existingState['reason'] !== '' ? (string) $existingState['reason'] : null
                );
                return true;
            }

            $depositToReturn = max(0, $depositAmount - $retainAmount);
            $depositStatus = 'returned';
            if ($retainAmount > 0 && $depositToReturn > 0) {
                $depositStatus = 'partial';
            } elseif ($retainAmount > 0 && $depositToReturn <= 0) {
                $depositStatus = 'retained';
            }
            $retentionReason = $condition === 'damaged' ? 'Équipement endommagé' : null;

            // Remboursement Stripe réel de la partie caution à rendre au client.
            if ($depositToReturn > 0) {
                $depositRefunded = $this->refundEquipmentDepositToClient($escrow, $depositToReturn);
                if (!$depositRefunded) {
                    return false;
                }
            }

            // Mettre à jour le rental request (tolérant si colonnes absentes en prod).
            if (str_contains($escrow->escrowable_type, 'EquipmentRental') && TableExistenceCache::has('equipment_rental_requests')) {
                $requestUpdates = [];
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_status')) {
                    $dbStatus = $this->getCompatibleRequestDepositStatus($depositStatus);
                    if ($dbStatus !== null) {
                        $requestUpdates['deposit_status'] = $dbStatus;
                    }
                }
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_retained')) {
                    $requestUpdates['deposit_retained'] = $retainAmount;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_retention_reason')) {
                    $requestUpdates['deposit_retention_reason'] = $retentionReason;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'equipment_condition')) {
                    $requestUpdates['equipment_condition'] = $condition;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'equipment_returned_at')) {
                    $requestUpdates['equipment_returned_at'] = now();
                }
                if (Schema::hasColumn('equipment_rental_requests', 'updated_at')) {
                    $requestUpdates['updated_at'] = now();
                }
                if (!empty($requestUpdates)) {
                    $this->updateEquipmentRentalRequestWithFallback((int) $escrow->escrowable_id, $requestUpdates);
                }

                if (TableExistenceCache::has('equipment_rentals')) {
                    $rentalUpdates = ['updated_at' => now()];
                    if (Schema::hasColumn('equipment_rentals', 'deposit_retained')) {
                        $rentalUpdates['deposit_retained'] = $retainAmount;
                    }
                    if (Schema::hasColumn('equipment_rentals', 'deposit_returned')) {
                        $rentalUpdates['deposit_returned'] = $depositToReturn;
                    }
                    DB::table('equipment_rentals')
                        ->where('rental_request_id', $escrow->escrowable_id)
                        ->update($rentalUpdates);
                }
            }

            $this->updateEscrowDepositMetadata(
                $escrowId,
                (float) $depositAmount,
                (float) $retainAmount,
                (float) $depositToReturn,
                $depositStatus,
                $retentionReason
            );

            $this->recordDepositLedgerEntries($escrow, (float) $depositToReturn, (float) $retainAmount, $retentionReason);

            Log::info("Caution escrow #{$escrowId}: retenu {$retainAmount}€, rendu {$depositToReturn}€");

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur gestion caution: " . $e->getMessage());
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Traiter les escrows expirés (libération auto après 48h)
     * À appeler via un cron/scheduler
     */
    public function processExpiredEscrows(): int
    {
        $count = 0;

        $expiredEscrows = DB::table('escrow_transactions')
            ->whereIn('status', ['pending', 'held', 'partial'])
            ->where('auto_release_at', '<=', now())
            ->get();

        foreach ($expiredEscrows as $escrow) {
            // Éviter de libérer avant livraison pour les ventes urgentes expédiées
            if ($escrow->status === 'partial' && !$this->isUrgentSaleEscrow($escrow)) {
                continue;
            }

            if ($this->isUrgentSaleEscrow($escrow)) {
                $shipment = $this->findShipmentForEscrow((int) $escrow->id);
                if ($shipment && $shipment->status !== 'delivered') {
                    continue;
                }
            }

            // Vérifier qu'il n'y a pas de litige ouvert
            $disputesQuery = $this->escrowDisputesQuery((int) $escrow->id);
            $hasDispute = $disputesQuery
                ? $disputesQuery->whereIn('status', ['open', 'under_review'])->exists()
                : false;

            if (!$hasDispute) {
                $this->releaseToPrestataire($escrow->id);
                $count++;
            }
        }

        Log::info("Escrows expirés traités: {$count}");

        return $count;
    }

    /**
     * Ouvrir un litige
     */
    public function openDispute(int $escrowId, int $userId, string $reason, string $description, ?array $evidence = null): ?int
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['pending', 'held', 'partial'])) {
                return null;
            }

            // Cas spécial SERVICES: "service non réalisé"
            // ========================================================
            // SÉCURITÉ C6: Au lieu d'un remboursement AUTOMATIQUE immédiat,
            // on crée un litige qui sera examiné/validé par la plateforme
            // dans un délai de 24h. Cela empêche les clients malveillants
            // d'obtenir un service gratuit en déclarant "non réalisé".
            // ========================================================
            if ($reason === 'service_not_provided') {
                if (!$this->isServiceEscrow($escrow)) {
                    return null;
                }

                $storedReason = 'other';
                $storedDescription = 'Service non réalisé (déclaration client). ' . $description;

                $disputeId = $this->insertEscrowDispute(
                    escrowId: $escrowId,
                    userId: $userId,
                    openedBy: 'client',
                    reason: $storedReason,
                    description: $storedDescription,
                    evidence: $evidence
                );

                // Marquer l'escrow comme disputé (en attente de review)
                DB::table('escrow_transactions')->where('id', $escrowId)->update([
                    'status' => 'disputed',
                    'updated_at' => now(),
                ]);

                // Notifier le prestataire pour qu'il puisse contester dans les 24h
                Log::info("Litige 'service non réalisé' #{$disputeId} créé pour escrow #{$escrowId} - en attente de vérification (24h auto-refund)");

                // Programmer un auto-refund après 24h si le prestataire ne conteste pas
                // Ceci sera traité par processExpiredDisputes() (CRON)
                try {
                    DB::table('escrow_disputes')->where('id', $disputeId)->update([
                        'auto_resolve_at' => now()->addHours(24),
                        'auto_resolve_action' => 'refund_full',
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Colonnes optionnelles - on continue
                    Log::warning("Impossible de définir auto_resolve_at pour dispute #{$disputeId}: " . $e->getMessage());
                }

                return $disputeId;
            }

            // Vérifier le délai
            $disputeWindowHours = (int) get_setting('escrow_dispute_window_hours', 72);
            $heldAt = $escrow->paid_at ?? $escrow->held_at ?? $escrow->created_at;
            if ($heldAt && Carbon::parse($heldAt)->addHours($disputeWindowHours)->isPast()) {
                // SÉCURITÉ M4: Retourner un message explicite au lieu de null silencieux
                Log::info("Litige refusé pour escrow #{$escrowId}: fenêtre de {$disputeWindowHours}h expirée");
                return -1; // Code spécial pour "fenêtre expirée" que le controller peut détecter
            }

            DB::table('escrow_transactions')->where('id', $escrowId)->update([
                'status' => 'disputed',
                'updated_at' => now(),
            ]);

            $disputeId = $this->insertEscrowDispute(
                escrowId: $escrowId,
                userId: $userId,
                openedBy: 'client',
                // Normalize reason for schema enum safety
                reason: in_array($reason, ['not_received', 'not_as_described', 'damaged', 'partial_service', 'quality_issue', 'wrong_item', 'other'], true)
                    ? $reason
                    : 'other',
                description: $description,
                evidence: $evidence
            );

            Log::info("Litige #{$disputeId} ouvert pour escrow #{$escrowId} par user #{$userId}");

            return $disputeId;
        } catch (\Exception $e) {
            Log::error("Erreur ouverture litige: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vente urgente (livraison): marquer livré.
     * Les fonds restent bloqués jusqu'à confirmation client (ou auto-release après délai).
     */
    public function markUrgentSaleDelivered(int $escrowId): array
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);

            if (!$escrow || !$this->isUrgentSaleEscrow($escrow) || !in_array($escrow->status, ['pending', 'held', 'partial'])) {
                return ['success' => false, 'message' => 'Transaction non éligible'];
            }

            $shipment = $this->findShipmentForEscrow($escrowId);
            if (!$shipment) {
                return ['success' => false, 'message' => 'Aucune expédition trouvée'];
            }

            $autoReleaseHours = (int) get_setting('escrow_auto_release_hours', 48);

            // Idempotence: once delivery timer is set, do not reset/extend it on replay.
            if ($shipment->status === 'delivered' && !empty($escrow->auto_release_at)) {
                return [
                    'success' => true,
                    'message' => 'Livraison déjà confirmée. Le délai de libération est déjà en cours.',
                ];
            }

            // Marquer livré si nécessaire
            if ($shipment->status !== 'delivered') {
                DB::table('shipments')->where('id', $shipment->id)->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'delivery_confirmed' => true,
                    'delivery_confirmed_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Only set auto_release_at once to avoid infinite timer extension.
            if (empty($escrow->auto_release_at)) {
                DB::table('escrow_transactions')->where('id', $escrowId)->update([
                    'auto_release_at' => now()->addHours($autoReleaseHours),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Livraison confirmée. Les fonds restent bloqués jusqu\'à confirmation du client (ou libération automatique).',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur markUrgentSaleDelivered: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur technique'];
        }
    }

    /**
     * @deprecated Non utilisé - le système est 100% automatique sans intervention admin.
     * Les litiges sont résolus automatiquement par processExpiredDisputesAutoSplit() après J+7.
     * Conservé uniquement pour compatibilité ou cas exceptionnels manuels.
     */
    public function resolveDispute(int $disputeId, string $resolution, float $refundAmount, int $adminId, string $notes = ''): bool
    {
        try {
            $dispute = DB::table('escrow_disputes')->find($disputeId);
            
            if (!$dispute || $dispute->status !== 'open' && $dispute->status !== 'under_review') {
                return false;
            }

            $escrow = DB::table('escrow_transactions')->find($dispute->escrow_id);

            DB::beginTransaction();

            // Règle business: en cas de litige résolu en faveur du client (total ou partiel),
            // la plateforme rend la commission.
            if (in_array($resolution, ['resolved_client', 'resolved_partial'], true) && (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0) > 0) {
                DB::table('escrow_transactions')->where('id', $escrow->id)->update([
                    'commission_amount' => 0,
                    'updated_at' => now(),
                ]);
                $escrow = DB::table('escrow_transactions')->find($dispute->escrow_id);
            }

            // Selon la résolution
            switch ($resolution) {
                case 'resolved_client':
                    // Rembourser tout au client (commission incluse)
                    $refundableAmount = (float) ($escrow->remaining_amount ?? $escrow->total_amount ?? 0);
                    $this->refundClient($escrow->id, $refundableAmount, 'Litige résolu en faveur du client');
                    break;

                case 'resolved_prestataire':
                    // Tout au prestataire
                    $this->releaseToPrestataire($escrow->id);
                    break;

                case 'resolved_partial':
                    // Partage
                    if ($refundAmount > 0) {
                        $this->refundClient($escrow->id, $refundAmount, 'Litige résolu partiellement');
                    }
                    $this->releaseToPrestataire($escrow->id);
                    break;
            }

            DB::table('escrow_disputes')->where('id', $disputeId)->update([
                'status' => $resolution,
                'refund_amount' => $refundAmount,
                'resolved_by' => $adminId,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Litige #{$disputeId} résolu: {$resolution}, remboursé {$refundAmount}€");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur résolution litige: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculer le remboursement selon les règles d'annulation
     * 
     * RÈGLES:
     * - Services: annulation dans délai = selon règles du presta, hors délai = 0%
     * - Équipement: dans délai = 100%, hors délai = selon % configuré par presta
     * - Vente urgente: pas d'annulation possible après paiement
     */
    public function calculateCancellationRefund($escrowable): array
    {
        $now = now();
        $fullRefund = true;
        $refundPercent = 100;
        $reason = '';

        // === SERVICES (Booking) ===
        if ($escrowable instanceof Booking || (is_object($escrowable) && isset($escrowable->start_datetime))) {
            $service = $escrowable->service ?? null;
            $cancelHours = $service->cancellation_hours ?? 24;
            $startDateTime = Carbon::parse($escrowable->start_datetime);
            $hoursUntilStart = $now->diffInHours($startDateTime, false);

            if ($hoursUntilStart < 0) {
                // Service déjà passé = PAS de remboursement
                $refundPercent = 0;
                $fullRefund = false;
                $reason = 'Service déjà commencé ou passé - Aucun remboursement';
            } elseif ($hoursUntilStart < $cancelHours) {
                // Hors délai = PAS de remboursement (service sera fait ou considéré comme fait)
                $refundPercent = 0;
                $fullRefund = false;
                $reason = "Annulation hors délai (moins de {$cancelHours}h) - Aucun remboursement";
            } else {
                // Dans les délais = remboursement selon les règles du presta
                $refundPercent = $service->cancellation_refund_percentage ?? 100;
                $fullRefund = ($refundPercent === 100);
                $reason = "Annulation dans les délais - Remboursement {$refundPercent}%";
            }
        }

        // === ÉQUIPEMENT (Location) ===
        if (str_contains(get_class($escrowable), 'EquipmentRental')) {
            $cancelHours = $escrowable->equipment->cancellation_hours ?? 48;
            $startDate = Carbon::parse($escrowable->start_date);
            $hoursUntilStart = $now->diffInHours($startDate, false);

            if ($hoursUntilStart < 0) {
                // Location en cours = PAS de remboursement location, caution selon état
                $refundPercent = 0;
                $fullRefund = false;
                $reason = 'Location déjà en cours - Aucun remboursement de la location';
            } elseif ($hoursUntilStart < $cancelHours) {
                // Hors délai = remboursement selon % configuré par le presta
                $refundPercent = $escrowable->equipment->cancellation_refund_percentage ?? 0;
                $fullRefund = false;
                $reason = "Annulation hors délai - Remboursement {$refundPercent}% selon configuration";
            } else {
                // Dans les délais = remboursement total (location + caution)
                $refundPercent = 100;
                $fullRefund = true;
                $reason = 'Annulation dans les délais - Remboursement total';
            }
        }

        // === VENTE URGENTE ===
        if (str_contains(get_class($escrowable), 'UrgentSale')) {
            // Pas d'annulation possible après paiement
            // Le client doit récupérer le produit
            $refundPercent = 0;
            $fullRefund = false;
            $reason = 'Vente urgente - Pas d\'annulation après paiement. Récupérez le produit.';
        }

        return [
            'full_refund' => $fullRefund,
            'refund_percent' => $refundPercent,
            'reason' => $reason,
        ];
    }

    /**
     * Annuler avec remboursement selon les règles
     */
    public function cancelWithRefund(int $escrowId, string $cancelledBy = 'client'): array
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['pending', 'held'])) {
                return ['success' => false, 'message' => 'Escrow non annulable'];
            }

            // Récupérer l'objet lié
            // SÉCURITÉ H7: Whitelist des classes autorisées pour éviter l'exécution
            // de code arbitraire via escrowable_type compromis en DB
            $allowedEscrowableClasses = [
                \App\Models\Booking::class,
                \App\Models\EquipmentRentalRequest::class,
                \App\Models\UrgentSalePurchase::class,
                \App\Models\FoodOrder::class,
            ];
            $escrowableClass = $escrow->escrowable_type;
            if (!in_array($escrowableClass, $allowedEscrowableClasses, true)) {
                Log::error("cancelWithRefund: escrowable_type non autorisé: {$escrowableClass}");
                return ['success' => false, 'message' => 'Type d\'objet non reconnu'];
            }
            $escrowable = $escrowableClass::find($escrow->escrowable_id);

            if (!$escrowable) {
                return ['success' => false, 'message' => 'Objet introuvable'];
            }

            $refundInfo = $this->calculateCancellationRefund($escrowable);

            // Récupérer les montants avec bonnes colonnes
            $totalAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
            $commissionAmount = (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0);
            $cancelledByNorm = in_array(strtolower(trim($cancelledBy)), ['client', 'prestataire'], true)
                ? strtolower(trim($cancelledBy))
                : 'client';

            $metadata = $this->decodeMetadata($escrow->metadata ?? null);
            $stripeFeesTotal = (float) (($metadata['stripe_fees'] ?? null) ?? ($escrow->stripe_fees ?? 0));
            if ($stripeFeesTotal <= 0 && $totalAmount > 0) {
                $stripeFeesTotal = (float) CommissionService::stripeFeesAmount($totalAmount);
            }

            // Règle business: pour les ventes urgentes annulées avec remboursement, la commission est rendue.
            if ($this->isUrgentSaleEscrow($escrow) && $refundInfo['refund_percent'] > 0 && $commissionAmount > 0) {
                DB::table('escrow_transactions')->where('id', $escrowId)->update([
                    'commission_amount' => 0,
                    'updated_at' => now(),
                ]);
                $escrow = DB::table('escrow_transactions')->find($escrowId);
                $commissionAmount = 0;
            }

            $grossRefundAmount = round($totalAmount * ($refundInfo['refund_percent'] / 100), 2);
            $refundRatio = max(0, min(1, (float) $refundInfo['refund_percent'] / 100));
            $stripeFeeOnRefund = $grossRefundAmount > 0
                ? round(min($stripeFeesTotal, $stripeFeesTotal * $refundRatio), 2)
                : 0.0;

            $clientRefundAmount = $grossRefundAmount;
            $stripeFeePayer = 'none';
            $allowFullEscrowRefund = false;

            $prestataireFeeCharge = null;
            if ($grossRefundAmount > 0 && $stripeFeeOnRefund > 0) {
                if ($cancelledByNorm === 'prestataire') {
                    $chargeResult = $this->chargeCancellationStripeFeeToPrestataire(
                        $escrow,
                        $stripeFeeOnRefund,
                        'Frais Stripe annulation à la charge du prestataire'
                    );
                    if (!($chargeResult['success'] ?? false)) {
                        $chargeMessage = trim((string) ($chargeResult['message'] ?? ''));
                        return [
                            'success' => false,
                            'message' => $chargeMessage !== ''
                                ? $chargeMessage
                                : 'Impossible d\'imputer les frais Stripe au prestataire.',
                        ];
                    }
                    $stripeFeePayer = 'prestataire';
                    $allowFullEscrowRefund = true;
                    $prestataireFeeCharge = $chargeResult;
                } else {
                    $clientRefundAmount = max(0, round($grossRefundAmount - $stripeFeeOnRefund, 2));
                    $stripeFeePayer = 'client';
                }
            }

            if ($clientRefundAmount > 0) {
                $refundOk = $this->refundClient(
                    $escrowId,
                    $clientRefundAmount,
                    'Annulation: ' . $refundInfo['reason'],
                    $allowFullEscrowRefund
                );
                if (!$refundOk) {
                    if (is_array($prestataireFeeCharge)) {
                        $this->refundCancellationStripeFeeToPrestataire(
                            $escrow,
                            $prestataireFeeCharge,
                            $stripeFeeOnRefund,
                            'Annulation rollback: remboursement client échoué'
                        );
                    }
                    return ['success' => false, 'message' => 'Remboursement client échoué'];
                }
            }

            // Le reste va au presta (compensation)
            $compensationAmount = $totalAmount - $grossRefundAmount - $commissionAmount;
            if ($compensationAmount > 0) {
                $released = $this->releaseToPrestataire($escrowId, $compensationAmount);
                if (!$released) {
                    if (is_array($prestataireFeeCharge)) {
                        $this->refundCancellationStripeFeeToPrestataire(
                            $escrow,
                            $prestataireFeeCharge,
                            $stripeFeeOnRefund,
                            'Annulation rollback: compensation prestataire échouée'
                        );
                    }
                    return ['success' => false, 'message' => 'Compensation prestataire échouée'];
                }
            }

            DB::table('escrow_transactions')->where('id', $escrowId)->update([
                'status' => 'cancelled',
                'notes' => "Annulé par {$cancelledByNorm}. {$refundInfo['reason']} | Refund client: {$clientRefundAmount}€ | Frais Stripe: {$stripeFeeOnRefund}€ ({$stripeFeePayer})",
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'refund_amount' => $clientRefundAmount,
                'gross_refund_amount' => $grossRefundAmount,
                'stripe_fee_amount' => $stripeFeeOnRefund,
                'stripe_fee_payer' => $stripeFeePayer,
                'refund_percent' => $refundInfo['refund_percent'],
                'reason' => $refundInfo['reason'],
            ];
        } catch (\Exception $e) {
            Log::error("Erreur annulation escrow: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function chargeCancellationStripeFeeToPrestataire(object $escrow, float $feeAmount, string $reason): array
    {
        $feeAmount = round(max(0, $feeAmount), 2);
        if ($feeAmount <= 0) {
            return ['success' => true, 'fee_amount' => 0.0];
        }

        try {
            $prestataireId = (int) ($escrow->prestataire_id ?? 0);
            if ($prestataireId <= 0) {
                return ['success' => false, 'message' => 'Prestataire introuvable pour débit Stripe.'];
            }

            $prestataire = DB::table('prestataires')
                ->where('id', $prestataireId)
                ->select('id', 'stripe_account_id')
                ->first();
            $stripeAccountId = trim((string) ($prestataire->stripe_account_id ?? ''));
            if ($stripeAccountId === '') {
                return ['success' => false, 'message' => 'Compte Stripe prestataire manquant.'];
            }

            $stripeService = app(StripePaymentService::class);
            $debit = $stripeService->debitConnectedAccountBalanceToPlatform(
                $stripeAccountId,
                $feeAmount,
                $reason,
                [
                    'escrow_id' => (string) ((int) ($escrow->id ?? 0)),
                    'client_id' => (string) ((int) ($escrow->client_id ?? 0)),
                    'prestataire_id' => (string) $prestataireId,
                    'type' => 'stripe_cancellation_fee',
                ]
            );

            $stripeDebitId = trim((string) ($debit->id ?? ''));

            DB::table('finance_ledger')->insert([
                'type' => 'stripe_cancellation_fee',
                'reference_id' => (int) ($escrow->id ?? 0),
                'user_id' => (int) ($escrow->client_id ?? 0),
                'prestataire_id' => $prestataireId,
                'amount' => -$feeAmount,
                'notes' => $reason,
                'meta' => json_encode([
                    'escrow_id' => (int) ($escrow->id ?? 0),
                    'stripe_fee_amount' => $feeAmount,
                    'charged_to' => 'prestataire',
                    'stripe_account_id' => $stripeAccountId,
                    'stripe_debit_id' => $stripeDebitId,
                    'stripe_debit_object' => (string) ($debit->object ?? ''),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'fee_amount' => $feeAmount,
                'stripe_account_id' => $stripeAccountId,
                'stripe_debit_id' => $stripeDebitId,
                'stripe_debit_object' => (string) ($debit->object ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::error('Impossible de débiter les frais Stripe d\'annulation au prestataire: ' . $e->getMessage(), [
                'escrow_id' => $escrow->id ?? null,
                'prestataire_id' => $escrow->prestataire_id ?? null,
                'fee_amount' => $feeAmount,
            ]);

            return [
                'success' => false,
                'message' => 'Débit Stripe prestataire échoué: ' . $e->getMessage(),
            ];
        }
    }

    private function refundCancellationStripeFeeToPrestataire(
        object $escrow,
        array $chargeContext,
        float $feeAmount,
        string $reason
    ): void {
        $feeAmount = round(max(0, $feeAmount), 2);
        if ($feeAmount <= 0) {
            return;
        }

        try {
            $prestataireId = (int) ($escrow->prestataire_id ?? 0);
            if ($prestataireId <= 0) {
                return;
            }

            $stripeDebitId = trim((string) ($chargeContext['stripe_debit_id'] ?? ''));
            if ($stripeDebitId === '') {
                Log::warning('Rollback frais Stripe annulation impossible: stripe_debit_id manquant', [
                    'escrow_id' => $escrow->id ?? null,
                    'prestataire_id' => $prestataireId,
                    'fee_amount' => $feeAmount,
                ]);
                return;
            }

            $stripeService = app(StripePaymentService::class);
            $refund = $stripeService->refundConnectedAccountDebit(
                $stripeDebitId,
                $feeAmount,
                $reason,
                [
                    'escrow_id' => (string) ((int) ($escrow->id ?? 0)),
                    'prestataire_id' => (string) $prestataireId,
                    'type' => 'stripe_cancellation_fee_reversal',
                ]
            );

            DB::table('finance_ledger')->insert([
                'type' => 'stripe_cancellation_fee_reversal',
                'reference_id' => (int) ($escrow->id ?? 0),
                'user_id' => (int) ($escrow->client_id ?? 0),
                'prestataire_id' => $prestataireId,
                'amount' => $feeAmount,
                'notes' => $reason,
                'meta' => json_encode([
                    'escrow_id' => (int) ($escrow->id ?? 0),
                    'stripe_fee_amount' => $feeAmount,
                    'stripe_debit_id' => $stripeDebitId,
                    'stripe_refund_id' => (string) ($refund->id ?? ''),
                    'stripe_refund_object' => (string) ($refund->object ?? ''),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Impossible de reverser les frais Stripe d\'annulation au prestataire: ' . $e->getMessage(), [
                'escrow_id' => $escrow->id ?? null,
                'prestataire_id' => $escrow->prestataire_id ?? null,
                'fee_amount' => $feeAmount,
                'charge_context' => $chargeContext,
            ]);
        }
    }

    /**
     * Signaler un produit non conforme (Vente urgente)
     * Remboursement partiel basé sur la différence constatée
     * 
     * @param int $escrowId
     * @param int $nonConformityPercent Pourcentage de non-conformité (10-100)
     * @param string $description Description du problème
     * @param array|null $evidence Photos/preuves
     * @return array
     */
    public function reportNonConformity(int $escrowId, string $description, ?array $evidence = null): array
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['pending', 'held', 'partial'])) {
                return ['success' => false, 'message' => 'Transaction non éligible'];
            }

            // Vérifier que c'est bien une vente urgente
            if (!$this->isUrgentSaleEscrow($escrow)) {
                return ['success' => false, 'message' => 'Uniquement pour les ventes urgentes'];
            }

            $shipment = $this->findShipmentForEscrow($escrowId);
            if ($shipment && $shipment->status !== 'delivered') {
                return ['success' => false, 'message' => 'La livraison doit être marquée comme livrée avant de signaler une non-conformité'];
            }

            // Nouveau comportement: ouverture d'un litige (pas de 70/30 automatique).
            // Si pas d'accord, un split automatique est appliqué au bout de 7 jours (voir processExpiredDisputesAutoSplit).
            $disputeId = $this->openDispute(
                escrowId: $escrowId,
                userId: (int) ($escrow->client_id ?? 0),
                reason: 'not_as_described',
                description: 'Non-conformité signalée (vente urgente). ' . $description,
                evidence: $evidence
            );

            if ($disputeId) {
                return [
                    'success' => true,
                    'dispute_id' => $disputeId,
                    'refund_amount' => 0,
                    'message' => 'Non-conformité enregistrée. Le litige est ouvert. Sans accord, un partage automatique sera appliqué après 7 jours.',
                ];
            }

            return ['success' => false, 'message' => 'Erreur lors de l\'ouverture du litige'];
        } catch (\Exception $e) {
            Log::error("Erreur signalement non-conformité: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Auto-résolution des litiges après délai: split 40% client / 60% vendeur.
     * Règle: si litige ouvert et pas d'accord, après 7 jours => remboursement partiel + libération du reste.
     */
    public function processExpiredDisputesAutoSplit(): int
    {
        $count = 0;

        $days = (int) get_setting('escrow_dispute_auto_split_days', 7);
        $clientPercent = (float) get_setting('escrow_dispute_auto_split_client_percent', 40);
        $clientPercent = max(0, min(100, $clientPercent));

        $threshold = now()->subDays(max(1, $days));

        $disputes = DB::table('escrow_disputes')
            ->whereIn('status', ['open', 'under_review'])
            ->where('created_at', '<=', $threshold)
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        foreach ($disputes as $dispute) {
            $escrow = DB::table('escrow_transactions')->find($dispute->escrow_id);
            if (!$escrow || !in_array($escrow->status, ['pending', 'held', 'partial', 'disputed'], true)) {
                continue;
            }

            // Règle business: en cas de litige, la plateforme rend la commission.
            // On annule donc la commission_amount avant de calculer/refaire les libérations.
            if ($this->isUrgentSaleEscrow($escrow) && (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0) > 0) {
                try {
                    DB::table('escrow_transactions')->where('id', (int) $escrow->id)->update([
                        'commission_amount' => 0,
                        'updated_at' => now(),
                    ]);
                    $escrow = DB::table('escrow_transactions')->find($dispute->escrow_id);
                } catch (\Throwable $ignored) {
                }
            }

            // If already refunded/released, skip
            if (in_array($escrow->status, ['released', 'refunded', 'cancelled'], true)) {
                continue;
            }

            $refundable = max(0, (float) ($escrow->remaining_amount ?? $escrow->total_amount ?? 0));
            if ($refundable <= 0) {
                continue;
            }

            $refundAmount = round($refundable * ($clientPercent / 100), 2);
            $releaseAmount = round($refundable - $refundAmount, 2);

            DB::beginTransaction();
            try {
                if ($refundAmount > 0) {
                    $this->refundClient((int) $escrow->id, $refundAmount, 'Auto-split litige J+' . $days);
                }
                if ($releaseAmount > 0) {
                    $this->releaseToPrestataire((int) $escrow->id, $releaseAmount);
                }

                DB::table('escrow_disputes')->where('id', $dispute->id)->update([
                    'status' => 'resolved_partial',
                    'refund_amount' => $refundAmount,
                    'resolution_notes' => 'Sans accord: split automatique ' . round($clientPercent, 2) . '% client / ' . round(100 - $clientPercent, 2) . '% vendeur (J+' . $days . '). Commission plateforme remboursée.',
                    'resolved_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();
                $count++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Erreur auto-split litige: ' . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Vente urgente: confirmation "retour reçu" côté vendeur => remboursement total.
     * Utilisé quand les deux parties sont d'accord et que le retour est effectif.
     */
    public function confirmUrgentSaleReturnReceivedAndRefundFull(int $escrowId): array
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            if (!$escrow || !$this->isUrgentSaleEscrow($escrow)) {
                return ['success' => false, 'message' => 'Transaction non éligible'];
            }

            if (in_array($escrow->status, ['released', 'refunded', 'cancelled'], true)) {
                return ['success' => false, 'message' => 'Transaction déjà clôturée'];
            }

            $refundable = max(0, (float) ($escrow->remaining_amount ?? $escrow->total_amount ?? 0));
            if ($refundable <= 0) {
                return ['success' => false, 'message' => 'Aucun montant à rembourser'];
            }

            // Règle business: en cas de retour reçu (accord) => remboursement total incluant la commission plateforme.
            // On annule donc la commission sur l'escrow.
            if ((float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0) > 0) {
                try {
                    DB::table('escrow_transactions')->where('id', $escrowId)->update([
                        'commission_amount' => 0,
                        'updated_at' => now(),
                    ]);
                    $escrow = DB::table('escrow_transactions')->find($escrowId);
                    $refundable = max(0, (float) ($escrow->remaining_amount ?? $escrow->total_amount ?? 0));
                } catch (\Throwable $ignored) {
                }
            }

            // Mark shipment returned if present
            $shipment = $this->findShipmentForEscrow($escrowId);
            if ($shipment) {
                DB::table('shipments')->where('id', $shipment->id)->update([
                    'status' => 'returned',
                    'updated_at' => now(),
                ]);
            }

            // Perform full refund
            $ok = $this->refundClient($escrowId, $refundable, 'Retour reçu (accord) - remboursement total');
            if (!$ok) {
                return ['success' => false, 'message' => 'Remboursement impossible'];
            }

            // Close any open dispute as closed
            try {
                $disputesQuery = $this->escrowDisputesQuery($escrowId);
                if ($disputesQuery) {
                    $updates = [
                        'status' => 'closed',
                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('escrow_disputes', 'refund_amount')) {
                        $updates['refund_amount'] = $refundable;
                    }
                    if (Schema::hasColumn('escrow_disputes', 'resolution_notes')) {
                        $updates['resolution_notes'] = 'Accord: retour reçu, remboursement total. Commission plateforme remboursée.';
                    }
                    if (Schema::hasColumn('escrow_disputes', 'resolution')) {
                        $updates['resolution'] = 'refund_full';
                    }
                    if (Schema::hasColumn('escrow_disputes', 'resolution_amount')) {
                        $updates['resolution_amount'] = $refundable;
                    }

                    $disputesQuery
                        ->whereIn('status', ['open', 'under_review'])
                        ->update($updates);
                }
            } catch (\Throwable $ignored) {
            }

            return ['success' => true, 'refund_amount' => $refundable];
        } catch (\Throwable $e) {
            Log::error('Erreur retour reçu remboursement total: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur technique'];
        }
    }

    /**
     * Confirmer la bonne réception d'une vente urgente
     * Le client confirme avoir reçu le produit conforme
     */
    public function confirmUrgentSaleDelivery(int $escrowId): bool
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['pending', 'held', 'partial'])) {
                return false;
            }

            if (!$this->isUrgentSaleEscrow($escrow)) {
                return false;
            }

            $shipment = $this->findShipmentForEscrow($escrowId);
            if ($shipment && $shipment->status !== 'delivered') {
                return false;
            }

            // Confirmer côté client
            $escrowUpdates = [
                'client_confirmed_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('escrow_transactions', 'client_confirmed')) {
                $escrowUpdates['client_confirmed'] = true;
            }

            DB::table('escrow_transactions')->where('id', $escrowId)->update($escrowUpdates);

            if ($shipment) {
                $shipmentUpdates = [
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('shipments', 'conformity_validated')) {
                    $shipmentUpdates['conformity_validated'] = true;
                }
                if (Schema::hasColumn('shipments', 'conformity_validated_at')) {
                    $shipmentUpdates['conformity_validated_at'] = now();
                }

                DB::table('shipments')->where('id', $shipment->id)->update($shipmentUpdates);
            }

            // Libérer les fonds (confirmation client)
            $this->releaseToPrestataire($escrowId);

            // Mettre à jour l'achat (schema variable selon migrations)
            if (TableExistenceCache::has('urgent_sale_purchases')) {
                $updates = ['updated_at' => now()];
                if (Schema::hasColumn('urgent_sale_purchases', 'buyer_confirmed_at')) {
                    $updates['buyer_confirmed_at'] = now();
                }
                if (Schema::hasColumn('urgent_sale_purchases', 'status')) {
                    try {
                        DB::table('urgent_sale_purchases')
                            ->where('id', $escrow->escrowable_id)
                            ->update(array_merge($updates, ['status' => 'completed']));
                    } catch (\Exception $ignored) {
                        // fallback sans toucher au status (enum potentiellement différent)
                        DB::table('urgent_sale_purchases')->where('id', $escrow->escrowable_id)->update($updates);
                    }
                } else {
                    DB::table('urgent_sale_purchases')->where('id', $escrow->escrowable_id)->update($updates);
                }
            }

            Log::info("Vente urgente #{$escrow->escrowable_id} confirmée, fonds libérés au presta");

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur confirmation vente urgente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourner l'équipement avec inspection
     * 
     * @param int $escrowId
     * @param string $condition 'good', 'damaged', 'partial_damage'
     * @param float $damagePercent Pourcentage de la caution à retenir (0-100)
     * @param string|null $damageDescription Description des dégâts
     * @param array|null $photos Photos de l'état
     */
    public function returnEquipment(int $escrowId, string $condition, float $damagePercent = 0, ?string $damageDescription = null, ?array $photos = null): array
    {
        try {
            $escrow = DB::table('escrow_transactions')->find($escrowId);
            
            if (!$escrow || !in_array($escrow->status, ['held', 'partial', 'released'])) {
                return ['success' => false, 'message' => 'Transaction non éligible'];
            }

            if (!str_contains($escrow->escrowable_type, 'EquipmentRental')) {
                return ['success' => false, 'message' => 'Uniquement pour les locations'];
            }

            $existingState = $this->getEquipmentDepositState($escrow);
            if ($existingState['processed']) {
                $alreadyRefunded = strtolower((string) ($existingState['meta']['deposit_refund_status'] ?? '')) === 'succeeded';
                $returnedAmount = (float) ($existingState['returned'] ?? 0);
                if ($returnedAmount > 0 && !$alreadyRefunded) {
                    $depositRefunded = $this->refundEquipmentDepositToClient($escrow, $returnedAmount);
                    if (!$depositRefunded) {
                        return [
                            'success' => false,
                            'message' => $this->getLastError() ?? 'Remboursement caution impossible.',
                        ];
                    }
                }

                return [
                    'success' => true,
                    'condition' => $condition,
                    'rental_released' => 0,
                    'deposit_retained' => (float) $existingState['retained'],
                    'deposit_returned_to_client' => (float) $existingState['returned'],
                    'message' => 'Caution déjà traitée pour cette location.',
                ];
            }

            $depositAmount = $this->resolveEscrowDepositAmount($escrow);
            $damagePercent = min(100, max(0, $damagePercent));
            $retainedAmount = round($depositAmount * ($damagePercent / 100), 2);
            $returnedToClient = $depositAmount - $retainedAmount;
            $depositStatus = $condition === 'good'
                ? 'returned'
                : ($retainedAmount >= $depositAmount ? 'retained' : 'partial');

            DB::beginTransaction();

            // Remboursement Stripe réel de la caution (si tout ou partie doit être rendu).
            // Déplacé DANS la transaction pour garantir la cohérence DB/Stripe.
            if ($returnedToClient > 0) {
                $depositRefunded = $this->refundEquipmentDepositToClient($escrow, $returnedToClient);
                if (!$depositRefunded) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => $this->getLastError() ?? 'Remboursement caution impossible.',
                    ];
                }
            }

            // Mettre à jour le rental request (tolérant si colonnes absentes en prod).
            if (TableExistenceCache::has('equipment_rental_requests')) {
                $requestUpdates = [];
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_status')) {
                    $dbStatus = $this->getCompatibleRequestDepositStatus($depositStatus);
                    if ($dbStatus !== null) {
                        $requestUpdates['deposit_status'] = $dbStatus;
                    }
                }
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_retained')) {
                    $requestUpdates['deposit_retained'] = $retainedAmount;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'deposit_retention_reason')) {
                    $requestUpdates['deposit_retention_reason'] = $damageDescription;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'equipment_condition')) {
                    $requestUpdates['equipment_condition'] = $condition;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'equipment_returned_at')) {
                    $requestUpdates['equipment_returned_at'] = now();
                }
                if (Schema::hasColumn('equipment_rental_requests', 'inspection_photos')) {
                    $requestUpdates['inspection_photos'] = $photos ? json_encode($photos) : null;
                }
                if (Schema::hasColumn('equipment_rental_requests', 'updated_at')) {
                    $requestUpdates['updated_at'] = now();
                }
                if (!empty($requestUpdates)) {
                    $this->updateEquipmentRentalRequestWithFallback((int) $escrow->escrowable_id, $requestUpdates);
                }
            }

            if (TableExistenceCache::has('equipment_rentals')) {
                $rentalUpdates = ['updated_at' => now()];
                if (Schema::hasColumn('equipment_rentals', 'deposit_retained')) {
                    $rentalUpdates['deposit_retained'] = $retainedAmount;
                }
                if (Schema::hasColumn('equipment_rentals', 'deposit_returned')) {
                    $rentalUpdates['deposit_returned'] = $returnedToClient;
                }
                DB::table('equipment_rentals')
                    ->where('rental_request_id', $escrow->escrowable_id)
                    ->update($rentalUpdates);
            }

            $this->updateEscrowDepositMetadata(
                $escrowId,
                (float) $depositAmount,
                (float) $retainedAmount,
                (float) $returnedToClient,
                $depositStatus,
                $damageDescription
            );

            // Libérer le montant de location au presta (après frais Stripe + commission)
            $commissionAmount = (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0);
             
            // Récupérer les frais Stripe depuis metadata ou recalculer
            $metadata = $this->decodeMetadata($escrow->metadata ?? null);
            $rentalBaseAmount = $this->resolveEquipmentRentalBaseAmount($escrow, $metadata);
            if ($rentalBaseAmount <= 0) {
                $rentalBaseAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
            }
            $stripeFees = (float) ($metadata['stripe_fees'] ?? CommissionService::stripeFeesAmount($rentalBaseAmount));
             
            // Montant à libérer = total - frais Stripe - commission
            $rentalAmount = max(0, round($rentalBaseAmount - $stripeFees - $commissionAmount, 2));
            if (!in_array((string) $escrow->status, ['released', 'refunded'], true)) {
                $this->releaseToPrestataire($escrowId, $rentalAmount);
            }

            $this->recordDepositLedgerEntries($escrow, (float) $returnedToClient, (float) $retainedAmount, $damageDescription);

            DB::commit();

            return [
                'success' => true,
                'condition' => $condition,
                'rental_released' => $rentalAmount,
                'deposit_retained' => $retainedAmount,
                'deposit_returned_to_client' => $returnedToClient,
                'message' => $condition === 'good' 
                    ? 'Équipement rendu en bon état. Location payée, caution restituée.'
                    : "Équipement endommagé. {$retainedAmount}€ de caution retenus."
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur retour équipement: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
