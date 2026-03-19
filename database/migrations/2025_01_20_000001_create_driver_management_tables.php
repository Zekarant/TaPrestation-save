<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table des notations de livreurs par les prestataires
        if (!Schema::hasTable('driver_ratings'))
            Schema::create('driver_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('delivery_drivers')->onDelete('cascade');
                $table->foreignId('food_order_id')->nullable()->constrained('food_orders')->onDelete('set null');
                $table->unsignedTinyInteger('rating'); // 1-5 étoiles
                $table->unsignedTinyInteger('punctuality_rating')->nullable(); // Ponctualité 1-5
                $table->unsignedTinyInteger('professionalism_rating')->nullable(); // Professionnalisme 1-5
                $table->unsignedTinyInteger('care_rating')->nullable(); // Soin des commandes 1-5
                $table->text('comment')->nullable();
                $table->boolean('is_public')->default(false); // Visible par le livreur
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
                $table->index(['prestataire_id', 'driver_id']);
            });

        // Table des préférences de livreurs par prestataire (whitelist/blacklist)
        if (!Schema::hasTable('prestataire_driver_preferences'))
            Schema::create('prestataire_driver_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('delivery_drivers')->onDelete('cascade');
                $table->enum('status', ['preferred', 'neutral', 'blocked'])->default('neutral');
                $table->unsignedTinyInteger('priority')->default(50); // 1-100, plus haut = préféré
                $table->text('notes')->nullable(); // Notes internes prestataire
                $table->string('block_reason')->nullable();
                $table->timestamp('blocked_at')->nullable();
                $table->timestamps();

                $table->unique(['prestataire_id', 'driver_id']);
                $table->index(['driver_id', 'status']);
            });

        // Table des notations des livreurs par les clients
        if (!Schema::hasTable('driver_client_ratings'))
            Schema::create('driver_client_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('delivery_drivers')->onDelete('cascade');
                $table->foreignId('food_order_id')->nullable()->constrained('food_orders')->onDelete('set null');
                $table->unsignedTinyInteger('rating'); // 1-5 étoiles
                $table->text('comment')->nullable();
                $table->boolean('anonymous')->default(true);
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
            });

        // Table tarification personnalisée livreurs
        if (!Schema::hasTable('driver_pricing'))
            Schema::create('driver_pricing', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('delivery_drivers')->onDelete('cascade');
                $table->decimal('base_fee', 8, 2)->default(3.00); // Frais de base
                $table->decimal('fee_per_km', 8, 2)->default(0.50); // Prix au km
                $table->decimal('min_order_value', 8, 2)->nullable(); // Valeur min commande
                $table->decimal('surge_multiplier', 5, 2)->default(1.00); // Multiplicateur heures de pointe
                $table->json('surge_hours')->nullable(); // Heures de pointe personnalisées
                $table->json('zone_pricing')->nullable(); // Tarifs par zone
                $table->boolean('accepts_tips')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('driver_id');
            });

        // Ajouter champs au prestataire pour gestion livraison
        Schema::table('prestataires', function (Blueprint $table) {
            if (!Schema::hasColumn('prestataires', 'delivery_mode')) {
                $table->enum('delivery_mode', ['internal', 'external', 'both'])->default('both')->after('food_delivery_enabled');
            }
            if (!Schema::hasColumn('prestataires', 'internal_drivers_only')) {
                $table->boolean('internal_drivers_only')->default(false)->after('delivery_mode');
            }
            if (!Schema::hasColumn('prestataires', 'max_external_drivers')) {
                $table->unsignedTinyInteger('max_external_drivers')->nullable()->after('internal_drivers_only');
            }
            if (!Schema::hasColumn('prestataires', 'driver_commission_rate')) {
                $table->decimal('driver_commission_rate', 5, 2)->nullable()->after('max_external_drivers');
            }
            if (!Schema::hasColumn('prestataires', 'auto_assign_drivers')) {
                $table->boolean('auto_assign_drivers')->default(true)->after('driver_commission_rate');
            }
            if (!Schema::hasColumn('prestataires', 'min_driver_rating')) {
                $table->decimal('min_driver_rating', 3, 2)->nullable()->after('auto_assign_drivers');
            }
        });

        // Ajouter champs au livreur pour tarification et employeur
        Schema::table('delivery_drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
                $table->foreignId('employer_prestataire_id')->nullable()->after('user_id')->constrained('prestataires')->onDelete('set null');
            }
            if (!Schema::hasColumn('delivery_drivers', 'is_internal')) {
                $table->boolean('is_internal')->default(false)->after('employer_prestataire_id');
            }
            if (!Schema::hasColumn('delivery_drivers', 'max_distance_km')) {
                $table->unsignedSmallInteger('max_distance_km')->default(15)->after('is_internal');
            }
            if (!Schema::hasColumn('delivery_drivers', 'preferred_zones')) {
                $table->json('preferred_zones')->nullable()->after('max_distance_km');
            }
            if (!Schema::hasColumn('delivery_drivers', 'accepts_cash')) {
                $table->boolean('accepts_cash')->default(true)->after('preferred_zones');
            }
            if (!Schema::hasColumn('delivery_drivers', 'accepts_card')) {
                $table->boolean('accepts_card')->default(true)->after('accepts_cash');
            }
            if (!Schema::hasColumn('delivery_drivers', 'bio')) {
                $table->text('bio')->nullable()->after('accepts_card');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_client_ratings');
        Schema::dropIfExists('driver_pricing');
        Schema::dropIfExists('prestataire_driver_preferences');
        Schema::dropIfExists('driver_ratings');

        Schema::table('prestataires', function (Blueprint $table) {
            $columns = [
                'delivery_mode',
                'internal_drivers_only',
                'max_external_drivers',
                'driver_commission_rate',
                'auto_assign_drivers',
                'min_driver_rating'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('prestataires', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
                $table->dropForeign(['employer_prestataire_id']);
            }
            $columns = [
                'employer_prestataire_id',
                'is_internal',
                'max_distance_km',
                'preferred_zones',
                'accepts_cash',
                'accepts_card',
                'bio'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('delivery_drivers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
