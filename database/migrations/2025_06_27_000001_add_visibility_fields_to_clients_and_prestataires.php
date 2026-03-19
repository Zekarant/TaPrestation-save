<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les champs de visibilité pour email et téléphone
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('email_visible')->default(false)->after('phone')->comment('Si true, email visible après premier échange');
            $table->boolean('phone_visible')->default(false)->after('email_visible')->comment('Si true, téléphone visible après premier échange');
        });

        Schema::table('prestataires', function (Blueprint $table) {
            $table->boolean('email_visible')->default(false)->after('phone')->comment('Si true, email visible sur profil');
            $table->boolean('phone_visible')->default(true)->after('email_visible')->comment('Si true, téléphone visible sur profil (par défaut oui pour prestataires)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['email_visible', 'phone_visible']);
        });

        Schema::table('prestataires', function (Blueprint $table) {
            $table->dropColumn(['email_visible', 'phone_visible']);
        });
    }
};
