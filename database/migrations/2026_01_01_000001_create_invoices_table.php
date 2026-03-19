<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // FAC-2026-0001
            $table->enum('type', ['client', 'prestataire', 'platform'])->default('client');
            
            // Relations
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Client acheteur
            $table->foreignId('prestataire_id')->nullable()->constrained()->nullOnDelete();
            
            // Item facturé (polymorphique)
            $table->string('invoiceable_type')->nullable(); // App\Models\Booking, UrgentSalePurchase, etc.
            $table->unsignedBigInteger('invoiceable_id')->nullable();
            
            // Informations facturation
            $table->string('billing_name'); // Nom complet du destinataire
            $table->string('billing_email');
            $table->string('billing_phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->default('France');
            $table->string('billing_company')->nullable();
            $table->string('billing_siret')->nullable();
            $table->string('billing_vat_number')->nullable();
            
            // Vendeur
            $table->string('seller_name');
            $table->text('seller_address')->nullable();
            $table->string('seller_siret')->nullable();
            $table->string('seller_vat_number')->nullable();
            
            // Montants
            $table->decimal('subtotal', 10, 2)->default(0); // HT
            $table->decimal('tax_rate', 5, 2)->default(20.00); // TVA %
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0); // TTC
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0); // Montant net prestataire
            $table->string('currency', 3)->default('EUR');
            
            // Statut
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled', 'refunded'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_at')->nullable();
            
            // Paiement
            $table->string('payment_method')->nullable(); // stripe, cash, transfer
            $table->string('payment_reference')->nullable(); // Stripe payment ID
            
            // Détails
            $table->text('description')->nullable();
            $table->json('line_items')->nullable(); // Détails des lignes
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            
            // PDF
            $table->string('pdf_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'type']);
            $table->index(['prestataire_id', 'type']);
            $table->index(['invoiceable_type', 'invoiceable_id']);
            $table->index('status');
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
