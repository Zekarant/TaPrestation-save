<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter le suivi du consentement ACHETEUR aux conditions de paiement
 * 
 * RGPD: On enregistre l'acceptation des conditions (timestamp, IP, user agent, version)
 * pour chaque transaction (réservation, location, achat).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Consentement acheteur sur les réservations (services)
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'buyer_consent_at')) {
                    $table->timestamp('buyer_consent_at')->nullable()
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('bookings', 'buyer_consent_ip')) {
                    $table->string('buyer_consent_ip', 45)->nullable()
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('bookings', 'buyer_consent_user_agent')) {
                    $table->text('buyer_consent_user_agent')->nullable()
                        ->comment('User agent lors du consentement');
                }
                if (!Schema::hasColumn('bookings', 'buyer_consent_version')) {
                    $table->string('buyer_consent_version', 20)->nullable()
                        ->comment('Version des CGV acceptées');
                }
            });
        }

        // Consentement acheteur sur les locations d'équipement
        if (Schema::hasTable('equipment_rental_requests')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('equipment_rental_requests', 'buyer_consent_at')) {
                    $table->timestamp('buyer_consent_at')->nullable()
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('equipment_rental_requests', 'buyer_consent_ip')) {
                    $table->string('buyer_consent_ip', 45)->nullable()
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('equipment_rental_requests', 'buyer_consent_user_agent')) {
                    $table->text('buyer_consent_user_agent')->nullable()
                        ->comment('User agent lors du consentement');
                }
                if (!Schema::hasColumn('equipment_rental_requests', 'buyer_consent_version')) {
                    $table->string('buyer_consent_version', 20)->nullable()
                        ->comment('Version des CGV acceptées');
                }
            });
        }

        // Consentement acheteur sur les achats vente urgente
        if (Schema::hasTable('urgent_sale_purchases')) {
            Schema::table('urgent_sale_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('urgent_sale_purchases', 'buyer_consent_at')) {
                    $table->timestamp('buyer_consent_at')->nullable()
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('urgent_sale_purchases', 'buyer_consent_ip')) {
                    $table->string('buyer_consent_ip', 45)->nullable()
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('urgent_sale_purchases', 'buyer_consent_user_agent')) {
                    $table->text('buyer_consent_user_agent')->nullable()
                        ->comment('User agent lors du consentement');
                }
                if (!Schema::hasColumn('urgent_sale_purchases', 'buyer_consent_version')) {
                    $table->string('buyer_consent_version', 20)->nullable()
                        ->comment('Version des CGV acceptées');
                }
            });
        }

        // Table centralisée pour le suivi des consentements (audit trail)
        if (!Schema::hasTable('payment_consents')) {
            Schema::create('payment_consents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('consentable_type'); // Booking, EquipmentRentalRequest, UrgentSalePurchase
                $table->unsignedBigInteger('consentable_id');
                $table->string('consent_type')->default('payment_terms'); // payment_terms, escrow_rules, rgpd
                $table->string('version', 20)->default('v1.0');
                $table->text('terms_hash')->nullable()->comment('Hash SHA256 des conditions acceptées');
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('consented_at');
                $table->timestamps();

                $table->index(['consentable_type', 'consentable_id']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['bookings', 'equipment_rental_requests', 'urgent_sale_purchases'];
        $columns = ['buyer_consent_at', 'buyer_consent_ip', 'buyer_consent_user_agent', 'buyer_consent_version'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                    foreach ($columns as $col) {
                        if (Schema::hasColumn($tableName, $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }

        Schema::dropIfExists('payment_consents');
    }
};
