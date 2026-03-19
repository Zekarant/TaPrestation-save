<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('food_orders', 'prestataire_reminder_sent_at')) {
                $table->dateTime('prestataire_reminder_sent_at')->nullable()->after('requested_at');
                $table->index('prestataire_reminder_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            if (Schema::hasColumn('food_orders', 'prestataire_reminder_sent_at')) {
                $table->dropIndex(['prestataire_reminder_sent_at']);
                $table->dropColumn('prestataire_reminder_sent_at');
            }
        });
    }
};
