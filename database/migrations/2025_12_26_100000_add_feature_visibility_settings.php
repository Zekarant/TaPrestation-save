<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds feature visibility settings for controlling payment, subscription, and other features
     */
    public function up(): void
    {
        // Add default feature visibility settings
        $features = [
            // Payment Features
            ['key' => 'feature_payments_enabled', 'value' => '1', 'description' => 'Activer/désactiver tous les paiements'],
            ['key' => 'feature_stripe_enabled', 'value' => '1', 'description' => 'Activer/désactiver Stripe'],
            ['key' => 'feature_paypal_enabled', 'value' => '1', 'description' => 'Activer/désactiver PayPal'],
            ['key' => 'feature_bank_transfer_enabled', 'value' => '0', 'description' => 'Activer/désactiver virement bancaire'],
            
            // Subscription Features
            ['key' => 'feature_subscription_enabled', 'value' => '0', 'description' => 'Activer/désactiver les abonnements'],
            ['key' => 'feature_subscription_button_visible', 'value' => '0', 'description' => 'Afficher bouton abonnement'],
            
            // Prestataire Payment Connection
            ['key' => 'feature_prestataire_stripe_connect', 'value' => '0', 'description' => 'Permettre connexion Stripe prestataire'],
            ['key' => 'feature_prestataire_paypal_connect', 'value' => '0', 'description' => 'Permettre connexion PayPal prestataire'],
            ['key' => 'feature_prestataire_iban_connect', 'value' => '1', 'description' => 'Permettre saisie IBAN prestataire'],
            
            // Booking Payments
            ['key' => 'feature_booking_payment_enabled', 'value' => '1', 'description' => 'Activer paiement des réservations'],
            ['key' => 'feature_booking_deposit_enabled', 'value' => '1', 'description' => 'Activer acompte réservations'],
            
            // Food Payments
            ['key' => 'feature_food_payment_enabled', 'value' => '1', 'description' => 'Activer paiement commandes food'],
            ['key' => 'feature_food_cash_enabled', 'value' => '1', 'description' => 'Activer paiement espèces food'],
            
            // Cart & Checkout
            ['key' => 'feature_cart_enabled', 'value' => '1', 'description' => 'Activer le panier'],
            ['key' => 'feature_checkout_enabled', 'value' => '1', 'description' => 'Activer le checkout'],
            
            // Wallet & Withdrawals
            ['key' => 'feature_wallet_enabled', 'value' => '0', 'description' => 'Activer le portefeuille'],
            ['key' => 'feature_withdrawal_enabled', 'value' => '0', 'description' => 'Activer les retraits'],
        ];

        foreach ($features as $feature) {
            DB::table('settings')->updateOrInsert(
                ['key' => $feature['key']],
                ['value' => $feature['value'], 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'feature_payments_enabled',
            'feature_stripe_enabled',
            'feature_paypal_enabled',
            'feature_bank_transfer_enabled',
            'feature_subscription_enabled',
            'feature_subscription_button_visible',
            'feature_prestataire_stripe_connect',
            'feature_prestataire_paypal_connect',
            'feature_prestataire_iban_connect',
            'feature_booking_payment_enabled',
            'feature_booking_deposit_enabled',
            'feature_food_payment_enabled',
            'feature_food_cash_enabled',
            'feature_cart_enabled',
            'feature_checkout_enabled',
            'feature_wallet_enabled',
            'feature_withdrawal_enabled',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
