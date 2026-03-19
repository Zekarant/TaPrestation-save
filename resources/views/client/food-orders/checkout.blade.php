@extends('layouts.app')

@section('title', 'Finaliser la commande')

@section('content')
<div class="bg-gray-50 min-h-screen py-8 pb-28 sm:pb-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('food.cart', $prestataire) }}" class="hover:text-orange-500">
                    <i class="bi bi-arrow-left me-1"></i>Retour au panier
                </a>
            </nav>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="bi bi-credit-card text-orange-500 me-2"></i>Finaliser la commande
            </h1>
        </div>

        {{-- Bannière d'explication pour le client --}}
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-900">📋 Comment ça marche ?</h4>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1.5">
                        <li class="flex items-start gap-2">
                            <span class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                            <span><strong>Choisissez</strong> votre mode de récupération (emporter ou livraison)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                            <span><strong>Confirmez</strong> votre commande - le prestataire reçoit une notification</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                            <span><strong>Suivez</strong> en temps réel la préparation de votre commande</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">4</span>
                            @if(($paymentPolicy ?? 'cash') === 'cash')
                                <span><strong>Payez</strong> à la réception (espèces ou carte selon le prestataire)</span>
                            @elseif(($paymentPolicy ?? 'cash') === 'deposit')
                                <span><strong>Payez</strong> un acompte de {{ $depositPercent ?? 30 }}% en ligne, le reste à la réception</span>
                            @else
                                <span><strong>Payez</strong> en ligne avant confirmation (paiement sécurisé)</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ route('food.place-order', $prestataire) }}" method="POST" id="checkoutForm">
            @csrf
            
            {{-- reCAPTCHA hidden token --}}
            @if(config('recaptcha.enabled') && config('recaptcha.site_key'))
                <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
            @endif
            
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Formulaire -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Type de livraison -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="bi bi-truck text-orange-500 me-2"></i>Mode de récupération
                        </h3>
                        
                        <div class="grid sm:grid-cols-2 gap-4">
                            @if($prestataire->food_pickup_enabled ?? true)
                            <div class="delivery-option relative cursor-pointer" onclick="selectDeliveryType('pickup')">
                                <input type="radio" name="delivery_type" value="pickup" id="delivery_pickup" class="hidden" checked>
                                <div class="border-2 rounded-xl p-4 transition hover:border-orange-300 delivery-card border-orange-500 bg-orange-50" data-value="pickup">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                            <i class="bi bi-bag text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold">À emporter</h4>
                                            <p class="text-sm text-gray-500">Récupérer sur place</p>
                                        </div>
                                    </div>
                                    <p class="text-green-600 text-sm mt-3 font-medium">Gratuit</p>
                                    @if($prestataire->food_min_order_pickup)
                                        <p class="text-xs text-gray-400 mt-1">Min. {{ number_format($prestataire->food_min_order_pickup, 2) }} €</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($prestataire->food_delivery_enabled ?? false)
                            <div class="delivery-option relative cursor-pointer" onclick="selectDeliveryType('delivery')">
                                <input type="radio" name="delivery_type" value="delivery" id="delivery_delivery" class="hidden" {{ !($prestataire->food_pickup_enabled ?? true) ? 'checked' : '' }}>
                                <div class="border-2 rounded-xl p-4 transition hover:border-orange-300 delivery-card border-gray-200" data-value="delivery">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center">
                                            <i class="bi bi-truck text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold">Livraison</h4>
                                            <p class="text-sm text-gray-500">À domicile ({{ $prestataire->food_delivery_radius_km ?? 5 }} km max)</p>
                                        </div>
                                    </div>
                                    <p class="text-orange-600 text-sm mt-3 font-medium">
                                        @if($prestataire->food_free_delivery_above && $totals['subtotal'] >= $prestataire->food_free_delivery_above)
                                            <span class="text-green-600">Livraison gratuite !</span>
                                        @else
                                            À partir de {{ number_format($prestataire->food_delivery_base_fee ?? 3, 2) }} €
                                        @endif
                                    </p>
                                    @if($prestataire->food_min_order_delivery)
                                        <p class="text-xs text-gray-400 mt-1">Min. {{ number_format($prestataire->food_min_order_delivery, 2) }} €</p>
                                    @endif
                                    @if($prestataire->food_free_delivery_above && $totals['subtotal'] < $prestataire->food_free_delivery_above)
                                        <p class="text-xs text-green-500 mt-1">
                                            <i class="bi bi-gift me-1"></i>
                                            Gratuite dès {{ number_format($prestataire->food_free_delivery_above, 2) }} €
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if(!($prestataire->food_pickup_enabled ?? true) && !($prestataire->food_delivery_enabled ?? false))
                            <div class="text-center py-4 text-red-500">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Ce prestataire n'accepte pas de commandes pour le moment.
                            </div>
                            {{-- Disable submit button when no delivery method --}}
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const submitBtn = document.getElementById('submitOrderButton');
                                    if (submitBtn) {
                                        submitBtn.disabled = true;
                                        submitBtn.title = 'Aucun mode de récupération disponible';
                                    }
                                });
                            </script>
                        @endif
                        
                        @error('delivery_type')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date/heure souhaitée -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="bi bi-calendar-event text-orange-500 me-2"></i>
                            {{ $scheduleConfig['requires_advance_order'] ? 'Date de disponibilité' : 'Date/heure souhaitée (optionnel)' }}
                        </h3>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                @if($scheduleConfig['requires_advance_order'])
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Date disponible obligatoire
                                    </label>
                                    <select
                                        name="requested_at"
                                        required
                                        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('requested_at') border-red-500 @enderror"
                                    >
                                        <option value="">Choisir une date</option>
                                        @foreach($scheduleConfig['date_options'] as $option)
                                            <option value="{{ $option['value'] }}" {{ old('requested_at') === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['short_label'] }} - {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Programmer la commande
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="requested_at"
                                        value="{{ old('requested_at') }}"
                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('requested_at') border-red-500 @enderror"
                                    >
                                @endif
                                @error('requested_at')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                @if($scheduleConfig['requires_advance_order'])
                                    <p class="text-xs text-amber-700 mt-1">
                                        Ce panier contient des produits sur commande. Les dates proposées commencent à J+{{ $scheduleConfig['min_preorder_days'] }}.
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mt-1">
                                        Si vous laissez vide, la commande est pour maintenant.
                                    </p>
                                @endif
                            </div>
                            <div class="p-3 {{ $scheduleConfig['requires_advance_order'] ? 'bg-amber-50 text-amber-800' : 'bg-gray-50 text-gray-600' }} rounded-lg text-sm">
                                <p class="font-medium {{ $scheduleConfig['requires_advance_order'] ? 'text-amber-900' : 'text-gray-800' }} mb-1">ℹ️ Info</p>
                                @if($scheduleConfig['requires_advance_order'])
                                    <p>Commande planifiée obligatoire. Première date possible : {{ $scheduleConfig['earliest_date_label'] }}.</p>
                                @else
                                    <p>Le prestataire verra la date dans son agenda et pourra accepter en avance.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Adresse de livraison -->
                    <div class="bg-white rounded-xl shadow-sm p-6" id="deliveryAddressSection" style="display: none;">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="bi bi-geo-alt text-orange-500 me-2"></i>Adresse de livraison
                        </h3>
                        
                        @if($prestataire->food_delivery_instructions)
                            <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-700">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ $prestataire->food_delivery_instructions }}
                            </div>
                        @endif
                        
                        <!-- Contact -->
                        <div class="grid sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="bi bi-person me-1 text-gray-400"></i>Nom du destinataire
                                </label>
                                <input type="text" 
                                       name="delivery_contact_name" 
                                       value="{{ old('delivery_contact_name', auth()->user()->name ?? '') }}"
                                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('delivery_contact_name') border-red-500 @enderror"
                                       placeholder="Votre nom complet">
                                @error('delivery_contact_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="bi bi-telephone me-1 text-gray-400"></i>Téléphone <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" 
                                       name="delivery_phone" 
                                       value="{{ old('delivery_phone', auth()->user()->phone ?? '') }}"
                                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('delivery_phone') border-red-500 @enderror"
                                       placeholder="06 XX XX XX XX">
                                @error('delivery_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-400 mt-1">Le livreur vous appellera à ce numéro</p>
                            </div>
                        </div>
                        
                        <!-- Bouton Géolocalisation -->
                        <div class="mb-4">
                            <button type="button" 
                                    id="geolocateBtn"
                                    onclick="useMyLocation()"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-md transition-all">
                                <svg id="geolocateIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span id="geolocateBtnText">📍 Utiliser ma position actuelle</span>
                            </button>
                            <p class="text-xs text-gray-400 mt-1.5 text-center">
                                <i class="bi bi-shield-check me-1"></i>
                                Votre position est utilisée uniquement pour la livraison
                            </p>
                        </div>
                        
                        <div class="relative flex items-center my-4">
                            <div class="flex-grow border-t border-gray-200"></div>
                            <span class="flex-shrink mx-3 text-gray-400 text-sm">ou saisissez manuellement</span>
                            <div class="flex-grow border-t border-gray-200"></div>
                        </div>
                        
                        <!-- Adresse avec autocomplete -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                <i class="bi bi-geo-alt-fill me-1 text-gray-400"></i>Adresse complète <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="delivery_address_input"
                                       name="delivery_address" 
                                       value="{{ old('delivery_address', auth()->user()->address ?? '') }}"
                                       class="w-full border rounded-lg p-3 pl-10 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('delivery_address') border-red-500 @enderror"
                                       placeholder="Commencez à taper votre adresse..."
                                       autocomplete="off">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="bi bi-search text-gray-400"></i>
                                </div>
                                <!-- Suggestions dropdown -->
                                <div id="address_suggestions" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto"></div>
                            </div>
                            <input type="hidden" name="delivery_lat" id="delivery_lat" value="{{ old('delivery_lat') }}">
                            <input type="hidden" name="delivery_lng" id="delivery_lng" value="{{ old('delivery_lng') }}">
                            @error('delivery_address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Tapez au moins 3 caractères puis sélectionnez votre adresse
                            </p>
                        </div>
                        
                        <!-- Détails immeuble -->
                        <div class="grid sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="bi bi-building me-1 text-gray-400"></i>Étage / Appartement
                                </label>
                                <input type="text" 
                                       name="delivery_floor" 
                                       value="{{ old('delivery_floor') }}"
                                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="Ex: 3ème étage, Apt 12">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="bi bi-key me-1 text-gray-400"></i>Code d'accès
                                </label>
                                <input type="text" 
                                       name="delivery_door_code" 
                                       value="{{ old('delivery_door_code') }}"
                                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="Ex: A1234, 5678#">
                            </div>
                        </div>
                        
                        <!-- Instructions pour le livreur -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                <i class="bi bi-signpost-2 me-1 text-gray-400"></i>Instructions pour le livreur
                            </label>
                            <textarea name="delivery_building_info" 
                                      class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                                      rows="2" 
                                      placeholder="Ex: Bâtiment B, sonner chez Dupont, porte bleue...">{{ old('delivery_building_info') }}</textarea>
                        </div>
                        
                        <!-- Carte de prévisualisation (optionnel) -->
                        <div id="delivery_map_preview" class="h-40 rounded-lg bg-gray-100 mb-4 hidden overflow-hidden">
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="bi bi-map text-3xl"></i>
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-400">
                            <i class="bi bi-shield-check me-1"></i>
                            Zone de livraison : {{ $prestataire->food_delivery_radius_km ?? 5 }} km autour de {{ $prestataire->city ?? 'l\'établissement' }}
                        </p>
                    </div>
                    
                    <!-- Notes -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="bi bi-chat-text text-orange-500 me-2"></i>Instructions spéciales
                        </h3>
                        
                        <textarea name="notes" 
                                  class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                                  rows="2" 
                                  placeholder="Allergies, préférences, instructions pour la livraison...">{{ old('notes') }}</textarea>
                        <p class="text-gray-400 text-sm mt-2">Optionnel - Le prestataire verra ce message</p>
                    </div>
                    
                    <!-- Récapitulatif des articles -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-bold text-lg mb-4">
                            <i class="bi bi-list-check text-orange-500 me-2"></i>Récapitulatif
                        </h3>
                        
                        <div class="space-y-3">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-center py-2 border-b last:border-0">
                                    <div class="flex items-center gap-3">
                                        <span class="bg-orange-100 text-orange-600 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <span>{{ $item['product']->name }}</span>
                                    </div>
                                    <span class="font-semibold">{{ number_format($item['total'], 2) }} €</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Total -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 sticky top-4">
                        <h3 class="font-bold text-lg mb-4">Total</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Sous-total</span>
                                <span>{{ number_format($totals['subtotal'], 2) }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Frais de service</span>
                                <span>{{ number_format($totals['service_fee'], 2) }} €</span>
                            </div>
                            <div class="flex justify-between" id="deliveryFeeRow" style="display:none">
                                <span class="text-gray-500">
                                    Frais de livraison
                                    <span id="distanceInfo" class="hidden text-xs text-gray-400 block"></span>
                                </span>
                                <span id="deliveryFeeAmount" class="text-gray-400 italic">En attente d'adresse</span>
                            </div>
                            @if($prestataire->food_delivery_fee_per_km > 0)
                            <div class="text-xs text-gray-400 -mt-2" id="feeBreakdown" style="display:none">
                                <span>Base: {{ number_format($prestataire->food_delivery_base_fee ?? 3, 2) }}€ + {{ number_format($prestataire->food_delivery_fee_per_km, 2) }}€/km</span>
                            </div>
                            @endif
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="flex justify-between font-bold text-xl">
                            <span>Total</span>
                            <span class="text-orange-600" id="totalAmount">{{ number_format($totals['subtotal'] + $totals['service_fee'], 2) }} €</span>
                        </div>

                        <div id="deliveryBlockingHint" class="hidden mt-4 p-3 rounded-lg text-sm">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Renseignez l'adresse et le téléphone pour confirmer la livraison.
                        </div>

                        <button type="submit" id="submitOrderButton"
                                class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-4 rounded-lg font-bold mt-6 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            @if(($paymentPolicy ?? 'cash') === 'cash')
                                <i class="bi bi-check-circle me-2"></i>Confirmer la commande
                            @elseif(($paymentPolicy ?? 'cash') === 'deposit')
                                <i class="bi bi-credit-card me-2"></i>Payer l'acompte de {{ $depositPercent ?? 30 }}%
                            @else
                                <i class="bi bi-credit-card me-2"></i>Payer et confirmer
                            @endif
                        </button>
                        
                        <p class="text-xs text-gray-400 text-center mt-4">
                            En confirmant, vous acceptez nos conditions générales
                        </p>
                        
                        <div class="mt-4 p-3 bg-orange-50 rounded-lg text-center">
                            <p class="text-sm text-orange-700">
                                <i class="bi bi-info-circle me-1"></i>
                                @if(($paymentPolicy ?? 'cash') === 'cash')
                                    Le paiement se fait à la réception
                                @elseif(($paymentPolicy ?? 'cash') === 'deposit')
                                    Acompte de {{ $depositPercent ?? 30 }}% en ligne, reste à la réception
                                @else
                                    Paiement intégral en ligne sécurisé
                                @endif
                            </p>
                            @if(($paymentPolicy ?? 'cash') !== 'cash')
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Votre paiement sera sécurisé par Stripe
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
console.log('Food checkout script loaded');

// Fonction pour sélectionner le mode de livraison
function selectDeliveryType(type) {
    // Mettre à jour le radio button
    const radio = document.getElementById('delivery_' + type);
    if (radio) {
        radio.checked = true;
        // Déclencher l'événement change pour recalculer les frais
        radio.dispatchEvent(new Event('change'));
    }
}

try {
const baseTotal = @json(($totals['subtotal'] ?? 0) + ($totals['service_fee'] ?? 0));
const deliveryBaseFee = @json((float) ($prestataire->food_delivery_base_fee ?? 3));
const deliveryFeePerKm = @json((float) ($prestataire->food_delivery_fee_per_km ?? 0));
const freeDeliveryAbove = @json((float) ($prestataire->food_free_delivery_above ?? 0));
const subtotal = @json((float) ($totals['subtotal'] ?? 0));
const prepTimeMinutes = @json((int) ($prestataire->food_estimated_prep_time ?? 30));
const maxRadiusKm = @json((float) ($prestataire->food_delivery_radius_km ?? 5));
const prestataireId = @json((int) $prestataire->id);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
const distanceApiUrl = @json(route('api.food.calculate-distance'));
const deliveryFeeApiUrl = @json(route('api.food.calculate-fee'));
const defaultDeliveryHintText = "Renseignez l'adresse et le téléphone pour confirmer la livraison.";
const outOfZoneHintPrefix = "Depassement de la zone de livraison";
const prestataireLocation = {
    lat: @json((float) ($prestataire->latitude ?? 0)),
    lng: @json((float) ($prestataire->longitude ?? 0))
};
let map = null;
let marker = null;
let searchTimeout = null;
let currentDeliveryDistance = 0;
let deliveryZoneBlocked = false;
let deliveryBlockedReason = '';

function setDeliveryBlocked(blocked, reason = '') {
    deliveryZoneBlocked = !!blocked;
    deliveryBlockedReason = blocked ? (reason || `${outOfZoneHintPrefix}.`) : '';
}

// Initialize address autocomplete with OpenStreetMap Nominatim
function initAddressAutocomplete() {
    const input = document.getElementById('delivery_address_input');
    const suggestionsDiv = document.getElementById('address_suggestions');
    
    if (!input) return;
    
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 3) {
            suggestionsDiv.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchAddress(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });
}

// Search address using OpenStreetMap Nominatim API (FREE)
async function searchAddress(query) {
    const suggestionsDiv = document.getElementById('address_suggestions');
    
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=fr,be,ch,lu&limit=5&addressdetails=1`);
        const results = await response.json();
        
        if (results.length > 0) {
            suggestionsDiv.innerHTML = results.map(result => `
                <div class="suggestion-item p-3 hover:bg-orange-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors"
                     onclick="selectAddress('${result.display_name.replace(/'/g, "\\'")}', ${result.lat}, ${result.lon})">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="text-sm text-gray-700">${result.display_name}</span>
                    </div>
                </div>
            `).join('');
            suggestionsDiv.classList.remove('hidden');
        } else {
            suggestionsDiv.innerHTML = '<div class="p-3 text-gray-500 text-sm">Aucune adresse trouvée</div>';
            suggestionsDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erreur recherche adresse:', error);
    }
}

// Select an address from suggestions
function selectAddress(address, lat, lng) {
    document.getElementById('delivery_address_input').value = address;
    document.getElementById('delivery_lat').value = lat;
    document.getElementById('delivery_lng').value = lng;
    document.getElementById('address_suggestions').classList.add('hidden');
    
    // Show map preview
    showMapPreview(lat, lng);
    
    // Calculate exact distance/fee/time via backend API
    calculateExactDelivery(lat, lng);
    updateSubmitAvailability();
}

// Show map preview using Leaflet + OpenStreetMap
function showMapPreview(lat, lng) {
    const mapDiv = document.getElementById('delivery_map_preview');
    if (!mapDiv) return;
    
    mapDiv.classList.remove('hidden');
    mapDiv.innerHTML = '<div id="leaflet_map" class="w-full h-full"></div>';
    
    // Initialize Leaflet map
    map = L.map('leaflet_map').setView([lat, lng], 16);
    
    // Add OpenStreetMap tiles (FREE)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    // Add marker
    marker = L.marker([lat, lng]).addTo(map);
    
    // Fix map display issue
    setTimeout(() => map.invalidateSize(), 100);
}

// Update fee/total UI
function renderDeliverySummary(fee, distance = null, estimatedTime = null, note = null, forceUnavailable = false) {
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
    if (!deliveryType || deliveryType.value !== 'delivery') return;

    fee = Math.round((Number(fee) || 0) * 100) / 100;
    const hasOutOfZoneNote = typeof note === 'string' && /hors zone|depassement|en dehors|zone de livraison/i.test(note);
    const isOutsideByDistance = distance !== null && isFinite(distance) && Number(distance) > Number(maxRadiusKm || 0);
    const isUnavailable = Boolean(forceUnavailable || hasOutOfZoneNote || isOutsideByDistance);

    // Update display
    const feeEl = document.getElementById('deliveryFeeAmount');
    feeEl.className = ''; // reset classes
    if (isUnavailable) {
        feeEl.textContent = 'Non disponible';
        feeEl.classList.add('text-red-600', 'font-semibold');
    } else if (fee === 0) {
        feeEl.textContent = 'Gratuit !';
        feeEl.classList.add('text-green-600', 'font-semibold');
    } else {
        feeEl.textContent = fee.toFixed(2) + ' €';
    }
    
    // Show fee row
    const feeRow = document.getElementById('deliveryFeeRow');
    if (feeRow) feeRow.style.display = 'flex';

    // Update total
    const total = isUnavailable ? baseTotal : (baseTotal + fee);
    document.getElementById('totalAmount').textContent = total.toFixed(2) + ' €';

    // Show distance/time info
    const distanceInfo = document.getElementById('distanceInfo');
    if (distanceInfo) {
        let info = '';
        if (distance !== null && isFinite(distance)) {
            info += `Distance: ${Number(distance).toFixed(1)} km`;
        }
        if (estimatedTime !== null && isFinite(estimatedTime)) {
            info += (info ? ' • ' : '') + `~${Math.round(Number(estimatedTime))} min`;
        }
        if (note) {
            info += (info ? ' • ' : '') + note;
        }
        distanceInfo.textContent = info || 'Estimation';
        distanceInfo.classList.remove('text-gray-400', 'text-red-600', 'font-semibold');
        if (isUnavailable) {
            distanceInfo.classList.add('text-red-600', 'font-semibold');
        } else {
            distanceInfo.classList.add('text-gray-400');
        }
        distanceInfo.classList.remove('hidden');
    }
}

// Calculate exact delivery fee + distance + ETA using backend APIs.
async function calculateExactDelivery(lat, lng) {
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
    if (!deliveryType || deliveryType.value !== 'delivery') return;
    setDeliveryBlocked(false);

    const nLat = Number(lat);
    const nLng = Number(lng);
    if (!isFinite(nLat) || !isFinite(nLng) || nLat === 0 || nLng === 0) {
        setDeliveryBlocked(true, 'Adresse invalide. Merci de sélectionner une adresse dans la liste.');
        updateSubmitAvailability();
        return;
    }

    try {
        const distanceResponse = await fetch(distanceApiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                prestataire_id: prestataireId,
                lat: nLat,
                lng: nLng,
            }),
        });
        const distanceData = await distanceResponse.json();

        if (!distanceResponse.ok || !distanceData.success) {
            // Fallback local si possible
            if (prestataireLocation.lat && prestataireLocation.lng) {
                const distance = calculateDistance(nLat, nLng, prestataireLocation.lat, prestataireLocation.lng);
                currentDeliveryDistance = distance;
                const fee = (freeDeliveryAbove > 0 && subtotal >= freeDeliveryAbove)
                    ? 0
                    : (deliveryBaseFee + (distance * deliveryFeePerKm));
                const eta = prepTimeMinutes + Math.ceil(distance * 3);
                const outOfZone = distance > maxRadiusKm;
                const note = outOfZone ? 'Hors zone de livraison' : 'Estimation locale';
                renderDeliverySummary(fee, distance, eta, note, outOfZone);
                if (outOfZone) {
                    setDeliveryBlocked(true, `${outOfZoneHintPrefix} : ${distance.toFixed(1)} km (max ${maxRadiusKm.toFixed(1)} km).`);
                }
                updateSubmitAvailability();
                return;
            }

            const feeEl = document.getElementById('deliveryFeeAmount');
            if (feeEl) {
                feeEl.textContent = 'Indisponible';
                feeEl.className = 'text-red-600 font-semibold';
            }
            const distanceInfo = document.getElementById('distanceInfo');
            if (distanceInfo) {
                distanceInfo.textContent = distanceData.message || 'Impossible de calculer la distance.';
                distanceInfo.classList.remove('hidden');
            }
            setDeliveryBlocked(true, distanceData.message || 'Impossible de calculer la distance de livraison.');
            updateSubmitAvailability();
            return;
        }

        const distance = Number(distanceData.distance || 0);
        currentDeliveryDistance = distance;
        const inDeliveryZone = Boolean(distanceData.in_delivery_zone ?? (distance <= maxRadiusKm));

        if (!inDeliveryZone) {
            const fee = (freeDeliveryAbove > 0 && subtotal >= freeDeliveryAbove)
                ? 0
                : (deliveryBaseFee + (distance * deliveryFeePerKm));
            const eta = prepTimeMinutes + Math.ceil(distance * 3);
            renderDeliverySummary(fee, distance, eta, 'Hors zone de livraison', true);
            setDeliveryBlocked(true, `${outOfZoneHintPrefix} : ${distance.toFixed(1)} km (max ${maxRadiusKm.toFixed(1)} km).`);
            updateSubmitAvailability();
            return;
        }

        const feeResponse = await fetch(deliveryFeeApiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                prestataire_id: prestataireId,
                distance: distance,
                order_total: subtotal,
            }),
        });
        const feeData = await feeResponse.json();

        if (!feeResponse.ok || !feeData.success) {
            const note = feeData.message || (distance > maxRadiusKm ? 'Hors zone de livraison' : 'Tarif indisponible');
            const forceUnavailable = distance > maxRadiusKm || /zone de livraison|hors zone|en dehors|indisponible/i.test(note);
            renderDeliverySummary(0, distance, prepTimeMinutes + Math.ceil(distance * 3), note, forceUnavailable);
            if (distance > maxRadiusKm || /zone de livraison|hors zone|en dehors/i.test(note)) {
                setDeliveryBlocked(true, note);
            }
            updateSubmitAvailability();
            return;
        }

        const fee = Number(feeData.delivery_fee || 0);
        const eta = Number(feeData.estimated_time || (prepTimeMinutes + Math.ceil(distance * 3)));
        renderDeliverySummary(fee, distance, eta);
        setDeliveryBlocked(false);
        updateSubmitAvailability();
    } catch (error) {
        console.error('Erreur calcul exact livraison:', error);

        // Fallback local
        if (prestataireLocation.lat && prestataireLocation.lng) {
            const distance = calculateDistance(nLat, nLng, prestataireLocation.lat, prestataireLocation.lng);
            currentDeliveryDistance = distance;
            const fee = (freeDeliveryAbove > 0 && subtotal >= freeDeliveryAbove)
                ? 0
                : (deliveryBaseFee + (distance * deliveryFeePerKm));
            const eta = prepTimeMinutes + Math.ceil(distance * 3);
            const outOfZone = distance > maxRadiusKm;
            renderDeliverySummary(fee, distance, eta, outOfZone ? 'Hors zone de livraison' : 'Estimation locale', outOfZone);
            if (distance > maxRadiusKm) {
                setDeliveryBlocked(true, `${outOfZoneHintPrefix} : ${distance.toFixed(1)} km (max ${maxRadiusKm.toFixed(1)} km).`);
            } else {
                setDeliveryBlocked(false);
            }
            updateSubmitAvailability();
        } else {
            setDeliveryBlocked(true, 'Impossible de calculer la distance de livraison.');
            updateSubmitAvailability();
        }
    }
}

// Calculate distance in km using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initAddressAutocomplete();
});

// Use geolocation to get current position
function useMyLocation() {
    const btn = document.getElementById('geolocateBtn');
    const btnText = document.getElementById('geolocateBtnText');
    const icon = document.getElementById('geolocateIcon');
    
    if (!navigator.geolocation) {
        alert('La géolocalisation n\'est pas supportée par votre navigateur.');
        return;
    }
    
    // Show loading state
    btn.disabled = true;
    btnText.textContent = 'Localisation en cours...';
    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>';
    icon.classList.add('animate-spin');
    
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Update hidden fields
            document.getElementById('delivery_lat').value = lat;
            document.getElementById('delivery_lng').value = lng;
            
            // Reverse geocode to get address using OpenStreetMap Nominatim (FREE)
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
                const data = await response.json();
                
                if (data && data.display_name) {
                    document.getElementById('delivery_address_input').value = data.display_name;
                    
                    // Show map preview
                    showMapPreview(lat, lng);
                    
                    // Calculate distance/fee/time
                    await calculateExactDelivery(lat, lng);
                    btnText.textContent = '✅ Position détectée !';
                    btn.classList.remove('from-blue-500', 'to-indigo-600', 'hover:from-blue-600', 'hover:to-indigo-700');
                    btn.classList.add('from-green-500', 'to-emerald-600');
                } else {
                    throw new Error('Adresse non trouvée');
                }
            } catch (error) {
                console.error('Erreur reverse geocoding:', error);
                // Fallback: just use coordinates
                document.getElementById('delivery_address_input').value = `Position: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                showMapPreview(lat, lng);
                btnText.textContent = '⚠️ Précisez votre adresse';
            }
            
            // Reset button state
            icon.classList.remove('animate-spin');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
            btn.disabled = false;
            
            // Reset button after 3 seconds
            setTimeout(() => {
                btnText.textContent = '📍 Utiliser ma position actuelle';
                btn.classList.remove('from-green-500', 'to-emerald-600');
                btn.classList.add('from-blue-500', 'to-indigo-600', 'hover:from-blue-600', 'hover:to-indigo-700');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>';
            }, 3000);
        },
        function(error) {
            btn.disabled = false;
            icon.classList.remove('animate-spin');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    btnText.textContent = '❌ Accès refusé';
                    alert('Vous avez refusé l\'accès à votre position. Veuillez l\'autoriser dans les paramètres de votre navigateur ou saisir votre adresse manuellement.');
                    break;
                case error.POSITION_UNAVAILABLE:
                    btnText.textContent = '❌ Position indisponible';
                    alert('Impossible de déterminer votre position. Veuillez saisir votre adresse manuellement.');
                    break;
                case error.TIMEOUT:
                    btnText.textContent = '❌ Délai dépassé';
                    alert('La demande de géolocalisation a expiré. Veuillez réessayer.');
                    break;
                default:
                    btnText.textContent = '❌ Erreur';
                    alert('Une erreur est survenue. Veuillez saisir votre adresse manuellement.');
            }
            
            // Reset button text after 3 seconds
            setTimeout(() => {
                btnText.textContent = '📍 Utiliser ma position actuelle';
            }, 3000);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000
        }
    );
}

document.querySelectorAll('input[name="delivery_type"]').forEach(input => {
    input.addEventListener('change', function() {
        const isDelivery = this.value === 'delivery';
        
        // Show/hide delivery address
        document.getElementById('deliveryAddressSection').style.display = isDelivery ? 'block' : 'none';
        
        // Show/hide delivery fee row & fee breakdown
        const feeRow = document.getElementById('deliveryFeeRow');
        const feeBreakdown = document.getElementById('feeBreakdown');
        if (feeRow) feeRow.style.display = isDelivery ? 'flex' : 'none';
        if (feeBreakdown) feeBreakdown.style.display = isDelivery ? 'block' : 'none';
        
        // Get current lat/lng if available
        const lat = parseFloat(document.getElementById('delivery_lat').value) || 0;
        const lng = parseFloat(document.getElementById('delivery_lng').value) || 0;
        
        if (!isDelivery) {
            // Pickup: no delivery fee, show base total
            document.getElementById('deliveryFeeAmount').textContent = '0.00 €';
            document.getElementById('deliveryFeeAmount').className = '';
            document.getElementById('totalAmount').textContent = baseTotal.toFixed(2) + ' €';
            setDeliveryBlocked(false);
        } else if (lat && lng) {
            // Delivery with address already entered: calculate real fee
            calculateExactDelivery(lat, lng);
        } else {
            // Delivery but no validated address yet: show an estimate based on base fee
            // so the user always sees a coherent total.
            const estimatedFee = (freeDeliveryAbove > 0 && subtotal >= freeDeliveryAbove) ? 0 : deliveryBaseFee;
            const feeEl = document.getElementById('deliveryFeeAmount');
            if (estimatedFee === 0) {
                feeEl.textContent = 'Gratuit !';
                feeEl.className = 'text-green-600 font-semibold';
            } else {
                feeEl.textContent = `~ ${estimatedFee.toFixed(2)} €`;
                feeEl.className = 'text-orange-600';
            }

            const distanceInfo = document.getElementById('distanceInfo');
            if (distanceInfo) {
                distanceInfo.textContent = 'Estimation (adresse à confirmer)';
                distanceInfo.classList.remove('hidden');
            }

            document.getElementById('totalAmount').textContent = (baseTotal + estimatedFee).toFixed(2) + ' €';
            setDeliveryBlocked(false);
        }
        
        // Update card styles
        document.querySelectorAll('.delivery-card').forEach(card => {
            card.classList.remove('border-orange-500', 'bg-orange-50');
            card.classList.add('border-gray-200');
        });
        this.closest('.delivery-option').querySelector('.delivery-card').classList.remove('border-gray-200');
        this.closest('.delivery-option').querySelector('.delivery-card').classList.add('border-orange-500', 'bg-orange-50');

        // Live enable/disable submit button depending on delivery required fields
        updateSubmitAvailability();
    });
    
    // Initialize
    if (input.checked) {
        input.dispatchEvent(new Event('change'));
    }
});

function updateSubmitAvailability() {
    const submitBtn = document.getElementById('submitOrderButton');
    const hint = document.getElementById('deliveryBlockingHint');
    if (!submitBtn) return;

    const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
    const isDelivery = deliveryType && deliveryType.value === 'delivery';

    if (!isDelivery) {
        submitBtn.disabled = false;
        if (hint) hint.classList.add('hidden');
        return;
    }

    const addressEl = document.querySelector('input[name="delivery_address"]');
    const phoneEl = document.querySelector('input[name="delivery_phone"]');
    const address = (addressEl?.value || '').trim();
    const phone = (phoneEl?.value || '').trim();
    const lat = Number(document.getElementById('delivery_lat')?.value || 0);
    const lng = Number(document.getElementById('delivery_lng')?.value || 0);
    const hasCoords = isFinite(lat) && isFinite(lng) && lat !== 0 && lng !== 0;
    const hasRequiredFields = address.length > 0 && phone.length > 0 && hasCoords;
    const ok = hasRequiredFields && !deliveryZoneBlocked;
    submitBtn.disabled = !ok;
    submitBtn.classList.toggle('opacity-50', !ok);
    submitBtn.classList.toggle('cursor-not-allowed', !ok);
    submitBtn.classList.toggle('pointer-events-none', !ok);

    if (deliveryZoneBlocked) {
        const feeEl = document.getElementById('deliveryFeeAmount');
        if (feeEl) {
            feeEl.textContent = 'Non disponible';
            feeEl.className = 'text-red-600 font-semibold';
        }
    }

    if (hint) {
        hint.classList.remove('bg-yellow-50', 'border-yellow-200', 'text-yellow-800', 'bg-red-50', 'border-red-200', 'text-red-700');

        if (ok) {
            hint.classList.add('hidden');
        } else {
            const reason = deliveryZoneBlocked
                ? (deliveryBlockedReason || `${outOfZoneHintPrefix}.`)
                : defaultDeliveryHintText;
            hint.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>${reason}`;
            if (deliveryZoneBlocked) {
                hint.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
            } else {
                hint.classList.add('bg-yellow-50', 'border', 'border-yellow-200', 'text-yellow-800');
            }
            hint.classList.remove('hidden');
        }
    }
}

const addressInputEl = document.getElementById('delivery_address_input');
if (addressInputEl) {
    addressInputEl.addEventListener('input', function () {
        const latEl = document.getElementById('delivery_lat');
        const lngEl = document.getElementById('delivery_lng');
        if (latEl) latEl.value = '';
        if (lngEl) lngEl.value = '';
        currentDeliveryDistance = 0;
        setDeliveryBlocked(false);

        const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
        if (deliveryType && deliveryType.value === 'delivery') {
            const estimatedFee = (freeDeliveryAbove > 0 && subtotal >= freeDeliveryAbove) ? 0 : deliveryBaseFee;
            const feeEl = document.getElementById('deliveryFeeAmount');
            if (feeEl) {
                if (estimatedFee === 0) {
                    feeEl.textContent = 'Gratuit !';
                    feeEl.className = 'text-green-600 font-semibold';
                } else {
                    feeEl.textContent = `~ ${estimatedFee.toFixed(2)} €`;
                    feeEl.className = 'text-orange-600';
                }
            }

            const distanceInfo = document.getElementById('distanceInfo');
            if (distanceInfo) {
                distanceInfo.textContent = 'Estimation (adresse à confirmer)';
                distanceInfo.classList.remove('hidden');
            }

            document.getElementById('totalAmount').textContent = (baseTotal + estimatedFee).toFixed(2) + ' €';
        }

        updateSubmitAvailability();
    });
    addressInputEl.addEventListener('change', updateSubmitAvailability);
}

const phoneEl = document.querySelector('input[name="delivery_phone"]');
if (phoneEl) {
    phoneEl.addEventListener('input', updateSubmitAvailability);
    phoneEl.addEventListener('change', updateSubmitAvailability);
}

// Initial state
document.addEventListener('DOMContentLoaded', updateSubmitAvailability);

// Form validation
const recaptchaEnabled = @json((bool) (config('recaptcha.enabled') && config('recaptcha.site_key')));
const recaptchaSiteKey = @json((string) config('recaptcha.site_key'));

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    console.log('Form submit triggered');
    
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
    console.log('Delivery type:', deliveryType ? deliveryType.value : 'NONE');
    
    if (!deliveryType) {
        alert('Veuillez choisir un mode de récupération.');
        return false;
    }
    
    if (deliveryType.value === 'delivery') {
        const address = document.querySelector('input[name="delivery_address"]').value.trim();
        const phone = document.querySelector('input[name="delivery_phone"]').value.trim();
        
        if (!address) {
            alert('Veuillez entrer votre adresse de livraison.');
            document.querySelector('input[name="delivery_address"]').focus();
            return false;
        }
        
        if (!phone) {
            alert('Veuillez entrer votre numéro de téléphone pour la livraison.');
            document.querySelector('input[name="delivery_phone"]').focus();
            return false;
        }
        
        // Validate phone format
        const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
        if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
            alert('Veuillez entrer un numéro de téléphone valide.');
            document.querySelector('input[name="delivery_phone"]').focus();
            return false;
        }

        // Vérifier que les coordonnées GPS sont bien renseignées pour calculer les frais de livraison
        const lat = document.getElementById('delivery_lat').value;
        const lng = document.getElementById('delivery_lng').value;
        if (!lat || !lng || lat === '0' || lng === '0') {
            alert('Veuillez sélectionner une adresse depuis les suggestions pour calculer les frais de livraison.');
            document.querySelector('input[name="delivery_address"]').focus();
            return false;
        }
    }
    
    // reCAPTCHA validation
    if (recaptchaEnabled && window.grecaptcha) {
        try {
            const token = await grecaptcha.execute(recaptchaSiteKey, { action: 'food_order' });
            document.getElementById('recaptcha_token').value = token;
        } catch (err) {
            console.error('reCAPTCHA error:', err);
            alert('Erreur de vérification reCAPTCHA. Veuillez recharger la page.');
            return false;
        }
    }
    
    // Submit the form
    this.submit();
});
} catch (e) {
    console.error('Erreur initialisation checkout:', e);
}
</script>
@endpush
