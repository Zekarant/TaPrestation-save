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
            $table->string('price_type')->nullable()->after('price');
            $table->string('city')->nullable()->after('status');
            $table->string('postal_code', 5)->nullable()->after('city');
            $table->string('address', 500)->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'city', 'postal_code', 'address']);
        });
    }
};
