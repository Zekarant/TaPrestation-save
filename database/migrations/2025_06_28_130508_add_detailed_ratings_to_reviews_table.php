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
        Schema::table('reviews', function (Blueprint $table) {
            $table->integer('punctuality_rating')->nullable()->after('rating');
            $table->integer('quality_rating')->nullable()->after('punctuality_rating');
            $table->integer('value_rating')->nullable()->after('quality_rating');
            $table->integer('communication_rating')->nullable()->after('value_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'punctuality_rating',
                'quality_rating',
                'value_rating',
                'communication_rating'
            ]);
        });
    }
};
