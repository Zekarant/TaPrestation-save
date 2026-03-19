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
            $table->boolean('requires_approval')->default(false);
            $table->integer('min_advance_hours')->default(0);
            $table->integer('max_advance_days')->default(30);
            $table->integer('buffer_between_appointments')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataires', function (Blueprint $table) {
            $table->dropColumn([
                'requires_approval',
                'min_advance_hours',
                'max_advance_days',
                'buffer_between_appointments'
            ]);
        });
    }
};
