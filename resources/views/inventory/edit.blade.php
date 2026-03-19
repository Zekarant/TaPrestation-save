@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('inventory.index') }}" class="text-blue-600 hover:text-blue-700">
            ← Back
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $item->id ? 'Modifier l\'article' : 'Ajouter un nouvel article' }}
            </h1>
            <p class="text-gray-600 mt-2">{{ $item->name ?? 'Créer un nouvel article d\'inventaire' }}</p>
        </div>
    </div>

    <form action="{{ $item->id ? route('inventory.update', $item) : route('inventory.store') }}" method="POST" class="space-y-8">
        @csrf
        @if ($item->id)
        @method('PUT')
        @endif

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Item Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $item->name) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="e.g., Professional Camera">
                    @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">SKU *</label>
                    <input type="text" name="sku" required value="{{ old('sku', $item->sku) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('sku') border-red-500 @enderror"
                        placeholder="e.g., CAM-001">
                    @error('sku')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Category *</label>
                    <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-500 @enderror">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Fournisseur</label>
                    <input type="text" name="supplier" value="{{ old('supplier', $item->supplier) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="ex : Fournisseur Pro SARL">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Description détaillée de l'article...">{{ old('description', $item->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Cost -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Pricing & Cost</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Cost per Unit (€) *</label>
                    <input type="number" name="cost_per_unit" required min="0" step="0.01" 
                        id="costPerUnit"
                        value="{{ old('cost_per_unit', $item->cost_per_unit) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('cost_per_unit') border-red-500 @enderror"
                        placeholder="0.00">
                    @error('cost_per_unit')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Selling Price (€) *</label>
                    <input type="number" name="price_per_unit" required min="0" step="0.01" 
                        id="pricePerUnit"
                        value="{{ old('price_per_unit', $item->price_per_unit) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('price_per_unit') border-red-500 @enderror"
                        placeholder="0.00">
                    @error('price_per_unit')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Profit Margin %</label>
                    <div class="flex items-center">
                        <input type="number" id="profitMargin" readonly 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                            placeholder="0.00">
                        <span class="ml-2 text-gray-600 font-semibold">%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Automatically calculated</p>
                </div>
            </div>
        </div>

        <!-- Stock Management -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Stock Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Current Quantity *</label>
                    <input type="number" name="quantity_in_stock" required min="0" 
                        value="{{ old('quantity_in_stock', $item->quantity_in_stock) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('quantity_in_stock') border-red-500 @enderror"
                        placeholder="0">
                    @error('quantity_in_stock')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Reorder Level *</label>
                    <input type="number" name="reorder_level" required min="0" 
                        value="{{ old('reorder_level', $item->reorder_level) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Niveau de stock minimum">
                    <p class="text-xs text-gray-500 mt-1">Alerte lorsque le stock descend en dessous de ce seuil</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Reorder Quantity</label>
                    <input type="number" name="reorder_quantity" min="0" 
                        value="{{ old('reorder_quantity', $item->reorder_quantity) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Quantité à commander">
                </div>
            </div>
        </div>

        <!-- Status & Visibility -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Status & Visibility</h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" 
                        {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }} 
                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm font-semibold text-gray-900">
                        Actif (Disponible à l'utilisation)
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_trackable" id="is_trackable" 
                        {{ old('is_trackable', $item->is_trackable ?? true) ? 'checked' : '' }} 
                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <label for="is_trackable" class="ml-2 text-sm font-semibold text-gray-900">
                        Traçable (Suivi des mouvements de stock)
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="in-stock" {{ old('status', $item->status) === 'in-stock' ? 'selected' : '' }}>En stock</option>
                        <option value="low-stock" {{ old('status', $item->status) === 'low-stock' ? 'selected' : '' }}>Stock faible</option>
                        <option value="out-of-stock" {{ old('status', $item->status) === 'out-of-stock' ? 'selected' : '' }}>Rupture de stock</option>
                        <option value="discontinued" {{ old('status', $item->status) === 'discontinued' ? 'selected' : '' }}>Discontinué</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Additional Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Last Restocked</label>
                    <input type="datetime-local" name="last_restocked" 
                        value="{{ old('last_restocked', $item->last_restocked?->format('Y-m-d\TH:i')) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        readonly>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Warranty (Months)</label>
                    <input type="number" name="warranty_months" min="0" 
                        value="{{ old('warranty_months', $item->warranty_months) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g., 12">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Tags (comma-separated)</label>
                    <input type="text" name="tags" 
                        value="{{ old('tags', implode(', ', $item->tags ?? [])) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g., equipment, rental, professional">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition-colors">
                {{ $item->id ? '💾 Update Item' : '➕ Create Item' }}
            </button>
            <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold text-center transition-colors">
                Annuler
            </a>
            @if ($item->id)
            <form action="{{ route('inventory.destroy', $item) }}" method="POST" style="display: inline;" 
                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    🗑️ Delete
                </button>
            </form>
            @endif
        </div>
    </form>
</div>

<script>
    const costInput = document.getElementById('costPerUnit');
    const priceInput = document.getElementById('pricePerUnit');
    const marginInput = document.getElementById('profitMargin');

    function calculateMargin() {
        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        
        if (cost > 0) {
            const margin = ((price - cost) / cost * 100).toFixed(2);
            marginInput.value = margin;
        } else {
            marginInput.value = '0.00';
        }
    }

    costInput.addEventListener('change', calculateMargin);
    priceInput.addEventListener('change', calculateMargin);
    
    // Calculate on page load
    calculateMargin();
</script>
@endsection
