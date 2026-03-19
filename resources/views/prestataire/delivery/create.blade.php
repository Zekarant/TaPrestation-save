@extends('layouts.app')

@section('title', 'Nouvelle Expédition')

@push('styles')
<style>
    .create-page {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        min-height: 100vh;
    }
    
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    
    .form-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: #1f2937;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .radio-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .radio-card:hover {
        border-color: #93c5fd;
    }
    
    .radio-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .radio-card input {
        display: none;
    }
    
    .checkbox-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .checkbox-card:hover {
        background: #f9fafb;
    }
    
    .checkbox-card.checked {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .summary-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
        color: white;
        border-radius: 16px;
        padding: 1.5rem;
    }
    
    .price-breakdown {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .submit-btn {
        width: 100%;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    }
</style>
@endpush

@section('content')
<div class="create-page py-4 sm:py-6">
    <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.create" />
        
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-3">
                <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                <span class="mx-2">›</span>
                <a href="{{ route('prestataire.logistics.index') }}" class="hover:text-blue-600">Livraisons</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900">Nouvelle expédition</span>
            </nav>
            
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                📦 Créer une nouvelle expédition
            </h1>
            <p class="text-gray-600 mt-1">Remplissez les informations pour créer une nouvelle livraison</p>
        </div>

        <form action="{{ route('prestataire.logistics.store') }}" method="POST" id="deliveryForm">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    
                    <!-- Booking Selection -->
                    @if(isset($bookings) && $bookings->count() > 0)
                    <div class="form-card p-4 sm:p-6 mb-6">
                        <h2 class="form-section-title">
                            <span class="text-xl">🎫</span>
                            Lier à une réservation (optionnel)
                        </h2>
                        
                        <select name="booking_id" id="bookingSelect" class="form-input">
                            <option value="">-- Aucune réservation --</option>
                            @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}" 
                                    data-client="{{ $booking->client->user->name ?? '' }}"
                                    data-phone="{{ $booking->client->user->phone ?? '' }}"
                                    data-address="{{ $booking->address ?? '' }}">
                                #{{ $booking->id }} - {{ $booking->client->user->name ?? 'Client' }} 
                                ({{ $booking->service->name ?? 'Service' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Pickup Address -->
                    <div class="form-card p-4 sm:p-6 mb-6">
                        <h2 class="form-section-title">
                            <span class="text-xl">📤</span>
                            Point d'enlèvement
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nom du contact *</label>
                                <input type="text" name="pickup_contact_name" class="form-input" 
                                       value="{{ old('pickup_contact_name', auth()->user()->name) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Téléphone *</label>
                                <input type="tel" name="pickup_contact_phone" class="form-input" 
                                       value="{{ old('pickup_contact_phone', auth()->user()->phone ?? '') }}" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Adresse complète *</label>
                            <input type="text" name="pickup_address" class="form-input" 
                                   placeholder="Numéro et nom de rue" 
                                   value="{{ old('pickup_address') }}" required>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Code postal *</label>
                                <input type="text" name="pickup_postal_code" class="form-input" 
                                       placeholder="75001" value="{{ old('pickup_postal_code') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ville *</label>
                                <input type="text" name="pickup_city" class="form-input" 
                                       placeholder="Paris" value="{{ old('pickup_city') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pays</label>
                                <input type="text" name="pickup_country" class="form-input" 
                                       value="{{ old('pickup_country', 'France') }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date/heure d'enlèvement souhaitée</label>
                            <input type="datetime-local" name="scheduled_pickup_at" class="form-input"
                                   value="{{ old('scheduled_pickup_at') }}">
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="form-card p-4 sm:p-6 mb-6">
                        <h2 class="form-section-title">
                            <span class="text-xl">📥</span>
                            Adresse de livraison
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nom du destinataire *</label>
                                <input type="text" name="delivery_contact_name" id="deliveryContactName" 
                                       class="form-input" value="{{ old('delivery_contact_name') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Téléphone *</label>
                                <input type="tel" name="delivery_contact_phone" id="deliveryContactPhone"
                                       class="form-input" value="{{ old('delivery_contact_phone') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="delivery_contact_email" class="form-input" 
                                       value="{{ old('delivery_contact_email') }}" placeholder="optionnel">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Adresse complète *</label>
                            <input type="text" name="delivery_address" id="deliveryAddress" class="form-input" 
                                   placeholder="Numéro et nom de rue" value="{{ old('delivery_address') }}" required>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Code postal *</label>
                                <input type="text" name="delivery_postal_code" class="form-input" 
                                       placeholder="75001" value="{{ old('delivery_postal_code') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ville *</label>
                                <input type="text" name="delivery_city" class="form-input" 
                                       placeholder="Paris" value="{{ old('delivery_city') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pays</label>
                                <input type="text" name="delivery_country" class="form-input" 
                                       value="{{ old('delivery_country', 'France') }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Instructions de livraison</label>
                            <textarea name="delivery_instructions" class="form-input" rows="2" 
                                      placeholder="Code d'entrée, étage, instructions spéciales...">{{ old('delivery_instructions') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date/heure de livraison souhaitée</label>
                            <input type="datetime-local" name="scheduled_delivery_at" class="form-input"
                                   value="{{ old('scheduled_delivery_at') }}">
                        </div>
                    </div>

                    <!-- Package Details -->
                    <div class="form-card p-4 sm:p-6 mb-6">
                        <h2 class="form-section-title">
                            <span class="text-xl">📦</span>
                            Détails du colis
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nombre de colis</label>
                                <input type="number" name="package_count" class="form-input" 
                                       min="1" value="{{ old('package_count', 1) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poids total (kg)</label>
                                <input type="number" name="weight" class="form-input" step="0.1" 
                                       placeholder="0.5" value="{{ old('weight') }}">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Longueur (cm)</label>
                                <input type="number" name="dimensions[length]" class="form-input" 
                                       placeholder="30" value="{{ old('dimensions.length') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Largeur (cm)</label>
                                <input type="number" name="dimensions[width]" class="form-input" 
                                       placeholder="20" value="{{ old('dimensions.width') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hauteur (cm)</label>
                                <input type="number" name="dimensions[height]" class="form-input" 
                                       placeholder="15" value="{{ old('dimensions.height') }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description du contenu</label>
                            <textarea name="description" class="form-input" rows="2" 
                                      placeholder="Décrivez le contenu du colis...">{{ old('description') }}</textarea>
                        </div>
                        
                        <!-- Special Options -->
                        <div class="mt-4">
                            <label class="form-label mb-3">Options spéciales</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="checkbox-card" onclick="this.classList.toggle('checked')">
                                    <input type="checkbox" name="fragile" value="1" {{ old('fragile') ? 'checked' : '' }}>
                                    <span class="text-xl">⚠️</span>
                                    <div>
                                        <p class="font-medium text-gray-900">Fragile</p>
                                        <p class="text-xs text-gray-500">Manipulation délicate</p>
                                    </div>
                                </label>
                                
                                <label class="checkbox-card" onclick="this.classList.toggle('checked')">
                                    <input type="checkbox" name="requires_signature" value="1" {{ old('requires_signature') ? 'checked' : '' }}>
                                    <span class="text-xl">✍️</span>
                                    <div>
                                        <p class="font-medium text-gray-900">Signature requise</p>
                                        <p class="text-xs text-gray-500">+2,00 €</p>
                                    </div>
                                </label>
                                
                                <label class="checkbox-card" onclick="this.classList.toggle('checked')">
                                    <input type="checkbox" name="is_insured" value="1" id="insuredCheckbox" {{ old('is_insured') ? 'checked' : '' }}>
                                    <span class="text-xl">🛡️</span>
                                    <div>
                                        <p class="font-medium text-gray-900">Assurance</p>
                                        <p class="text-xs text-gray-500">Protection du colis</p>
                                    </div>
                                </label>
                                
                                <label class="checkbox-card" onclick="this.classList.toggle('checked')">
                                    <input type="checkbox" name="has_cod" value="1" id="codCheckbox" {{ old('has_cod') ? 'checked' : '' }}>
                                    <span class="text-xl">💵</span>
                                    <div>
                                        <p class="font-medium text-gray-900">Contre-remboursement</p>
                                        <p class="text-xs text-gray-500">Paiement à la livraison</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-4 hidden" id="insuranceFields">
                            <div class="form-group">
                                <label class="form-label">Valeur à assurer (€)</label>
                                <input type="number" name="insurance_value" class="form-input" step="0.01"
                                       placeholder="100.00" value="{{ old('insurance_value') }}">
                            </div>
                        </div>
                        
                        <div class="mt-4 hidden" id="codFields">
                            <div class="form-group">
                                <label class="form-label">Montant à encaisser (€)</label>
                                <input type="number" name="cod_amount" class="form-input" step="0.01"
                                       placeholder="50.00" value="{{ old('cod_amount') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Type & Priority -->
                    <div class="form-card p-4 sm:p-6 mb-6">
                        <h2 class="form-section-title">
                            <span class="text-xl">🚚</span>
                            Type d'expédition
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <label class="radio-card selected" onclick="selectRadio(this, 'shipping_type')">
                                <input type="radio" name="shipping_type" value="standard" checked>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📦</span>
                                    <div>
                                        <p class="font-semibold text-gray-900">Standard</p>
                                        <p class="text-sm text-gray-500">2-4 jours • 4,90€</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="radio-card" onclick="selectRadio(this, 'shipping_type')">
                                <input type="radio" name="shipping_type" value="express">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">⚡</span>
                                    <div>
                                        <p class="font-semibold text-gray-900">Express</p>
                                        <p class="text-sm text-gray-500">24-48h • 9,90€</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="radio-card" onclick="selectRadio(this, 'shipping_type')">
                                <input type="radio" name="shipping_type" value="same_day">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🚀</span>
                                    <div>
                                        <p class="font-semibold text-gray-900">Même jour</p>
                                        <p class="text-sm text-gray-500">Aujourd'hui • 19,90€</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="radio-card" onclick="selectRadio(this, 'shipping_type')">
                                <input type="radio" name="shipping_type" value="scheduled">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📅</span>
                                    <div>
                                        <p class="font-semibold text-gray-900">Planifiée</p>
                                        <p class="text-sm text-gray-500">Date choisie • 6,90€</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <h3 class="form-label mb-3">Priorité</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="radio-card" onclick="selectRadio(this, 'priority')">
                                <input type="radio" name="priority" value="low">
                                <div class="text-center">
                                    <span class="text-xl">🟢</span>
                                    <p class="font-medium text-gray-700 text-sm mt-1">Basse</p>
                                </div>
                            </label>
                            
                            <label class="radio-card selected" onclick="selectRadio(this, 'priority')">
                                <input type="radio" name="priority" value="normal" checked>
                                <div class="text-center">
                                    <span class="text-xl">🔵</span>
                                    <p class="font-medium text-gray-700 text-sm mt-1">Normale</p>
                                </div>
                            </label>
                            
                            <label class="radio-card" onclick="selectRadio(this, 'priority')">
                                <input type="radio" name="priority" value="high">
                                <div class="text-center">
                                    <span class="text-xl">🟠</span>
                                    <p class="font-medium text-gray-700 text-sm mt-1">Haute</p>
                                </div>
                            </label>
                            
                            <label class="radio-card" onclick="selectRadio(this, 'priority')">
                                <input type="radio" name="priority" value="urgent">
                                <div class="text-center">
                                    <span class="text-xl">🔴</span>
                                    <p class="font-medium text-gray-700 text-sm mt-1">Urgente</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="lg:sticky lg:top-6">
                        <div class="summary-card">
                            <h3 class="text-lg font-bold mb-4">📋 Récapitulatif</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-200">Type</span>
                                    <span class="font-medium" id="summaryType">Standard</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-200">Priorité</span>
                                    <span class="font-medium" id="summaryPriority">Normale</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-200">Colis</span>
                                    <span class="font-medium" id="summaryPackages">1</span>
                                </div>
                            </div>
                            
                            <div class="price-breakdown">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>Frais de base</span>
                                    <span id="basePrice">4,90 €</span>
                                </div>
                                <div class="flex justify-between text-sm mb-2 hidden" id="signatureRow">
                                    <span>Signature</span>
                                    <span>+2,00 €</span>
                                </div>
                                <div class="flex justify-between text-sm mb-2 hidden" id="insuranceRow">
                                    <span>Assurance</span>
                                    <span id="insurancePrice">+0,00 €</span>
                                </div>
                                <div class="border-t border-blue-400 pt-2 mt-2">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span>Total estimé</span>
                                        <span id="totalPrice">4,90 €</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="submit-btn mt-6">
                                🚀 Créer l'expédition
                            </button>
                            
                            <p class="text-xs text-blue-200 text-center mt-4">
                                Un numéro de suivi sera généré automatiquement
                            </p>
                        </div>
                        
                        <div class="bg-white rounded-xl p-4 mt-4 border border-gray-100">
                            <h4 class="font-semibold text-gray-900 mb-2">💡 Conseils</h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li>• Vérifiez bien les adresses pour éviter les retards</li>
                                <li>• Indiquez les codes d'accès dans les instructions</li>
                                <li>• Pour les colis fragiles, optez pour l'assurance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectRadio(element, name) {
    document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
        radio.closest('.radio-card').classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input').checked = true;
    updateSummary();
}

function updateSummary() {
    const shippingType = document.querySelector('input[name="shipping_type"]:checked')?.value;
    const typeLabels = { standard: 'Standard', express: 'Express', same_day: 'Même jour', scheduled: 'Planifiée' };
    const basePrices = { standard: 4.90, express: 9.90, same_day: 19.90, scheduled: 6.90 };
    document.getElementById('summaryType').textContent = typeLabels[shippingType] || 'Standard';
    
    const priority = document.querySelector('input[name="priority"]:checked')?.value;
    const priorityLabels = { low: 'Basse', normal: 'Normale', high: 'Haute', urgent: 'Urgente' };
    document.getElementById('summaryPriority').textContent = priorityLabels[priority] || 'Normale';
    
    const packages = document.querySelector('input[name="package_count"]')?.value || 1;
    document.getElementById('summaryPackages').textContent = packages;
    
    let basePrice = basePrices[shippingType] || 4.90;
    let total = basePrice;
    
    document.getElementById('basePrice').textContent = basePrice.toFixed(2).replace('.', ',') + ' €';
    
    const signatureRow = document.getElementById('signatureRow');
    if (document.querySelector('input[name="requires_signature"]')?.checked) {
        signatureRow.classList.remove('hidden');
        total += 2;
    } else {
        signatureRow.classList.add('hidden');
    }
    
    const insuranceRow = document.getElementById('insuranceRow');
    const insuranceValue = parseFloat(document.querySelector('input[name="insurance_value"]')?.value) || 0;
    if (document.querySelector('input[name="is_insured"]')?.checked && insuranceValue > 0) {
        const insuranceFee = Math.max(1, insuranceValue * 0.02);
        insuranceRow.classList.remove('hidden');
        document.getElementById('insurancePrice').textContent = '+' + insuranceFee.toFixed(2).replace('.', ',') + ' €';
        total += insuranceFee;
    } else {
        insuranceRow.classList.add('hidden');
    }
    
    document.getElementById('totalPrice').textContent = total.toFixed(2).replace('.', ',') + ' €';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('insuredCheckbox')?.addEventListener('change', function() {
        document.getElementById('insuranceFields').classList.toggle('hidden', !this.checked);
        updateSummary();
    });
    
    document.getElementById('codCheckbox')?.addEventListener('change', function() {
        document.getElementById('codFields').classList.toggle('hidden', !this.checked);
    });
    
    document.getElementById('bookingSelect')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('deliveryContactName').value = option.dataset.client || '';
            document.getElementById('deliveryContactPhone').value = option.dataset.phone || '';
            document.getElementById('deliveryAddress').value = option.dataset.address || '';
        }
    });
    
    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('change', updateSummary);
    });
    
    document.querySelectorAll('.checkbox-card input:checked').forEach(input => {
        input.closest('.checkbox-card').classList.add('checked');
    });
    
    updateSummary();
});
</script>
@endpush
@endsection
