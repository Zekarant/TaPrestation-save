@extends('layouts.admin-modern')

@section('title', 'Analyse géographique')

@section('content')
<div class="page-header">
    <h1 class="page-title">🌍 Analyse géographique</h1>
    <p class="page-subtitle">Répartition des utilisateurs par région</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card-base">
        <h3 class="font-semibold mb-4">Top 10 villes</h3>
        <div class="space-y-3">
            @forelse($topCities ?? [] as $index => $city)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-semibold">
                            {{ $index + 1 }}
                        </span>
                        <span>{{ $city['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $city['percentage'] ?? 0 }}%"></div>
                        </div>
                        <span class="font-semibold w-16 text-right">{{ $city['count'] ?? 0 }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée géographique disponible</p>
            @endforelse
        </div>
    </div>
    
    <div class="card-base">
        <h3 class="font-semibold mb-4">Top 10 départements</h3>
        <div class="space-y-3">
            @forelse($topDepartments ?? [] as $index => $dept)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-sm font-semibold">
                            {{ $index + 1 }}
                        </span>
                        <span>{{ $dept['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $dept['percentage'] ?? 0 }}%"></div>
                        </div>
                        <span class="font-semibold w-16 text-right">{{ $dept['count'] ?? 0 }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card-base mt-6">
    <h3 class="font-semibold mb-4">Couverture de service</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-blue-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalCities ?? 0 }}</div>
            <div class="text-sm text-gray-500">Villes couvertes</div>
        </div>
        <div class="p-4 bg-green-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalDepartments ?? 0 }}</div>
            <div class="text-sm text-gray-500">Départements</div>
        </div>
        <div class="p-4 bg-purple-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $totalRegions ?? 0 }}</div>
            <div class="text-sm text-gray-500">Régions</div>
        </div>
        <div class="p-4 bg-orange-50 rounded-lg text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $averagePerCity ?? 0 }}</div>
            <div class="text-sm text-gray-500">Moy. par ville</div>
        </div>
    </div>
</div>
@endsection
