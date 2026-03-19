<?php

namespace App\Support;

use Illuminate\Support\Str;

final class PaymentMetadataNormalizer
{
    public static function normalize(array $metadata): array
    {
        if (empty($metadata)) {
            return [];
        }

        $normalized = $metadata;

        foreach ($metadata as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $canonical = self::canonicalKey($key);
            if ($canonical === '') {
                continue;
            }

            if (!array_key_exists($canonical, $normalized) || $normalized[$canonical] === null || $normalized[$canonical] === '') {
                $normalized[$canonical] = $value;
            }
        }

        $normalizedTxType = self::normalizeTransactionType($normalized['tx_type'] ?? ($normalized['type_transaction'] ?? null));
        $normalizedPaymentType = self::normalizePaymentType($normalized['payment_type'] ?? ($normalized['type_de_paiement'] ?? null));

        if ($normalizedTxType !== '') {
            $normalized['tx_type'] = $normalizedTxType;
        }

        if ($normalizedPaymentType !== '') {
            $normalized['payment_type'] = $normalizedPaymentType;
        }

        if (empty($normalized['payment_type']) && !empty($normalized['tx_type'])) {
            $normalized['payment_type'] = match ($normalized['tx_type']) {
                'deposit' => 'deposit',
                'balance' => 'balance',
                'refund' => 'refund',
                default => 'full',
            };
        }

        if (empty($normalized['tx_type']) && !empty($normalized['payment_type'])) {
            $normalized['tx_type'] = match ($normalized['payment_type']) {
                'deposit' => 'deposit',
                'balance' => 'balance',
                'refund' => 'refund',
                default => 'payment',
            };
        }

        return $normalized;
    }

    public static function normalizeTransactionType(?string $type): string
    {
        $slug = self::slugify($type);

        return match ($slug) {
            'deposit', 'acompte', 'avance' => 'deposit',
            'balance', 'solde' => 'balance',
            'refund', 'remboursement', 'rembourse' => 'refund',
            'payment', 'paiement', 'full', 'complet', 'complete' => 'payment',
            default => '',
        };
    }

    public static function normalizePaymentType(?string $type): string
    {
        $slug = self::slugify($type);

        return match ($slug) {
            'deposit', 'acompte', 'avance' => 'deposit',
            'balance', 'solde' => 'balance',
            'refund', 'remboursement', 'rembourse' => 'refund',
            'payment', 'paiement', 'full', 'complet', 'complete' => 'full',
            default => '',
        };
    }

    private static function canonicalKey(string $key): string
    {
        $slug = self::slugify($key);

        return match ($slug) {
            'user_id', 'id_utilisateur', 'id_de_l_utilisateur', 'id_user' => 'user_id',
            'user_email', 'email_utilisateur', 'email_de_l_utilisateur' => 'user_email',
            'booking_id', 'id_reservation', 'reservation_id' => 'booking_id',
            'rental_request_id',
            'equipment_rental_request_id',
            'id_demande_de_location',
            'id_demande_location',
            'demande_de_location_id' => 'rental_request_id',
            'food_order_id', 'commande_food_id', 'id_commande_food' => 'food_order_id',
            'tx_type', 'transaction_type', 'type_transaction' => 'tx_type',
            'payment_type', 'type_de_paiement', 'type_paiement' => 'payment_type',
            'payment_requirement', 'exigence_de_paiement' => 'payment_requirement',
            'security_deposit', 'depot_de_garantie', 'caution' => 'security_deposit',
            'rental_amount', 'montant_de_location' => 'rental_amount',
            'connected_account_id', 'id_compte_connecte', 'stripe_account_id' => 'connected_account_id',
            'escrow', 'entiercement' => 'escrow',
            default => $key,
        };
    }

    private static function slugify(?string $value): string
    {
        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return '';
        }

        $ascii = Str::ascii(mb_strtolower($stringValue, 'UTF-8'));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $ascii) ?? '';

        return trim($slug, '_');
    }
}
