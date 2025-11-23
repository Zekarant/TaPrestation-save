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
        Schema::table('prestataires', function (Blueprint $table) {
            if (!Schema::hasColumn('prestataires', 'portfolio_url')) {
                $table->string('portfolio_url')->nullable()->after('website');
            }
            
            if (!Schema::hasColumn('prestataires', 'secteur_activite')) {
                $table->string('secteur_activite')->nullable()->after('company_name');
            }
            
            if (!Schema::hasColumn('prestataires', 'competences')) {
                $table->string('competences')->nullable()->after('secteur_activite');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            if (Schema::hasColumn('prestataires', 'portfolio_url')) {
                $table->dropColumn('portfolio_url');
            }
            
            if (Schema::hasColumn('prestataires', 'secteur_activite')) {
                $table->dropColumn('secteur_activite');
            }
            
            if (Schema::hasColumn('prestataires', 'competences')) {
                $table->dropColumn('competences');
            }
        });
    }
};