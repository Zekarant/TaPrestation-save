@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Suivi de livraison</h1>
            <p class="text-gray-600 mt-2">Suivez votre livraison en temps réel</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form method="GET" class="flex gap-2">
            <input type="text" name="tracking_number" placeholder="Entrez le numéro de suivi..." 
                value="{{ request('tracking_number') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                Search
            </button>
        </form>
    </div>

    @if ($delivery)
    <!-- Delivery Status Header -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <p class="text-blue-100 mb-1">Tracking Number</p>
                <p class="text-2xl font-bold">{{ $delivery->tracking_number }}</p>
            </div>
            <div>
                <p class="text-blue-100 mb-1">Status</p>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                    <p class="text-2xl font-bold">{{ ucfirst($delivery->status) }}</p>
                </div>
            </div>
            <div>
                <p class="text-blue-100 mb-1">Provider</p>
                <p class="text-2xl font-bold">{{ $delivery->provider->name }}</p>
            </div>
        </div>
    </div>

    <!-- Delivery Timeline -->
    <div class="bg-white rounded-lg shadow p-8 mb-8">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Delivery Timeline</h3>
        <div class="space-y-6">
            @forelse ($delivery->milestones as $milestone)
            <div class="flex gap-6">
                <!-- Timeline Marker -->
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                        ✓
                    </div>
                    @if (!$loop->last)
                    <div class="w-1 h-24 bg-blue-200 my-2"></div>
                    @endif
                </div>

                <!-- Event Details -->
                <div class="flex-1 pb-6 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                    <h4 class="font-semibold text-gray-900">{{ $milestone->title }}</h4>
                    <p class="text-gray-600 text-sm">{{ $milestone->description }}</p>
                    <p class="text-gray-500 text-xs mt-2">{{ $milestone->created_at->format('d M Y H:i') }}</p>
                    
                    @if ($milestone->location)
                    <p class="text-gray-600 text-sm mt-2">
                        📍 {{ $milestone->location }}
                    </p>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-gray-600 text-center py-4">Aucune information de suivi disponible pour le moment</p>
            @endforelse
        </div>
    </div>

    <!-- Delivery Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- From/To -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Pickup Location</h3>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $delivery->from_address }}</p>
            @if ($delivery->pickup_date)
            <p class="text-xs text-gray-500 mt-2">📅 {{ $delivery->pickup_date->format('d M Y H:i') }}</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Delivery Location</h3>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $delivery->to_address }}</p>
            @if ($delivery->delivery_date)
            <p class="text-xs text-gray-500 mt-2">📅 Expected: {{ $delivery->delivery_date->format('d M Y H:i') }}</p>
            @else
            <p class="text-xs text-gray-500 mt-2">⏰ Date à confirmer</p>
            @endif
        </div>

        <!-- Package Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Package Information</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Weight:</span>
                    <span class="font-semibold text-gray-900">{{ $delivery->weight }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Dimensions:</span>
                    <span class="font-semibold text-gray-900">{{ $delivery->dimensions }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Cost:</span>
                    <span class="font-semibold text-gray-900">€{{ number_format($delivery->cost, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Insurance:</span>
                    <span class="font-semibold text-gray-900">
                        @if ($delivery->has_insurance)
                        ✓ Yes (€{{ $delivery->insurance_cost }})
                        @else
                        No
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Map (Stub) -->
    @if ($delivery->current_latitude && $delivery->current_longitude)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-bold text-gray-900 mb-4">Live Location</h3>
        <div id="map" class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
            <p class="text-gray-600">📍 {{ $delivery->current_latitude }}, {{ $delivery->current_longitude }}</p>
        </div>
        <p class="text-xs text-gray-500 mt-2">Last updated: {{ $delivery->updated_at->format('d M Y H:i:s') }}</p>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6 space-y-3">
        <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <button onclick="downloadLabel()" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                📥 Download Label
            </button>
            <button onclick="showContactForm()" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                📞 Contact Provider
            </button>
            <button onclick="reportIssue()" class="flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                🚨 Report Issue
            </button>
        </div>
    </div>

    @else
    <!-- No Results -->
    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
        <p class="text-gray-600 text-lg">Aucune livraison trouvée</p>
        <p class="text-gray-500 text-sm mt-2">Vérifiez votre numéro de suivi et réessayez</p>
    </div>
    @endif
</div>

<!-- Contact Modal -->
<div id="contactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Provider</h3>
        <form>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Subject *</label>
                <input type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Message *</label>
                <textarea rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                    Send
                </button>
                <button type="button" onclick="closeContactModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const contactModal = document.getElementById('contactModal');

    function downloadLabel() {
        window.location.href = `{{ route('delivery.label', $delivery ?? '') }}`;
    }

    function showContactForm() {
        contactModal.classList.remove('hidden');
    }

    function closeContactModal() {
        contactModal.classList.add('hidden');
    }

    function reportIssue() {
        alert('Signalement de problème bientôt disponible');
    }

    // Auto-refresh tracking every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endsection
