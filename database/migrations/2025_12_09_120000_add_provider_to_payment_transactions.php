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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('provider')->default('stripe')->after('status'); // stripe, paypal, etc.
            $table->string('transaction_id')->nullable()->after('provider'); // Generic transaction ID
            $table->string('stripe_payment_intent_id')->nullable()->change(); // Make nullable for non-Stripe payments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['provider', 'transaction_id']);
            $table->string('stripe_payment_intent_id')->nullable(false)->change();
        });
    }
};
