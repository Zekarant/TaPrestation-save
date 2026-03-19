@extends('layouts.admin-modern')

@section('title', 'État du système')
@section('page-title', 'État du système')

@section('content')
<div class="page-header">
    <h1 class="page-title">🖥️ État du système</h1>
    <p class="page-subtitle">Surveillance et santé de la plateforme</p>
</div>

<!-- System Status Cards -->
<div class="stats-grid mb-8">
    <div class="card-base stat-card {{ ($status['health'] ?? 'unknown') === 'healthy' ? 'success' : 'warning' }}">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Santé globale</p>
                <p class="text-value text-{{ ($status['health'] ?? 'unknown') === 'healthy' ? 'green' : 'yellow' }}-600">
                    {{ ($status['health'] ?? 'unknown') === 'healthy' ? 'Sain' : 'Attention' }}
                </p>
            </div>
            <div class="w-12 h-12 bg-{{ ($status['health'] ?? 'unknown') === 'healthy' ? 'green' : 'yellow' }}-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-{{ ($status['health'] ?? 'unknown') === 'healthy' ? 'check-circle' : 'exclamation-triangle' }} text-{{ ($status['health'] ?? 'unknown') === 'healthy' ? 'green' : 'yellow' }}-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card primary">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Version PHP</p>
                <p class="text-value text-blue-600">{{ $status['php_version'] ?? phpversion() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-php text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card info">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Version Laravel</p>
                <p class="text-value text-cyan-600">{{ $status['laravel_version'] ?? app()->version() }}</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-laravel text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card warning">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Environnement</p>
                <p class="text-value text-yellow-600">{{ ucfirst($status['environment'] ?? app()->environment()) }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-server text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- System Details -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Server Info -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-server mr-2 text-blue-600"></i> Informations serveur
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Système d'exploitation</span>
                <span class="font-medium">{{ PHP_OS }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Mémoire utilisée</span>
                <span class="font-medium">{{ $status['memory_usage'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Limite mémoire PHP</span>
                <span class="font-medium">{{ ini_get('memory_limit') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Max upload</span>
                <span class="font-medium">{{ ini_get('upload_max_filesize') }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-600">Temps d'exécution max</span>
                <span class="font-medium">{{ ini_get('max_execution_time') }}s</span>
            </div>
        </div>
    </div>

    <!-- Database Info -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-database mr-2 text-green-600"></i> Base de données
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Connexion</span>
                <span class="font-medium text-green-600">
                    <i class="fas fa-check-circle mr-1"></i> Connectée
                </span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Driver</span>
                <span class="font-medium">{{ $status['database_driver'] ?? 'Masque' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Base de données</span>
                <span class="font-medium">{{ $status['database_name'] ?? 'Masquee' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Tables</span>
                <span class="font-medium">{{ $status['tables_count'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-600">Taille</span>
                <span class="font-medium">{{ $status['db_size'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Services Status -->
<div class="card-base mb-8">
    <h3 class="text-lg font-bold text-gray-900 mb-4">
        <i class="fas fa-cogs mr-2 text-purple-600"></i> État des services
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(($status['services'] ?? []) as $service => $state)
        <div class="p-4 rounded-lg {{ $state ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full {{ $state ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                    <i class="fas {{ $state ? 'fa-check' : 'fa-times' }} {{ $state ? 'text-green-600' : 'text-red-600' }}"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ ucfirst($service) }}</p>
                    <p class="text-sm {{ $state ? 'text-green-600' : 'text-red-600' }}">
                        {{ $state ? 'Actif' : 'Inactif' }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Disk Usage -->
<div class="card-base">
    <h3 class="text-lg font-bold text-gray-900 mb-4">
        <i class="fas fa-hdd mr-2 text-orange-600"></i> Utilisation du disque
    </h3>
    <div class="space-y-4">
        <div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Stockage total</span>
                <span class="font-medium">{{ $status['disk_total'] ?? 'N/A' }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $status['disk_used_percent'] ?? 0 }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-sm">
                <span class="text-gray-500">Utilisé: {{ $status['disk_used'] ?? 'N/A' }}</span>
                <span class="text-gray-500">Libre: {{ $status['disk_free'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
