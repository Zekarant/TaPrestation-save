<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute un champ 'type' aux catégories pour différencier :
     * - service : prestations de services
     * - equipment : matériel à louer
     * - sale : annonces style Le Bon Coin
     */
    public function up(): void
    {
        // Ajouter le champ type à la table categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'type')) {
                $table->enum('type', ['service', 'equipment', 'sale'])->default('service')->after('name');
            }
        });
        
        // Mettre à jour les catégories existantes comme 'service'
        DB::table('categories')->whereNull('type')->orWhere('type', '')->update(['type' => 'service']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
