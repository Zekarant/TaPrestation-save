@extends('layouts.app')

@section('title', 'Livraison ' . $delivery->tracking_number)

@push('styles')
<style>
    .detail-page {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        min-height: 100vh;
    }
    
    .info-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    
    .timeline-container {
        position: relative;
    }
    
    .timeline-line {
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #3b82f6, #e5e7eb);
    }
    
    .timeline-event {
        position: relative;
        padding-left: 50px;
        padding-bottom: 1.5rem;
    }
    
    .timeline-event:last-child {
        padding-bottom: 0;
    }
    
    .timeline-dot {
        position: absolute;
        left: 12px;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .timeline-dot.active {
        background: #3b82f6;
        animation: pulse 2s infinite;
    }
    
    .timeline-dot.completed {
        background: #22c55e;
    }
    
    .timeline-dot.pending {
        background: #d1d5db;
    }
    
    .timeline-dot.failed {
        background: #ef4444;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
    }
    
    .status-banner {
        border-radius: 12px;
        padding: 1rem 1.5rem;
    }
    
    .status-pending { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
    .status-preparing { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }
    .status-in_transit { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .status-out_for_delivery { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .status-delivered { background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%); }
    .status-failed { background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%); }
    .status-cancelled { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); }
    
    .action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .map-container {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-radius: 12px;
        min-height: 200px;
        position: relative;
    }
    
    .copy-btn {
        transition: all 0.2s ease;
    }
    
    .copy-btn:hover {
        background: #e5e7eb;
    }
    
    .copy-btn.copied {
        background: #d1fae5;
        color: #059669;
    }
</style>
@endpush

@section('content')
<div class="detail-page py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.show" />
        
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-3">
                <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                <span class="mx-2">›</span>
                <a href="{{ route('prestataire.logistics.index') }}" class="hover:text-blue-600">Livraisons</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900">{{ $delivery->tracking_number }}</span>
            </nav>
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                            📦 {{ $delivery->tracking_number }}
                        </h1>
                        <button onclick="copyToClipboard('{{ $delivery->tracking_number }}')" 
                                class="copy-btn p-2 rounded-lg text-gray-500 hover:text-gray-700"
                                title="Copier">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-600">
                        Créée le {{ $delivery->created_at->format('d/m/Y à H:i') }}
                        @if($delivery->booking)
                        • Réservation #{{ $delivery->booking->id }}
                        @endif
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    @if(!in_array($delivery->status, ['delivered', 'failed', 'cancelled']))
                    <button onclick="window.print()" class="action-btn bg-gray-100 hover:bg-gray-200 text-gray-700">
                        🖨️ Étiquette
                    </button>
                    @endif
                    <a href="{{ route('prestataire.logistics.index') }}" class="action-btn bg-gray-100 hover:bg-gray-200 text-gray-700">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="status-banner status-{{ $delivery->status }} mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">{{ $delivery->status_icon ?? '📦' }}</span>
                <div>
                    <p class="font-bold text-lg text-gray-900">{{ $delivery->status_label ?? 'En cours' }}</p>
                    <p class="text-sm text-gray-700">
                        @if($delivery->status === 'delivered')
                        Livrée le {{ $delivery->delivered_at?->format('d/m/Y à H:i') ?? 'N/A' }}
                        @elseif($delivery->estimated_delivery)
                        Livraison estimée: {{ $delivery->estimated_delivery->format('d/m/Y') }}
                        @else
                        Traitement en cours
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Quick Actions based on status -->
            @if($delivery->status === 'pending')
            <form action="{{ route('prestataire.logistics.ready-for-pickup', $delivery) }}" method="POST">
                @csrf
                <button type="submit" class="action-btn bg-blue-600 hover:bg-blue-700 text-white">
                    ⚙️ Marquer comme prêt
                </button>
            </form>
            @elseif($delivery->status === 'ready_for_pickup')
            <form action="{{ route('prestataire.logistics.picked-up', $delivery) }}" method="POST">
                @csrf
                <button type="submit" class="action-btn bg-indigo-600 hover:bg-indigo-700 text-white">
                    🚚 Enlèvement effectué
                </button>
            </form>
            @elseif(in_array($delivery->status, ['in_transit', 'out_for_delivery']))
            <div class="flex gap-2">
                <form action="{{ route('prestataire.logistics.delivered', $delivery) }}" method="POST">
                    @csrf
                    <button type="submit" class="action-btn bg-green-600 hover:bg-green-700 text-white">
                        ✅ Marquer comme livré
                    </button>
                </form>
                <form action="{{ route('prestataire.logistics.failed', $delivery) }}" method="POST">
                    @csrf
                    <button type="submit" class="action-btn bg-red-600 hover:bg-red-700 text-white">
                        ❌ Échec
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                
                <!-- Addresses -->
                <div class="info-card p-4 sm:p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-xl">📍</span>
                        Adresses
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pickup Address -->
                        <div class="bg-blue-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Point d'enlèvement</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->pickup_contact_name ?? 'Expéditeur' }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $delivery->pickup_address ?? 'Adresse non définie' }}</p>
                            <p class="text-sm text-gray-600">{{ $delivery->pickup_postal_code ?? '' }} {{ $delivery->pickup_city ?? '' }}</p>
                            @if($delivery->pickup_contact_phone)
                            <p class="text-sm text-gray-500 mt-2">📞 {{ $delivery->pickup_contact_phone }}</p>
                            @endif
                        </div>
                        
                        <!-- Delivery Address -->
                        <div class="bg-green-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-2">Destination</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->delivery_contact_name ?? 'Destinataire' }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $delivery->delivery_address ?? 'Adresse non définie' }}</p>
                            <p class="text-sm text-gray-600">{{ $delivery->delivery_postal_code ?? '' }} {{ $delivery->delivery_city ?? '' }}</p>
                            @if($delivery->delivery_contact_phone)
                            <p class="text-sm text-gray-500 mt-2">📞 {{ $delivery->delivery_contact_phone }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($delivery->delivery_instructions)
                    <div class="mt-4 bg-yellow-50 rounded-lg p-3">
                        <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">Instructions de livraison</p>
                        <p class="text-sm text-gray-700">{{ $delivery->delivery_instructions }}</p>
                    </div>
                    @endif
                </div>

                <!-- Package Details -->
                <div class="info-card p-4 sm:p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-xl">📦</span>
                        Détails du colis
                    </h2>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $delivery->weight ?? '-' }}</p>
                            <p class="text-xs text-gray-500">kg</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $delivery->package_count ?? 1 }}</p>
                            <p class="text-xs text-gray-500">colis</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-lg font-bold text-gray-900">
                                @if($delivery->dimensions)
                                {{ $delivery->dimensions['length'] ?? '-' }}×{{ $delivery->dimensions['width'] ?? '-' }}×{{ $delivery->dimensions['height'] ?? '-' }}
                                @else
                                -
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">cm (L×l×H)</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($delivery->delivery_fee ?? 0, 2) }}€</p>
                            <p class="text-xs text-gray-500">Frais livraison</p>
                        </div>
                    </div>
                    
                    <!-- Special Handling -->
                    <div class="flex flex-wrap gap-2">
                        @if($delivery->fragile)
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">⚠️ Fragile</span>
                        @endif
                        @if($delivery->requires_signature)
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">✍️ Signature requise</span>
                        @endif
                        @if($delivery->is_insured)
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">🛡️ Assuré ({{ number_format($delivery->insurance_value ?? 0, 0) }}€)</span>
                        @endif
                        @if($delivery->cod_amount)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">💵 Contre-remboursement ({{ number_format($delivery->cod_amount, 0) }}€)</span>
                        @endif
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                            {{ match($delivery->shipping_type ?? 'standard') {
                                'express' => '⚡ Express',
                                'same_day' => '🚀 Même jour',
                                'scheduled' => '📅 Planifiée',
                                default => '📦 Standard'
                            } }}
                        </span>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="info-card p-4 sm:p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-xl">📜</span>
                        Historique du suivi
                    </h2>
                    
                    <div class="timeline-container">
                        <div class="timeline-line"></div>
                        
                        @forelse($delivery->trackingEvents ?? [] as $event)
                        <div class="timeline-event">
                            <div class="timeline-dot {{ $loop->first ? 'active' : 'completed' }}"></div>
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $event->status_label ?? ucfirst($event->status) }}</p>
                                    <p class="text-sm text-gray-600">{{ $event->description ?? '' }}</p>
                                    @if($event->location)
                                    <p class="text-xs text-gray-500 mt-1">📍 {{ $event->location }}</p>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 whitespace-nowrap">{{ $event->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="timeline-event">
                            <div class="timeline-dot active"></div>
                            <div>
                                <p class="font-semibold text-gray-900">Commande créée</p>
                                <p class="text-sm text-gray-600">En attente de traitement</p>
                                <p class="text-xs text-gray-400">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4 sm:space-y-6">
                
                <!-- Driver Info -->
                @if($delivery->driver)
                <div class="info-card p-4">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="text-lg">🚗</span>
                        Livreur assigné
                    </h3>
                    
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">{{ $delivery->driver->vehicle_icon ?? '🚚' }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $delivery->driver->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ ucfirst($delivery->driver->vehicle_type ?? 'Véhicule') }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-yellow-50 rounded-lg p-2">
                            <p class="text-lg font-bold text-yellow-600">⭐ {{ number_format($delivery->driver->rating ?? 5, 1) }}</p>
                            <p class="text-xs text-gray-500">Note</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-2">
                            <p class="text-lg font-bold text-green-600">{{ $delivery->driver->completed_deliveries ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Livraisons</p>
                        </div>
                    </div>
                    
                    @if($delivery->driver->phone)
                    <a href="tel:{{ $delivery->driver->phone }}" 
                       class="mt-4 w-full flex items-center justify-center gap-2 bg-green-100 hover:bg-green-200 text-green-700 py-2.5 rounded-lg font-medium transition">
                        📞 Contacter
                    </a>
                    @endif
                </div>
                @else
                <div class="info-card p-4">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="text-lg">🚗</span>
                        Livreur
                    </h3>
                    <div class="text-center py-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-xl">❓</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">Aucun livreur assigné</p>
                        <form action="{{ route('prestataire.logistics.auto-assign', $delivery) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition text-sm">
                                🎯 Auto-assigner
                            </button>
                        </form>
                    </div>
                </div>
                @endif
                
                <!-- Quick Info -->
                <div class="info-card p-4">
                    <h3 class="font-bold text-gray-900 mb-3">📋 Informations</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Priorité</span>
                            <span class="px-2 py-1 bg-{{ match($delivery->priority ?? 'normal') {
                                'urgent' => 'red',
                                'high' => 'orange',
                                'low' => 'gray',
                                default => 'green'
                            } }}-100 text-{{ match($delivery->priority ?? 'normal') {
                                'urgent' => 'red',
                                'high' => 'orange',
                                'low' => 'gray',
                                default => 'green'
                            } }}-700 rounded text-xs font-medium">
                                {{ $delivery->priority_label ?? 'Normal' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Type expédition</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($delivery->shipping_type ?? 'Standard') }}</span>
                        </div>
                        
                        @if($delivery->scheduled_pickup_at)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Enlèvement prévu</span>
                            <span class="text-sm font-medium text-gray-900">{{ $delivery->scheduled_pickup_at->format('d/m H:i') }}</span>
                        </div>
                        @endif
                        
                        @if($delivery->scheduled_delivery_at)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Livraison prévue</span>
                            <span class="text-sm font-medium text-gray-900">{{ $delivery->scheduled_delivery_at->format('d/m H:i') }}</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Tentatives</span>
                            <span class="text-sm font-medium text-gray-900">{{ $delivery->delivery_attempts ?? 0 }} / 3</span>
                        </div>
                        
                        @if($delivery->distance)
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-500">Distance</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($delivery->distance, 1) }} km</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Proof of Delivery -->
                @if($delivery->status === 'delivered')
                <div class="info-card p-4">
                    <h3 class="font-bold text-gray-900 mb-3">✅ Preuve de livraison</h3>
                    
                    @if($delivery->signature_image)
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Signature</p>
                        <img src="{{ $delivery->signature_image }}" alt="Signature" class="w-full bg-gray-50 rounded-lg p-2">
                    </div>
                    @endif
                    
                    @if($delivery->delivery_photo)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Photo du colis</p>
                        <img src="{{ $delivery->delivery_photo }}" alt="Photo livraison" class="w-full rounded-lg">
                    </div>
                    @endif
                    
                    @if($delivery->customer_rating)
                    <div class="mt-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Note client</p>
                        <div class="text-xl">
                            @for($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= $delivery->customer_rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                            @endfor
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Actions -->
                @if(!in_array($delivery->status, ['delivered', 'cancelled']))
                <div class="info-card p-4">
                    <h3 class="font-bold text-gray-900 mb-3">⚡ Actions</h3>
                    
                    <div class="space-y-2">
                        @if($delivery->status === 'failed')
                        <form action="{{ route('prestataire.logistics.update-status', $delivery) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="w-full bg-blue-100 hover:bg-blue-200 text-blue-700 py-2.5 rounded-lg font-medium transition text-sm">
                                🔄 Programmer nouvelle tentative
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('prestataire.logistics.cancel', $delivery) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette livraison ?')">
                            @csrf
                            <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 py-2.5 rounded-lg font-medium transition text-sm">
                                ❌ Annuler la livraison
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.classList.add('copied');
        setTimeout(() => btn.classList.remove('copied'), 2000);
    });
}
</script>
@endpush
@endsection
