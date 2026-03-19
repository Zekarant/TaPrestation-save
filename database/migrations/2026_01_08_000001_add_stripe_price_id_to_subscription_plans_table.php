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
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('name');
                $table->index('stripe_price_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'stripe_price_id')) {
                $table->dropIndex(['stripe_price_id']);
                $table->dropColumn('stripe_price_id');
            }
        });
    }
};
