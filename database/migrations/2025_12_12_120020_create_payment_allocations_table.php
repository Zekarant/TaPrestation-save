<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained('payment_transactions')->onDelete('cascade');

            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');

            $table->enum('type', ['payment', 'deposit', 'balance', 'refund'])->default('payment');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('eur');

            $table->timestamps();

            $table->index(['payment_transaction_id']);
            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
