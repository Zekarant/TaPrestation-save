@extends('layouts.admin-modern')

@section('title', 'Détails de l\'équipement - Administration')

@section('content')
<div class="bg-green-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-green-900 mb-2">
                            {{ $equipment->name }}
                        </h1>
                        <p class="text-lg text-green-700">
                            Détails complets de l'équipement
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.equipments.index') }}" class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour à la liste
                        </a>
                        <a href="{{ route('admin.equipments.edit', $equipment) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-edit mr-2"></i>
                            Modifier
                        </a>
                    </div>
                </div>
            </div>

            <!-- Equipment Details -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Image -->
                    <div>
                        @if($equipment->image)
                            <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->name }}" class="w-full h-64 object-cover rounded-lg border border-blue-200">
                        @else
                            <div class="w-full h-64 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-6xl">
                                {{ substr($equipment->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-xl font-bold text-blue-900 mb-4">Informations générales</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Type:</span>
                                    <span class="text-blue-900">{{ ucfirst($equipment->type ?? 'Non défini') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Statut:</span>
                                    @if($equipment->status == 'available')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>Disponible
                                        </span>
                                    @elseif($equipment->status == 'rented')
                                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-clock mr-1"></i>Loué
                                        </span>
                                    @elseif($equipment->status == 'maintenance')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-tools mr-1"></i>Maintenance
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-pause mr-1"></i>Inactif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Prix par jour:</span>
                                    <span class="text-blue-900 font-bold">{{ $equipment->daily_price ?? 0 }}€</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Note moyenne:</span>
                                    <span class="text-blue-900">{{ number_format($stats['average_rating'] ?? 0, 1) }}/5 ({{ $stats['total_reviews'] ?? 0 }} avis)</span>
                                </div>
                            </div>
                        </div>

                        @if($equipment->description)
                        <div>
                            <h3 class="text-xl font-bold text-blue-900 mb-4">Description</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $equipment->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if(isset($stats))
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-blue-600 uppercase tracking-wide">Note Moyenne</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($stats['average_rating'] ?? 0, 1) }}/5</div>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-star text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-green-600 uppercase tracking-wide">Total Avis</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total_reviews'] ?? 0 }}</div>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-comments text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-orange-600 uppercase tracking-wide">Signalements</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['pending_reports'] ?? 0 }}</div>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection