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
        Schema::create('client_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade'); // Client évalué
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade'); // Prestataire qui note
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null'); // Réservation liée
            $table->tinyInteger('rating')->unsigned(); // Note de 1 à 5
            $table->text('comment')->nullable(); // Commentaire optionnel
            $table->enum('punctuality', ['excellent', 'good', 'average', 'poor'])->nullable(); // Ponctualité
            $table->enum('communication', ['excellent', 'good', 'average', 'poor'])->nullable(); // Communication
            $table->enum('respect', ['excellent', 'good', 'average', 'poor'])->nullable(); // Respect
            $table->boolean('would_work_again')->default(true); // Travaillerait à nouveau avec ce client
            $table->timestamps();
            
            // Un prestataire ne peut noter un client qu'une fois par réservation
            $table->unique(['prestataire_id', 'booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_reviews');
    }
};
