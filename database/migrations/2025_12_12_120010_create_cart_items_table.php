<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');

            // Polymorphic target: Booking, EquipmentRentalRequest, UrgentSale
            $table->string('purchasable_type');
            $table->unsignedBigInteger('purchasable_id');

            $table->unsignedInteger('quantity')->default(1);

            // Snapshots to keep the cart stable even if the underlying price changes
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->decimal('line_deposit', 10, 2)->default(0);
            $table->string('currency', 3)->default('eur');

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['cart_id']);
            $table->index(['purchasable_type', 'purchasable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
