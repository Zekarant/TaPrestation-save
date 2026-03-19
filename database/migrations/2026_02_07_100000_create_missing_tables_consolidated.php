<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration consolidée: création des 6 tables manquantes en BDD
 * - auction_bids
 * - delivery_providers
 * - delivery_orders
 * - address_books
 * - advertisements
 * - payment_consents
 * 
 * + colonnes buyer_consent sur bookings/equipment_rental_requests/urgent_sale_purchases
 * + colonnes rating sur urgent_sale_reservations
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. AUCTION_BIDS
        // ============================================================
        if (!Schema::hasTable('auction_bids')) {
            Schema::create('auction_bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
                $table->decimal('bid_amount', 10, 2);
                $table->string('currency', 3)->default('EUR');
                $table->text('message')->nullable();
                $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['service_id', 'status']);
                $table->index(['client_id', 'status']);
                $table->index('expires_at');
            });
        }

        // ============================================================
        // 2. DELIVERY_PROVIDERS
        // ============================================================
        if (!Schema::hasTable('delivery_providers')) {
            Schema::create('delivery_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique();
                $table->string('api_key')->nullable();
                $table->string('api_url')->nullable();
                $table->json('coverage_areas')->nullable();
                $table->decimal('base_rate', 10, 2)->default(0);
                $table->decimal('per_km_rate', 10, 2)->default(0);
                $table->enum('delivery_type', ['standard', 'express', 'overnight'])->nullable();
                $table->integer('estimated_days')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('features')->nullable();
                $table->json('contact_info')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index('is_active');
            });
        }

        // ============================================================
        // 3. DELIVERY_ORDERS (schéma complet aligné avec le Model)
        // ============================================================
        if (!Schema::hasTable('delivery_orders')) {
            Schema::create('delivery_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('delivery_provider_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('driver_id')->nullable()->constrained('delivery_drivers')->onDelete('set null');
                $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->onDelete('set null');
                $table->string('tracking_number')->unique();
                $table->string('reference_number')->nullable();
                $table->enum('status', [
                    'pending', 'confirmed', 'preparing', 'ready_for_pickup',
                    'driver_assigned', 'picked_up', 'in_transit', 'out_for_delivery',
                    'delivered', 'failed', 'returned', 'cancelled'
                ])->default('pending');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('shipping_type', ['standard', 'express', 'same_day', 'overnight'])->default('standard');
                $table->decimal('weight', 10, 3)->nullable();
                $table->json('dimensions')->nullable();
                $table->integer('package_count')->default(1);
                $table->boolean('fragile')->default(false);
                $table->boolean('requires_signature')->default(false);
                $table->decimal('shipping_cost', 10, 2)->default(0);
                $table->decimal('insurance_cost', 10, 2)->default(0);
                $table->decimal('total_cost', 10, 2)->default(0);
                // Pickup info
                $table->string('pickup_address');
                $table->string('pickup_city')->nullable();
                $table->string('pickup_postal_code')->nullable();
                $table->string('pickup_contact_name')->nullable();
                $table->string('pickup_contact_phone')->nullable();
                $table->text('pickup_instructions')->nullable();
                $table->decimal('pickup_lat', 10, 8)->nullable();
                $table->decimal('pickup_lng', 11, 8)->nullable();
                // Delivery info
                $table->string('delivery_address');
                $table->string('delivery_city')->nullable();
                $table->string('delivery_postal_code')->nullable();
                $table->string('delivery_contact_name')->nullable();
                $table->string('delivery_contact_phone')->nullable();
                $table->text('delivery_instructions')->nullable();
                $table->decimal('delivery_lat', 10, 8)->nullable();
                $table->decimal('delivery_lng', 11, 8)->nullable();
                // Scheduling
                $table->timestamp('scheduled_pickup_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('scheduled_delivery_at')->nullable();
                $table->timestamp('estimated_delivery')->nullable();
                $table->timestamp('delivered_at')->nullable();
                // Delivery attempts
                $table->integer('delivery_attempts')->default(0);
                $table->integer('max_delivery_attempts')->default(3);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamp('next_attempt_at')->nullable();
                $table->text('failure_reason')->nullable();
                // Proof of delivery
                $table->string('signature_image')->nullable();
                $table->string('delivery_photo')->nullable();
                $table->string('recipient_name')->nullable();
                // Notes & tracking
                $table->json('tracking_history')->nullable();
                $table->text('notes')->nullable();
                $table->text('internal_notes')->nullable();
                // Rating
                $table->tinyInteger('customer_rating')->unsigned()->nullable();
                $table->text('customer_feedback')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['tracking_number', 'status']);
                $table->index('booking_id');
                $table->index('driver_id');
                $table->index('status');
                $table->index('scheduled_delivery_at');
            });
        }

        // ============================================================
        // 4. ADDRESS_BOOKS
        // ============================================================
        if (!Schema::hasTable('address_books')) {
            Schema::create('address_books', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('label')->nullable();
                $table->string('recipient_name');
                $table->string('street');
                $table->string('city');
                $table->string('postal_code');
                $table->string('country');
                $table->string('phone');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->json('tags')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('address_type')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'is_default']);
                $table->index('created_at');
            });
        }

        // ============================================================
        // 5. ADVERTISEMENTS
        // ============================================================
        if (!Schema::hasTable('advertisements')) {
            Schema::create('advertisements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('advertiser_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('image_url')->nullable();
                $table->string('target_url')->nullable();
                $table->string('category')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'paused', 'expired'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('daily_budget')->nullable();
                $table->integer('total_budget')->nullable();
                $table->integer('spent_amount')->default(0);
                $table->integer('impression_count')->default(0);
                $table->integer('click_count')->default(0);
                $table->integer('conversion_count')->default(0);
                $table->json('targeting')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['status', 'expires_at']);
                $table->index('advertiser_id');
            });
        }

        // ============================================================
        // 6. PAYMENT_CONSENTS
        // ============================================================
        if (!Schema::hasTable('payment_consents')) {
            Schema::create('payment_consents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('consentable_type');
                $table->unsignedBigInteger('consentable_id');
                $table->string('consent_type')->default('payment_terms');
                $table->string('version', 20)->default('v1.0');
                $table->text('terms_hash')->nullable();
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('consented_at');
                $table->timestamps();
                $table->index(['consentable_type', 'consentable_id']);
                $table->index('user_id');
            });
        }

        // ============================================================
        // 7. BUYER CONSENT COLUMNS (bookings, equipment_rental_requests, urgent_sale_purchases)
        // ============================================================
        $consentTables = ['bookings', 'equipment_rental_requests', 'urgent_sale_purchases'];
        foreach ($consentTables as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'buyer_consent_at')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->timestamp('buyer_consent_at')->nullable();
                    $table->string('buyer_consent_ip', 45)->nullable();
                    $table->text('buyer_consent_user_agent')->nullable();
                    $table->string('buyer_consent_version', 20)->nullable();
                });
            }
        }

        // ============================================================
        // 8. RATING COLUMNS on urgent_sale_reservations
        // ============================================================
        if (Schema::hasTable('urgent_sale_reservations') && !Schema::hasColumn('urgent_sale_reservations', 'client_rating')) {
            Schema::table('urgent_sale_reservations', function (Blueprint $table) {
                $table->unsignedTinyInteger('client_rating')->nullable()->after('completed_at');
                $table->text('client_rating_comment')->nullable()->after('client_rating');
                $table->timestamp('client_rated_at')->nullable()->after('client_rating_comment');
                $table->unsignedTinyInteger('seller_rating')->nullable()->after('client_rated_at');
                $table->text('seller_rating_comment')->nullable()->after('seller_rating');
                $table->timestamp('seller_rated_at')->nullable()->after('seller_rating_comment');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_consents');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('address_books');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('delivery_providers');
        Schema::dropIfExists('auction_bids');

        $consentTables = ['bookings', 'equipment_rental_requests', 'urgent_sale_purchases'];
        foreach ($consentTables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'buyer_consent_at')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn(['buyer_consent_at', 'buyer_consent_ip', 'buyer_consent_user_agent', 'buyer_consent_version']);
                });
            }
        }

        if (Schema::hasTable('urgent_sale_reservations') && Schema::hasColumn('urgent_sale_reservations', 'client_rating')) {
            Schema::table('urgent_sale_reservations', function (Blueprint $table) {
                $table->dropColumn(['client_rating', 'client_rating_comment', 'client_rated_at', 'seller_rating', 'seller_rating_comment', 'seller_rated_at']);
            });
        }
    }
};
