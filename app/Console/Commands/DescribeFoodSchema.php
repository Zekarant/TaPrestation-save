<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DescribeFoodSchema extends Command
{
    protected $signature = 'food:schema
                            {--table=food_orders : Table à inspecter (food_orders, food_products, food_order_items, etc.)}
                            {--create : Afficher SHOW CREATE TABLE}
                            {--columns : Afficher les colonnes (SHOW FULL COLUMNS)}
                            {--indexes : Afficher les index (SHOW INDEX)}
                            {--all : Tout afficher (par défaut)}';

    protected $description = 'Affiche le schéma SQL réel de la base (tables food_*) pour adaptation phpMyAdmin';

    public function handle(): int
    {
        $table = (string) $this->option('table');

        // Whitelist : seuls les noms de table alphanumériques avec underscores sont autorisés
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            $this->error("Nom de table invalide: {$table}");
            return 1;
        }

        if (!Schema::hasTable($table)) {
            $this->error("Table introuvable: {$table}");
            $this->line('Tables disponibles (préfixe food_):');

            $tables = collect(DB::select("SHOW TABLES"))
                ->map(fn($row) => array_values((array) $row)[0])
                ->filter(fn($name) => str_starts_with((string) $name, 'food_'))
                ->values();

            foreach ($tables as $name) {
                $this->line(' - ' . $name);
            }

            return 1;
        }

        $showAll = (bool) $this->option('all') || (! $this->option('create') && ! $this->option('columns') && ! $this->option('indexes'));
        $showCreate = $showAll || (bool) $this->option('create');
        $showColumns = $showAll || (bool) $this->option('columns');
        $showIndexes = $showAll || (bool) $this->option('indexes');

        $this->info('=== Schema: ' . $table . ' ===');

        if ($showCreate) {
            $this->newLine();
            $this->info('--- SHOW CREATE TABLE ---');
            $row = DB::selectOne('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`');
            $createSql = null;
            foreach ((array) $row as $k => $v) {
                if (stripos((string) $k, 'Create Table') !== false) {
                    $createSql = $v;
                    break;
                }
            }
            if ($createSql) {
                $this->line($createSql . ';');
            } else {
                $this->warn('Impossible de lire le CREATE TABLE (format inattendu).');
                $this->line(json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        if ($showColumns) {
            $this->newLine();
            $this->info('--- SHOW FULL COLUMNS ---');
            $cols = DB::select('SHOW FULL COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
            foreach ($cols as $c) {
                $arr = (array) $c;
                $this->line(sprintf(
                    '%-30s %-25s NULL=%-3s DEFAULT=%-12s EXTRA=%s',
                    $arr['Field'] ?? '',
                    $arr['Type'] ?? '',
                    $arr['Null'] ?? '',
                    isset($arr['Default']) ? var_export($arr['Default'], true) : 'NULL',
                    $arr['Extra'] ?? ''
                ));
            }
        }

        if ($showIndexes) {
            $this->newLine();
            $this->info('--- SHOW INDEX ---');
            $idx = DB::select('SHOW INDEX FROM `' . str_replace('`', '``', $table) . '`');
            foreach ($idx as $i) {
                $arr = (array) $i;
                $this->line(sprintf(
                    '%-30s UNIQUE=%-3s COL=%-25s SEQ=%s',
                    $arr['Key_name'] ?? '',
                    isset($arr['Non_unique']) ? ((int) $arr['Non_unique'] === 0 ? 'YES' : 'NO') : 'N/A',
                    $arr['Column_name'] ?? '',
                    $arr['Seq_in_index'] ?? ''
                ));
            }
        }

        $this->newLine();
        $this->info('✅ Copie/colle cette sortie ici pour que j\'adapte le SQL phpMyAdmin exactement à ta DB.');

        return 0;
    }
}
