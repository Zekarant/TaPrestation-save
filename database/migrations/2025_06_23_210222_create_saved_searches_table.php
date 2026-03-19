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
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->json('search_criteria');
            $table->enum('alert_frequency', ['immediate', 'daily', 'weekly', 'monthly', 'never'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_alert_sent')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'is_active']);
            $table->index(['alert_frequency']);
            $table->index(['last_alert_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};