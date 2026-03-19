<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les colonnes escrow et stripe transfer à food_orders
     */
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            // Colonnes escrow (si pas déjà présentes)
            if (!Schema::hasColumn('food_orders', 'escrow_status')) {
                $table->string('escrow_status')->nullable()->default('none')->after('payment_intent_id');
            }
            if (!Schema::hasColumn('food_orders', 'amount_held')) {
                $table->decimal('amount_held', 10, 2)->nullable()->after('escrow_status');
            }
            if (!Schema::hasColumn('food_orders', 'amount_released')) {
                $table->decimal('amount_released', 10, 2)->nullable()->after('amount_held');
            }
            if (!Schema::hasColumn('food_orders', 'amount_refunded')) {
                $table->decimal('amount_refunded', 10, 2)->nullable()->after('amount_released');
            }
            if (!Schema::hasColumn('food_orders', 'held_at')) {
                $table->timestamp('held_at')->nullable()->after('amount_refunded');
            }
            if (!Schema::hasColumn('food_orders', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('held_at');
            }
            if (!Schema::hasColumn('food_orders', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('released_at');
            }
            if (!Schema::hasColumn('food_orders', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('refunded_at');
            }
            
            // Stripe Transfer IDs
            if (!Schema::hasColumn('food_orders', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id')->nullable()->comment('Transfer ID pour paiement prestataire');
            }
            if (!Schema::hasColumn('food_orders', 'driver_stripe_transfer_id')) {
                $table->string('driver_stripe_transfer_id')->nullable()->comment('Transfer ID pour paiement livreur');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $columns = [
                'escrow_status',
                'amount_held',
                'amount_released',
                'amount_refunded',
                'held_at',
                'released_at',
                'refunded_at',
                'refund_reason',
                'stripe_transfer_id',
                'driver_stripe_transfer_id',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('food_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
