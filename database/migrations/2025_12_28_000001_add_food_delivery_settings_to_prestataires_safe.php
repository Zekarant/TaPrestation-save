<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            // Paramètres de livraison food - ajouter seulement si les colonnes n'existent pas
            if (!Schema::hasColumn('prestataires', 'food_delivery_enabled')) {
                $table->boolean('food_delivery_enabled')->default(false);
            }
            if (!Schema::hasColumn('prestataires', 'food_pickup_enabled')) {
                $table->boolean('food_pickup_enabled')->default(true);
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_radius_km')) {
                $table->integer('food_delivery_radius_km')->default(5);
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_base_fee')) {
                $table->decimal('food_delivery_base_fee', 8, 2)->default(3.00);
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_fee_per_km')) {
                $table->decimal('food_delivery_fee_per_km', 8, 2)->default(0.50);
            }
            if (!Schema::hasColumn('prestataires', 'food_min_order_delivery')) {
                $table->decimal('food_min_order_delivery', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('prestataires', 'food_min_order_pickup')) {
                $table->decimal('food_min_order_pickup', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('prestataires', 'food_free_delivery_above')) {
                $table->decimal('food_free_delivery_above', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('prestataires', 'food_estimated_prep_time')) {
                $table->integer('food_estimated_prep_time')->default(30);
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_schedule')) {
                $table->json('food_delivery_schedule')->nullable();
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_instructions')) {
                $table->text('food_delivery_instructions')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Ne rien faire dans down - les colonnes peuvent être supprimées manuellement si nécessaire
    }
};
