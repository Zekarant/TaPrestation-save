<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urgent_sale_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urgent_sale_id')->constrained('urgent_sales')->onDelete('restrict');
            $table->foreignId('buyer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('eur');

            $table->enum('status', ['paid', 'cancelled', 'refunded'])->default('paid');
            $table->timestamps();

            $table->index(['urgent_sale_id', 'buyer_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urgent_sale_purchases');
    }
};
