<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les champs nécessaires pour le paiement Stripe Connect des livreurs
     */
    public function up(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            // Stripe Connect pour recevoir les paiements automatiques
            $table->string('stripe_account_id')->nullable()->after('bank_details');
            $table->boolean('stripe_onboarding_complete')->default(false)->after('stripe_account_id');
            
            // Adresse postale (pour Stripe et facturation)
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('country', 2)->default('FR')->after('postal_code');
            
            // Date de naissance (requis pour Stripe Connect)
            $table->date('birth_date')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_onboarding_complete',
                'address',
                'city',
                'postal_code',
                'country',
                'birth_date',
            ]);
        });
    }
};
