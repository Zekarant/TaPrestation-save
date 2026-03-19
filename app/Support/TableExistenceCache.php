<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Audit 3.13: Cache les résultats de Schema::hasTable() pour éviter
 * les ~270 requêtes DB redondantes par requête HTTP.
 *
 * Usage: TableExistenceCache::has('table_name') au lieu de Schema::hasTable('table_name')
 */
class TableExistenceCache
{
    private static array $cache = [];

    public static function has(string $table): bool
    {
        if (!array_key_exists($table, self::$cache)) {
            self::$cache[$table] = Schema::hasTable($table);
        }

        return self::$cache[$table];
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
