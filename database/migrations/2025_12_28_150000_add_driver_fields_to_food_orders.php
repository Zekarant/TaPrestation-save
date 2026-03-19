<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les champs livreur aux commandes food
     */
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            // Livreur assigné
            if (!Schema::hasColumn('food_orders', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('prestataire_id');
            }
            
            // Statut de la livraison
            if (!Schema::hasColumn('food_orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending')->after('driver_id');
                // pending, assigned, picked_up, in_transit, delivered, failed
            }
            
            // Heure de pickup par le livreur
            if (!Schema::hasColumn('food_orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('ready_at');
            }
            
            // Distance de livraison en km
            if (!Schema::hasColumn('food_orders', 'delivery_distance')) {
                $table->decimal('delivery_distance', 8, 2)->nullable()->after('delivery_lng');
            }
            
            // Temps estimé de livraison en minutes
            if (!Schema::hasColumn('food_orders', 'estimated_delivery_time')) {
                $table->integer('estimated_delivery_time')->nullable()->after('delivery_distance');
            }
            
            // Notes pour le livreur
            if (!Schema::hasColumn('food_orders', 'driver_notes')) {
                $table->text('driver_notes')->nullable()->after('notes');
            }
            
            // Commission du livreur
            if (!Schema::hasColumn('food_orders', 'driver_commission')) {
                $table->decimal('driver_commission', 8, 2)->nullable()->after('delivery_fee');
            }
            
            // Index pour recherche rapide
            $table->index('driver_id');
            $table->index('delivery_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['delivery_status']);
            
            $columns = [
                'driver_id',
                'delivery_status',
                'picked_up_at',
                'delivery_distance',
                'estimated_delivery_time',
                'driver_notes',
                'driver_commission',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('food_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
