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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('target_url')->nullable();
            $table->string('category')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paused', 'expired'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('expires_at')->useCurrent();
            $table->integer('daily_budget')->nullable();
            $table->integer('total_budget')->nullable();
            $table->integer('spent_amount')->default(0);
            $table->integer('impression_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->json('targeting')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['status', 'expires_at']);
            $table->index('advertiser_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
