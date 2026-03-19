<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deleted_stripe_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('deleted_stripe_accounts', 'email_hash')) {
                $table->string('email_hash', 64)->nullable()->after('email')->index();
            }
        });

        DB::table('deleted_stripe_accounts')
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $email = strtolower(trim((string) ($row->email ?? '')));

                    DB::table('deleted_stripe_accounts')
                        ->where('id', $row->id)
                        ->update([
                            'email_hash' => $email !== '' ? hash('sha256', $email) : null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('deleted_stripe_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('deleted_stripe_accounts', 'email_hash')) {
                $table->dropColumn('email_hash');
            }
        });
    }
};
