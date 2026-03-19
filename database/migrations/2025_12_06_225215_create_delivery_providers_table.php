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
        Schema::create('delivery_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('api_key')->nullable();
            $table->string('api_url')->nullable();
            $table->json('coverage_areas')->nullable();
            $table->decimal('base_rate', 10, 2);
            $table->decimal('per_km_rate', 10, 2);
            $table->enum('delivery_type', ['standard', 'express', 'overnight'])->nullable();
            $table->integer('estimated_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->json('contact_info')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_providers');
    }
};
