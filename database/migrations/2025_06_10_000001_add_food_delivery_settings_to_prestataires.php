<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            // Paramètres de livraison food
            if (!Schema::hasColumn('prestataires', 'food_delivery_enabled')) {
                $afterCol = Schema::hasColumn('prestataires', 'video_storage_used_mb') ? 'video_storage_used_mb' : null;
                $col = $table->boolean('food_delivery_enabled')->default(false);
                if ($afterCol)
                    $col->after($afterCol);
            }
            if (!Schema::hasColumn('prestataires', 'food_pickup_enabled')) {
                $table->boolean('food_pickup_enabled')->default(true)->after('food_delivery_enabled');
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_radius_km')) {
                $table->integer('food_delivery_radius_km')->default(5)->after('food_pickup_enabled');
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_base_fee')) {
                $table->decimal('food_delivery_base_fee', 8, 2)->default(3.00)->after('food_delivery_radius_km');
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_fee_per_km')) {
                $table->decimal('food_delivery_fee_per_km', 8, 2)->default(0.50)->after('food_delivery_base_fee');
            }
            if (!Schema::hasColumn('prestataires', 'food_min_order_delivery')) {
                $table->decimal('food_min_order_delivery', 8, 2)->nullable()->after('food_delivery_fee_per_km');
            }
            if (!Schema::hasColumn('prestataires', 'food_min_order_pickup')) {
                $table->decimal('food_min_order_pickup', 8, 2)->nullable()->after('food_min_order_delivery');
            }
            if (!Schema::hasColumn('prestataires', 'food_free_delivery_above')) {
                $table->decimal('food_free_delivery_above', 8, 2)->nullable()->after('food_min_order_pickup');
            }
            if (!Schema::hasColumn('prestataires', 'food_estimated_prep_time')) {
                $table->integer('food_estimated_prep_time')->default(30)->after('food_free_delivery_above');
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_schedule')) {
                $table->json('food_delivery_schedule')->nullable()->after('food_estimated_prep_time');
            }
            if (!Schema::hasColumn('prestataires', 'food_delivery_instructions')) {
                $table->text('food_delivery_instructions')->nullable()->after('food_delivery_schedule');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            $table->dropColumn([
                'food_delivery_enabled',
                'food_pickup_enabled',
                'food_delivery_radius_km',
                'food_delivery_base_fee',
                'food_delivery_fee_per_km',
                'food_min_order_delivery',
                'food_min_order_pickup',
                'food_free_delivery_above',
                'food_estimated_prep_time',
                'food_delivery_schedule',
                'food_delivery_instructions'
            ]);
        });
    }
};
