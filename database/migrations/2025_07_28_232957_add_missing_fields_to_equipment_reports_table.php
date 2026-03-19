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
        Schema::table('equipment_reports', function (Blueprint $table) {
            // Les champs reporter_ip et user_agent existent déjà
            // Modifier contact_info pour être de type json (déjà json dans la migration initiale)
            // Aucune modification nécessaire
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_reports', function (Blueprint $table) {
            // Aucune modification à annuler
        });
    }
};
