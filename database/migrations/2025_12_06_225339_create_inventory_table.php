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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('category')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('initial_quantity')->nullable();
            $table->string('unit')->default('unité');
            $table->decimal('cost_per_unit', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('reorder_level')->default(1);
            $table->string('supplier')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->default('available');
            $table->json('photos')->nullable();
            $table->timestamp('last_restocked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'quantity']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
