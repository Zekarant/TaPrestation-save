@extends('layouts.app')

@section('title', 'Demande de location #' . $request->id)

@section('content')
<style>
    .bg-green-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .text-green-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 12px;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    @media (min-width: 640px) {
        .status-badge {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 20px;
        }
    }
    
    .status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
    }
    
    .status-badge.confirmed {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-color: #10b981;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
    }
    
    .status-badge.refused {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-color: #ef4444;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    
    .status-badge i {
        margin-right: 4px;
        font-size: 12px;
    }

    @media (min-width: 640px) {
        .status-badge i {
            margin-right: 6px;
            font-size: 14px;
        }
    }
    
    /* Mobile optimizations */
    @media (max-width: 640px) {
        .mobile-stack {
            flex-direction: column !important;
        }
        .mobile-full {
            width: 100% !important;
        }
        .mobile-text-sm {
            font-size: 0.875rem !important;
        }
        .mobile-p-3 {
            padding: 0.75rem !important;
        }
        .mobile-gap-2 {
            gap: 0.5rem !important;
        }
    }

    /* Safe area for mobile */
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .mobile-safe-bottom {
            padding-bottom: calc(5rem + env(safe-area-inset-bottom)) !important;
        }
    }
</style>

<div class="bg-green-50 mobile-safe-bottom">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête (même structure que les autres pages détails) -->
            <div class="flex items-start justify-between gap-4 mb-4 sm:mb-6">
                <div class="flex items-start gap-4 min-w-0">
                    <a href="{{ route('prestataire.bookings.index') }}" class="text-green-600 hover:text-green-800 mt-1">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-extrabold text-green-900 leading-tight">Détails de la demande de location</h1>
                        <div class="mt-1 text-sm sm:text-base text-green-700 truncate">
                            Demande #{{ $request->id }} - {{ Str::limit($request->equipment->name ?? 'Équipement supprimé', 60) }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-3">
                    @php
                        $requestStatus = (string) $request->status;
                        $cancelHours = max(0, (int) ($request->equipment?->cancellation_hours ?? 24));
                        $startDate = null;
                        if ($request->start_date) {
                            try {
                                $startDate = \Illuminate\Support\Carbon::parse($request->start_date)->startOfDay();
                            } catch (\Throwable $e) {
                                $startDate = null;
                            }
                        }
                        $cancelDeadline = $startDate ? $startDate->copy()->subHours($cancelHours) : null;
                        $canCancelNow = in_array($requestStatus, ['accepted', 'confirmed', 'in_preparation'], true)
                            && (!$cancelDeadline || now()->lessThanOrEqualTo($cancelDeadline));
                        $cancelDeadlinePassed = in_array($requestStatus, ['accepted', 'confirmed', 'in_preparation'], true) && !$canCancelNow;
                        $hasPrestataireCancelRoute = \Illuminate\Support\Facades\Route::has('prestataire.equipment-rental-requests.cancel');
                    @endphp
                    <span class="status-badge
                        @if($request->status === 'pending') pending
                        @elseif(in_array($request->status, ['accepted', 'confirmed', 'in_preparation'], true)) confirmed
                        @elseif(in_array($request->status, ['rejected', 'cancelled'], true)) refused
                        @endif">
                        @if($request->status === 'pending')
                            <i class="fas fa-clock"></i> En attente de confirmation
                        @elseif(in_array($request->status, ['accepted', 'confirmed'], true))
                            <i class="fas fa-check-circle"></i> Acceptée
                        @elseif($request->status === 'in_preparation')
                            <i class="fas fa-tools"></i> En préparation
                        @elseif($request->status === 'rejected')
                            <i class="fas fa-ban"></i> Refusée
                        @elseif($request->status === 'cancelled')
                            <i class="fas fa-ban"></i> Annulée
                        @endif
                    </span>

                    <div class="flex flex-wrap gap-2 justify-end">
                        @if($request->status === 'pending' && $request->equipment)
                            <button type="button" id="acceptRequestBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-check mr-1"></i>Accepter
                            </button>
                            <button type="button" id="rejectRequestBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-times mr-1"></i>Refuser
                            </button>
                        @elseif($canCancelNow && $hasPrestataireCancelRoute)
                            <form method="POST" action="{{ route('prestataire.equipment-rental-requests.cancel', $request) }}" onsubmit="return confirm('Confirmer l\\'annulation de cette location ?')">
                                @csrf
                                <input type="hidden" name="cancellation_reason" value="Annulée par le prestataire">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                    <i class="fas fa-ban mr-1"></i>Annuler la location
                                </button>
                            </form>
                        @elseif($cancelDeadlinePassed)
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">
                                Délai d'annulation dépassé
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 sm:mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 sm:mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($request->status === 'pending' && !$request->equipment)
            <div class="mb-4 sm:mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Impossible d'accepter ou refuser cette demande car l'équipement a été supprimé.
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-4 sm:space-y-8">
                <!-- Informations du client -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Informations du client</h2>
                    </div>
                    <!-- Layout vertical sur mobile, horizontal sur desktop -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:space-x-6 space-y-4 sm:space-y-0">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden mb-2">
                                @if($request->client && $request->client->user && $request->client->user->profile_photo_url)
                                    <img src="{{ $request->client->user->profile_photo_url }}" alt="{{ $request->client->first_name }} {{ $request->client->last_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-linear-to-br from-green-100 to-green-200 flex items-center justify-center">
                                        <span class="text-green-700 font-bold text-xl">
                                            {{ substr($request->client->first_name ?? '', 0, 1) }}{{ substr($request->client->last_name ?? '', 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-green-900">{{ $request->client->first_name }} {{ $request->client->last_name }}</h3>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="bg-green-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Email</div>
                                            <div class="text-sm font-semibold text-green-900">{{ $request->client->user->email ?? $request->client->email }}</div>
                                        </div>
                                    </div>
                                    @if($request->client->phone)
                                    <div class="bg-green-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Téléphone</div>
                                            <div class="text-sm font-semibold text-green-900">{{ $request->client->phone }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="space-y-4">
                                    @if($request->client->address)
                                    <div class="bg-green-50 rounded-lg p-3 flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Adresse</div>
                                            <div class="text-sm font-semibold text-green-900">{{ $request->client->address }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="bg-green-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Membre depuis</div>
                                            <div class="text-sm font-semibold text-green-900">{{ $request->client->created_at->format('F Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails de l'équipement -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Équipement demandé</h2>
                    </div>
                    @if($request->equipment)
                    <div class="flex flex-col md:flex-row md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        @if($request->equipment->main_photo)
                        <div class="w-full md:w-32 h-32 bg-green-50 border border-green-200 rounded-xl overflow-hidden flex-shrink-0">
                            <x-media-image :path="$request->equipment->main_photo" :alt="$request->equipment->name" class="w-full h-full object-cover" />
                        </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-green-900 mb-2">{{ $request->equipment->name }}</h3>
                            <p class="text-green-700 mb-4">{{ $request->equipment->brand }} {{ $request->equipment->model }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-xs font-medium text-green-600 uppercase tracking-wide">État</div>
                                    <div class="text-sm font-semibold text-green-900 mt-1">{{ $request->equipment->formatted_condition }}</div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Prix/jour</div>
                                    <div class="text-sm font-semibold text-green-900 mt-1">{{ number_format($request->equipment->daily_rate, 2) }}€</div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-xs font-medium text-green-600 uppercase tracking-wide">Disponibilité</div>
                                    <div class="text-sm font-semibold text-green-600 mt-1">Disponible</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('prestataire.equipment.show', $request->equipment) }}" 
                                   class="inline-flex items-center text-green-600 hover:text-green-800 text-sm font-medium">
                                    <span>Voir la fiche complète</span>
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div class="text-green-400 mb-2">
                            <i class="fas fa-exclamation-triangle text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-green-900 mb-2">Équipement supprimé</h3>
                        <p class="text-green-700">L'équipement associé à cette demande n'existe plus.</p>
                    </div>
                    @endif
                </div>
                
                <!-- Détails de la demande -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002-2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Détails de la demande</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
                        <div>
                            <h3 class="font-semibold text-green-900 mb-4 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6"></path>
                                </svg>
                                <span>Période de location</span>
                            </h3>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-green-700 font-medium">Date de début</span>
                                    <span class="font-semibold text-green-900">{{ $request->start_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-green-700 font-medium">Date de fin</span>
                                    <span class="font-semibold text-green-900">{{ $request->end_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="border-t border-green-200 pt-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-green-700 font-medium">Durée totale</span>
                                        <span class="font-bold text-green-600">{{ $request->start_date->diffInDays($request->end_date) + 1 }} jour(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="font-semibold text-green-900 mb-4 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                                <span>Options de livraison</span>
                            </h3>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-3">
                                <div class="flex items-center space-x-3">
                                    @if($request->delivery_required)
                                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <span class="font-medium text-green-900">Livraison demandée</span>
                                    @else
                                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                        <span class="text-green-700">Récupération sur place</span>
                                    @endif
                                </div>
                                
                                @if($request->delivery_required && $request->delivery_address)
                                <div class="border-t border-green-200 pt-3">
                                    <div class="text-sm text-green-700 font-medium mb-1">Adresse de livraison</div>
                                    <div class="text-green-900">{{ $request->delivery_address }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($request->message)
                    <div class="mt-8">
                        <h3 class="font-semibold text-green-900 mb-4 flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>Message du client</span>
                        </h3>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-green-900 leading-relaxed">{{ $request->message }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Historique -->
                @if($request->status !== 'pending')
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Historique</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-green-900">Demande créée</p>
                                <p class="text-sm text-green-600">{{ $request->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($request->status === 'accepted')
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-green-900">Demande acceptée</p>
                                <p class="text-sm text-green-600">{{ $request->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @elseif($request->status === 'rejected')
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-green-900">Demande refusée</p>
                                <p class="text-sm text-green-600">{{ $request->updated_at->format('d/m/Y à H:i') }}</p>
                                @if($request->rejection_reason)
                                <p class="text-sm text-green-700 mt-1">Raison: {{ $request->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Colonne latérale -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Récapitulatif financier -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Récapitulatif</h2>
                    </div>
                    <div class="space-y-3 sm:space-y-4">
                        <div class="bg-green-50 rounded-lg p-3 sm:p-4 border border-green-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs sm:text-sm text-green-600">Location ({{ $request->start_date->diffInDays($request->end_date) + 1 }} jours)</span>
                                <span class="font-semibold text-green-900 text-sm">{{ number_format(abs($request->rental_amount ?? $request->total_amount ?? 0), 2, ',', ' ') }}€</span>
                            </div>
                            
                            @if($request->delivery_required && isset($request->delivery_cost) && $request->delivery_cost > 0)
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs sm:text-sm text-green-600">Livraison</span>
                                <span class="font-semibold text-green-900 text-sm">{{ number_format(abs($request->delivery_cost), 2, ',', ' ') }}€</span>
                            </div>
                            @endif
                            
                            <div class="border-t border-green-300 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-green-900 text-sm">Total</span>
                                    <span class="font-bold text-lg sm:text-xl text-green-600">{{ number_format(abs($request->total_amount ?? 0), 2, ',', ' ') }}€</span>
                                </div>
                            </div>
                        </div>
                        
                        @if(isset($request->deposit_amount) && $request->deposit_amount > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-yellow-800 font-medium text-xs sm:text-sm">Caution demandée</span>
                                <span class="font-bold text-yellow-900 text-sm">{{ number_format(abs($request->deposit_amount), 2, ',', ' ') }}€</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Paiement & Escrow -->
                @php
                    $paymentContext = $paymentContext ?? [];
                    $logicalPaymentStatus = $paymentContext['logical_status'] ?? 'pending';
                    $paymentTypeLabel = strtolower((string) ($paymentContext['payment_type'] ?? ''));
                    $paymentTx = $paymentContext['transaction'] ?? null;
                    $paymentEscrow = $paymentContext['escrow'] ?? null;
                    $escrowRow = [];
                @endphp
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1a3 3 0 01-3-3m6 0a3 3 0 01-3 3"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Paiement</h2>
                    </div>

                    <div class="space-y-3">
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Statut</div>
                            <div class="text-sm font-semibold
                                @if($logicalPaymentStatus === 'paid') text-emerald-700
                                @elseif($logicalPaymentStatus === 'partial') text-amber-700
                                @elseif($logicalPaymentStatus === 'refunded') text-slate-700
                                @else text-gray-700 @endif">
                                @if($logicalPaymentStatus === 'paid')
                                    Payé
                                @elseif($logicalPaymentStatus === 'partial')
                                    Acompte payé (solde restant)
                                @elseif($logicalPaymentStatus === 'refunded')
                                    Remboursé
                                @else
                                    En attente de paiement
                                @endif
                            </div>
                        </div>

                        @if($paymentTypeLabel !== '')
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Type de paiement</div>
                                <div class="text-sm font-semibold text-green-900">
                                    @if($paymentTypeLabel === 'deposit')
                                        Acompte + caution
                                    @elseif($paymentTypeLabel === 'balance')
                                        Solde
                                    @elseif(in_array($paymentTypeLabel, ['full', 'payment'], true))
                                        Paiement total
                                    @else
                                        {{ ucfirst($paymentTypeLabel) }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($paymentTx)
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Transaction</div>
                                <div class="text-sm font-semibold text-green-900">#{{ $paymentTx->id }}</div>
                                @if(!empty($paymentTx->stripe_payment_intent_id))
                                    <div class="text-xs text-green-700 mt-1 break-all">{{ $paymentTx->stripe_payment_intent_id }}</div>
                                @endif
                                @if(!empty($paymentTx->paid_at))
                                    <div class="text-xs text-green-700 mt-1">Payé le {{ optional($paymentTx->paid_at)->format('d/m/Y à H:i') }}</div>
                                @endif
                            </div>
                        @endif

                        @if($paymentEscrow)
                            @php
                                $escrowRow = (array) $paymentEscrow;
                                $escrowStatus = strtolower((string) ($escrowRow['status'] ?? 'unknown'));
                            @endphp
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Escrow</div>
                                <div class="text-sm font-semibold text-green-900">#{{ $escrowRow['id'] ?? 'N/A' }}</div>
                                <div class="text-xs text-green-700 mt-1">Statut escrow: {{ $escrowStatus }}</div>
                                @if(!empty($escrowRow['stripe_payment_intent_id']))
                                    <div class="text-xs text-green-700 mt-1 break-all">{{ $escrowRow['stripe_payment_intent_id'] }}</div>
                                @endif
                            </div>
                        @endif

                        @php
                            $securityDeposit = (float) ($request->equipment->security_deposit ?? $request->security_deposit ?? 0);
                        @endphp
                        @if($securityDeposit > 0)
                            @php
                                $escrowMeta = [];
                                if (!empty($escrowRow['metadata'] ?? null)) {
                                    try {
                                        $escrowMeta = is_array($escrowRow['metadata'])
                                            ? $escrowRow['metadata']
                                            : (json_decode((string) $escrowRow['metadata'], true) ?: []);
                                    } catch (\Throwable $e) {
                                        $escrowMeta = [];
                                    }
                                }

                                $depositStatus = strtolower((string) ($request->deposit_status ?? ($escrowMeta['deposit_status'] ?? 'pending')));
                                $depositRetained = (float) ($request->deposit_retained ?? ($escrowMeta['deposit_retained'] ?? 0));
                                $depositReason = trim((string) ($request->deposit_retention_reason ?? ($escrowMeta['deposit_retention_reason'] ?? '')));
                                $depositReturned = max(0, $securityDeposit - $depositRetained);
                                if (isset($escrowMeta['deposit_returned'])) {
                                    $depositReturned = max(0, (float) $escrowMeta['deposit_returned']);
                                }

                                $depositProcessedAt = null;
                                $processedCandidate = $request->equipment_returned_at ?? ($escrowMeta['deposit_processed_at'] ?? null);
                                if (!empty($processedCandidate)) {
                                    try {
                                        $depositProcessedAt = \Illuminate\Support\Carbon::parse($processedCandidate)->format('d/m/Y à H:i');
                                    } catch (\Throwable $e) {
                                        $depositProcessedAt = null;
                                    }
                                }

                                $isReturned = $depositStatus === 'returned' || ($depositRetained <= 0 && !empty($depositProcessedAt));
                                $isRetained = $depositStatus === 'retained' || ($depositRetained >= $securityDeposit && $securityDeposit > 0);
                                $isPartial = $depositStatus === 'partial' || (!$isReturned && !$isRetained && $depositRetained > 0);
                            @endphp

                            <div class="rounded-lg p-3 border {{ $isReturned ? 'bg-emerald-50 border-emerald-200' : ($isPartial ? 'bg-amber-50 border-amber-200' : ($isRetained ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200')) }}">
                                <div class="text-xs font-medium mb-1 {{ $isReturned ? 'text-emerald-700' : ($isPartial ? 'text-amber-700' : ($isRetained ? 'text-red-700' : 'text-gray-700')) }}">
                                    Suivi caution client
                                </div>

                                @if($isReturned)
                                    <div class="text-sm font-semibold text-emerald-800">Restituée au client: {{ number_format($depositReturned, 2) }}€</div>
                                @elseif($isPartial)
                                    <div class="text-sm font-semibold text-amber-800">
                                        Retenue: {{ number_format($depositRetained, 2) }}€ • Restituée: {{ number_format($depositReturned, 2) }}€
                                    </div>
                                @elseif($isRetained)
                                    <div class="text-sm font-semibold text-red-800">Retenue intégrale: {{ number_format($depositRetained, 2) }}€</div>
                                @else
                                    <div class="text-sm font-semibold text-gray-800">En attente de traitement après retour</div>
                                @endif

                                @if($depositReason !== '')
                                    <div class="text-xs mt-1 {{ $isRetained || $isPartial ? 'text-red-700' : 'text-gray-600' }}">{{ $depositReason }}</div>
                                @endif
                                @if($depositProcessedAt)
                                    <div class="text-xs mt-1 text-gray-600">Traitée le {{ $depositProcessedAt }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Informations complémentaires -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Informations</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Demande créée</div>
                            <div class="text-sm font-semibold text-green-900">{{ $request->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        
                        @if($request->status !== 'pending')
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Dernière mise à jour</div>
                            <div class="text-sm font-semibold text-green-900">{{ $request->updated_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        @endif
                        
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Référence</div>
                            <div class="text-sm font-semibold text-green-900 font-mono">#{{ $request->id }}</div>
                        </div>
                        
                        @if($request->request_number)
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                            <div class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Numéro de demande</div>
                            <div class="text-sm font-semibold text-green-900 font-mono">{{ $request->request_number }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modale de confirmation d'acceptation -->
<div id="acceptConfirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-green-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-50">
                    <svg class="h-7 w-7 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation d'acceptation</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir accepter cette demande de location ?
                </p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelAcceptBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmAcceptBtn" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                        Accepter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modale de confirmation de refus -->
<div id="rejectConfirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-red-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation de refus</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir refuser cette demande de location ?
                </p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelRejectBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmRejectBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                        Refuser
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accept request modal elements
    const acceptBtn = document.getElementById('acceptRequestBtn');
    const acceptModal = document.getElementById('acceptConfirmationModal');
    const cancelAcceptBtn = document.getElementById('cancelAcceptBtn');
    const confirmAcceptBtn = document.getElementById('confirmAcceptBtn');
    
    // Reject request modal elements
    const rejectBtn = document.getElementById('rejectRequestBtn');
    const rejectModal = document.getElementById('rejectConfirmationModal');
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    
    // Handle accept button click
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            acceptModal.classList.remove('hidden');
            
            // Add animation classes
            setTimeout(() => {
                acceptModal.classList.remove('opacity-0');
                const modalContent = acceptModal.querySelector('.modal-show');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('opacity-0');
            }, 10);
        });
    }
    
    // Handle reject button click
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            rejectModal.classList.remove('hidden');
            
            // Add animation classes
            setTimeout(() => {
                rejectModal.classList.remove('opacity-0');
                const modalContent = rejectModal.querySelector('.modal-show');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('opacity-0');
            }, 10);
        });
    }
    
    // Handle cancel accept
    if (cancelAcceptBtn) {
        cancelAcceptBtn.addEventListener('click', function() {
            closeAcceptModal();
        });
    }
    
    // Handle confirm accept
    if (confirmAcceptBtn) {
        confirmAcceptBtn.addEventListener('click', function() {
            // Create a form dynamically and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('prestataire.equipment-rental-requests.accept', $request) }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PATCH';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Handle cancel reject
    if (cancelRejectBtn) {
        cancelRejectBtn.addEventListener('click', function() {
            closeRejectModal();
        });
    }
    
    // Handle confirm reject
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            // Create a form dynamically and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('prestataire.equipment-rental-requests.reject', $request) }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PATCH';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Close modals when clicking outside
    if (acceptModal) {
        acceptModal.addEventListener('click', function(e) {
            if (e.target === acceptModal) {
                closeAcceptModal();
            }
        });
    }
    
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) {
            if (e.target === rejectModal) {
                closeRejectModal();
            }
        });
    }
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (acceptModal && !acceptModal.classList.contains('hidden')) {
                closeAcceptModal();
            }
            if (rejectModal && !rejectModal.classList.contains('hidden')) {
                closeRejectModal();
            }
        }
    });
    
    // Function to close accept modal with animation
    function closeAcceptModal() {
        const modalContent = acceptModal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
        }
        if (acceptModal) {
            acceptModal.classList.add('opacity-0');
            
            setTimeout(() => {
                acceptModal.classList.add('hidden');
            }, 300);
        }
    }
    
    // Function to close reject modal with animation
    function closeRejectModal() {
        const modalContent = rejectModal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
        }
        if (rejectModal) {
            rejectModal.classList.add('opacity-0');
            
            setTimeout(() => {
                rejectModal.classList.add('hidden');
            }, 300);
        }
    }
});
</script>
@endpush

