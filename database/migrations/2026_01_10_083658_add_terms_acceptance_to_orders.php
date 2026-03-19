<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les champs de suivi de l'acceptation des conditions de paiement (RGPD)
     */
    public function up(): void
    {
        // Bookings (services)
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_terms_version')->nullable()->after('payment_status');
            $table->timestamp('payment_terms_accepted_at')->nullable()->after('payment_terms_version');
            $table->string('payment_terms_ip')->nullable()->after('payment_terms_accepted_at');
        });

        // Food Orders
        if (Schema::hasTable('food_orders')) {
            Schema::table('food_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('food_orders', 'payment_terms_version')) {
                    $table->string('payment_terms_version')->nullable();
                    $table->timestamp('payment_terms_accepted_at')->nullable();
                    $table->string('payment_terms_ip')->nullable();
                }
            });
        }

        // Equipment Rental Requests
        if (Schema::hasTable('equipment_rental_requests')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('equipment_rental_requests', 'payment_terms_version')) {
                    $table->string('payment_terms_version')->nullable();
                    $table->timestamp('payment_terms_accepted_at')->nullable();
                    $table->string('payment_terms_ip')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_terms_version', 'payment_terms_accepted_at', 'payment_terms_ip']);
        });

        if (Schema::hasTable('food_orders')) {
            Schema::table('food_orders', function (Blueprint $table) {
                if (Schema::hasColumn('food_orders', 'payment_terms_version')) {
                    $table->dropColumn(['payment_terms_version', 'payment_terms_accepted_at', 'payment_terms_ip']);
                }
            });
        }

        if (Schema::hasTable('equipment_rental_requests')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                if (Schema::hasColumn('equipment_rental_requests', 'payment_terms_version')) {
                    $table->dropColumn(['payment_terms_version', 'payment_terms_accepted_at', 'payment_terms_ip']);
                }
            });
        }
    }
};
