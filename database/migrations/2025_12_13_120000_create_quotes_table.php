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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations du devis
            $table->string('reference_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Lignes du devis (JSON)
            // Format: [{"description": "...", "quantity": 1, "unit": "heure", "unit_price": 50.00}]
            $table->json('items');
            
            // Montants
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // TVA %
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            
            // Validité et conditions
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable(); // Notes internes
            $table->text('terms')->nullable(); // Conditions générales
            
            // Statut
            $table->enum('status', [
                'draft',      // Brouillon
                'sent',       // Envoyé au client
                'viewed',     // Consulté par le client
                'accepted',   // Accepté
                'rejected',   // Refusé
                'expired',    // Expiré
                'cancelled',  // Annulé
            ])->default('draft');
            
            // Dates de suivi
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Index
            $table->index(['prestataire_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
