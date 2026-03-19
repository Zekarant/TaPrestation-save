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
        Schema::table('notification_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_settings', 'food_order_notifications')) {
                $table->boolean('food_order_notifications')->default(true)->after('promotion_notifications');
            }
            if (!Schema::hasColumn('notification_settings', 'equipment_notifications')) {
                $table->boolean('equipment_notifications')->default(true)->after('food_order_notifications');
            }
            if (!Schema::hasColumn('notification_settings', 'newsletter_notifications')) {
                $table->boolean('newsletter_notifications')->default(false)->after('equipment_notifications');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn(['food_order_notifications', 'equipment_notifications', 'newsletter_notifications']);
        });
    }
};
