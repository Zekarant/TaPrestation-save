<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SYSTÈME DE PAIEMENT SÉCURISÉ (ESCROW) + LIVRAISON
     * 
     * Ce système permet de bloquer l'argent jusqu'à confirmation de la prestation/livraison.
     * Protège à la fois le client ET le prestataire.
     */
    public function up(): void
    {
        // ========================================
        // 1. TABLE ESCROW - Fonds bloqués
        // ========================================
        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            
            // Références
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('prestataire_id');
            $table->string('escrowable_type'); // App\Models\Booking, EquipmentRentalRequest, UrgentSalePurchase
            $table->unsignedBigInteger('escrowable_id');
            
            // Montants
            $table->decimal('amount', 10, 2)->comment('Montant principal bloqué');
            $table->decimal('deposit_amount', 10, 2)->default(0)->comment('Caution/dépôt de garantie');
            $table->decimal('platform_fee', 10, 2)->default(0)->comment('Commission plateforme');
            $table->decimal('released_amount', 10, 2)->default(0)->comment('Montant déjà libéré');
            $table->string('currency', 3)->default('EUR');
            
            // Statuts
            $table->enum('status', [
                'pending',      // En attente de paiement
                'held',         // Argent bloqué
                'partial',      // Partiellement libéré
                'released',     // Entièrement libéré au presta
                'refunded',     // Remboursé au client
                'disputed',     // Litige en cours
                'cancelled'     // Annulé
            ])->default('pending');
            
            // Stripe
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            
            // Dates importantes
            $table->timestamp('held_at')->nullable()->comment('Date de blocage');
            $table->timestamp('auto_release_at')->nullable()->comment('Date de libération auto');
            $table->timestamp('released_at')->nullable()->comment('Date de libération effective');
            $table->timestamp('refunded_at')->nullable();
            
            // Confirmation
            $table->boolean('client_confirmed')->default(false);
            $table->timestamp('client_confirmed_at')->nullable();
            $table->boolean('prestataire_confirmed')->default(false);
            $table->timestamp('prestataire_confirmed_at')->nullable();
            
            // Infos additionnelles
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['escrowable_type', 'escrowable_id']);
            $table->index('status');
            $table->index('auto_release_at');
        });

        // ========================================
        // 2. TABLE LITIGES
        // ========================================
        Schema::create('escrow_disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escrow_id');
            $table->unsignedBigInteger('opened_by')->comment('User ID qui ouvre le litige');
            
            $table->enum('reason', [
                'not_received',         // Produit/service non reçu
                'not_as_described',     // Non conforme à la description
                'damaged',              // Endommagé
                'partial_service',      // Service partiel
                'quality_issue',        // Problème de qualité
                'wrong_item',           // Mauvais article
                'other'                 // Autre
            ]);
            
            $table->text('description');
            $table->json('evidence')->nullable()->comment('Photos, preuves');
            
            $table->enum('status', [
                'open',
                'under_review',
                'resolved_client',      // Résolu en faveur du client
                'resolved_prestataire', // Résolu en faveur du presta
                'resolved_partial',     // Résolu partiellement
                'closed'
            ])->default('open');
            
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('escrow_id')->references('id')->on('escrow_transactions')->onDelete('cascade');
        });

        // ========================================
        // 3. COLONNE payment_mode sur prestataires
        // ========================================
        if (!Schema::hasColumn('prestataires', 'payment_mode')) {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->enum('payment_mode', ['escrow', 'direct'])->default('escrow')
                      ->after('stripe_account_id')
                      ->comment('escrow = sécurisé, direct = paiement immédiat');
                $table->boolean('payment_mode_accepted_terms')->default(false)->after('payment_mode');
                $table->timestamp('payment_mode_accepted_at')->nullable()->after('payment_mode_accepted_terms');
            });
        }

        // ========================================
        // 4. COLONNES pour caution équipement
        // ========================================
        if (!Schema::hasColumn('equipment_rental_requests', 'deposit_status')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                $table->enum('deposit_status', ['pending', 'held', 'returned', 'retained', 'partial'])
                      ->default('pending')
                      ->after('security_deposit');
                $table->decimal('deposit_retained', 10, 2)->default(0)->after('deposit_status');
                $table->text('deposit_retention_reason')->nullable()->after('deposit_retained');
                $table->timestamp('equipment_returned_at')->nullable();
                $table->enum('equipment_condition', ['excellent', 'good', 'damaged', 'lost'])->nullable();
            });
        }

        // ========================================
        // 5. TABLE Notes mutuelles
        // ========================================
        Schema::create('mutual_ratings', function (Blueprint $table) {
            $table->id();
            
            // Qui note qui
            $table->unsignedBigInteger('rater_id')->comment('User qui donne la note');
            $table->unsignedBigInteger('rated_id')->comment('User qui reçoit la note');
            $table->enum('rater_type', ['client', 'prestataire']);
            
            // Référence à la transaction
            $table->string('ratable_type'); // Booking, EquipmentRentalRequest, UrgentSalePurchase
            $table->unsignedBigInteger('ratable_id');
            
            // Note
            $table->tinyInteger('rating')->unsigned()->comment('1 à 5');
            $table->text('comment')->nullable();
            $table->boolean('would_recommend')->default(true);
            
            // Modération
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_flagged')->default(false);
            
            $table->timestamps();
            
            $table->index(['rated_id', 'rater_type']);
            $table->index(['ratable_type', 'ratable_id']);
            $table->unique(['rater_id', 'ratable_type', 'ratable_id']);
        });

        // ========================================
        // 6. TABLE Livraison (transporteurs)
        // ========================================
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            
            // Référence
            $table->string('shippable_type'); // UrgentSalePurchase, etc.
            $table->unsignedBigInteger('shippable_id');
            $table->unsignedBigInteger('escrow_id')->nullable();
            
            // Transporteur
            $table->enum('carrier', [
                'mondial_relay',
                'chronopost',
                'colissimo',
                'ups',
                'dhl',
                'fedex',
                'hand_delivery'  // Remise en main propre
            ]);
            
            // Infos livraison
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('label_url')->nullable();
            
            // Adresses
            $table->json('sender_address');
            $table->json('recipient_address');
            $table->string('relay_point_id')->nullable()->comment('ID point relais');
            
            // Statuts
            $table->enum('status', [
                'pending',          // En attente d'expédition
                'label_created',    // Étiquette créée
                'shipped',          // Expédié
                'in_transit',       // En transit
                'out_for_delivery', // En cours de livraison
                'delivered',        // Livré
                'returned',         // Retourné
                'lost',             // Perdu
                'cancelled'
            ])->default('pending');
            
            // Dates
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            
            // Confirmation
            $table->boolean('delivery_confirmed')->default(false);
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->boolean('conformity_validated')->default(false);
            $table->timestamp('conformity_validated_at')->nullable();
            
            // Infos supplémentaires
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->decimal('weight', 8, 3)->nullable()->comment('Poids en kg');
            $table->json('dimensions')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['shippable_type', 'shippable_id']);
            $table->index('tracking_number');
            $table->index('status');
        });

        // ========================================
        // 7. TABLE Achats Vente Urgente
        // ========================================
        if (!Schema::hasTable('urgent_sale_purchases')) {
            Schema::create('urgent_sale_purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number')->unique();
                $table->unsignedBigInteger('urgent_sale_id');
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('prestataire_id');
                
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 10, 2);
                
                $table->enum('status', [
                    'pending',
                    'paid',
                    'confirmed',    // Client a confirmé réception
                    'completed',
                    'disputed',
                    'refunded',
                    'cancelled'
                ])->default('pending');
                
                $table->enum('delivery_method', [
                    'pickup',           // Retrait sur place
                    'hand_delivery',    // Remise en main propre
                    'shipping'          // Livraison transporteur
                ])->default('pickup');
                
                $table->string('pickup_code', 6)->nullable();
                $table->timestamp('pickup_code_verified_at')->nullable();
                
                $table->unsignedBigInteger('payment_transaction_id')->nullable();
                $table->unsignedBigInteger('escrow_id')->nullable();
                
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index('purchase_number');
                $table->index('status');
            });
        }

        // ========================================
        // 8. Settings pour le système
        // ========================================
        $settings = [
            ['key' => 'escrow_auto_release_hours', 'value' => '48'],
            ['key' => 'escrow_dispute_window_hours', 'value' => '72'],
            ['key' => 'escrow_commission_rate', 'value' => '5'],
            ['key' => 'direct_payment_commission_rate', 'value' => '3'],
            ['key' => 'equipment_deposit_retention_max_percent', 'value' => '100'],
            ['key' => 'urgent_sale_refund_percent', 'value' => '80'],
            ['key' => 'shipping_carriers_enabled', 'value' => json_encode(['mondial_relay', 'chronopost', 'colissimo'])],
        ];

        foreach ($settings as $setting) {
            \DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('mutual_ratings');
        Schema::dropIfExists('escrow_disputes');
        Schema::dropIfExists('escrow_transactions');
        Schema::dropIfExists('urgent_sale_purchases');
        
        Schema::table('prestataires', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'payment_mode_accepted_terms', 'payment_mode_accepted_at']);
        });
        
        Schema::table('equipment_rental_requests', function (Blueprint $table) {
            $table->dropColumn(['deposit_status', 'deposit_retained', 'deposit_retention_reason', 'equipment_returned_at', 'equipment_condition']);
        });
    }
};
