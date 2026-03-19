<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds payment connection fields to prestataires table
     */
    public function up(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            // Stripe Connect
            if (!Schema::hasColumn('prestataires', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('prestataires', 'stripe_onboarding_completed')) {
                $table->boolean('stripe_onboarding_completed')->default(false)->after('stripe_account_id');
            }
            
            // PayPal
            if (!Schema::hasColumn('prestataires', 'paypal_email')) {
                $table->string('paypal_email')->nullable()->after('stripe_onboarding_completed');
            }
            
            // Bank account (IBAN)
            if (!Schema::hasColumn('prestataires', 'account_holder_name')) {
                $table->string('account_holder_name')->nullable()->after('paypal_email');
            }
            if (!Schema::hasColumn('prestataires', 'iban')) {
                $table->string('iban')->nullable()->after('account_holder_name');
            }
            if (!Schema::hasColumn('prestataires', 'bic')) {
                $table->string('bic')->nullable()->after('iban');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            $columns = [
                'stripe_account_id',
                'stripe_onboarding_completed',
                'paypal_email',
                'account_holder_name',
                'iban',
                'bic'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('prestataires', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
