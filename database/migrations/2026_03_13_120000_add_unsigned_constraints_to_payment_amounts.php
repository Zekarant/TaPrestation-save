<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sécurité: Empêcher les montants négatifs dans les tables de paiement.
     * Les montants négatifs pourraient être exploités pour créditer frauduleusement un compte.
     */
    public function up(): void
    {
        // payment_transactions.amount
        $this->addCheckSafe('payment_transactions', 'amount', 'chk_payment_amount_positive');

        // escrow_transactions - toutes les colonnes de montant possibles
        $this->addCheckSafe('escrow_transactions', 'amount', 'chk_escrow_amount_positive');
        $this->addCheckSafe('escrow_transactions', 'total_amount', 'chk_escrow_total_positive');
        $this->addCheckSafe('escrow_transactions', 'commission_amount', 'chk_escrow_commission_positive');
        $this->addCheckSafe('escrow_transactions', 'platform_fee', 'chk_escrow_platform_fee_positive');
        $this->addCheckSafe('escrow_transactions', 'released_amount', 'chk_escrow_released_positive');
        $this->addCheckSafe('escrow_transactions', 'deposit_amount', 'chk_escrow_deposit_positive');
    }

    /**
     * Ajoute une contrainte CHECK >= 0 si la table et la colonne existent.
     */
    private function addCheckSafe(string $table, string $column, string $constraint): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            try {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraint} CHECK ({$column} >= 0)");
            } catch (\Throwable $e) {
                // Contrainte déjà existante ou colonne incompatible - ignorer
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $constraints = [
            'payment_transactions' => ['chk_payment_amount_positive'],
            'escrow_transactions' => [
                'chk_escrow_amount_positive',
                'chk_escrow_total_positive',
                'chk_escrow_commission_positive',
                'chk_escrow_platform_fee_positive',
                'chk_escrow_released_positive',
                'chk_escrow_deposit_positive',
            ],
        ];

        foreach ($constraints as $table => $names) {
            if (Schema::hasTable($table)) {
                foreach ($names as $name) {
                    try { DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$name}"); } catch (\Throwable $e) {}
                }
            }
        }
    }
};
