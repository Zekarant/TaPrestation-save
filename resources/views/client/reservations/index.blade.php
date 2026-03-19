@extends('layouts.app')

@section('content')
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .header-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        position: relative;
    }
    .header-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .header-btn .tooltip {
        position: absolute;
        bottom: -32px;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 50;
    }
    .header-btn:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .reservation-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .reservation-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .action-btn:hover {
        transform: translateY(-1px);
    }
</style>

<div class="min-h-screen bg-gradient-to-b from-orange-50 via-amber-50 to-white">
    <div class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Header Professionnel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Titre et retour -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('client.dashboard') }}" 
                       class="header-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                        <i class="fas fa-arrow-left"></i>
                        <span class="tooltip">Retour</span>
                    </a>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-orange-500"></i>
                            Mes Réservations
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">Vos réservations de produits anti-gaspi</p>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('urgent-sales.index') }}" 
                       class="header-btn bg-orange-500 text-white hover:bg-orange-600">
                        <i class="fas fa-search"></i>
                        <span class="tooltip">Découvrir</span>
                    </a>
                    <button onclick="window.location.reload()" 
                            class="header-btn bg-blue-500 text-white hover:bg-blue-600">
                        <i class="fas fa-sync-alt"></i>
                        <span class="tooltip">Actualiser</span>
                    </button>
                    <a href="{{ route('client.dashboard') }}" 
                       class="header-btn bg-gray-700 text-white hover:bg-gray-800">
                        <i class="fas fa-home"></i>
                        <span class="tooltip">Accueil</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        @php
            $pending = $reservations->where('status', 'pending')->count();
            $confirmed = $reservations->where('status', 'confirmed')->count();
            $completed = $reservations->where('status', 'completed')->count();
            $cancelled = $reservations->where('status', 'cancelled')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $pending }}</div>
                    <div class="text-xs text-gray-500">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-100 text-blue-600">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $confirmed }}</div>
                    <div class="text-xs text-gray-500">Confirmées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-100 text-green-600">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $completed }}</div>
                    <div class="text-xs text-gray-500">Finalisées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-100 text-red-600">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $cancelled }}</div>
                    <div class="text-xs text-gray-500">Annulées</div>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 mb-6 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        
        <!-- Liste des réservations -->
        @if($reservations->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-100 to-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-sm">
                    <i class="fas fa-shopping-bag text-4xl text-orange-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune réservation</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">Vous n'avez pas encore réservé de produits anti-gaspi. Découvrez les offres disponibles près de chez vous !</p>
                <a href="{{ route('urgent-sales.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl hover:from-orange-600 hover:to-amber-600 transition font-semibold shadow-lg shadow-orange-200">
                    <i class="fas fa-search"></i>
                    Découvrir les annonces
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($reservations as $reservation)
                    <div class="reservation-card">
                        <div class="p-5">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Image produit -->
                                @if($reservation->urgentSale && $reservation->urgentSale->photos && count($reservation->urgentSale->photos) > 0)
                                    <div class="w-full sm:w-28 h-28 rounded-xl overflow-hidden flex-shrink-0 shadow-sm">
                                        <img src="{{ asset('storage/' . $reservation->urgentSale->photos[0]) }}" 
                                             alt="{{ $reservation->urgentSale->title ?? 'Produit' }}" 
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-full sm:w-28 h-28 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-image text-3xl text-gray-300"></i>
                                    </div>
                                @endif
                                
                                <!-- Info réservation -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div>
                                            <!-- Badge statut -->
                                            <span class="status-badge mb-2
                                                @if($reservation->status === 'pending') bg-yellow-100 text-yellow-700
                                                @elseif($reservation->status === 'confirmed') bg-blue-100 text-blue-700
                                                @elseif($reservation->status === 'completed') bg-green-100 text-green-700
                                                @elseif($reservation->status === 'cancelled') bg-red-100 text-red-700
                                                @else bg-gray-100 text-gray-700
                                                @endif
                                            ">
                                                @if($reservation->status === 'pending')
                                                    <i class="fas fa-clock"></i> En attente
                                                @elseif($reservation->status === 'confirmed')
                                                    <i class="fas fa-check-circle"></i> Confirmée
                                                @elseif($reservation->status === 'completed')
                                                    <i class="fas fa-trophy"></i> Finalisée
                                                @elseif($reservation->status === 'cancelled')
                                                    <i class="fas fa-times-circle"></i> Annulée
                                                @else
                                                    {{ ucfirst($reservation->status) }}
                                                @endif
                                            </span>
                                            
                                            <!-- Titre produit -->
                                            @if($reservation->urgentSale)
                                                <h3 class="font-bold text-gray-900 text-lg">
                                                    <a href="{{ route('urgent-sales.show', $reservation->urgentSale) }}" class="hover:text-orange-600 transition">
                                                        {{ Str::limit($reservation->urgentSale->title, 50) }}
                                                    </a>
                                                </h3>
                                            @else
                                                <h3 class="font-semibold text-gray-400 italic flex items-center gap-2">
                                                    <i class="fas fa-ban"></i> Produit supprimé
                                                </h3>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Détails -->
                                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-cubes text-gray-400 w-4"></i>
                                            <span>Qté : <strong class="text-gray-800">{{ $reservation->quantity }}</strong></span>
                                        </div>
                                        
                                        @if($reservation->urgentSale)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-euro-sign text-gray-400 w-4"></i>
                                                <span>Total : <strong class="text-orange-600 text-base">{{ number_format($reservation->quantity * $reservation->urgentSale->price, 2, ',', ' ') }}€</strong></span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar-alt text-gray-400 w-4"></i>
                                            <span>{{ $reservation->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                        
                                        @if($reservation->urgentSale && $reservation->urgentSale->prestataire && $reservation->urgentSale->prestataire->user)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-store text-gray-400 w-4"></i>
                                                <span><strong>{{ $reservation->urgentSale->prestataire->user->name }}</strong></span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Message si présent -->
                                    @if($reservation->message)
                                        <div class="mt-4 p-3 bg-amber-50 border border-amber-100 rounded-xl text-sm text-gray-700 flex items-start gap-2">
                                            <i class="fas fa-comment-dots text-amber-500 mt-0.5"></i>
                                            <span>{{ $reservation->message }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex sm:flex-col gap-2 sm:items-end mt-3 sm:mt-0">
                                    @if($reservation->urgentSale)
                                        <a href="{{ route('urgent-sales.show', $reservation->urgentSale) }}" 
                                           class="action-btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                                            <i class="fas fa-eye"></i>
                                            <span>Voir</span>
                                        </a>
                                    @endif
                                    
                                    @if($reservation->status === 'pending')
                                        <form action="{{ route('my-reservations.cancel', $reservation) }}" method="POST" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                            @csrf
                                            <button type="submit" class="action-btn bg-red-100 text-red-700 hover:bg-red-200">
                                                <i class="fas fa-times"></i>
                                                <span>Annuler</span>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($reservation->status === 'confirmed' && $reservation->urgentSale && $reservation->urgentSale->prestataire && $reservation->urgentSale->prestataire->user)
                                        <a href="{{ route('messaging.show', $reservation->urgentSale->prestataire->user) }}" 
                                           class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200">
                                            <i class="fas fa-comments"></i>
                                            <span>Contacter</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($reservations->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2">
                    {{ $reservations->links() }}
                </div>
            </div>
            @endif
        @endif
        
    </div>
</div>
@endsection
