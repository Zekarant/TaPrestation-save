<?php

namespace App\Services;

use App\Models\Prestataire;
use App\Models\User;

class CommissionService
{
    /**
     * Supported types: service|booking, rental|equipment, urgent_sale, food
     * Supported sides: prestataire, client
     */
    public static function ratePercent(string $type, string $side = 'prestataire'): float
    {
        $type = self::normalizeType($type);
        $side = $side === 'client' ? 'client' : 'prestataire';

        // Prestataire commission (platform fee taken from prestataire earnings)
        if ($side === 'prestataire') {
            return match ($type) {
                'service' => (float) get_setting('commission_services', '10'),
                'rental' => (float) get_setting('commission_rentals', '8'),
                'food' => (float) get_setting('commission_food', '15'),
                'urgent_sale' => (float) get_setting('commission_urgent_sales', (string) get_setting('commission_services', '10')),
                default => (float) get_setting('commission_services', '10'),
            };
        }

        // Client fee (added on top of the base price)
        return match ($type) {
            'service' => (float) get_setting('commission_client_services', '0'),
            'rental' => (float) get_setting('commission_client_rentals', '0'),
            'food' => (float) get_setting('commission_client_food', '0'),
            'urgent_sale' => (float) get_setting('commission_client_urgent_sales', '0'),
            default => 0.0,
        };
    }

    public static function isEnabledFor(string $side, ?User $client = null, ?Prestataire $prestataire = null): bool
    {
        if ($side === 'client') {
            return !((bool) ($client?->commission_client_disabled ?? false));
        }

        return !((bool) ($prestataire?->commission_prestataire_disabled ?? false));
    }

    public static function feeAmount(
        float $baseAmount,
        string $type,
        string $side = 'prestataire',
        ?User $client = null,
        ?Prestataire $prestataire = null
    ): float {
        $baseAmount = (float) $baseAmount;
        if ($baseAmount <= 0) {
            return 0.0;
        }

        $side = $side === 'client' ? 'client' : 'prestataire';

        if (!self::isEnabledFor($side, $client, $prestataire)) {
            return 0.0;
        }

        $rate = self::ratePercent($type, $side);
        if ($rate <= 0) {
            return 0.0;
        }

        return round($baseAmount * ($rate / 100), 2);
    }

    public static function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        return match ($type) {
            'booking' => 'service',
            'equipment' => 'rental',
            'rental' => 'rental',
            'service' => 'service',
            'food' => 'food',
            'urgent', 'urgent_sale', 'urgent-sale', 'flash', 'flash_sale', 'vente_flash', 'vente- flash' => 'urgent_sale',
            default => $type,
        };
    }

    /**
     * Calculer les frais Stripe (environ 1.4% + 0.25€ pour les cartes européennes)
     * Ces taux peuvent varier selon le type de carte et la région
     */
    public static function stripeFeesAmount(float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }
        
        // Frais Stripe standard EU: 1.4% + 0.25€
        $percentFee = (float) get_setting('stripe_fee_percent', '1.4');
        $fixedFee = (float) get_setting('stripe_fee_fixed', '0.25');
        
        return round(($amount * $percentFee / 100) + $fixedFee, 2);
    }

    /**
     * Calculer le montant net après TOUS les frais (Stripe + commission plateforme)
     * C'est ce que le prestataire reçoit réellement
     */
    public static function netAmountForPrestataire(
        float $clientPays,
        string $type,
        ?Prestataire $prestataire = null
    ): array {
        $stripeFees = self::stripeFeesAmount($clientPays);
        $amountAfterStripe = $clientPays - $stripeFees;
        
        $platformCommission = self::feeAmount($clientPays, $type, 'prestataire', null, $prestataire);
        $prestataireReceives = round($amountAfterStripe - $platformCommission, 2);
        
        return [
            'client_pays' => $clientPays,
            'stripe_fees' => $stripeFees,
            'amount_after_stripe' => round($amountAfterStripe, 2),
            'platform_commission' => $platformCommission,
            'platform_commission_rate' => self::ratePercent($type, 'prestataire'),
            'prestataire_receives' => max(0, $prestataireReceives),
        ];
    }
}
