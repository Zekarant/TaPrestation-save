<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urgent_sale_reservations', function (Blueprint $table) {
            // Note du prestataire sur le client
            $table->unsignedTinyInteger('client_rating')->nullable()->after('seller_notes');
            $table->text('client_rating_comment')->nullable()->after('client_rating');
            $table->timestamp('client_rated_at')->nullable()->after('client_rating_comment');
            
            // Note du client sur le prestataire
            $table->unsignedTinyInteger('seller_rating')->nullable()->after('client_rated_at');
            $table->text('seller_rating_comment')->nullable()->after('seller_rating');
            $table->timestamp('seller_rated_at')->nullable()->after('seller_rating_comment');
        });
    }

    public function down(): void
    {
        Schema::table('urgent_sale_reservations', function (Blueprint $table) {
            $table->dropColumn([
                'client_rating', 'client_rating_comment', 'client_rated_at',
                'seller_rating', 'seller_rating_comment', 'seller_rated_at',
            ]);
        });
    }
};
