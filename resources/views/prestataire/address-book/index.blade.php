@extends('layouts.app')

@section('title', 'Carnet d\'adresses - Prestataire')

@push('styles')
<style>
    .address-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .address-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    
    .address-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .address-card.default {
        border-color: #3b82f6;
    }
    
    .add-card {
        background: white;
        border: 2px dashed #d1d5db;
        border-radius: 16px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .add-card:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="address-container py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">
                        <i class="fas fa-address-book text-purple-500 mr-2"></i>Carnet d'adresses
                    </h1>
                    <p class="text-gray-600 mt-1">Gérez vos adresses de livraison et facturation</p>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total adresses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ count($addresses ?? []) ?: 3 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Adresses livraison</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['shipping'] ?? 2 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Adresses facturation</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['billing'] ?? 1 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Addresses Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add New Address Card -->
            <div class="add-card p-6 flex flex-col items-center justify-center min-h-[250px]" 
                 x-data
                 @click="$dispatch('open-modal', 'add-address')">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-plus text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 font-medium">Ajouter une adresse</p>
            </div>
            
            @forelse($addresses ?? [] as $address)
            <div class="address-card p-6 {{ ($address->is_default ?? false) ? 'default' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        @if($address->type ?? 'shipping' === 'shipping')
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                <i class="fas fa-truck mr-1"></i> Livraison
                            </span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">
                                <i class="fas fa-file-invoice mr-1"></i> Facturation
                            </span>
                        @endif
                        @if($address->is_default ?? false)
                            <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-medium">
                                <i class="fas fa-star mr-1"></i> Par défaut
                            </span>
                        @endif
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-edit mr-2"></i> Modifier
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-star mr-2"></i> Définir par défaut
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-trash mr-2"></i> Supprimer
                            </a>
                        </div>
                    </div>
                </div>
                
                <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $address->label ?? 'Adresse principale' }}</h3>
                
                <div class="space-y-2 text-gray-600">
                    <p class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                        <span>{{ $address->street ?? '123 Rue de la Paix' }}</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-city text-gray-400"></i>
                        <span>{{ $address->postal_code ?? '75001' }} {{ $address->city ?? 'Paris' }}</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-globe text-gray-400"></i>
                        <span>{{ $address->country ?? 'France' }}</span>
                    </p>
                    @if($address->phone ?? null)
                    <p class="flex items-center gap-2">
                        <i class="fas fa-phone text-gray-400"></i>
                        <span>{{ $address->phone }}</span>
                    </p>
                    @endif
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                    <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                    @if(!($address->is_default ?? false))
                    <button class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-star mr-1"></i> Par défaut
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <!-- Default sample addresses for demo -->
            <div class="address-card p-6 default">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                            <i class="fas fa-truck mr-1"></i> Livraison
                        </span>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-medium">
                            <i class="fas fa-star mr-1"></i> Par défaut
                        </span>
                    </div>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">Adresse principale</h3>
                <div class="space-y-2 text-gray-600">
                    <p class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                        <span>123 Rue de la République</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-city text-gray-400"></i>
                        <span>75001 Paris</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-globe text-gray-400"></i>
                        <span>France</span>
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                    <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                </div>
            </div>
            
            <div class="address-card p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">
                            <i class="fas fa-file-invoice mr-1"></i> Facturation
                        </span>
                    </div>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">Siège social</h3>
                <div class="space-y-2 text-gray-600">
                    <p class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                        <span>45 Avenue des Champs-Élysées</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-city text-gray-400"></i>
                        <span>75008 Paris</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-globe text-gray-400"></i>
                        <span>France</span>
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                    <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                    <button class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-star mr-1"></i> Par défaut
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
