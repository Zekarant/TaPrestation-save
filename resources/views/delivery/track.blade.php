@extends('layouts.app')

@section('title', isset($delivery) ? 'Suivi ' . $delivery->tracking_number : 'Suivi de livraison')

@push('styles')
<style>
    .track-page {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #0ea5e9 100%);
        min-height: 100vh;
    }
    
    .track-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        overflow: hidden;
    }
    
    .search-box {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 16px;
        padding: 2rem;
        color: white;
    }
    
    .search-input {
        width: 100%;
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
        border: none;
        border-radius: 12px;
        background: white;
        color: #1e3a8a;
        font-weight: 500;
    }
    
    .search-input::placeholder {
        color: #94a3b8;
    }
    
    .search-input:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(255,255,255,0.3);
    }
    
    .search-btn {
        padding: 1rem 2rem;
        background: #1e3a8a;
        color: white;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .search-btn:hover {
        background: #1e40af;
        transform: translateY(-2px);
    }
    
    .status-header {
        padding: 2rem;
        text-align: center;
    }
    
    .status-pending { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
    .status-preparing { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }
    .status-ready_for_pickup { background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); }
    .status-picked_up { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .status-in_transit { background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%); }
    .status-out_for_delivery { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .status-delivered { background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%); }
    .status-failed { background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%); }
    .status-cancelled { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); }
    
    .timeline-vertical {
        position: relative;
        padding-left: 40px;
    }
    
    .timeline-vertical::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #3b82f6, #e5e7eb);
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-dot {
        position: absolute;
        left: -33px;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .timeline-dot.active {
        background: #3b82f6;
        animation: pulse-dot 2s infinite;
    }
    
    .timeline-dot.completed {
        background: #22c55e;
    }
    
    .timeline-dot.pending {
        background: #d1d5db;
    }
    
    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    }
    
    .progress-bar-container {
        background: #e5e7eb;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin: 1.5rem 0;
    }
    
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1s ease;
    }
    
    .package-animation {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
</style>
@endpush

@section('content')
<div class="track-page py-6 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                📦 Suivi de livraison
            </h1>
            <p class="text-blue-100">Suivez votre colis en temps réel</p>
        </div>

        <!-- Search Box -->
        <div class="search-box mb-8">
            <form action="{{ route('delivery.track-by-number', ':tracking') }}" method="GET" onsubmit="return handleSearch(this)" id="trackForm">
                <label class="block text-sm font-medium mb-2 text-blue-100">Numéro de suivi</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" 
                           id="trackingInput"
                           class="search-input flex-1" 
                           placeholder="Ex: TP-ABC123XYZ"
                           value="{{ $delivery->tracking_number ?? '' }}"
                           required>
                    <button type="submit" class="search-btn flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Suivre
                    </button>
                </div>
            </form>
        </div>

        @if(isset($delivery))
        <!-- Delivery Status Card -->
        <div class="track-card">
            
            <!-- Status Header -->
            <div class="status-header status-{{ $delivery->status }}">
                <div class="package-animation text-6xl mb-4">
                    {{ match($delivery->status) {
                        'pending' => '📦',
                        'preparing' => '⚙️',
                        'ready_for_pickup' => '✅',
                        'picked_up' => '🚚',
                        'in_transit' => '🚚',
                        'out_for_delivery' => '🏃',
                        'delivered' => '🎉',
                        'failed' => '❌',
                        'cancelled' => '🚫',
                        default => '📦'
                    } }}
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">
                    {{ $delivery->status_label ?? ucfirst($delivery->status) }}
                </h2>
                <p class="text-gray-700">
                    @if($delivery->status === 'delivered')
                    Livrée le {{ $delivery->delivered_at?->format('d/m/Y à H:i') ?? 'N/A' }}
                    @elseif($delivery->estimated_delivery)
                    Livraison estimée: {{ $delivery->estimated_delivery->format('d/m/Y') }}
                    @else
                    En cours de traitement
                    @endif
                </p>
                
                <!-- Progress Bar -->
                @php
                    $progressMap = [
                        'pending' => 10,
                        'preparing' => 25,
                        'ready_for_pickup' => 40,
                        'picked_up' => 55,
                        'in_transit' => 70,
                        'out_for_delivery' => 85,
                        'delivered' => 100,
                        'failed' => 85,
                        'cancelled' => 0
                    ];
                    $progress = $progressMap[$delivery->status] ?? 0;
                    $progressColor = match($delivery->status) {
                        'delivered' => '#22c55e',
                        'failed', 'cancelled' => '#ef4444',
                        default => '#3b82f6'
                    };
                @endphp
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $progress }}%; background: {{ $progressColor }};"></div>
                </div>
            </div>
            
            <!-- Tracking Number -->
            <div class="bg-gray-50 px-6 py-4 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Numéro de suivi</p>
                    <p class="font-mono text-lg font-bold text-blue-600">{{ $delivery->tracking_number }}</p>
                </div>
                <button onclick="copyTracking('{{ $delivery->tracking_number }}')" 
                        class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copier
                </button>
            </div>
            
            <!-- Delivery Info -->
            <div class="p-6 border-b">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Destination</p>
                        <p class="font-semibold text-gray-900">{{ $delivery->delivery_contact_name ?? 'Destinataire' }}</p>
                        <p class="text-gray-600">{{ $delivery->delivery_city ?? '' }}</p>
                    </div>
                    
                    @if($delivery->driver)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Livreur</p>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span>{{ $delivery->driver->vehicle_icon ?? '🚚' }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $delivery->driver->first_name }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="p-6">
                <h3 class="font-bold text-gray-900 mb-6">📜 Historique du suivi</h3>
                
                <div class="timeline-vertical">
                    @forelse($delivery->trackingEvents ?? collect() as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $loop->first ? 'active' : 'completed' }}"></div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $event->status_label ?? ucfirst($event->status) }}</p>
                                <p class="text-sm text-gray-600">{{ $event->description ?? '' }}</p>
                                @if($event->location)
                                <p class="text-xs text-gray-500 mt-1">📍 {{ $event->location }}</p>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 whitespace-nowrap">
                                {{ $event->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <!-- Default timeline if no events -->
                    <div class="timeline-item">
                        <div class="timeline-dot {{ in_array($delivery->status, ['delivered', 'failed', 'cancelled']) ? 'completed' : ($delivery->status === 'pending' ? 'active' : 'completed') }}"></div>
                        <div>
                            <p class="font-semibold text-gray-900">Commande créée</p>
                            <p class="text-sm text-gray-600">Votre colis a été enregistré</p>
                            <p class="text-xs text-gray-400">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if(in_array($delivery->status, ['preparing', 'ready_for_pickup', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered']))
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $delivery->status === 'preparing' ? 'active' : 'completed' }}"></div>
                        <div>
                            <p class="font-semibold text-gray-900">En préparation</p>
                            <p class="text-sm text-gray-600">Le colis est en cours de préparation</p>
                        </div>
                    </div>
                    @endif
                    
                    @if(in_array($delivery->status, ['in_transit', 'out_for_delivery', 'delivered']))
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $delivery->status === 'in_transit' ? 'active' : 'completed' }}"></div>
                        <div>
                            <p class="font-semibold text-gray-900">En transit</p>
                            <p class="text-sm text-gray-600">Votre colis est en route</p>
                        </div>
                    </div>
                    @endif
                    
                    @if(in_array($delivery->status, ['out_for_delivery', 'delivered']))
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $delivery->status === 'out_for_delivery' ? 'active' : 'completed' }}"></div>
                        <div>
                            <p class="font-semibold text-gray-900">En cours de livraison</p>
                            <p class="text-sm text-gray-600">Le livreur est en chemin</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($delivery->status === 'delivered')
                    <div class="timeline-item">
                        <div class="timeline-dot completed"></div>
                        <div>
                            <p class="font-semibold text-green-700">Livré ✓</p>
                            <p class="text-sm text-gray-600">Votre colis a été livré</p>
                            @if($delivery->delivered_at)
                            <p class="text-xs text-gray-400">{{ $delivery->delivered_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforelse
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="bg-gray-50 p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-900">Besoin d'aide ?</p>
                        <p class="text-sm text-gray-600">Notre équipe est à votre écoute</p>
                    </div>
                    <a href="mailto:support@taprestation.com" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Contacter le support
                    </a>
                </div>
            </div>
        </div>
        
        @elseif(isset($error))
        <!-- Error State -->
        <div class="track-card p-8 text-center">
            <div class="text-6xl mb-4">🔍</div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Livraison non trouvée</h2>
            <p class="text-gray-600 mb-6">{{ $error }}</p>
            <p class="text-sm text-gray-500">Vérifiez votre numéro de suivi et réessayez</p>
        </div>
        @else
        <!-- Initial State -->
        <div class="track-card p-8 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Entrez votre numéro de suivi</h2>
            <p class="text-gray-600">Saisissez le numéro de suivi fourni lors de votre commande pour suivre votre colis en temps réel.</p>
            
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="p-3">
                    <div class="text-2xl mb-1">📦</div>
                    <p class="text-xs text-gray-500">Préparé</p>
                </div>
                <div class="p-3">
                    <div class="text-2xl mb-1">🚚</div>
                    <p class="text-xs text-gray-500">En transit</p>
                </div>
                <div class="p-3">
                    <div class="text-2xl mb-1">🏃</div>
                    <p class="text-xs text-gray-500">En livraison</p>
                </div>
                <div class="p-3">
                    <div class="text-2xl mb-1">✅</div>
                    <p class="text-xs text-gray-500">Livré</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function handleSearch(form) {
    const tracking = document.getElementById('trackingInput').value.trim();
    if (tracking) {
        window.location.href = '/track/' + encodeURIComponent(tracking);
        return false;
    }
    return false;
}

function copyTracking(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Numéro de suivi copié !');
    });
}
</script>
@endpush
@endsection
