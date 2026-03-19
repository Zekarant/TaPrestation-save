@extends('layouts.app')

@section('title', 'Mes Enchères - Prestataire')

@push('styles')
<style>
    .auctions-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .auction-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .auction-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12);
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .countdown {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        font-weight: bold;
        color: #ef4444;
    }
    
    .bid-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="auctions-container py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-gavel text-amber-500 mr-2"></i>Mes Enchères
                    </h1>
                    <p class="text-gray-600 mt-1">Gérez vos enchères et suivez les offres</p>
                </div>
                <button class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Créer une enchère
                </button>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-gavel text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Enchères actives</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] ?? 3 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vendues</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['sold'] ?? 12 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-euro-sign text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Revenus totaux</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['revenue'] ?? 2450, 0, ',', ' ') }} €</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Enchérisseurs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['bidders'] ?? 45 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-6" x-data="{ activeTab: 'active' }">
            <div class="flex gap-2 border-b border-gray-200">
                <button @click="activeTab = 'active'" 
                        :class="activeTab === 'active' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-3 font-medium border-b-2 transition-colors">
                    Actives ({{ $stats['active'] ?? 3 }})
                </button>
                <button @click="activeTab = 'pending'" 
                        :class="activeTab === 'pending' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-3 font-medium border-b-2 transition-colors">
                    En attente ({{ $stats['pending'] ?? 2 }})
                </button>
                <button @click="activeTab = 'completed'" 
                        :class="activeTab === 'completed' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-3 font-medium border-b-2 transition-colors">
                    Terminées ({{ $stats['completed'] ?? 12 }})
                </button>
            </div>
        </div>
        
        <!-- Auctions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($auctions ?? [] as $auction)
            <div class="auction-card">
                <div class="relative">
                    <img src="{{ $auction->image ?? asset('images/placeholder.svg') }}" 
                         alt="{{ $auction->title }}" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-3 right-3">
                        <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                            {{ $auction->bids_count ?? 0 }} offres
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $auction->title ?? 'Article en enchère' }}</h3>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Prix actuel</p>
                            <p class="text-xl font-bold text-green-600">{{ number_format($auction->current_bid ?? 150, 0, ',', ' ') }} €</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Prix de départ</p>
                            <p class="text-sm text-gray-600">{{ number_format($auction->starting_price ?? 100, 0, ',', ' ') }} €</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-clock"></i>
                            <span class="countdown">{{ $auction->time_remaining ?? '2j 5h 30m' }}</span>
                        </div>
                        @php
                            $status = $auction->status ?? 'active';
                            $statusClass = match($status) {
                                'active' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'completed' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    
                    <div class="flex gap-2">
                        <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-eye mr-1"></i> Voir
                        </button>
                        <button class="flex-1 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-edit mr-1"></i> Gérer
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-xl shadow-sm">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-gavel text-amber-500 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune enchère</h3>
                    <p class="text-gray-500 mb-4">Vous n'avez pas encore créé d'enchère</p>
                    <button class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Créer ma première enchère
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
