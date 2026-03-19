<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Support\TableExistenceCache;

trait HandlesEmptyTables
{
    /**
     * Créer un paginateur vide
     */
    protected function emptyPaginator(int $perPage = 20): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            $perPage,
            1,
            ['path' => request()->url()]
        );
    }

    /**
     * Vérifier si une table existe
     */
    protected function tableExists(string $table): bool
    {
        return TableExistenceCache::has($table);
    }

    /**
     * Vérifier si plusieurs tables existent
     */
    protected function tablesExist(array $tables): bool
    {
        foreach ($tables as $table) {
            if (!TableExistenceCache::has($table)) {
                return false;
            }
        }
        return true;
    }
}
