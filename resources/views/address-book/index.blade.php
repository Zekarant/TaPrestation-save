@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Address Book</h1>
            <p class="text-gray-600 mt-2">Manage your saved addresses</p>
        </div>
        <button onclick="openAddressModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            + Add Address
        </button>
    </div>

    @if ($addresses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($addresses as $address)
        <div class="bg-white rounded-lg shadow-lg p-6 {{ $address->is_default ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $address->label ?? 'Address' }}</h3>
                    <p class="text-sm text-gray-600">{{ $address->address_type ?? 'Personal' }}</p>
                </div>
                @if ($address->is_default)
                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                    Default
                </span>
                @endif
            </div>

            <div class="space-y-2 mb-4 text-sm text-gray-700">
                <p>{{ $address->recipient_name }}</p>
                <p>{{ $address->street }}</p>
                <p>{{ $address->postal_code }} {{ $address->city }}</p>
                <p>{{ $address->country }}</p>
                <p class="font-semibold text-gray-900">📞 {{ $address->phone }}</p>
            </div>

            @if ($address->latitude && $address->longitude)
            <div class="mb-4 text-xs text-gray-600">
                <p>📍 {{ number_format($address->latitude, 6) }}, {{ number_format($address->longitude, 6) }}</p>
            </div>
            @endif

            @if ($address->tags)
            <div class="mb-4 flex gap-2 flex-wrap">
                @foreach (json_decode($address->tags, true) as $tag)
                <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                    {{ $tag }}
                </span>
                @endforeach
            </div>
            @endif

            <div class="flex gap-2 pt-4 border-t border-gray-200">
                @if (!$address->is_default)
                <form action="{{ route('address-book.default', $address) }}" method="POST" class="flex-1">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full text-blue-600 hover:text-blue-800 font-semibold text-sm py-2">
                        Set as Default
                    </button>
                </form>
                @endif

                <button onclick="editAddress({{ $address->id }})" class="flex-1 text-gray-600 hover:text-gray-800 font-semibold text-sm py-2">
                    Edit
                </button>

                <form action="{{ route('address-book.destroy', $address) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this address?')" class="w-full text-red-600 hover:text-red-800 font-semibold text-sm py-2">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-12 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">No Saved Addresses</h2>
        <p class="text-gray-600 mb-6">Start by adding your first address to your address book.</p>
        <button onclick="openAddressModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
            Add Your First Address
        </button>
    </div>
    @endif
</div>

<!-- Add/Edit Address Modal -->
<div id="addressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6" id="modalTitle">Add Address</h2>

        <form id="addressForm" action="{{ route('address-book.store') }}" method="POST">
            @csrf
            <input type="hidden" id="addressId" name="id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Label (e.g., Home, Office)</label>
                    <input type="text" name="label" id="label" placeholder="Home" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Address Type</label>
                    <select name="address_type" id="address_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Personal">Personal</option>
                        <option value="Business">Business</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Recipient Name *</label>
                    <input type="text" name="recipient_name" id="recipient_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Phone Number *</label>
                    <input type="tel" name="phone" id="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Street Address *</label>
                <input type="text" name="street" id="street" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">City *</label>
                    <input type="text" name="city" id="city" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Postal Code *</label>
                    <input type="text" name="postal_code" id="postal_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Country *</label>
                    <input type="text" name="country" id="country" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Coordinates (optional)</label>
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" step="0.000001" name="latitude" id="latitude" placeholder="Latitude" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <input type="number" step="0.000001" name="longitude" id="longitude" placeholder="Longitude" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_default" id="is_default" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-900 font-semibold">Set as default address</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition-colors">
                    Save Address
                </button>
                <button type="button" onclick="closeAddressModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const addressModal = document.getElementById('addressModal');
    const addressForm = document.getElementById('addressForm');

    function openAddressModal() {
        addressForm.reset();
        document.getElementById('modalTitle').textContent = 'Add Address';
        document.getElementById('addressId').value = '';
        addressForm.action = '{{ route('address-book.store') }}';
        addressForm.method = 'POST';
        addressModal.classList.remove('hidden');
    }

    function closeAddressModal() {
        addressModal.classList.add('hidden');
        addressForm.reset();
    }

    function editAddress(id) {
        const address = @json($addresses->keyBy('id')->toArray());
        const addr = address[id];
        
        document.getElementById('modalTitle').textContent = 'Edit Address';
        document.getElementById('label').value = addr.label || '';
        document.getElementById('address_type').value = addr.address_type || 'Personal';
        document.getElementById('recipient_name').value = addr.recipient_name;
        document.getElementById('phone').value = addr.phone;
        document.getElementById('street').value = addr.street;
        document.getElementById('city').value = addr.city;
        document.getElementById('postal_code').value = addr.postal_code;
        document.getElementById('country').value = addr.country;
        document.getElementById('latitude').value = addr.latitude || '';
        document.getElementById('longitude').value = addr.longitude || '';
        document.getElementById('is_default').checked = addr.is_default;
        
        addressForm.action = '{{ route('address-book.update', ['address' => ':id']) }}'.replace(':id', id);
        addressForm.method = 'POST';
        addressForm.innerHTML = '@csrf @method('PATCH')' + addressForm.innerHTML;
        
        addressModal.classList.remove('hidden');
    }

    // Close modal when clicking outside
    addressModal.addEventListener('click', function(e) {
        if (e.target === addressModal) {
            closeAddressModal();
        }
    });
</script>
@endsection
