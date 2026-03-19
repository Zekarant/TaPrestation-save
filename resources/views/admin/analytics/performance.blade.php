@extends('layouts.admin-modern')

@section('title', 'Performance du site')

@section('content')
<div class="page-header">
    <h1 class="page-title">📊 Performance du site</h1>
    <p class="page-subtitle">Métriques de performance et de trafic</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-blue-600">{{ number_format($pageViews ?? 0) }}</div>
        <div class="text-sm text-gray-500">Pages vues (30j)</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-green-600">{{ number_format($uniqueVisitors ?? 0) }}</div>
        <div class="text-sm text-gray-500">Visiteurs uniques</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-purple-600">{{ $averageSessionTime ?? '0:00' }}</div>
        <div class="text-sm text-gray-500">Durée moy. session</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-orange-600">{{ number_format($bounceRate ?? 0, 1) }}%</div>
        <div class="text-sm text-gray-500">Taux de rebond</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card-base">
        <h3 class="font-semibold mb-4">Pages les plus visitées</h3>
        <div class="space-y-3">
            @forelse($topPages ?? [] as $page)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 truncate flex-1">{{ $page['path'] ?? '/' }}</span>
                    <span class="font-semibold ml-4">{{ number_format($page['views'] ?? 0) }}</span>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
            @endforelse
        </div>
    </div>
    
    <div class="card-base">
        <h3 class="font-semibold mb-4">Sources de trafic</h3>
        <div class="space-y-3">
            @forelse($trafficSources ?? [] as $source)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @php
                            $icon = match($source['type'] ?? '') {
                                'organic' => 'fa-search text-green-600',
                                'direct' => 'fa-link text-blue-600',
                                'referral' => 'fa-share text-purple-600',
                                'social' => 'fa-share-alt text-pink-600',
                                default => 'fa-globe text-gray-600'
                            };
                        @endphp
                        <i class="fas {{ $icon }}"></i>
                        <span>{{ $source['name'] ?? 'Autre' }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $source['percentage'] ?? 0 }}%"></div>
                        </div>
                        <span class="font-semibold w-12 text-right">{{ $source['percentage'] ?? 0 }}%</span>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span>Direct</span>
                    <span class="font-semibold">100%</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="card-base mt-6">
    <h3 class="font-semibold mb-4">État du système</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500 mb-1">Version PHP</div>
            <div class="font-semibold">{{ $phpVersion ?? phpversion() }}</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500 mb-1">Version Laravel</div>
            <div class="font-semibold">{{ $laravelVersion ?? app()->version() }}</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500 mb-1">Espace disque</div>
            <div class="font-semibold">{{ $diskSpace ?? 'N/A' }}</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500 mb-1">Uptime</div>
            <div class="font-semibold">{{ $uptime ?? 'N/A' }}</div>
        </div>
    </div>
</div>
@endsection
