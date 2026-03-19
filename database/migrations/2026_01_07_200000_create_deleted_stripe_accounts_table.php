<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archive des comptes Stripe des utilisateurs supprimés
     * Pour éviter de créer des doublons si l'utilisateur se réinscrit
     */
    public function up(): void
    {
        Schema::create('deleted_stripe_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index(); // Email de l'utilisateur (pour matching à la réinscription)
            $table->string('stripe_customer_id')->nullable()->index(); // Customer ID Stripe
            $table->string('stripe_account_id')->nullable()->index(); // Connect Account ID (si prestataire)
            $table->unsignedBigInteger('original_user_id')->nullable(); // ID original de l'utilisateur
            $table->string('user_name')->nullable(); // Nom pour référence
            $table->string('user_role')->nullable(); // client ou prestataire
            $table->json('metadata')->nullable(); // Infos supplémentaires (phone, etc.)
            $table->timestamp('deleted_at'); // Date de suppression du compte
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deleted_stripe_accounts');
    }
};
