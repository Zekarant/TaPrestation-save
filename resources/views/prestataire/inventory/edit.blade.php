@extends('layouts.app')

@section('title', 'Modifier - ' . $item->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-4 sm:py-6">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="max-w-4xl mx-auto">
            
            {{-- En-tête compact --}}
            <div class="mb-4 sm:mb-6">
                <a href="{{ route('prestataire.inventory.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 text-sm mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour
                </a>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-2xl">✏️</span>
                    Modifier l'article
                </h1>
                <p class="text-gray-500 text-sm mt-1 truncate">{{ $item->name }}</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-red-700 font-semibold mb-2">Erreurs de validation :</p>
                    <ul class="list-disc list-inside text-red-600 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulaire --}}
            <form action="{{ route('prestataire.inventory.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="inventoryForm">
                @csrf
                @method('PUT')

                {{-- Section Informations de base --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-600 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informations
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                                <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                                <select name="category"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('category') border-red-500 @enderror">
                                    <option value="">Choisir...</option>
                                    <option value="equipment" {{ old('category', $item->category) == 'equipment' ? 'selected' : '' }}>Équipement</option>
                                    <option value="material" {{ old('category', $item->category) == 'material' ? 'selected' : '' }}>Matériel</option>
                                    <option value="consumable" {{ old('category', $item->category) == 'consumable' ? 'selected' : '' }}>Consommable</option>
                                    <option value="accessory" {{ old('category', $item->category) == 'accessory' ? 'selected' : '' }}>Accessoire</option>
                                    <option value="other" {{ old('category', $item->category) == 'other' ? 'selected' : '' }}>Autre</option>
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input type="text" name="sku" value="{{ old('sku', $item->sku) }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('sku') border-red-500 @enderror"
                                       placeholder="Référence">
                                @error('sku')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="2"
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $item->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Prix et Bénéfices --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-green-600 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Prix
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Achat € *</label>
                                <div class="relative">
                                    <input type="number" name="cost_per_unit" step="0.01" min="0" 
                                           value="{{ old('cost_per_unit', $item->cost_per_unit) }}" required
                                           id="costPrice"
                                           class="w-full px-3 py-2 pl-6 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('cost_per_unit') border-red-500 @enderror">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                                </div>
                                @error('cost_per_unit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vente € *</label>
                                <div class="relative">
                                    <input type="number" name="selling_price" step="0.01" min="0" 
                                           value="{{ old('selling_price', $item->selling_price) }}" required
                                           id="sellingPrice"
                                           class="w-full px-3 py-2 pl-6 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('selling_price') border-red-500 @enderror">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                                </div>
                                @error('selling_price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marge</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                                    <span id="profitAmount" class="text-sm font-bold text-emerald-600">+0€</span>
                                    <span id="profitPercent" class="text-xs font-semibold px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded">0%</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Indicateur visuel de marge compact --}}
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-600">Rentabilité</span>
                                <span id="profitLabel" class="text-xs font-medium text-gray-600">-</span>
                            </div>
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="profitBar" class="h-full bg-gray-400 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        {{-- Résumé financier complet --}}
                        @php
                            $itemProfit = ($item->selling_price - $item->cost_per_unit) * $item->quantity;
                            $itemMargin = $item->cost_per_unit > 0 ? (($item->selling_price - $item->cost_per_unit) / $item->cost_per_unit) * 100 : 0;
                        @endphp
                        <div class="mt-3 p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                            <p class="text-xs font-medium text-indigo-800 mb-2">💰 Résumé financier (stock: {{ $item->quantity }})</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-xs text-indigo-600">Coût total</p>
                                    <p class="text-sm font-bold text-gray-800">{{ number_format($item->cost_per_unit * $item->quantity, 0) }}€</p>
                                </div>
                                <div>
                                    <p class="text-xs text-indigo-600">Valeur vente</p>
                                    <p class="text-sm font-bold text-gray-800">{{ number_format($item->selling_price * $item->quantity, 0) }}€</p>
                                </div>
                                <div>
                                    <p class="text-xs text-indigo-600">Bénéfice</p>
                                    <p class="text-sm font-bold {{ $itemProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $itemProfit >= 0 ? '+' : '' }}{{ number_format($itemProfit, 0) }}€
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Stock --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-blue-600 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Gestion du Stock
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                                <input type="number" name="quantity" min="0" value="{{ old('quantity', $item->quantity) }}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                @if($item->initial_quantity)
                                    <p class="text-xs text-gray-400 mt-1">Initial: {{ $item->initial_quantity }}</p>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                                <select name="unit"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="unité" {{ old('unit', $item->unit) == 'unité' ? 'selected' : '' }}>Unité</option>
                                    <option value="pièce" {{ old('unit', $item->unit) == 'pièce' ? 'selected' : '' }}>Pièce</option>
                                    <option value="lot" {{ old('unit', $item->unit) == 'lot' ? 'selected' : '' }}>Lot</option>
                                    <option value="boîte" {{ old('unit', $item->unit) == 'boîte' ? 'selected' : '' }}>Boîte</option>
                                    <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="mètre" {{ old('unit', $item->unit) == 'mètre' ? 'selected' : '' }}>Mètre</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil alerte</label>
                                <input type="number" name="reorder_level" min="0" value="{{ old('reorder_level', $item->reorder_level ?? 5) }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       title="Alerte si stock ≤ cette valeur">
                            </div>
                        </div>
                        
                        {{-- Infos de suivi --}}
                        @if($item->last_restocked_at || $item->created_at)
                        <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-3 text-xs text-gray-500">
                            @if($item->last_restocked_at)
                                <span>📦 Dernier réappro: {{ $item->last_restocked_at->format('d/m/Y') }}</span>
                            @endif
                            @if($item->created_at)
                                <span>📅 Créé le: {{ $item->created_at->format('d/m/Y') }}</span>
                            @endif
                            @if($item->updated_at && $item->updated_at != $item->created_at)
                                <span>✏️ Modifié: {{ $item->updated_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Section Fournisseur et Emplacement --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-amber-600 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Fournisseur
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                                <input type="text" name="supplier" value="{{ old('supplier', $item->supplier) }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Amazon, LDLC...">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
                                <input type="text" name="location" value="{{ old('location', $item->location) }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Étagère A3...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Photos actuelles --}}
                @php
                    $photos = $item->getPhotosArray();
                    $photoUrls = $item->getPhotoUrls();
                @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-pink-600 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Photos ({{ count($photos) }})
                        </h2>
                    </div>
                    <div class="p-4">
                        {{-- Photos existantes avec bouton supprimer --}}
                        @if(count($photos) > 0 && count($photoUrls) > 0)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Photos actuelles</p>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2" id="existingPhotos">
                                @foreach($photos as $index => $photo)
                                    @php
                                        $photoUrl = $photoUrls[$index] ?? null;
                                    @endphp
                                    @if($photoUrl)
                                    <div class="relative group" id="photo-container-{{ $index }}">
                                        <img src="{{ $photoUrl }}" alt="Photo {{ $index + 1 }}" 
                                             class="w-full aspect-square object-cover rounded-lg border border-gray-200"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2210%22 fill=%22%239ca3af%22%3ENo image%3C/text%3E%3C/svg%3E';">
                                        {{-- Bouton supprimer --}}
                                        <button type="button" 
                                                onclick="removePhoto({{ $index }}, '{{ addslashes($photo) }}')"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"
                                                title="Supprimer cette photo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        {{-- Input hidden pour garder les photos existantes --}}
                                        <input type="hidden" name="existing_photos[]" value="{{ $photo }}" id="photo-input-{{ $index }}">
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg text-center text-gray-500 text-sm">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Aucune photo pour cet article
                        </div>
                        @endif
                        
                        {{-- Ajouter de nouvelles photos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ajouter des photos</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-pink-400 transition cursor-pointer" 
                                 onclick="document.getElementById('newPhotos').click()">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <p class="text-sm text-gray-500">Cliquez pour ajouter des photos</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP (max 2Mo chacune)</p>
                            </div>
                            <input type="file" id="newPhotos" name="new_photos[]" multiple accept="image/*" class="hidden" onchange="previewNewPhotos(this)">
                            
                            {{-- Prévisualisation des nouvelles photos --}}
                            <div id="newPhotosPreview" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-3" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                {{-- Lien avec Vente Flash --}}
                @php
                    // Chercher les ventes urgentes liées à cet article d'inventaire
                    $linkedUrgentSales = \App\Models\UrgentSale::where('inventory_item_id', $item->id)->get();
                @endphp
                
                @if($item->urgent_sale_id || $linkedUrgentSales->count() > 0)
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-orange-100 rounded-full">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-orange-800">⚡ Ventes flash liées</p>
                            <p class="text-sm text-orange-600">Cet article est lié à {{ $linkedUrgentSales->count() }} vente(s) urgente(s)</p>
                        </div>
                    </div>
                    
                    @foreach($linkedUrgentSales as $sale)
                    <div class="flex items-center justify-between bg-white rounded-lg p-3 mb-2 border border-orange-100">
                        <div>
                            <p class="font-medium text-gray-800">{{ $sale->title }}</p>
                            <p class="text-sm text-gray-500">{{ number_format($sale->price, 2) }} € • {{ ucfirst($sale->status) }}</p>
                        </div>
                        <a href="{{ route('prestataire.urgent-sales.edit', $sale->id) }}" 
                           class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition text-sm font-medium">
                            Modifier →
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                {{-- Pas de vente liée - proposer d'en créer une --}}
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-xl p-4 flex items-center gap-4">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-orange-800">💡 Créer une vente flash ?</p>
                        <p class="text-sm text-orange-600">Vendez cet article rapidement avec une annonce urgente</p>
                    </div>
                    <a href="{{ route('prestataire.urgent-sales.from-inventory', $item->id) }}" 
                       class="px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-lg hover:from-orange-600 hover:to-amber-600 transition text-sm font-semibold shadow-md">
                        ⚡ Créer une vente flash
                    </a>
                </div>
                @endif

                {{-- Boutons d'action du formulaire principal --}}
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('prestataire.inventory.index') }}" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </form>

            {{-- Zone de danger --}}
            <div class="mt-6 pt-4 border-t-2 border-red-200 bg-red-50 rounded-lg p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-red-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Zone de danger
                        </h3>
                        <p class="text-xs text-red-600 mt-1">Cette action est irréversible</p>
                    </div>
                    <form action="{{ route('prestataire.inventory.destroy', $item) }}" method="POST" 
                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet article ?\n\nCette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition flex items-center gap-2 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer cet article
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Calcul automatique de la marge
    const costInput = document.getElementById('costPrice');
    const sellingInput = document.getElementById('sellingPrice');
    const profitAmount = document.getElementById('profitAmount');
    const profitPercent = document.getElementById('profitPercent');
    const profitBar = document.getElementById('profitBar');
    const profitLabel = document.getElementById('profitLabel');

    function calculateProfit() {
        const cost = parseFloat(costInput.value) || 0;
        const selling = parseFloat(sellingInput.value) || 0;
        const profit = selling - cost;
        const margin = cost > 0 ? ((profit / cost) * 100) : 0;
        
        // Afficher le bénéfice
        profitAmount.textContent = (profit >= 0 ? '+' : '') + profit.toFixed(2) + ' €';
        profitAmount.className = profit >= 0 ? 'text-lg font-bold text-emerald-600' : 'text-lg font-bold text-red-600';
        
        // Afficher le pourcentage
        profitPercent.textContent = margin.toFixed(1) + '%';
        
        // Couleur du pourcentage
        if (margin >= 30) {
            profitPercent.className = 'text-sm font-semibold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg';
            profitBar.className = 'h-full bg-emerald-500 rounded-full transition-all duration-300';
            profitLabel.textContent = '🎯 Excellente marge !';
            profitLabel.className = 'text-sm font-medium text-emerald-600';
        } else if (margin >= 15) {
            profitPercent.className = 'text-sm font-semibold px-2 py-1 bg-green-100 text-green-700 rounded-lg';
            profitBar.className = 'h-full bg-green-500 rounded-full transition-all duration-300';
            profitLabel.textContent = '✅ Bonne marge';
            profitLabel.className = 'text-sm font-medium text-green-600';
        } else if (margin >= 0) {
            profitPercent.className = 'text-sm font-semibold px-2 py-1 bg-amber-100 text-amber-700 rounded-lg';
            profitBar.className = 'h-full bg-amber-500 rounded-full transition-all duration-300';
            profitLabel.textContent = '⚠️ Marge faible';
            profitLabel.className = 'text-sm font-medium text-amber-600';
        } else {
            profitPercent.className = 'text-sm font-semibold px-2 py-1 bg-red-100 text-red-700 rounded-lg';
            profitBar.className = 'h-full bg-red-500 rounded-full transition-all duration-300';
            profitLabel.textContent = '❌ Vente à perte !';
            profitLabel.className = 'text-sm font-medium text-red-600';
        }
        
        // Barre de progression (max 50% pour l'affichage)
        const barWidth = Math.min(Math.max(margin + 10, 0), 50) * 2;
        profitBar.style.width = barWidth + '%';
    }

    costInput.addEventListener('input', calculateProfit);
    sellingInput.addEventListener('input', calculateProfit);
    
    // Calculer au chargement
    calculateProfit();
    
    // ===== GESTION DES PHOTOS =====
    
    // Supprimer une photo existante
    function removePhoto(index, photoPath) {
        if (confirm('Supprimer cette photo ?')) {
            const container = document.getElementById('photo-container-' + index);
            const input = document.getElementById('photo-input-' + index);
            if (container) container.remove();
            if (input) input.remove();
        }
    }
    
    // Prévisualiser les nouvelles photos
    function previewNewPhotos(input) {
        const preview = document.getElementById('newPhotosPreview');
        preview.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            preview.style.display = 'grid';
            
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Nouvelle photo" class="w-full aspect-square object-cover rounded-lg border-2 border-pink-300">
                        <div class="absolute bottom-0 left-0 right-0 bg-pink-500 text-white text-xs text-center py-0.5 rounded-b-lg">Nouveau</div>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        } else {
            preview.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
