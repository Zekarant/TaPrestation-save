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
        Schema::table('prestataires', function (Blueprint $table) {
            if (!Schema::hasColumn('prestataires', 'stripe_onboarding_completed')) {
                $table->boolean('stripe_onboarding_completed')->default(false)->after('stripe_account_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            if (Schema::hasColumn('prestataires', 'stripe_onboarding_completed')) {
                $table->dropColumn('stripe_onboarding_completed');
            }
        });
    }
};
