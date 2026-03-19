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
        // Créer la table des zones de livraison SI elle n'existe pas
        if (!Schema::hasTable('delivery_zones')) {
            Schema::create('delivery_zones', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 20)->unique();
                $table->text('description')->nullable();
                $table->json('cities')->nullable();
                $table->json('postal_codes')->nullable();
                $table->decimal('min_lat', 10, 8)->nullable();
                $table->decimal('max_lat', 10, 8)->nullable();
                $table->decimal('min_lng', 11, 8)->nullable();
                $table->decimal('max_lng', 11, 8)->nullable();
                $table->decimal('base_delivery_fee', 10, 2)->default(5.00);
                $table->decimal('per_km_fee', 10, 2)->default(0.50);
                $table->decimal('min_order_amount', 10, 2)->nullable();
                $table->decimal('free_delivery_threshold', 10, 2)->nullable();
                $table->integer('estimated_delivery_days')->default(3);
                $table->boolean('express_available')->default(true);
                $table->decimal('express_surcharge', 10, 2)->default(5.00);
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(10);
                $table->json('working_hours')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'priority']);
            });
        }

        // Créer la table des livreurs SI elle n'existe pas
        if (!Schema::hasTable('delivery_drivers')) {
            Schema::create('delivery_drivers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('email')->unique();
                $table->string('phone', 20);
                $table->enum('vehicle_type', ['bike', 'scooter', 'car', 'van', 'truck'])->default('car');
                $table->string('vehicle_plate', 20)->nullable();
                $table->string('license_number', 50)->nullable();
                $table->string('photo')->nullable();
                $table->enum('status', ['available', 'busy', 'offline', 'on_break'])->default('offline');
                $table->boolean('is_available')->default(false);
                $table->decimal('current_lat', 10, 8)->nullable();
                $table->decimal('current_lng', 11, 8)->nullable();
                $table->timestamp('last_location_update')->nullable();
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->unsignedInteger('total_deliveries')->default(0);
                $table->unsignedInteger('completed_deliveries')->default(0);
                $table->unsignedInteger('failed_deliveries')->default(0);
                $table->unsignedInteger('average_delivery_time')->default(0)->comment('In minutes');
                $table->json('working_hours')->nullable();
                $table->decimal('commission_rate', 5, 2)->default(0.80)->comment('80% default');
                $table->decimal('total_earnings', 12, 2)->default(0);
                $table->json('bank_details')->nullable();
                $table->json('documents')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('verified_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'is_available', 'status']);
                $table->index(['zone_id', 'is_available']);
            });
        }

        // Créer la table des événements de suivi SI elle n'existe pas ET si delivery_orders existe
        if (!Schema::hasTable('delivery_tracking_events') && Schema::hasTable('delivery_orders')) {
            Schema::create('delivery_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
                $table->string('status', 50);
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('signature')->nullable();
                $table->string('photo')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['delivery_order_id', 'created_at']);
                $table->index('status');
            });
        } elseif (!Schema::hasTable('delivery_tracking_events')) {
            // Créer sans la contrainte foreign key si delivery_orders n'existe pas encore
            Schema::create('delivery_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_order_id');
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('status', 50);
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('signature')->nullable();
                $table->string('photo')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['delivery_order_id', 'created_at']);
                $table->index('status');
            });
        }

        // Ajouter les nouvelles colonnes à delivery_orders (compatible avec structure existante)
        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                // Adresse de collecte - renommer origin_address en pickup_address
                if (Schema::hasColumn('delivery_orders', 'origin_address') && !Schema::hasColumn('delivery_orders', 'pickup_address')) {
                    $table->renameColumn('origin_address', 'pickup_address');
                }
                
                // Adresse de livraison - renommer destination_address en delivery_address
                if (Schema::hasColumn('delivery_orders', 'destination_address') && !Schema::hasColumn('delivery_orders', 'delivery_address')) {
                    $table->renameColumn('destination_address', 'delivery_address');
                }
            });
            
            // Ajouter les nouvelles colonnes maintenant que les renommages sont faits
            Schema::table('delivery_orders', function (Blueprint $table) {
            // Colonnes de base pour coordonnées
            if (!Schema::hasColumn('delivery_orders', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'pickup_lng')) {
                $table->decimal('pickup_lng', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_lat')) {
                $table->decimal('delivery_lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_lng')) {
                $table->decimal('delivery_lng', 11, 8)->nullable();
            }
            
            // Adresse de collecte détaillée
            if (!Schema::hasColumn('delivery_orders', 'pickup_city')) {
                $table->string('pickup_city', 100)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'pickup_postal_code')) {
                $table->string('pickup_postal_code', 20)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'pickup_contact_name')) {
                $table->string('pickup_contact_name', 100)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'pickup_contact_phone')) {
                $table->string('pickup_contact_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'pickup_instructions')) {
                $table->text('pickup_instructions')->nullable();
            }
            
            // Adresse de livraison détaillée
            if (!Schema::hasColumn('delivery_orders', 'delivery_city')) {
                $table->string('delivery_city', 100)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_postal_code')) {
                $table->string('delivery_postal_code', 20)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_contact_name')) {
                $table->string('delivery_contact_name', 100)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_contact_phone')) {
                $table->string('delivery_contact_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_instructions')) {
                $table->text('delivery_instructions')->nullable();
            }
            
            // Dates et horaires
            if (!Schema::hasColumn('delivery_orders', 'scheduled_pickup_at')) {
                $table->timestamp('scheduled_pickup_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'scheduled_delivery_at')) {
                $table->timestamp('scheduled_delivery_at')->nullable();
            }
            
            // Tentatives de livraison
            if (!Schema::hasColumn('delivery_orders', 'delivery_attempts')) {
                $table->unsignedTinyInteger('delivery_attempts')->default(0);
            }
            if (!Schema::hasColumn('delivery_orders', 'max_delivery_attempts')) {
                $table->unsignedTinyInteger('max_delivery_attempts')->default(3);
            }
            if (!Schema::hasColumn('delivery_orders', 'last_attempt_at')) {
                $table->timestamp('last_attempt_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'next_attempt_at')) {
                $table->timestamp('next_attempt_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'failure_reason')) {
                $table->string('failure_reason')->nullable();
            }
            
            // Preuve de livraison
            if (!Schema::hasColumn('delivery_orders', 'signature_image')) {
                $table->string('signature_image')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'delivery_photo')) {
                $table->string('delivery_photo')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'recipient_name')) {
                $table->string('recipient_name', 100)->nullable();
            }
            
            // Notes et feedback
            if (!Schema::hasColumn('delivery_orders', 'internal_notes')) {
                $table->text('internal_notes')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'customer_rating')) {
                $table->unsignedTinyInteger('customer_rating')->nullable();
            }
            if (!Schema::hasColumn('delivery_orders', 'customer_feedback')) {
                $table->text('customer_feedback')->nullable();
            }
        });
        } // Fin du if (Schema::hasTable('delivery_orders'))
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les nouvelles colonnes de delivery_orders si la table existe
        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $columns = [
                    'driver_id', 'zone_id', 'reference_number', 'priority', 'shipping_type',
                    'dimensions', 'package_count', 'fragile', 'requires_signature',
                    'insurance_cost', 'total_cost',
                    'pickup_city', 'pickup_postal_code', 'pickup_contact_name', 'pickup_contact_phone',
                    'pickup_instructions', 'pickup_lat', 'pickup_lng',
                    'delivery_city', 'delivery_postal_code', 'delivery_contact_name', 'delivery_contact_phone',
                    'delivery_instructions', 'delivery_lat', 'delivery_lng',
                    'scheduled_pickup_at', 'scheduled_delivery_at',
                    'delivery_attempts', 'max_delivery_attempts', 'last_attempt_at', 'next_attempt_at', 'failure_reason',
                    'signature_image', 'delivery_photo', 'recipient_name',
                    'internal_notes', 'customer_rating', 'customer_feedback'
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('delivery_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('delivery_tracking_events');
        Schema::dropIfExists('delivery_drivers');
        Schema::dropIfExists('delivery_zones');
    }
};
