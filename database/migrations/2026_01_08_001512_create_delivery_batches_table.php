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
        // Table des batches (groupes de commandes)
        Schema::create('delivery_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->enum('status', ['available', 'assigned', 'picking_up', 'delivering', 'completed', 'cancelled'])->default('available');
            
            // Calculs totaux
            $table->decimal('total_distance', 8, 2)->default(0); // Km total
            $table->integer('total_time')->default(0); // Minutes estimées
            $table->decimal('driver_earnings', 8, 2)->default(0); // Ce que le livreur gagne
            $table->decimal('platform_fee', 8, 2)->default(0); // Ce que la plateforme prend
            
            // Positions
            $table->decimal('first_pickup_lat', 10, 7)->nullable();
            $table->decimal('first_pickup_lng', 10, 7)->nullable();
            $table->decimal('last_dropoff_lat', 10, 7)->nullable();
            $table->decimal('last_dropoff_lng', 10, 7)->nullable();
            
            // Timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('driver_id');
        });
        
        // Table pivot batch <-> commandes
        Schema::create('delivery_batch_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('delivery_batches')->cascadeOnDelete();
            $table->foreignId('food_order_id')->constrained('food_orders')->cascadeOnDelete();
            $table->integer('pickup_order')->default(1); // Ordre de récupération
            $table->integer('delivery_order')->default(1); // Ordre de livraison
            $table->decimal('distance_to_pickup', 8, 2)->nullable(); // Km jusqu'au resto
            $table->decimal('distance_to_dropoff', 8, 2)->nullable(); // Km jusqu'au client
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->unique(['batch_id', 'food_order_id']);
            $table->index('food_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_batch_orders');
        Schema::dropIfExists('delivery_batches');
    }
};
