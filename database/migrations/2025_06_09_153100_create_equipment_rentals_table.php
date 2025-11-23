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
        Schema::create('equipment_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('rental_number')->unique();
            $table->foreignId('rental_request_id')->constrained('equipment_rental_requests')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            
            // Dates et horaires
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('actual_start_datetime')->nullable();
            $table->timestamp('actual_end_datetime')->nullable();
            $table->integer('planned_duration_days');
            $table->integer('actual_duration_days')->nullable();
            
            // Montants et facturation
            $table->decimal('unit_price', 8, 2);
            $table->decimal('base_amount', 10, 2);
            $table->decimal('security_deposit', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('pickup_fee', 8, 2)->default(0);
            $table->decimal('late_fee', 8, 2)->default(0);
            $table->decimal('damage_fee', 8, 2)->default(0);
            $table->decimal('cleaning_fee', 8, 2)->default(0);
            $table->decimal('additional_fees', 8, 2)->default(0);
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->decimal('deposit_returned', 8, 2)->default(0);
            $table->decimal('deposit_retained', 8, 2)->default(0);
            
            // Adresses et livraison
            $table->text('delivery_address')->nullable();
            $table->text('pickup_address')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('picked_up_by')->nullable()->constrained('users')->onDelete('set null');
            
            // État et condition
            $table->enum('status', [
                'confirmed', 'in_preparation', 'ready_for_delivery', 
                'delivered', 'in_use', 'ready_for_pickup', 
                'returned', 'completed', 'cancelled', 'disputed'
            ])->default('confirmed');
            $table->enum('payment_status', [
                'pending', 'partial', 'paid', 'refunded', 'disputed'
            ])->default('pending');
            $table->text('delivery_notes')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->text('condition_notes')->nullable();
            $table->json('delivery_photos')->nullable();
            $table->json('pickup_photos')->nullable();
            
            // Évaluation et retour
            $table->enum('equipment_condition_delivered', ['excellent', 'very_good', 'good', 'fair'])->nullable();
            $table->enum('equipment_condition_returned', ['excellent', 'very_good', 'good', 'fair'])->nullable();
            $table->text('damage_report')->nullable();
            $table->json('damage_photos')->nullable();
            $table->boolean('late_return')->default(false);
            $table->integer('late_days')->default(0);
            $table->integer('late_hours')->default(0);
            
            // Signatures et validation
            $table->text('client_signature_delivery')->nullable();
            $table->text('client_signature_pickup')->nullable();
            $table->text('prestataire_signature_delivery')->nullable();
            $table->text('prestataire_signature_pickup')->nullable();
            $table->timestamp('client_validated_delivery_at')->nullable();
            $table->timestamp('client_validated_pickup_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Index
            $table->index(['equipment_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['prestataire_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['status', 'payment_status']);
            $table->index(['actual_end_datetime', 'late_return']);
            $table->index('rental_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_rentals');
    }
};