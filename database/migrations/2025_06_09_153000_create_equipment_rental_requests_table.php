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
        Schema::create('equipment_rental_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            
            // Dates et durée
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_days');
            $table->integer('duration_hours')->nullable();
            
            // Montants
            $table->decimal('unit_price', 8, 2); // Prix unitaire au moment de la demande
            $table->decimal('total_amount', 10, 2);
            $table->decimal('security_deposit', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('final_amount', 10, 2); // Montant total avec frais
            
            // Adresses
            $table->text('delivery_address')->nullable();
            $table->text('pickup_address')->nullable();
            $table->boolean('delivery_required')->default(false);
            $table->boolean('pickup_required')->default(false);
            
            // Messages et communication
            $table->text('client_message')->nullable();
            $table->text('prestataire_response')->nullable();
            $table->text('special_requirements')->nullable();
            $table->json('client_contact_info')->nullable();
            
            // Statut et gestion
            $table->enum('status', [
                'pending', 'accepted', 'rejected', 'cancelled', 
                'expired', 'confirmed', 'in_preparation'
            ])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->string('source')->default('web'); // web, mobile, api
            $table->ipAddress('client_ip')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['equipment_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['prestataire_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['status', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_rental_requests');
    }
};