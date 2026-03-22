<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassadors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('referral_code', 32)->unique();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->string('phone', 20)->nullable();
            $table->string('city', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('stripe_account_id', 255)->nullable();
            $table->string('stripe_account_status', 50)->nullable();
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->decimal('total_commission_paid', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ambassador_referral_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer_url', 500)->nullable();
            $table->boolean('converted')->default(false);
            $table->foreignId('converted_prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            $table->timestamp('visited_at');

            $table->index('ambassador_id');
        });

        Schema::create('prestataire_ambassador_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prestataire_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('source', ['referral_link', 'manual_creation', 'admin_assigned']);
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->index('ambassador_id');
        });

        Schema::create('ambassador_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prestataire_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('escrow_transaction_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('order_type', 30);
            $table->decimal('base_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('payout_batch_id')->nullable();
            $table->timestamps();

            $table->index('ambassador_id');
            $table->index('prestataire_id');
            $table->index('escrow_transaction_id');
            $table->index('status');
        });

        Schema::create('ambassador_payout_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->string('stripe_transfer_id', 255)->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('ambassador_id');
        });

        Schema::create('ambassador_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('description', 500);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['ambassador_id', 'created_at']);
        });

        // Add foreign key for payout_batch_id on ambassador_commissions
        Schema::table('ambassador_commissions', function (Blueprint $table) {
            $table->foreign('payout_batch_id')->references('id')->on('ambassador_payout_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop FK first to avoid constraint errors
        Schema::table('ambassador_commissions', function (Blueprint $table) {
            $table->dropForeign(['payout_batch_id']);
        });

        Schema::dropIfExists('ambassador_activity_logs');
        Schema::dropIfExists('ambassador_payout_batches');
        Schema::dropIfExists('ambassador_commissions');
        Schema::dropIfExists('prestataire_ambassador_assignments');
        Schema::dropIfExists('ambassador_referral_visits');
        Schema::dropIfExists('ambassadors');
    }
};
