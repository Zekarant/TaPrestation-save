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
        Schema::create('client_prestataire_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->timestamps();
            
            // Garantir qu'un client ne peut suivre un prestataire qu'une seule fois
            $table->unique(['client_id', 'prestataire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_prestataire_follows');
    }
};
