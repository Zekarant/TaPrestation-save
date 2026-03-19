<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Permet aux clients (non-prestataires) de publier des annonces vente flash
     */
    public function up(): void
    {
        Schema::table('urgent_sales', function (Blueprint $table) {
            // user_id pour les clients qui publient sans être prestataire
            if (!Schema::hasColumn('urgent_sales', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('prestataire_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Rendre prestataire_id nullable (pour les annonces client)
            // Note: Si déjà nullable, pas besoin de changer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urgent_sales', function (Blueprint $table) {
            if (Schema::hasColumn('urgent_sales', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
