@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Gestion de l'inventaire</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Suivez votre équipement et vos fournitures</p>
        </div>
        <div class="flex gap-2 sm:gap-3">
            <a href="{{ route('inventory.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg font-semibold transition-colors text-sm">
                📥 Export
            </a>
            <button onclick="openInventoryModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg font-semibold transition-colors text-sm">
                + Add Item
            </button>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total Items</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $analytics['total_items'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Inventory Value</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 truncate">€{{ number_format($analytics['total_value'] ?? 0, 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Low Stock</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600">{{ $analytics['low_stock_count'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Profit Margin</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-purple-600">{{ number_format($analytics['average_profit_margin'] ?? 0, 1) }}%</p>
        </div>
    </div>

    @if ($items->count() > 0)
    <!-- Inventory Table (Desktop) -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">Item Name</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900 hidden md:table-cell">Category</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Qty</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900 hidden lg:table-cell">Cost</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Price</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900 hidden lg:table-cell">Margin</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($items as $item)
                <tr class="hover:bg-gray-50 transition-colors {{ $item->isLowStock() ? 'bg-red-50' : '' }}">
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 font-semibold text-gray-900 text-sm">{{ $item->name }}</td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-gray-600 text-sm hidden md:table-cell">{{ $item->category }}</td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center">
                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs sm:text-sm font-semibold">
                            {{ $item->quantity }} {{ $item->unit }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center text-gray-600 text-sm hidden lg:table-cell">€{{ number_format($item->cost_per_unit, 2) }}</td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center text-gray-600 text-sm">€{{ number_format($item->selling_price, 2) }}</td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center hidden lg:table-cell">
                        @php
                            $margin = (($item->selling_price - $item->cost_per_unit) / $item->cost_per_unit) * 100;
                        @endphp
                        <span class="inline-block {{ $margin > 30 ? 'text-green-600' : ($margin > 10 ? 'text-yellow-600' : 'text-red-600') }} font-semibold">
                            {{ number_format($margin, 1) }}%
                        </span>
                    </td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center">
                        @if ($item->isLowStock())
                        <span class="inline-block bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs font-semibold">
                            ⚠️ Low
                        </span>
                        @else
                        <span class="inline-block bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs font-semibold">
                            ✓ OK
                        </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-center">
                        <div class="flex gap-2 justify-center">
                            <!-- Actions de publication -->
                            <div class="relative group">
                                <button class="text-purple-600 hover:text-purple-800 font-semibold text-sm flex items-center">
                                    <i class="fas fa-share-alt mr-1"></i> Publier
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10 border border-gray-200">
                                    <a href="{{ route('equipment.create', ['inventory_id' => $item->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                        <i class="fas fa-truck-loading mr-2 text-blue-500"></i> Louer ce matériel
                                    </a>
                                    <a href="{{ route('urgent-sales.create', ['inventory_id' => $item->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                        <i class="fas fa-tag mr-2 text-green-500"></i> Vendre ce matériel
                                    </a>
                                </div>
                            </div>

                            <button onclick="editItem({{ $item->id }})" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                Edit
                            </button>
                            <form action="{{ route('inventory.destroy', $item) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this item?')" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <!-- Inventory Cards (Mobile) -->
    <div class="sm:hidden space-y-3">
        @foreach ($items as $item)
        <div class="bg-white rounded-lg shadow p-4 {{ $item->isLowStock() ? 'border-l-4 border-red-500' : '' }}">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-gray-900 text-sm">{{ $item->name }}</h3>
                @if ($item->isLowStock())
                <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs font-semibold">⚠️ Low</span>
                @else
                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs font-semibold">✓ OK</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mb-2">{{ $item->category }}</p>
            <div class="grid grid-cols-3 gap-2 text-center text-xs mb-3">
                <div class="bg-blue-50 rounded-lg p-2">
                    <div class="font-bold text-blue-700">{{ $item->quantity }} {{ $item->unit }}</div>
                    <div class="text-gray-500">Stock</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <div class="font-bold text-gray-700">€{{ number_format($item->selling_price, 2) }}</div>
                    <div class="text-gray-500">Prix</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    @php $margin = $item->cost_per_unit > 0 ? (($item->selling_price - $item->cost_per_unit) / $item->cost_per_unit) * 100 : 0; @endphp
                    <div class="font-bold {{ $margin > 30 ? 'text-green-600' : ($margin > 10 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($margin, 1) }}%</div>
                    <div class="text-gray-500">Marge</div>
                </div>
            </div>
            <div class="flex gap-2 text-xs">
                <div class="relative group flex-1">
                    <button class="w-full text-purple-600 bg-purple-50 hover:bg-purple-100 font-semibold py-2 rounded-lg flex items-center justify-center gap-1">
                        <i class="fas fa-share-alt"></i> Publier
                    </button>
                    <div class="absolute left-0 right-0 mt-1 bg-white rounded-md shadow-lg hidden group-hover:block z-10 border">
                        <a href="{{ route('equipment.create', ['inventory_id' => $item->id]) }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-truck-loading mr-1 text-blue-500"></i> Louer</a>
                        <a href="{{ route('urgent-sales.create', ['inventory_id' => $item->id]) }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-tag mr-1 text-green-500"></i> Vendre</a>
                    </div>
                </div>
                <button onclick="editItem({{ $item->id }})" class="flex-1 text-blue-600 bg-blue-50 hover:bg-blue-100 font-semibold py-2 rounded-lg">Edit</button>
                <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete?')" class="w-full text-red-600 bg-red-50 hover:bg-red-100 font-semibold py-2 rounded-lg">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $items->links() }}
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-12 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">No Inventory Items</h2>
        <p class="text-gray-600 mb-6">Start by adding your first inventory item.</p>
        <button onclick="openInventoryModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
            Add Item
        </button>
    </div>
    @endif
</div>

<!-- Add/Edit Item Modal -->
<div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6" id="modalTitle">Add Inventory Item</h2>

        <form id="itemForm" action="{{ route('inventory.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Item Name *</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Category *</label>
                    <input type="text" name="category" id="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">SKU (optional)</label>
                    <input type="text" name="sku" id="sku" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Supplier (optional)</label>
                    <input type="text" name="supplier" id="supplier" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Quantity *</label>
                    <input type="number" name="quantity" id="quantity" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Unit *</label>
                    <select name="unit" id="unit" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="piece">Piece</option>
                        <option value="kg">Kg</option>
                        <option value="liter">Liter</option>
                        <option value="meter">Meter</option>
                        <option value="box">Box</option>
                        <option value="pack">Pack</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Reorder Level *</label>
                    <input type="number" name="reorder_level" id="reorder_level" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Cost per Unit *</label>
                    <input type="number" name="cost_per_unit" id="cost_per_unit" required min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Selling Price *</label>
                    <input type="number" name="selling_price" id="selling_price" required min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Margin %</label>
                    <input type="number" id="margin" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Description (optional)</label>
                <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition-colors">
                    Save Item
                </button>
                <button type="button" onclick="closeItemModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const itemModal = document.getElementById('itemModal');
    const itemForm = document.getElementById('itemForm');
    const costInput = document.getElementById('cost_per_unit');
    const priceInput = document.getElementById('selling_price');
    const marginInput = document.getElementById('margin');

    function openInventoryModal() {
        itemForm.reset();
        document.getElementById('modalTitle').textContent = 'Add Inventory Item';
        itemForm.action = '{{ route('inventory.store') }}';
        itemModal.classList.remove('hidden');
    }

    function closeItemModal() {
        itemModal.classList.add('hidden');
        itemForm.reset();
    }

    function editItem(id) {
        alert('Fonctionnalité de modification bientôt disponible !');
    }

    // Calculate margin automatically
    function calculateMargin() {
        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        
        if (cost > 0) {
            const margin = ((price - cost) / cost) * 100;
            marginInput.value = margin.toFixed(1);
        }
    }

    costInput.addEventListener('change', calculateMargin);
    priceInput.addEventListener('change', calculateMargin);

    itemModal.addEventListener('click', function(e) {
        if (e.target === itemModal) {
            closeItemModal();
        }
    });
</script>
@endsection
