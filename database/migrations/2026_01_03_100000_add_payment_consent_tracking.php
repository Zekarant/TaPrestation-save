<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter le suivi du consentement aux conditions de paiement
 * sur les différents types d'annonces (services, équipements, ventes urgentes)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les colonnes de consentement à la table services
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                if (!Schema::hasColumn('services', 'payment_consent_at')) {
                    $table->timestamp('payment_consent_at')->nullable()->after('status')
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('services', 'payment_consent_ip')) {
                    $table->string('payment_consent_ip', 45)->nullable()->after('payment_consent_at')
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('services', 'payment_consent_user_agent')) {
                    $table->text('payment_consent_user_agent')->nullable()->after('payment_consent_ip')
                        ->comment('User agent lors du consentement');
                }
            });
        }

        // Ajouter les colonnes de consentement à la table equipment
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                if (!Schema::hasColumn('equipment', 'payment_consent_at')) {
                    $table->timestamp('payment_consent_at')->nullable()
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('equipment', 'payment_consent_ip')) {
                    $table->string('payment_consent_ip', 45)->nullable()
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('equipment', 'payment_consent_user_agent')) {
                    $table->text('payment_consent_user_agent')->nullable()
                        ->comment('User agent lors du consentement');
                }
            });
        }

        // Ajouter les colonnes de consentement à la table urgent_sales
        if (Schema::hasTable('urgent_sales')) {
            Schema::table('urgent_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('urgent_sales', 'payment_consent_at')) {
                    $table->timestamp('payment_consent_at')->nullable()
                        ->comment('Horodatage du consentement aux conditions de paiement');
                }
                if (!Schema::hasColumn('urgent_sales', 'payment_consent_ip')) {
                    $table->string('payment_consent_ip', 45)->nullable()
                        ->comment('Adresse IP lors du consentement');
                }
                if (!Schema::hasColumn('urgent_sales', 'payment_consent_user_agent')) {
                    $table->text('payment_consent_user_agent')->nullable()
                        ->comment('User agent lors du consentement');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn([
                    'payment_consent_at',
                    'payment_consent_ip',
                    'payment_consent_user_agent'
                ]);
            });
        }

        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn([
                    'payment_consent_at',
                    'payment_consent_ip',
                    'payment_consent_user_agent'
                ]);
            });
        }

        if (Schema::hasTable('urgent_sales')) {
            Schema::table('urgent_sales', function (Blueprint $table) {
                $table->dropColumn([
                    'payment_consent_at',
                    'payment_consent_ip',
                    'payment_consent_user_agent'
                ]);
            });
        }
    }
};
