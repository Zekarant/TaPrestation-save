@extends('layouts.app')

@section('title', 'Créer un devis')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages-ergonomics.css') }}">
<style>
    .quote-form-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .form-section {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
    }
    
    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-label.required::after {
        content: ' *';
        color: #dc2626;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .client-selector {
        position: relative;
    }
    
    .client-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .client-card:hover, .client-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .client-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.25rem;
    }
    
    .line-items-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .line-items-table th {
        background: #f3f4f6;
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.8rem;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .line-items-table th:first-child {
        border-radius: 8px 0 0 8px;
    }
    
    .line-items-table th:last-child {
        border-radius: 0 8px 8px 0;
    }
    
    .line-items-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    
    .line-item-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9rem;
    }
    
    .line-item-input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .btn-add-line {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 1rem;
        background: #eff6ff;
        color: #2563eb;
        border: 1px dashed #3b82f6;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-add-line:hover {
        background: #dbeafe;
    }
    
    .btn-remove-line {
        padding: 0.35rem;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-remove-line:hover {
        background: #fecaca;
    }
    
    .totals-section {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-top: 1rem;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
    }
    
    .total-row.grand-total {
        border-top: 2px solid #e5e7eb;
        padding-top: 1rem;
        margin-top: 0.5rem;
    }
    
    .total-label {
        font-weight: 500;
        color: #4b5563;
    }
    
    .total-value {
        font-weight: 600;
        color: #1f2937;
    }
    
    .grand-total .total-label,
    .grand-total .total-value {
        font-size: 1.25rem;
        color: #1f2937;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 1.5rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #d1d5db;
    }
    
    .btn-secondary:hover {
        background: #e5e7eb;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        
        {{-- En-tête --}}
        <div class="mb-6">
            <a href="{{ route('prestataire.quotes.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour aux devis
            </a>
            <h1 class="text-2xl font-bold text-gray-900">📝 Nouveau devis</h1>
            <p class="text-gray-600 mt-1">Créez un devis détaillé pour votre client</p>
        </div>
        
        <form action="{{ route('prestataire.quotes.store') }}" method="POST" id="quoteForm" class="quote-form-container"
              x-data="quoteForm()">
            @csrf
            
            {{-- Section Client --}}
            <div class="form-section">
                <h2 class="section-title">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Client destinataire
                </h2>
                
                <div class="form-group">
                    <label class="form-label required">Sélectionnez un client</label>
                    
                    @if($clients->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($clients as $client)
                                <label class="client-card" 
                                       :class="{ 'selected': selectedClient == {{ $client->id }} }">
                                    <input type="radio" name="client_id" value="{{ $client->id }}" 
                                           x-model="selectedClient" class="sr-only"
                                           {{ ($selectedClient && $selectedClient->id == $client->id) ? 'checked' : '' }}>
                                    <div class="client-avatar">
                                        {{ substr($client->user->name ?? 'C', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $client->user->name ?? 'Client' }}</h4>
                                        <p class="text-sm text-gray-600">{{ $client->user->email ?? '' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p>Vous n'avez pas encore de clients.</p>
                            <p class="text-sm">Les clients apparaîtront après votre première réservation.</p>
                        </div>
                    @endif
                    
                    @error('client_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            {{-- Section Informations du devis --}}
            <div class="form-section">
                <h2 class="section-title">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informations du devis
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label required" for="title">Titre du devis</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="Ex: Prestation de conseil marketing"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-textarea" rows="3"
                                  placeholder="Description détaillée de la prestation...">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="service_id">Service associé (optionnel)</label>
                        <select id="service_id" name="service_id" class="form-select">
                            <option value="">-- Aucun service --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ ($selectedService && $selectedService->id == $service->id) ? 'selected' : '' }}>
                                    {{ $service->title }} ({{ number_format($service->price, 2, ',', ' ') }}€)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="valid_until">Valide jusqu'au</label>
                        <input type="date" id="valid_until" name="valid_until" class="form-input"
                               value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            
            {{-- Section Lignes du devis --}}
            <div class="form-section">
                <h2 class="section-title">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Détail des prestations
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="line-items-table">
                        <thead>
                            <tr>
                                <th style="width: 40%">Description</th>
                                <th style="width: 15%">Quantité</th>
                                <th style="width: 15%">Unité</th>
                                <th style="width: 20%">Prix unitaire</th>
                                <th style="width: 10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td>
                                        <input type="text" 
                                               :name="'items[' + index + '][description]'"
                                               x-model="item.description"
                                               class="line-item-input" 
                                               placeholder="Description de la ligne"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               :name="'items[' + index + '][quantity]'"
                                               x-model.number="item.quantity"
                                               class="line-item-input" 
                                               min="0.01" step="0.01"
                                               @input="calculateTotals()"
                                               required>
                                    </td>
                                    <td>
                                        <select :name="'items[' + index + '][unit]'"
                                                x-model="item.unit"
                                                class="line-item-input">
                                            <option value="">-</option>
                                            <option value="heure">heure</option>
                                            <option value="jour">jour</option>
                                            <option value="forfait">forfait</option>
                                            <option value="unité">unité</option>
                                            <option value="m²">m²</option>
                                            <option value="km">km</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="relative">
                                            <input type="number" 
                                                   :name="'items[' + index + '][unit_price]'"
                                                   x-model.number="item.unit_price"
                                                   class="line-item-input pr-8" 
                                                   min="0" step="0.01"
                                                   @input="calculateTotals()"
                                                   required>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">€</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" @click="removeItem(index)" 
                                                class="btn-remove-line" x-show="items.length > 1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <button type="button" @click="addItem()" class="btn-add-line">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter une ligne
                    </button>
                </div>
                
                {{-- Totaux --}}
                <div class="totals-section">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="form-group">
                                <label class="form-label">TVA (%)</label>
                                <input type="number" name="tax_rate" x-model.number="taxRate" 
                                       class="form-input" min="0" max="100" step="0.1"
                                       @input="calculateTotals()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Remise</label>
                                <div class="flex gap-2">
                                    <input type="number" name="discount_amount" x-model.number="discountAmount" 
                                           class="form-input flex-1" min="0" step="0.01"
                                           @input="calculateTotals()">
                                    <select name="discount_type" x-model="discountType" 
                                            class="form-select w-24" @change="calculateTotals()">
                                        <option value="fixed">€</option>
                                        <option value="percentage">%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="total-row">
                                <span class="total-label">Sous-total HT</span>
                                <span class="total-value" x-text="formatCurrency(subtotal)"></span>
                            </div>
                            <div class="total-row" x-show="discountAmount > 0">
                                <span class="total-label">Remise</span>
                                <span class="total-value text-red-600" x-text="'-' + formatCurrency(discountValue)"></span>
                            </div>
                            <div class="total-row" x-show="taxRate > 0">
                                <span class="total-label">TVA (<span x-text="taxRate"></span>%)</span>
                                <span class="total-value" x-text="formatCurrency(taxAmount)"></span>
                            </div>
                            <div class="total-row grand-total">
                                <span class="total-label">Total TTC</span>
                                <span class="total-value text-blue-600" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Section Notes et conditions --}}
            <div class="form-section">
                <h2 class="section-title">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Notes et conditions
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="notes">Notes internes (non visibles par le client)</label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="3"
                                  placeholder="Notes personnelles...">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="terms">Conditions générales</label>
                        <textarea id="terms" name="terms" class="form-textarea" rows="3"
                                  placeholder="Conditions de paiement, délais...">{{ old('terms', "Devis valable 30 jours.\nPaiement : 30% à la commande, solde à la livraison.\nTVA non applicable, article 293 B du CGI.") }}</textarea>
                    </div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('prestataire.quotes.index') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" name="save_draft" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Enregistrer brouillon
                </button>
                <button type="submit" name="send_immediately" value="1" class="btn btn-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enregistrer et envoyer
                </button>
            </div>
        </form>
        
    </div>
</div>

@push('scripts')
<script>
function quoteForm() {
    return {
        selectedClient: {{ $selectedClient ? $selectedClient->id : 'null' }},
        items: [
            { description: '', quantity: 1, unit: 'forfait', unit_price: 0 }
        ],
        taxRate: 0,
        discountAmount: 0,
        discountType: 'fixed',
        subtotal: 0,
        discountValue: 0,
        taxAmount: 0,
        total: 0,
        
        addItem() {
            this.items.push({ description: '', quantity: 1, unit: 'forfait', unit_price: 0 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },
        
        calculateTotals() {
            // Sous-total
            this.subtotal = this.items.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            }, 0);
            
            // Remise
            if (this.discountType === 'percentage') {
                this.discountValue = this.subtotal * (parseFloat(this.discountAmount) || 0) / 100;
            } else {
                this.discountValue = parseFloat(this.discountAmount) || 0;
            }
            
            const afterDiscount = this.subtotal - this.discountValue;
            
            // TVA
            this.taxAmount = afterDiscount * (parseFloat(this.taxRate) || 0) / 100;
            
            // Total
            this.total = afterDiscount + this.taxAmount;
        },
        
        formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        },
        
        init() {
            this.calculateTotals();
        }
    }
}
</script>
@endpush
@endsection
