@extends('layouts.app')

@section('title', 'Ajouter un article - Ma Boutique')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            {{-- En-tête --}}
            <div class="mb-8">
                <a href="{{ route('prestataire.inventory.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour à la boutique
                </a>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="text-4xl">📦</span>
                    Ajouter un article
                </h1>
                <p class="text-gray-600 mt-2">Renseignez les informations de votre nouvel article d'inventaire</p>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('prestataire.inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Section Informations de base --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informations de base
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'article *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                                       placeholder="Ex: Canon EOS 5D Mark IV">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie *</label>
                                <select name="category" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('category') border-red-500 @enderror">
                                    <option value="">Sélectionnez une catégorie</option>
                                    <option value="equipment" {{ old('category') == 'equipment' ? 'selected' : '' }}>📷 Équipement</option>
                                    <option value="material" {{ old('category') == 'material' ? 'selected' : '' }}>🔧 Matériel</option>
                                    <option value="consumable" {{ old('category') == 'consumable' ? 'selected' : '' }}>📦 Consommable</option>
                                    <option value="accessory" {{ old('category') == 'accessory' ? 'selected' : '' }}>🎒 Accessoire</option>
                                    <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>📋 Autre</option>
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SKU / Référence</label>
                                <input type="text" name="sku" value="{{ old('sku') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('sku') border-red-500 @enderror"
                                       placeholder="Ex: CAM-EOS5D-001">
                                @error('sku')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                                          placeholder="Décrivez l'article en détail...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Prix et Bénéfices --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Prix et Bénéfices
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Prix d'achat (€) *
                                    <span class="text-xs text-gray-500 font-normal block">Votre coût d'acquisition</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="cost_per_unit" step="0.01" min="0" value="{{ old('cost_per_unit') }}" required
                                           id="costPrice"
                                           class="w-full px-4 py-3 pl-8 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('cost_per_unit') border-red-500 @enderror"
                                           placeholder="0.00">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">€</span>
                                </div>
                                @error('cost_per_unit')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Prix de vente (€) *
                                    <span class="text-xs text-gray-500 font-normal block">Prix affiché aux clients</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price') }}" required
                                           id="sellingPrice"
                                           class="w-full px-4 py-3 pl-8 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('selling_price') border-red-500 @enderror"
                                           placeholder="0.00">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">€</span>
                                </div>
                                @error('selling_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Marge bénéficiaire
                                    <span class="text-xs text-gray-500 font-normal block">Calculée automatiquement</span>
                                </label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <span id="profitAmount" class="text-lg font-bold text-emerald-600">+0.00 €</span>
                                        <span id="profitPercent" class="text-sm font-semibold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Indicateur visuel de marge --}}
                        <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Indicateur de rentabilité</span>
                                <span id="profitLabel" class="text-sm font-medium text-gray-600">-</span>
                            </div>
                            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div id="profitBar" class="h-full bg-gray-400 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between mt-1 text-xs text-gray-400">
                                <span>Perte</span>
                                <span>0%</span>
                                <span>15%</span>
                                <span>30%+</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Stock --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Gestion du Stock
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantité en stock *</label>
                                <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unité de mesure</label>
                                <select name="unit"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="unité" {{ old('unit', 'unité') == 'unité' ? 'selected' : '' }}>Unité(s)</option>
                                    <option value="pièce" {{ old('unit') == 'pièce' ? 'selected' : '' }}>Pièce(s)</option>
                                    <option value="lot" {{ old('unit') == 'lot' ? 'selected' : '' }}>Lot(s)</option>
                                    <option value="boîte" {{ old('unit') == 'boîte' ? 'selected' : '' }}>Boîte(s)</option>
                                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogramme(s)</option>
                                    <option value="mètre" {{ old('unit') == 'mètre' ? 'selected' : '' }}>Mètre(s)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Seuil d'alerte stock
                                    <span class="text-xs text-gray-500 font-normal block">Alerte si stock ≤ cette valeur</span>
                                </label>
                                <input type="number" name="reorder_level" min="0" value="{{ old('reorder_level', 5) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Fournisseur et Emplacement --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Fournisseur et Emplacement
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fournisseur</label>
                                <input type="text" name="supplier" value="{{ old('supplier') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Ex: Amazon, LDLC, Boulanger...">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Emplacement de stockage</label>
                                <input type="text" name="location" value="{{ old('location') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Ex: Étagère A3, Bureau, Entrepôt...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Photos --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Photos de l'article
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-500 transition cursor-pointer" id="dropZone">
                            <input type="file" name="photos[]" id="photoInput" multiple accept="image/*" class="hidden">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-gray-600 font-medium">Cliquez ou glissez-déposez vos photos ici</p>
                            <p class="text-sm text-gray-400 mt-1">PNG, JPG, WEBP jusqu'à 5 MB chacune</p>
                        </div>
                        <div id="photoPreview" class="mt-4 grid grid-cols-4 gap-4"></div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-end">
                    <a href="{{ route('prestataire.inventory.index') }}" 
                       class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition text-center">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Ajouter à l'inventaire
                    </button>
                </div>
            </form>
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

    // Gestion des photos
    const dropZone = document.getElementById('dropZone');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');

    dropZone.addEventListener('click', () => photoInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        photoInput.files = e.dataTransfer.files;
        showPreviews();
    });

    photoInput.addEventListener('change', showPreviews);

    function showPreviews() {
        photoPreview.innerHTML = '';
        Array.from(photoInput.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Aperçu photo ${index + 1}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition rounded-lg flex items-center justify-center">
                        <span class="text-white text-xs">Photo ${index + 1}</span>
                    </div>
                `;
                photoPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush
@endsection
