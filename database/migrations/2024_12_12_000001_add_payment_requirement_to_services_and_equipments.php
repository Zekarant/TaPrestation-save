<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds payment_requirement field to services and equipments tables.
     * This allows prestataires to choose what payment is required to validate a booking/rental:
     * - 'none': No payment required upfront
     * - 'deposit': Deposit (acompte) required to validate
     * - 'full': Full payment required to validate
     */
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'payment_requirement')) {
            Schema::table('services', function (Blueprint $table) {
                $table->enum('payment_requirement', ['none', 'deposit', 'full'])
                      ->default('none')
                      ->comment('Payment required to validate booking: none, deposit, or full');
            });
        }

        if (!Schema::hasColumn('equipment', 'payment_requirement')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->enum('payment_requirement', ['none', 'deposit', 'full'])
                      ->default('none')
                      ->after('security_deposit')
                      ->comment('Payment required to validate rental: none, deposit, or full');
            });
        }

        // Also add to urgent_sales for consistency
        if (!Schema::hasColumn('urgent_sales', 'payment_requirement')) {
            Schema::table('urgent_sales', function (Blueprint $table) {
                $table->enum('payment_requirement', ['none', 'full'])
                      ->default('full')
                      ->after('price')
                      ->comment('Payment required to purchase: none or full (urgent sales are always full)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('payment_requirement');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('payment_requirement');
        });

        Schema::table('urgent_sales', function (Blueprint $table) {
            $table->dropColumn('payment_requirement');
        });
    }
};
