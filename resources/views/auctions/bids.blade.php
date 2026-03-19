@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Auction Bids</h1>
            <p class="text-gray-600 mt-2">{{ $service->name ?? 'Service' }}</p>
        </div>
        @if (auth()->user()->role === 'client')
        <button onclick="openBidModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            + Place Bid
        </button>
        @endif
    </div>

    <!-- Highest Bid Card -->
    @if ($highestBid)
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-8 mb-8">
        <p class="text-blue-100 mb-2">Highest Bid</p>
        <div class="flex items-baseline justify-between">
            <div>
                <p class="text-4xl font-bold">€{{ number_format($highestBid->bid_amount, 2) }}</p>
                <p class="text-blue-100 mt-2">by {{ $highestBid->client->name ?? 'Anonymous' }}</p>
            </div>
            <div class="text-right">
                <p class="text-blue-100 mb-1">Expires in</p>
                <p class="text-2xl font-bold">{{ $highestBid->expires_at->diffInDays(now()) }} days</p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 mb-8">
        <p class="text-gray-600 text-center">No bids yet. Be the first to bid!</p>
    </div>
    @endif

    <!-- Bids List -->
    <div class="space-y-4">
        @forelse ($bids as $bid)
        <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $bid->status === 'accepted' ? 'border-green-500' : ($bid->status === 'rejected' ? 'border-red-500' : 'border-blue-500') }}">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">€{{ number_format($bid->bid_amount, 2) }}</h3>
                    <p class="text-gray-600 text-sm">{{ $bid->client->name ?? 'Anonymous' }}</p>
                    @if ($bid->message)
                    <p class="text-gray-700 mt-2 text-sm">{{ $bid->message }}</p>
                    @endif
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                        {{ $bid->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $bid->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $bid->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $bid->status === 'expired' ? 'bg-gray-100 text-gray-800' : '' }}
                    ">
                        {{ ucfirst($bid->status) }}
                    </span>
                    <p class="text-xs text-gray-600 mt-2">{{ $bid->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Prestataire Actions -->
            @if (auth()->user()->id === $service->prestataire_id && $bid->status === 'pending')
            <div class="mt-4 flex gap-2">
                <form action="{{ route('auction.accept', $bid) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold transition-colors">
                        Accept Bid
                    </button>
                </form>
                <form action="{{ route('auction.reject', $bid) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition-colors">
                        Reject
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <p class="text-gray-600">No bids placed yet.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Place Bid Modal -->
<div id="bidModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Place Your Bid</h3>
        <form action="{{ route('auction.place-bid', $service) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Bid Amount (€) *</label>
                <input type="number" name="bid_amount" required min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Message (optional)</label>
                <textarea name="message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Days Until Expiry *</label>
                <select name="days_to_expire" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="1">1 day</option>
                    <option value="3" selected>3 days</option>
                    <option value="7">1 week</option>
                    <option value="14">2 weeks</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                    Place Bid
                </button>
                <button type="button" onclick="closeBidModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const bidModal = document.getElementById('bidModal');

    function openBidModal() {
        bidModal.classList.remove('hidden');
    }

    function closeBidModal() {
        bidModal.classList.add('hidden');
    }
</script>
@endsection
