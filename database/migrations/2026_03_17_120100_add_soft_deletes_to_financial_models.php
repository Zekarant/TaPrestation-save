<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables financières/transactionnelles qui ne doivent jamais être supprimées définitivement.
     */
    private array $tables = [
        'payment_transactions',
        'refunds',
        'bookings',
        'equipment_rentals',
        'equipment_rental_requests',
        'urgent_sale_purchases',
        'urgent_sale_reservations',
        'user_subscriptions',
        'reviews',
        'client_reviews',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
