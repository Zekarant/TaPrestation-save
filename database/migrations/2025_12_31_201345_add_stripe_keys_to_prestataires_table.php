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
            $table->string('stripe_public_key')->nullable()->after('stripe_account_id');
            $table->string('stripe_secret_key')->nullable()->after('stripe_public_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            $table->dropColumn(['stripe_public_key', 'stripe_secret_key']);
        });
    }
};
