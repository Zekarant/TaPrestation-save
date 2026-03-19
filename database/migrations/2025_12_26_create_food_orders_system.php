<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des produits alimentaires (menu du prestataire)
        Schema::create('food_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('category')->nullable(); // entrée, plat, dessert, boisson, etc.
            $table->boolean('is_available')->default(true);
            $table->integer('preparation_time')->nullable(); // en minutes
            $table->integer('stock')->nullable(); // null = illimité
            $table->json('options')->nullable(); // options supplémentaires (taille, suppléments, etc.)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Table des commandes
        Schema::create('food_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'pending',      // En attente de validation par le prestataire
                'accepted',     // Acceptée par le prestataire
                'rejected',     // Refusée par le prestataire
                'preparing',    // En préparation
                'ready',        // Prête à être récupérée/livrée
                'delivered',    // Livrée/Récupérée
                'completed',    // Terminée (les deux ont confirmé)
                'cancelled'     // Annulée
            ])->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->text('notes')->nullable(); // Instructions spéciales
            $table->datetime('requested_at')->nullable(); // Date/heure souhaitée
            $table->datetime('accepted_at')->nullable();
            $table->datetime('ready_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->boolean('client_confirmed')->default(false);
            $table->boolean('prestataire_confirmed')->default(false);
            $table->string('rejection_reason')->nullable();
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('payment_intent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['prestataire_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        // Table des items de commande
        Schema::create('food_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('food_product_id')->constrained()->onDelete('cascade');
            $table->string('product_name'); // Copie du nom au moment de la commande
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->json('options')->nullable(); // Options choisies
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_order_items');
        Schema::dropIfExists('food_orders');
        Schema::dropIfExists('food_products');
    }
};
