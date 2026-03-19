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
        Schema::table('equipment_rental_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_rental_requests', 'start_time')) {
                $table->time('start_time')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('equipment_rental_requests', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('equipment_rental_requests', 'is_hourly')) {
                $table->boolean('is_hourly')->default(false)->after('end_time');
            }
            if (!Schema::hasColumn('equipment_rental_requests', 'duration_hours')) {
                $table->integer('duration_hours')->nullable()->after('is_hourly');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_rental_requests', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'is_hourly', 'duration_hours']);
        });
    }
};
