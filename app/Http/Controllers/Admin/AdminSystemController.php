<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

use App\Support\TableExistenceCache;
class AdminSystemController extends Controller
{
    private const BACKUP_FILENAME_PATTERN = '/\A[a-zA-Z0-9._-]+\z/';
    private const LOG_FILENAME_PATTERN = '/\A[a-zA-Z0-9._-]+\.log\z/';

    /**
     * 19. État du système
     */
    public function status()
    {
        $databaseReachable = false;
        $tablesCount = 'N/A';
        $databaseSize = 'N/A';

        try {
            DB::connection()->getPdo();
            $databaseReachable = true;

            $tablesCount = count(Schema::getTableListing());

            if ((string) config('database.default') === 'mysql') {
                $databaseName = (string) config('database.connections.mysql.database');

                if ($databaseName !== '') {
                    $databaseSizeMb = DB::table('information_schema.tables')
                        ->selectRaw('ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb')
                        ->where('table_schema', $databaseName)
                        ->value('size_mb');

                    if ($databaseSizeMb !== null) {
                        $databaseSize = $this->formatBytes((int) round(((float) $databaseSizeMb) * 1024 * 1024));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Admin system status database probe failed', ['error' => $e->getMessage()]);
        }

        $diskRoot = $this->resolveDiskRoot();
        $diskTotal = @disk_total_space($diskRoot);
        $diskFree = @disk_free_space($diskRoot);
        $diskUsed = ($diskTotal !== false && $diskFree !== false) ? ($diskTotal - $diskFree) : false;
        $diskUsedPercent = ($diskTotal !== false && $diskTotal > 0 && $diskUsed !== false)
            ? round(($diskUsed / $diskTotal) * 100, 2)
            : 0;

        $services = [
            'database' => $databaseReachable,
            'cache' => $this->isCacheAvailable(),
            'queue' => filled(config('queue.default')),
            'storage' => is_writable(storage_path()),
        ];

        preg_match('/(\d+)/', app()->version(), $laravelMajorMatch);

        $status = [
            'health' => in_array(false, $services, true) ? 'warning' : 'healthy',
            'php_version' => 'PHP ' . PHP_MAJOR_VERSION . '.x',
            'laravel_version' => 'Laravel ' . ($laravelMajorMatch[1] ?? '?') . '.x',
            'environment' => app()->environment('production') ? 'production' : 'non-production',
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'tables_count' => $tablesCount,
            'db_size' => $databaseSize,
            'database_driver' => $databaseReachable ? 'Masque' : 'Indisponible',
            'database_name' => $databaseReachable ? 'Masquee' : 'Indisponible',
            'disk_total' => $diskTotal !== false ? $this->formatBytes($diskTotal) : 'N/A',
            'disk_free' => $diskFree !== false ? $this->formatBytes($diskFree) : 'N/A',
            'disk_used' => $diskUsed !== false ? $this->formatBytes($diskUsed) : 'N/A',
            'disk_used_percent' => $diskUsedPercent,
            'services' => $services,
        ];

        return view('admin.system.status', compact('status'));
    }

    /**
     * 20. Logs du système
     */
    public function logs(Request $request)
    {
        $resolvedLog = $this->resolveLogPath($request->get('file', 'laravel.log'));
        if ($resolvedLog === null) {
            return back()->with('error', 'Fichier de log invalide.');
        }
        $logFile = $resolvedLog['name'];
        $logPath = $resolvedLog['path'];
        
        $logs = [];
        $logFiles = glob(storage_path('logs/*.log'));
        $logFiles = array_map('basename', $logFiles);

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            // Prendre les dernières 500 lignes
            $lines = explode("\n", $content);
            $lines = array_slice($lines, -500);
            
            foreach ($lines as $line) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\.(\w+):(.*)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'env' => $matches[2],
                        'level' => $matches[3],
                        'message' => trim($matches[4]),
                    ];
                }
            }
            $logs = array_reverse($logs);
        }

        return view('admin.system.logs', compact('logs', 'logFiles', 'logFile'));
    }

    public function clearLogs(Request $request)
    {
        $resolvedLog = $this->resolveLogPath($request->get('file', 'laravel.log'));
        if ($resolvedLog === null) {
            return back()->with('error', 'Fichier de log invalide.');
        }
        $logPath = $resolvedLog['path'];

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return back()->with('success', 'Logs vidés avec succès.');
    }

    /**
     * 21. Cache management
     */
    public function cache()
    {
        $cacheDriver = config('cache.default');
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $routesCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));
        $viewsCached = count(glob(storage_path('framework/views/*.php'))) > 0;

        return view('admin.system.cache', compact('cacheDriver', 'configCached', 'routesCached', 'viewsCached'));
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');

        switch ($type) {
            case 'config':
                Artisan::call('config:clear');
                break;
            case 'route':
                Artisan::call('route:clear');
                break;
            case 'view':
                Artisan::call('view:clear');
                break;
            case 'cache':
                Artisan::call('cache:clear');
                break;
            case 'all':
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
                break;
        }

        return back()->with('success', 'Cache vidé avec succès.');
    }

    public function optimizeCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return back()->with('success', 'Cache optimisé avec succès.');
    }

    /**
     * 22. Maintenance mode
     */
    public function maintenance()
    {
        $isMaintenanceMode = app()->isDownForMaintenance();
        return view('admin.system.maintenance', compact('isMaintenanceMode'));
    }

    public function toggleMaintenance(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            return back()->with('success', 'Mode maintenance désactivé.');
        } else {
            $secret = $request->get('secret', bin2hex(random_bytes(16)));
            Artisan::call('down', [
                '--secret' => $secret,
                '--render' => 'errors.503',
            ]);
            return back()->with('success', "Mode maintenance activé. Secret: $secret");
        }
    }

    /**
     * 23. Backups
     */
    public function backups()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (File::exists($backupPath)) {
            $files = File::files($backupPath);
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => date('d/m/Y H:i', $file->getMTime()),
                ];
            }
        }

        return view('admin.system.backups', compact('backups'));
    }
    
    private function formatBytes($bytes)
    {
        $bytes = (float) $bytes;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function createBackup(Request $request)
    {
        $type = $request->get('type', 'database');
        if (!in_array($type, ['database', 'files', 'full'], true)) {
            return back()->with('error', 'Type de backup invalide.');
        }

        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $filename = $type . '_backup_' . date('Y-m-d_H-i-s');

        try {
            if ($type === 'database') {
                $this->backupDatabase($backupPath . '/' . $filename . '.sql');
            } elseif ($type === 'files') {
                $this->backupFiles($backupPath . '/' . $filename . '.zip');
            } elseif ($type === 'full') {
                $this->backupDatabase($backupPath . '/' . $filename . '_db.sql');
                $this->backupFiles($backupPath . '/' . $filename . '_files.zip');
            }
        } catch (\Throwable $e) {
            Log::error('Backup failed', ['type' => $type, 'error' => $e->getMessage()]);
            return back()->with('error', 'Échec de la création du backup.');
        }

        return back()->with('success', 'Backup créé avec succès.');
    }

    public function downloadBackup($filename)
    {
        $path = $this->resolveBackupPath((string) $filename);
        if (!$path) {
            return back()->with('error', 'Nom de fichier invalide.');
        }
        
        if (File::exists($path)) {
            return response()->download($path);
        }

        return back()->with('error', 'Fichier non trouvé.');
    }

    public function deleteBackup($filename)
    {
        $path = $this->resolveBackupPath((string) $filename);
        if (!$path) {
            return back()->with('error', 'Nom de fichier invalide.');
        }
        
        if (File::exists($path)) {
            File::delete($path);
            return back()->with('success', 'Backup supprimé.');
        }

        return back()->with('error', 'Fichier non trouvé.');
    }

    /**
     * 24. Tâches planifiées (CRON)
     */
    public function scheduledTasks()
    {
        $tasks = [
            ['name' => 'Nettoyage des sessions expirées', 'schedule' => 'Quotidien', 'last_run' => Cache::get('task_session_cleanup_last', 'Jamais')],
            ['name' => 'Envoi des rappels', 'schedule' => 'Toutes les heures', 'last_run' => Cache::get('task_reminders_last', 'Jamais')],
            ['name' => 'Génération des rapports', 'schedule' => 'Hebdomadaire', 'last_run' => Cache::get('task_reports_last', 'Jamais')],
            ['name' => 'Backup automatique', 'schedule' => 'Quotidien', 'last_run' => Cache::get('task_backup_last', 'Jamais')],
            ['name' => 'Nettoyage des fichiers temporaires', 'schedule' => 'Quotidien', 'last_run' => Cache::get('task_temp_cleanup_last', 'Jamais')],
        ];

        return view('admin.system.scheduled-tasks', compact('tasks'));
    }

    public function runTask(Request $request)
    {
        $task = $request->get('task');

        switch ($task) {
            case 'session_cleanup':
                Artisan::call('session:gc');
                Cache::put('task_session_cleanup_last', now()->toDateTimeString());
                break;
            case 'cache_cleanup':
                Artisan::call('cache:clear');
                Cache::put('task_cache_cleanup_last', now()->toDateTimeString());
                break;
        }

        return back()->with('success', 'Tâche exécutée avec succès.');
    }

    /**
     * 25. Gestion des files d'attente
     */
    public function queues()
    {
        $pendingJobs = 0;
        $failedJobs = 0;
        $failedJobsList = collect();
        $processedJobs = 0;

        try {
            if (TableExistenceCache::has('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }
            if (TableExistenceCache::has('failed_jobs')) {
                $failedJobs = DB::table('failed_jobs')->count();
                $failedJobsList = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(50)->get();
            }
        } catch (\Exception $e) {
            // Tables don't exist
        }

        return view('admin.system.queues', compact('pendingJobs', 'failedJobs', 'failedJobsList', 'processedJobs'));
    }

    public function retryFailedJob($id)
    {
        Artisan::call('queue:retry', ['id' => $id]);
        return back()->with('success', 'Job relancé.');
    }

    public function deleteFailedJob($id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        return back()->with('success', 'Job supprimé.');
    }

    public function clearFailedJobs()
    {
        Artisan::call('queue:flush');
        return back()->with('success', 'Tous les jobs échoués ont été supprimés.');
    }

    // Méthodes privées
    private function backupDatabase($path)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = (string) (config('database.connections.mysql.port') ?? '3306');

        if (empty($database) || empty($username) || empty($host)) {
            throw new \RuntimeException('Configuration base de données incomplète.');
        }

        $command = [
            'mysqldump',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--result-file=' . $path,
            $database,
        ];

        $env = [];
        if (!empty($password)) {
            // Avoid exposing DB password in process args
            $env['MYSQL_PWD'] = (string) $password;
        }

        $process = new Process($command, base_path(), $env);
        $process->setTimeout(180);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('mysqldump failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        if (!File::exists($path) || (int) File::size($path) <= 0) {
            throw new \RuntimeException('Le fichier de backup SQL est invalide.');
        }
    }

    private function resolveDiskRoot(): string
    {
        $basePath = base_path();
        $root = realpath($basePath);

        if ($root !== false) {
            return $root;
        }

        return $basePath;
    }

    private function isCacheAvailable(): bool
    {
        try {
            $key = 'admin-system-status-' . bin2hex(random_bytes(6));
            Cache::put($key, 'ok', 10);
            $available = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $available;
        } catch (\Throwable $e) {
            Log::warning('Admin system status cache probe failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function backupFiles($path)
    {
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de creer l\'archive de backup.');
        }

        $files = File::allFiles(storage_path('app/public'));
        foreach ($files as $file) {
            $zip->addFile($file->getRealPath(), $file->getRelativePathname());
        }
        $zip->close();
    }

    private function resolveBackupPath(string $filename): ?string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return null;
        }

        if (!preg_match(self::BACKUP_FILENAME_PATTERN, $filename)) {
            return null;
        }

        if ($filename !== basename($filename)) {
            return null;
        }

        $backupDir = storage_path('app/backups');
        $backupDirReal = realpath($backupDir);
        if ($backupDirReal === false) {
            return null;
        }

        $candidate = $backupDirReal . DIRECTORY_SEPARATOR . $filename;
        $candidateReal = realpath($candidate);

        if ($candidateReal !== false) {
            $prefix = rtrim($backupDirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (!str_starts_with($candidateReal, $prefix)) {
                return null;
            }
            return $candidateReal;
        }

        return $candidate;
    }

    private function resolveLogPath(?string $filename): ?array
    {
        $filename = trim((string) $filename);
        if ($filename === '') {
            $filename = 'laravel.log';
        }

        if ($filename !== basename($filename)) {
            return null;
        }

        if (!preg_match(self::LOG_FILENAME_PATTERN, $filename)) {
            return null;
        }

        $logDir = storage_path('logs');
        $logDirReal = realpath($logDir);
        if ($logDirReal === false) {
            return null;
        }

        $candidate = $logDirReal . DIRECTORY_SEPARATOR . $filename;
        $candidateReal = realpath($candidate);
        $prefix = rtrim($logDirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($candidateReal !== false) {
            if (!str_starts_with($candidateReal, $prefix)) {
                return null;
            }

            return [
                'name' => $filename,
                'path' => $candidateReal,
            ];
        }

        return [
            'name' => $filename,
            'path' => $candidate,
        ];
    }
}
