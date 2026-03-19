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
        Schema::table('prestataire_availabilities', function (Blueprint $table) {
            // Ajouter les colonnes de pause (optionnelles)
            if (!Schema::hasColumn('prestataire_availabilities', 'break_start')) {
                $table->time('break_start')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('prestataire_availabilities', 'break_end')) {
                $table->time('break_end')->nullable()->after('break_start');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataire_availabilities', function (Blueprint $table) {
            if (Schema::hasColumn('prestataire_availabilities', 'break_start')) {
                $table->dropColumn('break_start');
            }
            if (Schema::hasColumn('prestataire_availabilities', 'break_end')) {
                $table->dropColumn('break_end');
            }
        });
    }
};
