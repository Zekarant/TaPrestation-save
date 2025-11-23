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
        if (!Schema::hasTable('matching_alerts')) {
            Schema::create('matching_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_search_id')->constrained('saved_searches')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->decimal('matching_score', 5, 2)->default(0.00);
            $table->json('alert_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['saved_search_id', 'is_read']);
            $table->index(['prestataire_id']);
            $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matching_alerts');
    }
};
