@extends('layouts.admin-modern')

@section('title', 'Gestion du cache')

@section('content')
<div class="page-header">
    <h1 class="page-title">🗄️ Gestion du cache</h1>
    <p class="page-subtitle">Optimisez les performances du site</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card-base">
        <h3 class="font-semibold text-gray-900 mb-4">
            <i class="fas fa-broom text-blue-600 mr-2"></i> Vider le cache
        </h3>
        <p class="text-gray-600 mb-4">Supprime toutes les données mises en cache</p>
        <form action="{{ route('admin.system.cache.clear') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i> Vider le cache
            </button>
        </form>
    </div>
    
    <div class="card-base">
        <h3 class="font-semibold text-gray-900 mb-4">
            <i class="fas fa-rocket text-green-600 mr-2"></i> Optimiser
        </h3>
        <p class="text-gray-600 mb-4">Régénère les caches de configuration et routes</p>
        <form action="{{ route('admin.system.cache.optimize') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-sync mr-2"></i> Optimiser
            </button>
        </form>
    </div>
</div>

<div class="mt-6 card-base">
    <h3 class="font-semibold text-gray-900 mb-4">État du cache</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $cacheDriver ?? 'file' }}</div>
            <div class="text-sm text-gray-500">Driver</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">{{ $configCached ? '✓' : '✗' }}</div>
            <div class="text-sm text-gray-500">Config cache</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">{{ $routesCached ? '✓' : '✗' }}</div>
            <div class="text-sm text-gray-500">Routes cache</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">{{ $viewsCached ? '✓' : '✗' }}</div>
            <div class="text-sm text-gray-500">Views cache</div>
        </div>
    </div>
</div>
@endsection
