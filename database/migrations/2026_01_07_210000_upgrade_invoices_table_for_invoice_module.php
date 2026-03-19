<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        $driver = DB::getDriverName();

        Schema::table('invoices', function (Blueprint $table) {
            // Columns required by the newer Invoice module (Eloquent model + admin screens)
            if (!Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('id');
                $table->index('invoice_number');
            }

            if (!Schema::hasColumn('invoices', 'prestataire_id')) {
                $table->foreignId('prestataire_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('invoices', 'invoiceable_type')) {
                $table->string('invoiceable_type')->nullable()->after('prestataire_id');
            }
            if (!Schema::hasColumn('invoices', 'invoiceable_id')) {
                $table->unsignedBigInteger('invoiceable_id')->nullable()->after('invoiceable_type');
            }

            if (!Schema::hasColumn('invoices', 'billing_name')) {
                $table->string('billing_name')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'billing_email')) {
                $table->string('billing_email')->nullable();
            }

            if (!Schema::hasColumn('invoices', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('invoices', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'net_amount')) {
                $table->decimal('net_amount', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 3)->nullable();
            }

            if (!Schema::hasColumn('invoices', 'issued_at')) {
                $table->timestamp('issued_at')->nullable();
                $table->index('issued_at');
            }
            if (!Schema::hasColumn('invoices', 'due_at')) {
                $table->timestamp('due_at')->nullable();
            }

            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable();
            }

            if (!Schema::hasColumn('invoices', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'line_items')) {
                $table->json('line_items')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'terms')) {
                $table->text('terms')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }

            if (!Schema::hasColumn('invoices', 'deleted_at')) {
                $table->softDeletes();
            }

            // Helpful indexes for polymorphic lookups (use prefix length to stay under 767-byte InnoDB limit)
            if (Schema::hasColumn('invoices', 'invoiceable_type') && Schema::hasColumn('invoices', 'invoiceable_id')) {
                $existingIndexes = collect(DB::select('SHOW INDEX FROM `invoices`'))->pluck('Key_name')->unique();
                if (!$existingIndexes->contains('invoices_invoiceable_type_invoiceable_id_index')) {
                    DB::statement('ALTER TABLE `invoices` ADD INDEX `invoices_invoiceable_type_invoiceable_id_index` (`invoiceable_type`(191), `invoiceable_id`)');
                }
            }
        });

        // Backfill best-effort values from the older admin invoices schema (created in 2024_01_15_000000)
        if ($driver === 'mysql' && Schema::hasColumn('invoices', 'number') && Schema::hasColumn('invoices', 'invoice_number')) {
            DB::table('invoices')
                ->whereNull('invoice_number')
                ->whereNotNull('number')
                ->update(['invoice_number' => DB::raw('`number`')]);
        }

        if ($driver === 'mysql' && Schema::hasColumn('invoices', 'created_at') && Schema::hasColumn('invoices', 'issued_at')) {
            DB::table('invoices')
                ->whereNull('issued_at')
                ->whereNotNull('created_at')
                ->update(['issued_at' => DB::raw('`created_at`')]);
        }

        if ($driver === 'mysql' && Schema::hasColumn('invoices', 'tax') && Schema::hasColumn('invoices', 'tax_amount')) {
            DB::table('invoices')
                ->whereNull('tax_amount')
                ->whereNotNull('tax')
                ->update(['tax_amount' => DB::raw('`tax`')]);
        }

        if ($driver === 'mysql' && Schema::hasColumn('invoices', 'due_date') && Schema::hasColumn('invoices', 'due_at')) {
            DB::table('invoices')
                ->whereNull('due_at')
                ->whereNotNull('due_date')
                ->update(['due_at' => DB::raw('`due_date`')]);
        }

        // Try to populate billing info from users for legacy invoices
        if ($driver === 'mysql' && Schema::hasColumn('invoices', 'user_id') && Schema::hasColumn('invoices', 'billing_name') && Schema::hasColumn('invoices', 'billing_email')) {
            DB::statement(
                "UPDATE `invoices` i " .
                "JOIN `users` u ON u.id = i.user_id " .
                "SET i.billing_name = COALESCE(i.billing_name, u.name), " .
                "    i.billing_email = COALESCE(i.billing_email, u.email) " .
                "WHERE i.user_id IS NOT NULL"
            );
        }

        // Map old status to a newer-ish value when possible
        if (Schema::hasColumn('invoices', 'status')) {
            DB::table('invoices')->where('status', 'unpaid')->update(['status' => 'issued']);
        }

        // Ensure currency is present
        if (Schema::hasColumn('invoices', 'currency')) {
            DB::table('invoices')->whereNull('currency')->update(['currency' => 'EUR']);
        }
    }

    public function down(): void
    {
        // Non-destructive migration: do not drop columns.
    }
};
