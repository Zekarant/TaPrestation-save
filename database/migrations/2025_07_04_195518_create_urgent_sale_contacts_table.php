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
        Schema::create('urgent_sale_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urgent_sale_id')->constrained('urgent_sales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['urgent_sale_id', 'status']);
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urgent_sale_contacts');
    }
};
