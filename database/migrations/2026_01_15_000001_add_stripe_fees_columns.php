<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Ajout des colonnes stripe_fees
 * 
 * Cette migration ajoute une colonne stripe_fees aux tables concernées
 * pour tracker les frais Stripe prélevés sur chaque transaction.
 * 
 * FORMULE STRIPE EU: 1.4% + 0.25€
 * 
 * CORRECTION DU BUG:
 * Avant: prestataire_amount = total - commission (bug quand commission = 0%)
 * Après: prestataire_amount = total - stripe_fees - commission (correct!)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter stripe_fees à escrow_transactions
        if (!Schema::hasColumn('escrow_transactions', 'stripe_fees')) {
            Schema::table('escrow_transactions', function (Blueprint $table) {
                $table->decimal('stripe_fees', 10, 2)->default(0.00)->after('commission_amount')
                    ->comment('Frais Stripe EU: 1.4% + 0.25€');
            });
        }

        // 2. Ajouter stripe_fees à food_orders
        if (!Schema::hasColumn('food_orders', 'stripe_fees')) {
            Schema::table('food_orders', function (Blueprint $table) {
                $table->decimal('stripe_fees', 10, 2)->default(0.00)->after('platform_fee')
                    ->comment('Frais Stripe EU: 1.4% + 0.25€');
            });
        }

        // 3. Ajouter stripe_fees à payment_transactions
        if (Schema::hasTable('payment_transactions') && !Schema::hasColumn('payment_transactions', 'stripe_fees')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->decimal('stripe_fees', 10, 2)->default(0.00)->after('amount')
                    ->comment('Frais Stripe EU: 1.4% + 0.25€');
            });
        }

        // 4. Ajouter stripe_fees à equipment_rental_requests
        if (Schema::hasTable('equipment_rental_requests') && !Schema::hasColumn('equipment_rental_requests', 'stripe_fees')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                $table->decimal('stripe_fees', 10, 2)->default(0.00)->after('platform_fee')
                    ->comment('Frais Stripe EU: 1.4% + 0.25€');
            });
        }

        // 5. Ajouter stripe_fees à urgent_sale_purchases
        if (Schema::hasTable('urgent_sale_purchases') && !Schema::hasColumn('urgent_sale_purchases', 'stripe_fees')) {
            Schema::table('urgent_sale_purchases', function (Blueprint $table) {
                $table->decimal('stripe_fees', 10, 2)->default(0.00)->after('platform_fee')
                    ->comment('Frais Stripe EU: 1.4% + 0.25€');
            });
        }

        // 6. Recalculer les frais Stripe pour les escrows existants (sans paiement effectué)
        // Note: Exécuté dans le SQL manuellement pour plus de contrôle
    }

    public function down(): void
    {
        // 1. Supprimer de escrow_transactions
        if (Schema::hasColumn('escrow_transactions', 'stripe_fees')) {
            Schema::table('escrow_transactions', function (Blueprint $table) {
                $table->dropColumn('stripe_fees');
            });
        }

        // 2. Supprimer de food_orders
        if (Schema::hasColumn('food_orders', 'stripe_fees')) {
            Schema::table('food_orders', function (Blueprint $table) {
                $table->dropColumn('stripe_fees');
            });
        }

        // 3. Supprimer de payment_transactions
        if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'stripe_fees')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn('stripe_fees');
            });
        }

        // 4. Supprimer de equipment_rental_requests
        if (Schema::hasTable('equipment_rental_requests') && Schema::hasColumn('equipment_rental_requests', 'stripe_fees')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                $table->dropColumn('stripe_fees');
            });
        }

        // 5. Supprimer de urgent_sale_purchases
        if (Schema::hasTable('urgent_sale_purchases') && Schema::hasColumn('urgent_sale_purchases', 'stripe_fees')) {
            Schema::table('urgent_sale_purchases', function (Blueprint $table) {
                $table->dropColumn('stripe_fees');
            });
        }
    }
};
