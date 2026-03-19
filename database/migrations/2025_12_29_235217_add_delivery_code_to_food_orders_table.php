<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            // Code de confirmation de livraison (4 chiffres)
            $table->string('delivery_code', 4)->nullable()->after('delivery_status');
            $table->timestamp('code_verified_at')->nullable()->after('delivery_code');
            
            // Paiements automatiques
            $table->timestamp('prestataire_paid_at')->nullable();
            $table->timestamp('driver_paid_at')->nullable();
            $table->decimal('prestataire_payout', 10, 2)->nullable();
            $table->decimal('driver_payout', 10, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_code',
                'code_verified_at',
                'prestataire_paid_at',
                'driver_paid_at',
                'prestataire_payout',
                'driver_payout',
                'platform_fee'
            ]);
        });
    }
};
