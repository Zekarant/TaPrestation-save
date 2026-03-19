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
        Schema::table('services', function (Blueprint $table) {
            // Durée estimée du service pour la gestion des réservations
            $table->integer('estimated_duration')->nullable()->default(1)->after('duration');
            $table->enum('duration_unit', ['minutes', 'hours', 'days'])->default('hours')->after('estimated_duration');
            $table->integer('buffer_time')->nullable()->default(15)->after('duration_unit')->comment('Temps tampon entre réservations en minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['estimated_duration', 'duration_unit', 'buffer_time']);
        });
    }
};
