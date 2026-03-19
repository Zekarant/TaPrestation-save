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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_provider_id')->constrained()->onDelete('restrict');
            $table->string('tracking_number')->unique();
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'delivered', 'failed', 'cancelled'])->default('pending');
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('shipping_cost', 10, 2);
            $table->string('pickup_address');
            $table->string('delivery_address');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->json('tracking_history')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['tracking_number', 'status']);
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
