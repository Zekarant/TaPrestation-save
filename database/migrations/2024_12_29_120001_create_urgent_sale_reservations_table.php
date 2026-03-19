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
        if (!Schema::hasTable('urgent_sale_reservations')) {
            Schema::create('urgent_sale_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('urgent_sale_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->integer('quantity')->default(1);
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
                $table->text('message')->nullable();
                $table->text('seller_notes')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                
                // Index pour performances
                $table->index(['urgent_sale_id', 'status']);
                $table->index(['client_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urgent_sale_reservations');
    }
};
