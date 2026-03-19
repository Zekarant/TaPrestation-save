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
        // Tables pour les paramètres du site
        if (!Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('text'); // text, textarea, boolean, number, json
                $table->timestamps();
            });
        }

        // Table pour les pages statiques
        if (!Schema::hasTable('static_pages')) {
            Schema::create('static_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamps();
            });
        }

        // Table pour les FAQ
        if (!Schema::hasTable('faq_categories')) {
            Schema::create('faq_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('faq_categories')->nullOnDelete();
                $table->text('question');
                $table->longText('answer');
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Table pour les bannières
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image');
                $table->string('link')->nullable();
                $table->string('position')->default('home'); // home, sidebar, footer, etc.
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }

        // Table pour les témoignages
        if (!Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('company')->nullable();
                $table->text('content');
                $table->integer('rating')->default(5);
                $table->string('photo')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Table pour les templates d'email
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('general'); // general, booking, payment, notification
                $table->string('subject');
                $table->longText('body');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Table pour les transactions (si pas existante)
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('prestataire_id')->nullable();
                $table->string('reference')->unique();
                $table->decimal('amount', 10, 2);
                $table->decimal('commission', 10, 2)->default(0);
                $table->string('type')->default('payment'); // payment, refund, withdrawal, payout
                $table->string('status')->default('pending'); // pending, completed, failed, cancelled
                $table->string('payment_method')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Table pour les logs de transactions
        if (!Schema::hasTable('transaction_logs')) {
            Schema::create('transaction_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
                $table->string('action');
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // Table pour les retraits
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('method')->default('bank_transfer');
                $table->string('status')->default('pending'); // pending, completed, rejected
                $table->text('bank_details')->nullable();
                $table->text('admin_notes')->nullable();
                $table->string('transaction_reference')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les remboursements
        if (!Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->text('reason');
                $table->string('status')->default('pending'); // pending, completed, rejected
                $table->text('admin_notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les factures
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('number')->unique();
                $table->string('type')->default('service'); // service, subscription, commission
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->string('status')->default('unpaid'); // unpaid, paid, cancelled
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // Table pour les lignes de facture
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total', 10, 2);
                $table->timestamps();
            });
        }

        // Table pour les versements prestataires
        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prestataire_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('method')->default('bank_transfer');
                $table->string('status')->default('pending'); // pending, completed, cancelled
                $table->text('notes')->nullable();
                $table->string('transaction_reference')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les tickets de support
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('ticket_number')->unique();
                $table->string('subject');
                $table->text('description');
                $table->string('category')->nullable();
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('last_reply_at')->nullable();
                $table->foreignId('last_reply_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('closed_at')->nullable();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les messages des tickets
        if (!Schema::hasTable('support_ticket_messages')) {
            Schema::create('support_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_internal')->default(false);
                $table->timestamps();
            });
        }

        // Table pour l'historique des tickets
        if (!Schema::hasTable('support_ticket_history')) {
            Schema::create('support_ticket_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->text('details')->nullable();
                $table->timestamp('created_at');
            });
        }

        // Table pour les messages de contact
        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->string('status')->default('unread'); // unread, read, pending, replied
                $table->timestamp('read_at')->nullable();
                $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les litiges
        if (!Schema::hasTable('disputes')) {
            Schema::create('disputes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('transaction_id')->nullable();
                $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('prestataire_id')->constrained('users')->cascadeOnDelete();
                $table->string('subject');
                $table->text('description');
                $table->string('status')->default('open'); // open, investigating, resolved, closed
                $table->text('resolution')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les messages des litiges
        if (!Schema::hasTable('dispute_messages')) {
            Schema::create('dispute_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_admin')->default(false);
                $table->timestamp('created_at');
            });
        }

        // Table pour les preuves des litiges
        if (!Schema::hasTable('dispute_evidence')) {
            Schema::create('dispute_evidence', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('file_path');
                $table->string('file_type');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Table pour les catégories d'articles d'aide
        if (!Schema::hasTable('help_categories')) {
            Schema::create('help_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // Table pour les articles d'aide
        if (!Schema::hasTable('help_articles')) {
            Schema::create('help_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('help_categories')->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content');
                $table->integer('order')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_published')->default(false);
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->integer('views')->default(0);
                $table->timestamps();
            });
        }

        // Table pour les IPs bloquées
        if (!Schema::hasTable('blocked_ips')) {
            Schema::create('blocked_ips', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address');
                $table->text('reason')->nullable();
                $table->timestamp('blocked_until')->nullable();
                $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Table pour les logs de connexion
        if (!Schema::hasTable('login_logs')) {
            Schema::create('login_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('email')->nullable();
                $table->string('ip_address');
                $table->string('user_agent')->nullable();
                $table->boolean('successful')->default(true);
                $table->string('failure_reason')->nullable();
                $table->timestamps();
            });
        }

        // Table pour le journal d'audit
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('model_type')->nullable();
                $table->unsignedBigInteger('model_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['model_type', 'model_id']);
            });
        }

        // Ajouter la colonne balance aux prestataires si elle n'existe pas
        if (Schema::hasTable('prestataires') && !Schema::hasColumn('prestataires', 'balance')) {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->decimal('balance', 10, 2)->default(0)->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('help_categories');
        Schema::dropIfExists('dispute_evidence');
        Schema::dropIfExists('dispute_messages');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('support_ticket_history');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('transaction_logs');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('static_pages');
        Schema::dropIfExists('site_settings');
    }
};
