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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'push_subscriptions')) {
                $table->json('push_subscriptions')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'push_enabled')) {
                $table->boolean('push_enabled')->default(false)->after('push_subscriptions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'push_subscriptions')) {
                $table->dropColumn('push_subscriptions');
            }
            if (Schema::hasColumn('users', 'push_enabled')) {
                $table->dropColumn('push_enabled');
            }
        });
    }
};
