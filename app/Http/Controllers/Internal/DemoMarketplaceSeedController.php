<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DemoMarketplaceSeedController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $defaultConfig = [
            'enabled' => true,
            'token' => env('DEMO_SEED_TOKEN', ''),
            'count' => 250,
            'refresh' => true,
            'use_remote_images' => false,
            'single_use' => false,
        ];

        $configPath = storage_path('app/demo-marketplace-web.json');
        $config = $defaultConfig;

        if (File::exists($configPath)) {
            $decoded = json_decode(File::get($configPath), true);

            if (!is_array($decoded)) {
                return response("Configuration invalide dans storage/app/demo-marketplace-web.json\n", 500)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }

            $config = array_merge($defaultConfig, $decoded);
        }

        $expectedToken = (string) ($config['token'] ?? '');
        $enabled = (bool) ($config['enabled'] ?? false);

        if (!$enabled || $expectedToken === '' || !hash_equals($expectedToken, $token)) {
            abort(404);
        }

        $count = max(1, min(500, (int) ($config['count'] ?? 250)));
        $refresh = (bool) ($config['refresh'] ?? true);
        $useRemoteImages = (bool) ($config['use_remote_images'] ?? false);
        $singleUse = !array_key_exists('single_use', $config) || (bool) $config['single_use'];

        $exitCode = Artisan::call('demo:seed-marketplace', [
            '--count' => $count,
            '--refresh' => $refresh,
            '--without-remote-images' => !$useRemoteImages,
        ]);

        if ($singleUse && File::exists($configPath)) {
            File::delete($configPath);
        }

        $status = $exitCode === 0 ? 200 : 500;
        $prefix = $exitCode === 0 ? "Generation terminee.\n\n" : "Generation echouee.\n\n";

        return response($prefix . Artisan::output(), $status)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
