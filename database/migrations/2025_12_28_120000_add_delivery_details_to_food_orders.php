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
            // Coordonnées GPS pour le suivi
            if (!Schema::hasColumn('food_orders', 'delivery_lat')) {
                $table->decimal('delivery_lat', 10, 8)->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('food_orders', 'delivery_lng')) {
                $table->decimal('delivery_lng', 11, 8)->nullable()->after('delivery_lat');
            }
            
            // Détails supplémentaires pour le livreur
            if (!Schema::hasColumn('food_orders', 'delivery_floor')) {
                $table->string('delivery_floor', 50)->nullable()->after('delivery_lng');
            }
            if (!Schema::hasColumn('food_orders', 'delivery_door_code')) {
                $table->string('delivery_door_code', 50)->nullable()->after('delivery_floor');
            }
            if (!Schema::hasColumn('food_orders', 'delivery_building_info')) {
                $table->text('delivery_building_info')->nullable()->after('delivery_door_code');
            }
            if (!Schema::hasColumn('food_orders', 'delivery_contact_name')) {
                $table->string('delivery_contact_name')->nullable()->after('delivery_building_info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_lat',
                'delivery_lng',
                'delivery_floor',
                'delivery_door_code',
                'delivery_building_info',
                'delivery_contact_name'
            ]);
        });
    }
};
