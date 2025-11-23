@extends('layouts.app')

@section('title', 'Détails de la réservation #' . ($booking->id ?? 'N/A'))

@section('content')
@php
    // Ensure variables are defined for backward compatibility
    $isMultiSlotSession = $isMultiSlotSession ?? false;
    $allBookings = $allBookings ?? collect([$booking]);
    $relatedBookings = $relatedBookings ?? collect();
    $totalSessionPrice = $totalSessionPrice ?? ($booking->total_price ?? 0);
    
    // Function to clean session ID from notes for display
    function cleanNotesForDisplay($notes) {
        if (!$notes) return null;
        return trim(preg_replace('/\[SESSION:[^\]]+\]/', '', $notes)) ?: null;
    }
@endphp

<style>
    .bg-blue-gradient {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .text-blue-gradient {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px; /* Reduced padding */
        border-radius: 20px; /* Slightly smaller border radius */
        font-weight: 600;
        font-size: 14px;
        border: 2px solid;
        transition: all 0.3s ease;
    }
    
    .status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2); /* Reduced shadow */
    }
    
    .status-badge.confirmed {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-color: #10b981;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2); /* Reduced shadow */
    }
    
    .status-badge.completed {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2); /* Reduced shadow */
    }
    
    .status-badge.refused {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-color: #ef4444;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2); /* Reduced shadow */
    }
    
    .status-badge.cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-color: #ef4444;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2); /* Reduced shadow */
    }
    
    .status-badge i {
        margin-right: 6px; /* Reduced margin */
        font-size: 14px; /* Slightly smaller icon */
    }
</style>

<div class="bg-blue-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-blue-900 mb-2">Détails de la réservation</h1>
                <p class="text-lg text-blue-700">Réservation #{{ $booking->booking_number ?? 'N/A' }} - {{ $booking->service->name ?? 'Service supprimé' }}</p>
            </div>
            
            <div class="flex justify-center mb-8">
                <a href="{{ route('prestataire.bookings.index') }}" 
                   class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-6 py-3 rounded-lg text-center transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> Retour aux réservations
                </a>
            </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statut et actions -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 mb-6">
            <!-- Flex container for responsive layout -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h2 class="text-xl font-bold text-blue-800 border-b border-blue-200 pb-2 sm:border-0 sm:pb-0 sm:mb-0">Statut de la réservation</h2>
                <span class="status-badge sm:ml-auto sm:flex-shrink-0
                    @if(($booking->status ?? '') === 'pending') pending
                    @elseif(($booking->status ?? '') === 'confirmed') confirmed
                    @elseif(($booking->status ?? '') === 'completed') completed
                    @elseif(($booking->status ?? '') === 'cancelled') cancelled
                    @elseif(($booking->status ?? '') === 'refused') refused
                    @endif">
                    @if(($booking->status ?? '') === 'pending') 
                        <i class="fas fa-clock"></i> En attente de confirmation
                    @elseif(($booking->status ?? '') === 'confirmed') 
                        <i class="fas fa-check-circle"></i> Confirmée
                    @elseif(($booking->status ?? '') === 'completed') 
                        <i class="fas fa-check-double"></i> Terminée
                    @elseif(($booking->status ?? '') === 'cancelled') 
                        <i class="fas fa-times-circle"></i> Annulée
                    @elseif(($booking->status ?? '') === 'refused') 
                        <i class="fas fa-ban"></i> Refusée
                    @else
                        <i class="fas fa-question-circle"></i> Statut inconnu
                    @endif
                </span>
            </div>

            <!-- Actions selon le statut -->
            @if(($booking->status ?? '') === 'pending' && $booking->service)
                <div class="mt-4 flex flex-wrap gap-2">
                    <!-- Accept button with modal -->
                    <button type="button" id="acceptBookingBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-3 rounded transition duration-200 text-sm">
                        <i class="fas fa-check mr-1"></i>Accepter
                    </button>
                    <!-- Reject button with modal -->
                    <button type="button" id="rejectBookingBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded transition duration-200 text-sm">
                        <i class="fas fa-times mr-1"></i>Refuser
                    </button>
                </div>
            @elseif(($booking->status ?? '') === 'pending' && !$booking->service)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Impossible d'accepter ou refuser cette réservation car le service a été supprimé.
                    </p>
                </div>
            @elseif(($booking->status ?? '') === 'confirmed' && $booking->service)
                <div class="mt-4 flex flex-wrap gap-2">
                    <!-- Complete booking button with modal -->
                    <button type="button" id="completeBookingBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-3 rounded transition duration-200 text-sm">
                        <i class="fas fa-check-double mr-1"></i>Marquer comme terminée
                    </button>
                </div>
            @elseif(($booking->status ?? '') === 'confirmed' && !$booking->service)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Impossible de marquer cette réservation comme terminée car le service a été supprimé.
                    </p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Informations du client -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Informations du client</h2>
                    </div>
                    <div class="flex items-start space-x-6">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden mb-2">
                                @if($booking->client && $booking->client->user && $booking->client->user->profile_photo_url)
                                    <img src="{{ $booking->client->user->profile_photo_url }}" alt="{{ $booking->client->first_name ?? '' }} {{ $booking->client->last_name ?? '' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                                        <span class="text-blue-700 font-bold text-xl">
                                            {{ substr($booking->client->first_name ?? '', 0, 1) }}{{ substr($booking->client->last_name ?? '', 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-blue-900">{{ $booking->client->first_name ?? '' }} {{ $booking->client->last_name ?? '' }}</h3>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="bg-blue-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Email</div>
                                            <div class="text-sm font-semibold text-blue-900">{{ ($booking->client && $booking->client->user) ? ($booking->client->user->email ?? $booking->client->email) : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @if($booking->client && $booking->client->phone)
                                    <div class="bg-blue-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Téléphone</div>
                                            <div class="text-sm font-semibold text-blue-900">{{ $booking->client->phone }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="space-y-4">
                                    @if($booking->client && $booking->client->address)
                                    <div class="bg-blue-50 rounded-lg p-3 flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Adresse</div>
                                            <div class="text-sm font-semibold text-blue-900">{{ $booking->client->address }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="bg-blue-50 rounded-lg p-3 flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"></path>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Membre depuis</div>
                                            <div class="text-sm font-semibold text-blue-900">{{ $booking->client && $booking->client->created_at ? $booking->client->created_at->format('F Y') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails du service -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Détails du service</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Nom du service</div>
                                <div class="text-lg font-bold text-blue-900 mt-1">{{ $booking->service->name ?? 'Service non disponible' }}</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Catégorie</div>
                                <div class="text-sm font-semibold text-blue-900 mt-1">
                                    @if($booking->service && $booking->service->category)
                                        {{ $booking->service->category->first()->name ?? 'Non spécifiée' }}
                                    @else
                                        Non spécifiée
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Prix</div>
                                <div class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($booking->total_price ?? 0, 2, ',', ' ') }} €</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Durée</div>
                                <div class="text-sm font-semibold text-blue-900 mt-1">{{ $booking->getDurationFormatted() }}</div>
                            </div>
                        </div>
                    </div>
                    @if($booking->service && $booking->service->description)
                    <div class="mt-6 bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Description</div>
                        <div class="text-gray-700">{{ $booking->service->description }}</div>
                    </div>
                    @endif
                </div>
                
                <!-- Détails de la réservation -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Détails de la réservation</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Date de début</div>
                                <div class="text-lg font-bold text-blue-900 mt-1">{{ $booking->start_datetime ? $booking->start_datetime->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-sm text-blue-700">{{ $booking->start_datetime ? $booking->start_datetime->format('H:i') : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Date de fin</div>
                                <div class="text-lg font-bold text-blue-900 mt-1">{{ $booking->end_datetime ? $booking->end_datetime->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-sm text-blue-700">{{ $booking->end_datetime ? $booking->end_datetime->format('H:i') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($isMultiSlotSession)
                    <div class="mt-6 bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Session multiple</div>
                        <div class="text-sm text-blue-700">
                            Cette réservation fait partie d'une session de {{ $allBookings->count() }} créneaux.
                            Prix total: <span class="font-bold">{{ number_format($totalSessionPrice, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                    @endif
                    
                    @if(cleanNotesForDisplay($booking->client_notes))
                    <div class="mt-6 bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Notes du client</div>
                        <div class="text-gray-700">{{ cleanNotesForDisplay($booking->client_notes) }}</div>
                    </div>
                    @endif
                    
                    @if(cleanNotesForDisplay($booking->prestataire_notes))
                    <div class="mt-6 bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Vos notes</div>
                        <div class="text-gray-700">{{ cleanNotesForDisplay($booking->prestataire_notes) }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Colonne latérale -->
            <div class="space-y-6">
                <!-- Résumé -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Résumé
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Numéro:</span>
                            <span class="font-medium">#{{ $booking->booking_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Date de création:</span>
                            <span class="font-medium">{{ $booking->created_at ? $booking->created_at->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Statut:</span>
                            <span class="font-medium
                                @if(($booking->status ?? '') === 'pending') text-yellow-600
                                @elseif(($booking->status ?? '') === 'confirmed') text-green-600
                                @elseif(($booking->status ?? '') === 'completed') text-blue-600
                                @elseif(($booking->status ?? '') === 'cancelled') text-red-600
                                @elseif(($booking->status ?? '') === 'refused') text-red-600
                                @endif">
                                @if(($booking->status ?? '') === 'pending') En attente
                                @elseif(($booking->status ?? '') === 'confirmed') Confirmée
                                @elseif(($booking->status ?? '') === 'completed') Terminée
                                @elseif(($booking->status ?? '') === 'cancelled') Annulée
                                @elseif(($booking->status ?? '') === 'refused') Refusée
                                @else Statut inconnu
                                @endif
                            </span>
                        </div>
                        <hr class="border-gray-200 my-2">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-blue-600">{{ number_format($booking->total_price ?? 0, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>
                
                <!-- Communication -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                    <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Communication
                    </h3>
                    <div class="space-y-3">
                        @if($booking->client && $booking->client->user)
                        <a href="{{ route('messaging.conversation', $booking->client->user->id) }}?message=Bonjour {{ $booking->client->user->name ?? '' }}, concernant votre réservation #{{ $booking->booking_number ?? 'N/A' }} du {{ $booking->start_datetime ? $booking->start_datetime->format('d/m/Y à H:i') : 'N/A' }}, je vous contacte pour..." 
                           class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="text-blue-800 font-medium">Envoyer un message</span>
                        </a>
                        @if($booking->client->user->phone)
                        <a href="tel:{{ $booking->client->user->phone }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-green-800 font-medium">Appeler (urgence)</span>
                        </a>
                        @endif
                        @else
                        <div class="text-gray-500 italic">Informations de contact non disponibles</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modale de confirmation d'acceptation -->
<div id="acceptConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(59, 130, 246, 0.8);">
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-blue-500 transform transition-all duration-300 scale-95 opacity-0 modal-show">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                <svg class="h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation d'acceptation</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir accepter cette réservation ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelAcceptBtn" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmAcceptBtn" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                    Accepter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modale de confirmation de refus -->
<div id="rejectConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300 scale-95 opacity-0 modal-show">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de refus</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir refuser cette réservation ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelRejectBtn" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmRejectBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    Refuser
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modale de confirmation de réservation terminée -->
<div id="completeConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(59, 130, 246, 0.8);">
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-blue-500 transform transition-all duration-300 scale-95 opacity-0 modal-show">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                <svg class="h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de réservation terminée</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir marquer cette réservation comme terminée ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelCompleteBtn" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmCompleteBtn" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                    Marquer comme terminée
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accept booking modal elements
    const acceptBtn = document.getElementById('acceptBookingBtn');
    const acceptModal = document.getElementById('acceptConfirmationModal');
    const cancelAcceptBtn = document.getElementById('cancelAcceptBtn');
    const confirmAcceptBtn = document.getElementById('confirmAcceptBtn');
    
    // Reject booking modal elements
    const rejectBtn = document.getElementById('rejectBookingBtn');
    const rejectModal = document.getElementById('rejectConfirmationModal');
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    
    // Complete booking modal elements
    const completeBtn = document.getElementById('completeBookingBtn');
    const completeModal = document.getElementById('completeConfirmationModal');
    const cancelCompleteBtn = document.getElementById('cancelCompleteBtn');
    const confirmCompleteBtn = document.getElementById('confirmCompleteBtn');
    
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
    
    // Handle complete button click
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            completeModal.classList.remove('hidden');
            
            // Add animation classes
            setTimeout(() => {
                completeModal.classList.remove('opacity-0');
                const modalContent = completeModal.querySelector('.modal-show');
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
            form.action = '{{ route('prestataire.bookings.accept', $booking) }}';
            
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
            form.action = '{{ route('prestataire.bookings.reject', $booking) }}';
            
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
    
    // Handle cancel complete
    if (cancelCompleteBtn) {
        cancelCompleteBtn.addEventListener('click', function() {
            closeCompleteModal();
        });
    }
    
    // Handle confirm complete
    if (confirmCompleteBtn) {
        confirmCompleteBtn.addEventListener('click', function() {
            // Create a form dynamically and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('prestataire.bookings.complete.prestataire', $booking) }}';
            
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
    
    if (completeModal) {
        completeModal.addEventListener('click', function(e) {
            if (e.target === completeModal) {
                closeCompleteModal();
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
            if (completeModal && !completeModal.classList.contains('hidden')) {
                closeCompleteModal();
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
    
    // Function to close complete modal with animation
    function closeCompleteModal() {
        const modalContent = completeModal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
        }
        if (completeModal) {
            completeModal.classList.add('opacity-0');
            
            setTimeout(() => {
                completeModal.classList.add('hidden');
            }, 300);
        }
    }
});
</script>
@endpush
