<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les champs pour tracker les rappels 4h avant commandes planifiées
     */
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->timestamp('client_reminder_4h_sent_at')->nullable()->after('prestataire_reminder_sent_at');
            $table->timestamp('prestataire_reminder_4h_sent_at')->nullable()->after('client_reminder_4h_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn(['client_reminder_4h_sent_at', 'prestataire_reminder_4h_sent_at']);
        });
    }
};
