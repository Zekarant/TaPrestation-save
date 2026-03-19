@extends('layouts.app')

@section('title', 'Modifier ' . $foodProduct->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50">
    <!-- Header -->
    <div class="bg-white shadow-lg border-b-4 border-orange-500">
        <div class="container mx-auto px-4 py-4 sm:py-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('prestataire.food-products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <span class="text-3xl mr-3">✏️</span>
                        Modifier le produit
                    </h1>
                    <p class="text-gray-500 mt-1">{{ $foodProduct->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        <div class="max-w-2xl mx-auto">
            <form action="{{ route('prestataire.food-products.update', $foodProduct) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                @csrf
                @method('PUT')

                <!-- Nom -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nom du produit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $foodProduct->name) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                        placeholder="Ex: Pizza Margherita, Gâteau au chocolat...">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie et Prix -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Catégorie <span class="text-red-500">*</span>
                        </label>
                        <select name="category" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                            <option value="">Choisir...</option>
                            <option value="entree" {{ old('category', $foodProduct->category) == 'entree' ? 'selected' : '' }}>🥗 Entrée</option>
                            <option value="plat" {{ old('category', $foodProduct->category) == 'plat' ? 'selected' : '' }}>🍖 Plat</option>
                            <option value="dessert" {{ old('category', $foodProduct->category) == 'dessert' ? 'selected' : '' }}>🍰 Dessert</option>
                            <option value="boisson" {{ old('category', $foodProduct->category) == 'boisson' ? 'selected' : '' }}>🥤 Boisson</option>
                            <option value="amuse_bouche" {{ old('category', $foodProduct->category) == 'amuse_bouche' ? 'selected' : '' }}>🍢 Amuse-bouche</option>
                            <option value="gateau" {{ old('category', $foodProduct->category) == 'gateau' ? 'selected' : '' }}>🎂 Gâteau</option>
                            <option value="pizza" {{ old('category', $foodProduct->category) == 'pizza' ? 'selected' : '' }}>🍕 Pizza</option>
                            <option value="sandwich" {{ old('category', $foodProduct->category) == 'sandwich' ? 'selected' : '' }}>🥪 Sandwich</option>
                            <option value="salade" {{ old('category', $foodProduct->category) == 'salade' ? 'selected' : '' }}>🥗 Salade</option>
                            <option value="autre" {{ old('category', $foodProduct->category) == 'autre' ? 'selected' : '' }}>📦 Autre</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Prix (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="price" id="priceInput" value="{{ old('price', $foodProduct->price) }}" required step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition pr-12"
                                placeholder="0.00">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">€</span>
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Aperçu des gains après commission -->
                @php
                    $commissionRate = \App\Services\CommissionService::ratePercent('food', 'prestataire');
                    $stripeFeePercent = (float) get_setting('stripe_fee_percent', '1.4');
                    $stripeFeeFixed = (float) get_setting('stripe_fee_fixed', '0.25');
                    $price = $foodProduct->price;
                    $initialStripeFee = round(($price * $stripeFeePercent / 100) + $stripeFeeFixed, 2);
                    $initialCommission = round($price * ($commissionRate / 100), 2);
                    $initialReceive = round($price - $initialStripeFee - $initialCommission, 2);
                @endphp
                <div id="earningsPreview" class="mb-6 p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl border border-orange-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <span class="text-lg mr-2">💰</span> Aperçu de vos gains nets
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                            <span class="text-sm text-gray-600">💳 Client paie</span>
                            <span class="font-bold text-gray-800"><span id="clientPays">{{ number_format($price, 2) }}</span>€</span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                            <span class="text-sm text-gray-600">
                                <i class="fab fa-stripe text-purple-500"></i> Frais Stripe (~{{ $stripeFeePercent }}% + {{ number_format($stripeFeeFixed, 2) }}€)
                            </span>
                            <span class="font-bold text-purple-600">-<span id="stripeFee">{{ number_format($initialStripeFee, 2) }}</span>€</span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                            <span class="text-sm text-gray-600">🏢 Commission TaPrestation ({{ $commissionRate }}%)</span>
                            <span class="font-bold text-orange-500">-<span id="commissionAmount">{{ number_format($initialCommission, 2) }}</span>€</span>
                        </div>
                        <hr class="border-gray-300 my-2">
                        <div class="flex justify-between items-center bg-white p-3 rounded-lg border-2 border-orange-400">
                            <span class="text-sm font-semibold text-gray-700">✅ Vous recevez</span>
                            <span class="text-xl font-bold text-orange-600"><span id="youReceive">{{ number_format($initialReceive, 2) }}</span>€</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Montant versé après validation de la commande par le client
                    </p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition resize-none"
                        placeholder="Décrivez votre produit, ses ingrédients...">{{ old('description', $foodProduct->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Temps de préparation et Stock -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Temps de préparation (minutes)
                        </label>
                        <input type="number" name="preparation_time" value="{{ old('preparation_time', $foodProduct->preparation_time) }}" min="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Ex: 15">
                        @error('preparation_time')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Stock (laisser vide = illimité)
                        </label>
                        <input type="number" name="stock" value="{{ old('stock', $foodProduct->stock) }}" min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Ex: 50">
                        @error('stock')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sur commande / en gros -->
                <div class="mb-6 p-4 bg-amber-50 rounded-xl border border-amber-200">
                    <label class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            id="isPreorderOnly"
                            name="is_preorder_only"
                            value="1"
                            {{ old('is_preorder_only', $foodProduct->is_preorder_only) ? 'checked' : '' }}
                            class="w-5 h-5 mt-0.5 text-amber-500 border-gray-300 rounded focus:ring-amber-500"
                        >
                        <span>
                            <span class="block text-gray-800 font-semibold">Produit sur commande / en gros</span>
                            <span class="block text-sm text-gray-600 mt-1">
                                Le client devra choisir une date future respectant votre délai minimum avant retrait ou livraison.
                            </span>
                        </span>
                    </label>

                    <div id="preorderFields" class="mt-4 {{ old('is_preorder_only', $foodProduct->is_preorder_only) ? '' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Délai minimum avant retrait/livraison
                        </label>
                        <select
                            name="min_preorder_days"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                        >
                            <option value="">Choisir un délai</option>
                            @for($day = 2; $day <= 30; $day++)
                                <option value="{{ $day }}" {{ (string) old('min_preorder_days', $foodProduct->min_preorder_days) === (string) $day ? 'selected' : '' }}>
                                    {{ $day }} jour{{ $day > 1 ? 's' : '' }} minimum
                                </option>
                            @endfor
                        </select>
                        @error('min_preorder_days')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-2">
                            Exemple : `5 jours` = commande possible seulement à partir de J+5.
                        </p>
                    </div>
                </div>

                <!-- Image actuelle -->
                @if($foodProduct->image)
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Photo actuelle</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ storage_asset_url($foodProduct->image) }}" alt="{{ $foodProduct->name }}" class="w-24 h-24 object-cover rounded-xl border">
                        <label class="flex items-center text-sm text-gray-600">
                            <input type="checkbox" name="remove_image" value="1" class="mr-2 rounded text-red-500 focus:ring-red-500">
                            Supprimer l'image
                        </label>
                    </div>
                </div>
                @endif

                <!-- Nouvelle image -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ $foodProduct->image ? 'Nouvelle photo (optionnel)' : 'Photo du produit' }}
                    </label>
                    <div id="imageUploadTrigger" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-orange-400 transition cursor-pointer">
                        <input type="file" name="image" id="image" accept="image/*" class="hidden">
                        <div id="imagePreview" class="hidden mb-3">
                            <img id="preview" src="" alt="Aperçu" class="mx-auto max-h-40 rounded-lg">
                        </div>
                        <div id="uploadPlaceholder">
                            <span class="text-4xl block mb-2">📷</span>
                            <p class="text-gray-500">Cliquez pour changer la photo</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG ou WebP (max 2 Mo)</p>
                        </div>
                    </div>
                    @error('image')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Disponibilité -->
                <div class="mb-8">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $foodProduct->is_available) ? 'checked' : '' }}
                            class="w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                        <span class="ml-3 text-gray-700 font-medium">Produit actif et visible à la commande</span>
                    </label>
                </div>

                <!-- Options de Paiement -->
                @php($cashOnlyMode = function_exists('cash_only_mode') && cash_only_mode())
                <div class="mb-8 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="text-xl mr-2">💳</span> Options de paiement
                    </h3>

                    @if($cashOnlyMode)
                        <input type="hidden" name="payment_policy" value="cash">
                        <input type="hidden" name="deposit_percent" value="0">

                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <p class="font-semibold">Paiement en ligne désactivé</p>
                            <p class="mt-1">Ce produit reste disponible uniquement en paiement en espèces à la remise.</p>
                        </div>
                    @else
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Mode de paiement accepté
                            </label>
                            <select name="payment_policy" id="paymentPolicy"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="cash" {{ old('payment_policy', $foodProduct->payment_policy ?? 'cash') == 'cash' ? 'selected' : '' }}>💵 Paiement en espèces à la remise</option>
                                <option value="deposit" {{ old('payment_policy', $foodProduct->payment_policy) == 'deposit' ? 'selected' : '' }}>💰 Acompte en ligne + solde à la remise</option>
                                <option value="full_prepay" {{ old('payment_policy', $foodProduct->payment_policy) == 'full_prepay' ? 'selected' : '' }}>💳 Prépaiement intégral en ligne</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                L'acompte ou le prépaiement sécurise la commande et sera libéré après validation du code client.
                            </p>
                            @error('payment_policy')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="depositPercentField" class="{{ old('payment_policy', $foodProduct->payment_policy) == 'deposit' ? '' : 'hidden' }}">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Pourcentage d'acompte (%)
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="range" name="deposit_percent" id="depositRange" 
                                    value="{{ old('deposit_percent', $foodProduct->deposit_percent ?? 30) }}" min="10" max="100" step="5"
                                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                <span id="depositValue" class="w-16 text-center font-bold text-blue-600 text-lg">{{ old('deposit_percent', $foodProduct->deposit_percent ?? 30) }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Le client paiera cet acompte à la commande, le reste en espèces à la remise.
                            </p>
                            @error('deposit_percent')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <a href="{{ route('prestataire.food-products.index') }}" 
                        class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-center transition">
                        Annuler
                    </a>
                    <button type="submit" 
                        class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-md transition flex items-center justify-center">
                        <span class="mr-2">✓</span> Enregistrer
                    </button>
                </div>
            </form>

            <!-- Zone de danger -->
            <div class="mt-6 bg-red-50 rounded-2xl border border-red-200 p-6">
                <h3 class="text-lg font-bold text-red-800 mb-2">⚠️ Zone de danger</h3>
                <p class="text-sm text-red-600 mb-4">La suppression est irréversible.</p>
                <form action="{{ route('prestataire.food-products.destroy', $foodProduct) }}" method="POST" id="deleteFoodProductForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition">
                        🗑️ Supprimer ce produit
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Taux de commission Food (récupéré du backend)
const COMMISSION_RATE = {{ \App\Services\CommissionService::ratePercent('food', 'prestataire') }};
const STRIPE_FEE_PERCENT = {{ (float) get_setting('stripe_fee_percent', '1.4') }};
const STRIPE_FEE_FIXED = {{ (float) get_setting('stripe_fee_fixed', '0.25') }};

function updateEarningsPreview() {
    const priceInput = document.getElementById('priceInput');
    const price = parseFloat(priceInput.value) || 0;
    const preview = document.getElementById('earningsPreview');
    
    if (price > 0) {
        // Frais Stripe: X% + 0.25€
        const stripeFee = Math.round(((price * STRIPE_FEE_PERCENT / 100) + STRIPE_FEE_FIXED) * 100) / 100;
        // Commission TaPrestation
        const commission = Math.round(price * (COMMISSION_RATE / 100) * 100) / 100;
        // Ce que le presta reçoit
        const youReceive = Math.round((price - stripeFee - commission) * 100) / 100;
        
        document.getElementById('clientPays').textContent = price.toFixed(2);
        document.getElementById('stripeFee').textContent = stripeFee.toFixed(2);
        document.getElementById('commissionAmount').textContent = commission.toFixed(2);
        document.getElementById('youReceive').textContent = youReceive.toFixed(2);
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('uploadPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleDepositField() {
    const policySelect = document.getElementById('paymentPolicy');
    const depositField = document.getElementById('depositPercentField');
    if (!policySelect || !depositField) {
        return;
    }

    const policy = policySelect.value;
    if (policy === 'deposit') {
        depositField.classList.remove('hidden');
    } else {
        depositField.classList.add('hidden');
    }
}

function togglePreorderFields() {
    const checkbox = document.getElementById('isPreorderOnly');
    const fields = document.getElementById('preorderFields');
    const select = fields ? fields.querySelector('select[name="min_preorder_days"]') : null;

    if (!checkbox || !fields) {
        return;
    }

    if (checkbox.checked) {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
        if (select) {
            select.value = '';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('priceInput')?.addEventListener('input', updateEarningsPreview);
    document.getElementById('isPreorderOnly')?.addEventListener('change', togglePreorderFields);
    document.getElementById('imageUploadTrigger')?.addEventListener('click', function() {
        document.getElementById('image')?.click();
    });
    document.getElementById('image')?.addEventListener('change', function() {
        previewImage(this);
    });
    document.getElementById('paymentPolicy')?.addEventListener('change', toggleDepositField);
    document.getElementById('depositRange')?.addEventListener('input', function() {
        document.getElementById('depositValue').textContent = this.value + '%';
    });
    document.getElementById('deleteFoodProductForm')?.addEventListener('submit', function(event) {
        if (!window.confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
            event.preventDefault();
        }
    });
    updateEarningsPreview();
    togglePreorderFields();
    toggleDepositField();
});
</script>
@endpush
@endsection
