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
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('deposit_percentage', 5, 2)->default(0)->after('price');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('total_price');
            $table->enum('payment_status', ['pending', 'deposit_paid', 'paid', 'refunded', 'failed'])->default('pending')->after('status');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->enum('type', ['payment', 'deposit', 'balance', 'refund'])->default('payment')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('deposit_percentage');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'payment_status']);
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
