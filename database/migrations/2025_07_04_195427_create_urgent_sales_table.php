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
        Schema::create('urgent_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('condition', ['new', 'good', 'used', 'fair']);
            $table->json('photos')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['active', 'sold', 'withdrawn', 'reported', 'blocked'])->default('active');
            $table->string('slug')->unique();
            $table->integer('views_count')->default(0);
            $table->integer('contact_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['status', 'created_at']);
            $table->index(['prestataire_id', 'status']);
            $table->index('is_urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urgent_sales');
    }
};
