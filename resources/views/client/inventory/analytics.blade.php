@extends('layouts.app')

@section('content')
<div class="container py-6">
    <h1 class="text-2xl font-bold mb-4">Analytique inventaire</h1>

    @if(isset($stats))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white shadow-sm rounded p-4">
                <p class="text-sm text-gray-500">Total articles</p>
                <p class="text-2xl font-bold">{{ $stats['total_items'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded p-4">
                <p class="text-sm text-gray-500">Valeur totale</p>
                <p class="text-2xl font-bold">{{ number_format($stats['total_value'] ?? 0, 2) }} €</p>
            </div>
            <div class="bg-white shadow-sm rounded p-4">
                <p class="text-sm text-gray-500">Articles en rupture</p>
                <p class="text-2xl font-bold">{{ $stats['low_stock_count'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white shadow-sm rounded p-4">
            <p class="text-sm text-gray-500 mb-2">Répartition par catégorie</p>
            @if(!empty($stats['items_by_category']))
                <ul class="space-y-1">
                    @foreach($stats['items_by_category'] as $cat => $count)
                        <li class="flex justify-between text-sm">
                            <span>{{ $cat }}</span>
                            <span class="font-semibold">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 text-sm">Aucune donnée.</p>
            @endif
        </div>
    @else
        <p class="text-gray-600">Aucune donnée disponible.</p>
    @endif
</div>
@endsection
