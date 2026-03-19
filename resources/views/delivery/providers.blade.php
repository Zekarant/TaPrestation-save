@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Delivery Providers</h1>
            <p class="text-gray-600 mt-2">Select a courier to deliver your service</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Search providers..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Price Range</label>
                <select id="priceFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Prices</option>
                    <option value="0-10">Under €10</option>
                    <option value="10-20">€10 - €20</option>
                    <option value="20-50">€20 - €50</option>
                    <option value="50">Over €50</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Delivery Time</label>
                <select id="timeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Times</option>
                    <option value="24">Next 24h</option>
                    <option value="2-3">2-3 Days</option>
                    <option value="1-week">1 Week</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Rating</label>
                <select id="ratingFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Ratings</option>
                    <option value="4.5">4.5+ Stars</option>
                    <option value="4">4+ Stars</option>
                    <option value="3.5">3.5+ Stars</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Providers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse ($providers as $provider)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow border-2 border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4">
                <h3 class="text-lg font-bold text-gray-900">{{ $provider->name }}</h3>
                <div class="flex items-center gap-2 mt-1">
                    <div class="flex text-yellow-400">
                        @for ($i = 0; $i < floor($provider->rating); $i++)
                            ★
                        @endfor
                    </div>
                    <span class="text-sm text-gray-600">{{ number_format($provider->rating, 1) }} ({{ $provider->reviews_count }} reviews)</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4 space-y-3">
                <!-- Service Area -->
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase">Coverage Area</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $provider->coverage_area ?? 'Nationwide' }}</p>
                </div>

                <!-- Delivery Options -->
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Delivery Options</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Standard</span>
                            <span class="font-semibold text-gray-900">€{{ number_format($provider->standard_price, 2) }}</span>
                        </div>
                        <div class="text-xs text-gray-600">{{ $provider->standard_days ?? '5-7' }} business days</div>
                        
                        @if ($provider->express_price)
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm text-gray-700">Express</span>
                            <span class="font-semibold text-gray-900">€{{ number_format($provider->express_price, 2) }}</span>
                        </div>
                        <div class="text-xs text-gray-600">{{ $provider->express_days ?? '2-3' }} business days</div>
                        @endif

                        @if ($provider->overnight_price)
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm text-gray-700">Overnight</span>
                            <span class="font-semibold text-gray-900">€{{ number_format($provider->overnight_price, 2) }}</span>
                        </div>
                        <div class="text-xs text-gray-600">Next business day</div>
                        @endif
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Features</p>
                    <div class="space-y-1 text-sm">
                        @if ($provider->has_tracking)
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-green-600">✓</span> Real-time tracking
                        </div>
                        @endif
                        @if ($provider->has_insurance)
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-green-600">✓</span> Full insurance
                        </div>
                        @endif
                        @if ($provider->has_signature)
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-green-600">✓</span> Signature required
                        </div>
                        @endif
                        @if ($provider->has_pickup)
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-green-600">✓</span> Free pickup
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Contact -->
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Contact</p>
                    <p class="text-sm text-blue-600 hover:text-blue-700">📞 {{ $provider->phone }}</p>
                    <p class="text-sm text-blue-600 hover:text-blue-700">✉️ {{ $provider->email }}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 p-4 flex gap-2">
                <button onclick="openProviderModal({{ $provider->id }}, '{{ $provider->name }}')" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition-colors">
                    Select
                </button>
                <button onclick="openProviderDetails({{ $provider->id }})" 
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold transition-colors">
                    Details
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-gray-50 rounded-lg p-12 text-center">
            <p class="text-gray-600 text-lg">No providers available for your location.</p>
            <p class="text-gray-500 text-sm mt-2">Please check back later or contact support.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Provider Details Modal -->
<div id="providerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div id="providerModalContent">
            <!-- Content loaded via JS -->
        </div>
        <button type="button" onclick="closeProviderModal()" class="mt-4 w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
            Close
        </button>
    </div>
</div>

<!-- Selection Modal -->
<div id="selectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Select Delivery Option</h3>
        <form id="selectionForm" method="POST">
            @csrf
            <input type="hidden" id="providerId" name="provider_id">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Delivery Type *</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="delivery_type" value="standard" checked class="w-4 h-4">
                        <span class="ml-2 text-sm text-gray-700">Standard Delivery</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="delivery_type" value="express" class="w-4 h-4">
                        <span class="ml-2 text-sm text-gray-700">Express Delivery</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="delivery_type" value="overnight" class="w-4 h-4">
                        <span class="ml-2 text-sm text-gray-700">Overnight Delivery</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Delivery Address *</label>
                <textarea name="delivery_address" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Special Instructions</label>
                <textarea name="special_instructions" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                    Confirm Selection
                </button>
                <button type="button" onclick="closeSelectionModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const providerModal = document.getElementById('providerModal');
    const selectionModal = document.getElementById('selectionModal');

    function openProviderModal(providerId, providerName) {
        document.getElementById('providerId').value = providerId;
        selectionModal.classList.remove('hidden');
    }

    function closeProviderModal() {
        providerModal.classList.add('hidden');
    }

    function closeSelectionModal() {
        selectionModal.classList.add('hidden');
    }

    function openProviderDetails(providerId) {
        providerModal.classList.remove('hidden');
        fetch(`/delivery/providers/${providerId}`)
            .then(r => r.text())
            .then(html => document.getElementById('providerModalContent').innerHTML = html);
    }

    // Filter functionality
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('priceFilter').addEventListener('change', applyFilters);
    document.getElementById('timeFilter').addEventListener('change', applyFilters);
    document.getElementById('ratingFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const price = document.getElementById('priceFilter').value;
        const time = document.getElementById('timeFilter').value;
        const rating = document.getElementById('ratingFilter').value;
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (price) params.append('price', price);
        if (time) params.append('time', time);
        if (rating) params.append('rating', rating);
        
        window.location.href = `{{ route('delivery.providers') }}?${params.toString()}`;
    }
</script>
@endsection
