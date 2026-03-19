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
        flex-wrap: wrap;
        max-width: 100%;
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
    
    .status-badge.completed {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }
    
    .status-badge.refused, .status-badge.cancelled {
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

<div class="bg-blue-50 mobile-safe-bottom">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête (même structure que les autres pages détails) -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4 sm:mb-6">
                <div class="flex items-start gap-4 min-w-0">
                    <a href="{{ route('prestataire.bookings.index') }}" class="text-blue-600 hover:text-blue-800 mt-1">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-extrabold text-blue-900 leading-tight">Détails de la réservation</h1>
                        <div class="mt-1 text-sm sm:text-base text-blue-700 truncate">
                            #{{ $booking->booking_number ?? 'N/A' }} - {{ Str::limit($booking->service->name ?? 'Service supprimé', 60) }}
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-auto flex flex-col items-start sm:items-end gap-3">
                    <span class="status-badge
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

                    <div class="flex flex-wrap gap-2 justify-start sm:justify-end">
                        @if(($booking->status ?? '') === 'pending' && $booking->service)
                            <button type="button" id="acceptBookingBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-check mr-1"></i>Accepter
                            </button>
                            <button type="button" id="rejectBookingBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-times mr-1"></i>Refuser
                            </button>
                        @elseif(($booking->status ?? '') === 'confirmed' && $booking->service)
                            <button type="button" id="completeBookingBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-check-double mr-1"></i>Marquer comme terminée
                            </button>
                            @php
                                $paymentReq = $booking->service->payment_requirement ?? 'none';
                            @endphp
                            @if(in_array($paymentReq, ['none', 'deposit']) && ($booking->payment_status ?? '') !== 'paid')
                                <button type="button" id="confirmCashPaymentBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Confirmer paiement espèces
                                </button>
                            @endif
                        @elseif(($booking->status ?? '') === 'completed' && ($booking->payment_status ?? '') !== 'paid')
                            @php
                                $paymentReqCompleted = $booking->service->payment_requirement ?? 'none';
                            @endphp
                            @if(in_array($paymentReqCompleted, ['none', 'deposit']))
                                <button type="button" id="confirmCashPaymentBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Confirmer paiement espèces
                                </button>
                            @endif
                        @elseif(($booking->status ?? '') === 'completed')
                            @php
                                $prestataireId = auth()->user()->prestataire ? auth()->user()->prestataire->id : null;
                                $hasReviewedClient = $prestataireId ? \App\Models\ClientReview::where('prestataire_id', $prestataireId)
                                    ->where('booking_id', $booking->id)
                                    ->exists() : false;
                                $clientUserId = $booking->client && $booking->client->user ? $booking->client->user->id : null;
                            @endphp
                            @if(!$hasReviewedClient)
                                <a href="{{ route('client-reviews.create', $booking) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                    <i class="fas fa-star mr-1"></i>Noter ce client
                                </a>
                            @else
                                <span class="bg-green-100 text-green-700 font-bold py-2 px-4 rounded-lg text-sm">
                                    <i class="fas fa-check mr-1"></i>Client noté
                                </span>
                            @endif
                            @if($clientUserId)
                                <a href="{{ route('client.reviews', $clientUserId) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                    <i class="fas fa-comments mr-1"></i>Voir les avis du client
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
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

            @if(($booking->status ?? '') === 'pending' && !$booking->service)
                <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Impossible d'accepter ou refuser cette réservation car le service a été supprimé.
                    </p>
                </div>
            @elseif(($booking->status ?? '') === 'confirmed' && !$booking->service)
                <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Impossible de marquer cette réservation comme terminée car le service a été supprimé.
                    </p>
                </div>
            @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-4 sm:space-y-8">
                <!-- Informations du client -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Informations du client</h2>
                    </div>
                    <!-- Layout vertical sur mobile, horizontal sur desktop -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:space-x-6 space-y-4 sm:space-y-0">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden mb-2">
                                @if($booking->client && $booking->client->user && $booking->client->user->profile_photo_url)
                                    <img src="{{ $booking->client->user->profile_photo_url }}" alt="{{ $booking->client->first_name ?? '' }} {{ $booking->client->last_name ?? '' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-linear-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                                        <span class="text-blue-700 font-bold text-lg sm:text-xl">
                                            {{ substr($booking->client->first_name ?? '', 0, 1) }}{{ substr($booking->client->last_name ?? '', 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-center">
                                <h3 class="text-base sm:text-lg font-semibold text-blue-900">{{ $booking->client->first_name ?? '' }} {{ $booking->client->last_name ?? '' }}</h3>
                                
                                {{-- Note et avis du client --}}
                                @if($booking->client && $booking->client->user)
                                    @php
                                        $clientUser = $booking->client->user;
                                        $clientRating = $clientUser->client_rating;
                                        $reviewsCount = $clientUser->client_reviews_count;
                                        $wouldWorkAgain = $clientUser->would_work_again_percentage;
                                    @endphp
                                    @if($reviewsCount > 0)
                                        <div class="mt-2">
                                            <div class="flex items-center justify-center space-x-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= round($clientRating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="text-sm font-bold text-gray-700 ml-1">{{ number_format($clientRating, 1) }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">{{ $reviewsCount }} avis</p>
                                            @if($wouldWorkAgain !== null)
                                                <p class="text-xs {{ $wouldWorkAgain >= 80 ? 'text-green-600' : ($wouldWorkAgain >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ $wouldWorkAgain }}% retravailleraient avec ce client
                                                </p>
                                            @endif
                                            <a href="{{ route('client.reviews', $clientUser) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                                Voir tous les avis →
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 mt-2">Nouveau client - Pas encore d'avis</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <div class="grid grid-cols-1 gap-3 sm:gap-6">
                                <div class="space-y-3 sm:space-y-4">
                                    <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3 flex items-center space-x-2 sm:space-x-3">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Email</div>
                                            <div class="text-xs sm:text-sm font-semibold text-blue-900 truncate">{{ ($booking->client && $booking->client->user) ? ($booking->client->user->email ?? $booking->client->email) : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @if($booking->client && $booking->client->phone)
                                    <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3 flex items-center space-x-2 sm:space-x-3">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Téléphone</div>
                                            <div class="text-xs sm:text-sm font-semibold text-blue-900">{{ $booking->client->phone }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($booking->client && $booking->client->address)
                                    <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3 flex items-start space-x-2 sm:space-x-3">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Adresse</div>
                                            <div class="text-xs sm:text-sm font-semibold text-blue-900">{{ $booking->client->address }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3 flex items-center space-x-2 sm:space-x-3">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"></path>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Membre depuis</div>
                                            <div class="text-xs sm:text-sm font-semibold text-blue-900">{{ $booking->client && $booking->client->created_at ? $booking->client->created_at->format('F Y') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails du service -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Détails du service</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Service</div>
                                <div class="text-sm sm:text-lg font-bold text-blue-900 mt-1 line-clamp-2">{{ $booking->service->name ?? 'Service non disponible' }}</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Catégorie</div>
                                <div class="text-xs sm:text-sm font-semibold text-blue-900 mt-1">
                                    @if($booking->service && $booking->service->category)
                                        {{ $booking->service->category->first()->name ?? 'Non spécifiée' }}
                                    @else
                                        Non spécifiée
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Prix</div>
                                <div class="text-lg sm:text-2xl font-bold text-blue-600 mt-1">{{ number_format($booking->total_price ?? 0, 2, ',', ' ') }} €</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Durée</div>
                                <div class="text-xs sm:text-sm font-semibold text-blue-900 mt-1">{{ $booking->getDurationFormatted() }}</div>
                            </div>
                        </div>
                    </div>
                    @if($booking->service && $booking->service->description)
                    <div class="mt-4 sm:mt-6 bg-blue-50 rounded-lg p-3 sm:p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Description</div>
                        <div class="text-sm text-gray-700">{{ $booking->service->description }}</div>
                    </div>
                    @endif
                </div>
                
                <!-- Détails de la réservation -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 2m8-2l2 2m-2-2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Détails de la réservation</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Début</div>
                                <div class="text-sm sm:text-lg font-bold text-blue-900 mt-1">{{ $booking->start_datetime ? $booking->start_datetime->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-xs sm:text-sm text-blue-700">{{ $booking->start_datetime ? $booking->start_datetime->format('H:i') : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-blue-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Fin</div>
                                <div class="text-sm sm:text-lg font-bold text-blue-900 mt-1">{{ $booking->end_datetime ? $booking->end_datetime->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-xs sm:text-sm text-blue-700">{{ $booking->end_datetime ? $booking->end_datetime->format('H:i') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($isMultiSlotSession)
                    <div class="mt-4 sm:mt-6 bg-blue-50 rounded-lg p-3 sm:p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Session multiple</div>
                        <div class="text-xs sm:text-sm text-blue-700">
                            {{ $allBookings->count() }} créneaux - Total: <span class="font-bold">{{ number_format($totalSessionPrice, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                    @endif
                    
                    @if(cleanNotesForDisplay($booking->client_notes))
                    <div class="mt-4 sm:mt-6 bg-blue-50 rounded-lg p-3 sm:p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Notes du client</div>
                        <div class="text-sm text-gray-700">{{ cleanNotesForDisplay($booking->client_notes) }}</div>
                    </div>
                    @endif
                    
                    @if(cleanNotesForDisplay($booking->prestataire_notes))
                    <div class="mt-4 sm:mt-6 bg-blue-50 rounded-lg p-3 sm:p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Vos notes</div>
                        <div class="text-sm text-gray-700">{{ cleanNotesForDisplay($booking->prestataire_notes) }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Colonne latérale -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Résumé -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-blue-800 mb-3 sm:mb-4 flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Résumé
                    </h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-start justify-between gap-3 text-xs sm:text-sm">
                            <span class="text-gray-600 flex-shrink-0">Numéro:</span>
                            <span class="font-medium min-w-0 text-right break-all">#{{ $booking->booking_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs sm:text-sm">
                            <span class="text-gray-600 flex-shrink-0">Création:</span>
                            <span class="font-medium min-w-0 text-right break-words">{{ $booking->created_at ? $booking->created_at->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs sm:text-sm">
                            <span class="text-gray-600 flex-shrink-0">Statut:</span>
                            <span class="font-medium min-w-0 text-right break-words
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
                        <div class="flex items-start justify-between gap-3 text-base sm:text-lg font-bold">
                            <span class="flex-shrink-0">Total:</span>
                            <span class="text-blue-600 min-w-0 text-right">{{ number_format($booking->total_price ?? 0, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>
                
                <!-- Communication -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-blue-800 mb-3 sm:mb-4 flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Communication
                    </h3>
                    <div class="space-y-2 sm:space-y-3">
                        @if($booking->client && $booking->client->user)
                        <a href="{{ route('messaging.conversation', $booking->client->user->id) }}?message=Bonjour {{ $booking->client->user->name ?? '' }}, concernant votre réservation #{{ $booking->booking_number ?? 'N/A' }} du {{ $booking->start_datetime ? $booking->start_datetime->format('d/m/Y à H:i') : 'N/A' }}, je vous contacte pour..." 
                           class="flex items-center p-2.5 sm:p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="text-blue-800 font-medium text-sm sm:text-base">Message</span>
                        </a>
                        @if($booking->client->user->phone)
                        <a href="tel:{{ $booking->client->user->phone }}" class="flex items-center p-2.5 sm:p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-green-800 font-medium text-sm sm:text-base">Appeler</span>
                        </a>
                        @endif
                        @else
                        <div class="text-gray-500 italic text-sm">Contact non disponible</div>
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
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-blue-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-50">
                    <svg class="h-7 w-7 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation d'acceptation</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir accepter cette réservation ?
                </p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelAcceptBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmAcceptBtn" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
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
                    Êtes-vous sûr de vouloir refuser cette réservation ?
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

<!-- Modale de confirmation de réservation terminée -->
<div id="completeConfirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-blue-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-50">
                    <svg class="h-7 w-7 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation de réservation terminée</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir marquer cette réservation comme terminée ?
                </p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelCompleteBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmCompleteBtn" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                        Marquer comme terminée
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modale de confirmation paiement espèces -->
<div id="cashPaymentModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-green-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-50">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmer le paiement en espèces</h3>
                @php
                    $paymentReqModal = $booking->service->payment_requirement ?? 'none';
                    $totalPrice = (float) ($booking->total_price ?? 0);
                    if ($paymentReqModal === 'deposit') {
                        $depositAmt = (float) ($booking->deposit_amount ?? round($totalPrice * 0.30, 2));
                        $cashAmt = round($totalPrice - $depositAmt, 2);
                    } else {
                        $cashAmt = $totalPrice;
                    }
                @endphp
                <p class="text-gray-600 mt-2">
                    @if($paymentReqModal === 'deposit')
                        Le client a payé un acompte en ligne. Confirmez-vous avoir reçu le reste en espèces ?
                    @else
                        Confirmez-vous avoir reçu le paiement en espèces du client ?
                    @endif
                </p>
                <p class="text-2xl font-bold text-green-600 mt-3">{{ number_format($cashAmt, 2, ',', ' ') }} €</p>
                @if($paymentReqModal === 'deposit')
                    <p class="text-xs text-gray-500 mt-1">Reste après acompte de {{ number_format($depositAmt, 2, ',', ' ') }} €</p>
                @endif
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelCashPaymentBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmCashPaymentSubmitBtn" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                        <i class="fas fa-check mr-1"></i>Confirmer
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
            form.action = "{{ route('prestataire.bookings.accept', $booking) }}";
            
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
            form.action = "{{ route('prestataire.bookings.reject', $booking) }}";
            
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
            form.action = "{{ route('prestataire.bookings.complete.prestataire', $booking) }}";
            
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
    
    // Cash payment modal elements
    const cashPaymentBtn = document.getElementById('confirmCashPaymentBtn');
    const cashPaymentModal = document.getElementById('cashPaymentModal');
    const cancelCashPaymentBtn = document.getElementById('cancelCashPaymentBtn');
    const confirmCashPaymentSubmitBtn = document.getElementById('confirmCashPaymentSubmitBtn');

    if (cashPaymentBtn) {
        cashPaymentBtn.addEventListener('click', function() {
            cashPaymentModal.classList.remove('hidden');
            setTimeout(() => {
                cashPaymentModal.classList.remove('opacity-0');
                const modalContent = cashPaymentModal.querySelector('.modal-show');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('opacity-0');
            }, 10);
        });
    }

    if (cancelCashPaymentBtn) {
        cancelCashPaymentBtn.addEventListener('click', function() {
            closeCashPaymentModal();
        });
    }

    if (confirmCashPaymentSubmitBtn) {
        confirmCashPaymentSubmitBtn.addEventListener('click', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('prestataire.bookings.confirm-cash', $booking) }}";
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Close modals when clicking outside
    if (cashPaymentModal) {
        cashPaymentModal.addEventListener('click', function(e) {
            if (e.target === cashPaymentModal) {
                closeCashPaymentModal();
            }
        });
    }

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
            if (cashPaymentModal && !cashPaymentModal.classList.contains('hidden')) {
                closeCashPaymentModal();
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

    // Function to close cash payment modal with animation
    function closeCashPaymentModal() {
        if (!cashPaymentModal) return;
        const modalContent = cashPaymentModal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
        }
        cashPaymentModal.classList.add('opacity-0');
        setTimeout(() => {
            cashPaymentModal.classList.add('hidden');
        }, 300);
    }
});
</script>
@endpush

{{-- Rappel pour activer les notifications (contexte réservation) --}}
@include('components.notification-context-alert', ['context' => 'booking'])

