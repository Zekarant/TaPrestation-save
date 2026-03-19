@extends('layouts.app')

@section('title', 'Ajouter un équipement - Étape 2')

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2">Ajouter un équipement</h1>
                <p class="text-base sm:text-lg text-green-700">Étape 2 : Tarifs et conditions</p>
            </div>

            <!-- Barre de progression -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900">Processus de création</h2>
                    <span class="text-xs sm:text-sm text-green-600">Étape 2 sur 4</span>
                </div>
                <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 overflow-x-auto">
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Informations de base</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            2
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Tarifs et conditions</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            3
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            4
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Localisation et résumé</span>
                    </div>
                </div>
                <!-- Labels mobiles -->
                <div class="flex justify-between mt-2 sm:hidden text-xs text-gray-600">
                    <span class="text-green-600 font-medium">Info</span>
                    <span class="text-green-600 font-medium">Tarifs</span>
                    <span>Photos</span>
                    <span>Résumé</span>
                </div>
            </div>

            <!-- Formulaire Étape 2 -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prestataire.equipment.create.step1') }}" class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1">
                            <i class="fas fa-arrow-left text-base sm:text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-green-900">Tarifs et conditions</h2>
                            <p class="text-xs sm:text-sm text-green-700">Définissez vos prix et les conditions de location</p>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Oups!</strong>
                        <span class="block sm:inline">Quelque chose s'est mal passé.</span>
                        <ul class="mt-2">
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('prestataire.equipment.store.step2') }}" method="POST" x-data="{ pricingType: '{{ old('price_per_hour', session('equipment_step2.price_per_hour')) ? 'hourly' : 'daily' }}' }">
                    @csrf
                    
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Tarifs -->
                        <div class="bg-green-50 rounded-xl p-4 sm:p-6 border border-green-100">
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">
                                <i class="fas fa-tag text-green-600 mr-2"></i>Configuration des tarifs
                            </h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-green-700 mb-2">Type de tarification principale</label>
                                    <select x-model="pricingType" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="daily">Par jour (Standard)</option>
                                        <option value="hourly">Par heure</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="security_deposit" class="block text-sm font-medium text-green-700 mb-2">Caution (€) *</label>
                                    <input type="number" name="security_deposit" id="security_deposit" value="{{ old('security_deposit', session('equipment_step2.security_deposit')) }}" required min="0" step="0.01" placeholder="100" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('security_deposit') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                <div x-show="pricingType === 'daily' || pricingType === 'hourly'" class="transition-all duration-300">
                                    <label for="price_per_day" class="block text-sm font-medium text-green-700 mb-2">Prix par jour (€) <span x-show="pricingType === 'daily'">*</span></label>
                                    <input type="number" name="price_per_day" id="price_per_day" value="{{ old('price_per_day', session('equipment_step2.price_per_day')) }}" :required="pricingType === 'daily'" min="0" step="0.01" placeholder="50" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('price_per_day') border-red-500 @enderror">
                                </div>
                                
                                <div x-show="pricingType === 'hourly'" class="transition-all duration-300">
                                    <label for="price_per_hour" class="block text-sm font-medium text-green-700 mb-2">Prix par heure (€) *</label>
                                    <input type="number" name="price_per_hour" id="price_per_hour" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="10.00" value="{{ old('price_per_hour', session('equipment_step2.price_per_hour')) }}">
                                </div>

                                <div>
                                    <label for="price_per_week" class="block text-sm font-medium text-green-700 mb-2">Prix par semaine (€) <span class="text-xs text-gray-500">(Optionnel)</span></label>
                                    <input type="number" name="price_per_week" id="price_per_week" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="300.00" value="{{ old('price_per_week', session('equipment_step2.price_per_week')) }}">
                                </div>
                                
                                <div>
                                    <label for="price_per_month" class="block text-sm font-medium text-green-700 mb-2">Prix par mois (€) <span class="text-xs text-gray-500">(Optionnel)</span></label>
                                    <input type="number" name="price_per_month" id="price_per_month" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="1000.00" value="{{ old('price_per_month', session('equipment_step2.price_per_month')) }}">
                                </div>
                            </div>
                            
                            <div x-show="pricingType === 'hourly'" class="mt-2 text-sm text-green-600 bg-green-100 p-2 rounded">
                                <i class="fas fa-info-circle mr-1"></i> En choisissant la tarification par heure, n'oubliez pas de définir aussi un prix par jour pour les locations plus longues.
                            </div>
                            
                            <!-- Simulateur de gains avec durée -->
                            <div class="mt-4" id="earnings-simulator">
                                @php
                                    $commissionRate = \App\Services\CommissionService::ratePercent('rental', 'prestataire');
                                    $stripeFeePercent = (float) get_setting('stripe_fee_percent', '1.4');
                                    $stripeFeeFixed = (float) get_setting('stripe_fee_fixed', '0.25');
                                @endphp
                                
                                <!-- Sélecteur de durée de simulation -->
                                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-3">
                                    <label class="block text-sm font-medium text-green-700 mb-2">
                                        <i class="fas fa-calculator mr-1"></i> Simuler vos gains pour une location de :
                                    </label>
                                    <div class="flex gap-2 items-center">
                                        <input type="number" id="sim_duration" min="1" value="1" class="w-20 px-3 py-2 border border-green-300 rounded-md focus:ring-2 focus:ring-green-500">
                                        <select id="sim_period" class="px-3 py-2 border border-green-300 rounded-md focus:ring-2 focus:ring-green-500">
                                            <option value="hour">heure(s)</option>
                                            <option value="day" selected>jour(s)</option>
                                            <option value="week">semaine(s)</option>
                                            <option value="month">mois</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="earningsPreviewEquipment" class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hidden">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <span class="text-lg mr-2">💰</span> Aperçu de vos gains nets
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                                            <span class="text-sm text-gray-600">💳 Client paie (<span id="eq-duration-label">1 jour</span>)</span>
                                            <span class="font-bold text-gray-800"><span id="eq-client-pays">0.00</span>€</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                                            <span class="text-sm text-gray-600">
                                                <i class="fab fa-stripe text-purple-500"></i> Frais Stripe (~{{ $stripeFeePercent }}% + {{ number_format($stripeFeeFixed, 2) }}€)
                                            </span>
                                            <span class="font-bold text-purple-600">-<span id="eq-stripe-fee">0.00</span>€</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg">
                                            <span class="text-sm text-gray-600">🏢 Commission TaPrestation ({{ $commissionRate }}%)</span>
                                            <span class="font-bold text-orange-500">-<span id="eq-commission">0.00</span>€</span>
                                        </div>
                                        <hr class="border-gray-300 my-2">
                                        <div class="flex justify-between items-center bg-white p-3 rounded-lg border-2 border-green-400">
                                            <span class="text-sm font-semibold text-gray-700">✅ Vous recevez</span>
                                            <span class="text-xl font-bold text-green-600"><span id="eq-you-receive">0.00</span>€</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-3 text-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Montant versé après validation par le client
                                    </p>
                                </div>
                            </div>
                            
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const commissionRate = {{ $commissionRate }};
                                const stripeFeePercent = {{ $stripeFeePercent }};
                                const stripeFeeFixed = {{ $stripeFeeFixed }};
                                
                                const pricePerDay = document.getElementById('price_per_day');
                                const pricePerHour = document.getElementById('price_per_hour');
                                const pricePerWeek = document.getElementById('price_per_week');
                                const pricePerMonth = document.getElementById('price_per_month');
                                const simDuration = document.getElementById('sim_duration');
                                const simPeriod = document.getElementById('sim_period');
                                const previewEl = document.getElementById('earningsPreviewEquipment');
                                
                                function updateEquipmentEarnings() {
                                    const duration = parseFloat(simDuration?.value) || 1;
                                    const period = simPeriod?.value || 'day';
                                    
                                    let unitPrice = 0;
                                    let periodLabel = '';
                                    
                                    // Obtenir le prix selon la période sélectionnée
                                    switch(period) {
                                        case 'hour':
                                            unitPrice = parseFloat(pricePerHour?.value) || 0;
                                            periodLabel = duration + ' heure' + (duration > 1 ? 's' : '');
                                            break;
                                        case 'day':
                                            unitPrice = parseFloat(pricePerDay?.value) || 0;
                                            periodLabel = duration + ' jour' + (duration > 1 ? 's' : '');
                                            break;
                                        case 'week':
                                            unitPrice = parseFloat(pricePerWeek?.value) || (parseFloat(pricePerDay?.value) || 0) * 7;
                                            periodLabel = duration + ' semaine' + (duration > 1 ? 's' : '');
                                            break;
                                        case 'month':
                                            unitPrice = parseFloat(pricePerMonth?.value) || (parseFloat(pricePerDay?.value) || 0) * 30;
                                            periodLabel = duration + ' mois';
                                            break;
                                    }
                                    
                                    const totalPrice = unitPrice * duration;
                                    
                                    if (totalPrice > 0) {
                                        const stripeFee = Math.round(((totalPrice * stripeFeePercent / 100) + stripeFeeFixed) * 100) / 100;
                                        const commission = Math.round(totalPrice * (commissionRate / 100) * 100) / 100;
                                        const youReceive = Math.round((totalPrice - stripeFee - commission) * 100) / 100;
                                        
                                        document.getElementById('eq-client-pays').textContent = totalPrice.toFixed(2);
                                        document.getElementById('eq-stripe-fee').textContent = stripeFee.toFixed(2);
                                        document.getElementById('eq-commission').textContent = commission.toFixed(2);
                                        document.getElementById('eq-you-receive').textContent = youReceive.toFixed(2);
                                        document.getElementById('eq-duration-label').textContent = periodLabel;
                                        previewEl.classList.remove('hidden');
                                    } else {
                                        previewEl.classList.add('hidden');
                                    }
                                }
                                
                                // Écouter tous les champs
                                [pricePerDay, pricePerHour, pricePerWeek, pricePerMonth, simDuration, simPeriod].forEach(el => {
                                    if (el) {
                                        el.addEventListener('input', updateEquipmentEarnings);
                                        el.addEventListener('change', updateEquipmentEarnings);
                                    }
                                });
                                
                                updateEquipmentEarnings();
                            });
                            </script>
                        </div>

                        <!-- Acompte et Validation automatique -->
                        @php($cashOnlyMode = function_exists('cash_only_mode') && cash_only_mode())
                        <div class="bg-blue-50 rounded-xl p-4 sm:p-6 border border-blue-100" @unless($cashOnlyMode) x-data="{ paymentReq: '{{ old('payment_requirement', session('equipment_step2.payment_requirement', 'none')) }}' }" @endunless>
                            @if($cashOnlyMode)
                                <input type="hidden" name="payment_requirement" value="none">
                                <input type="hidden" name="deposit_percentage" value="0">
                                <input type="hidden" name="auto_accept_on_deposit" value="0">

                                <h3 class="text-base sm:text-lg font-semibold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                                    <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>Paiement de la location
                                </h3>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                                    <p class="font-semibold">Paiement en ligne désactivé</p>
                                    <p class="mt-1">La demande de location sera créée sans acompte ni paiement en ligne. Le règlement se fera directement avec le client.</p>
                                </div>
                            @else
                            <h3 class="text-base sm:text-lg font-semibold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                                <i class="fas fa-hand-holding-usd text-blue-600 mr-2"></i>Acompte et Validation de réservation
                            </h3>
                            
                            <!-- Exigence de paiement -->
                            <div class="mb-4">
                                <label for="payment_requirement" class="block text-sm font-medium text-blue-700 mb-2">
                                    Paiement requis pour valider la location
                                </label>
                                <select id="payment_requirement" name="payment_requirement" x-model="paymentReq"
                                        class="w-full px-3 py-2 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_requirement') border-red-500 @enderror">
                                    <option value="none">
                                        Aucun (réservation sans paiement immédiat)
                                    </option>
                                    <option value="deposit">
                                        Acompte obligatoire pour valider
                                    </option>
                                    <option value="full">
                                        Paiement complet obligatoire pour valider
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Choisissez si un paiement est requis avant de confirmer une demande de location.</p>
                                @error('payment_requirement')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Pourcentage d'acompte (visible si deposit sélectionné) -->
                            <div x-show="paymentReq === 'deposit'" class="mb-4 transition-all duration-300">
                                <label for="deposit_percentage" class="block text-sm font-medium text-blue-700 mb-2">
                                    Pourcentage d'acompte (%)
                                </label>
                                <input type="number" name="deposit_percentage" id="deposit_percentage" 
                                       value="{{ old('deposit_percentage', session('equipment_step2.deposit_percentage', 30)) }}" 
                                       min="1" max="100" step="1" 
                                       class="w-full sm:w-1/2 px-3 py-2 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('deposit_percentage') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Pourcentage du prix total que le client doit payer en acompte (ex: 30%).</p>
                                @error('deposit_percentage')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Auto-acceptation si acompte payé -->
                            <div x-show="paymentReq === 'deposit'" class="mb-4 transition-all duration-300">
                                <div class="flex items-center">
                                    <input type="checkbox" name="auto_accept_on_deposit" id="auto_accept_on_deposit" value="1" 
                                           {{ old('auto_accept_on_deposit', session('equipment_step2.auto_accept_on_deposit')) ? 'checked' : '' }} 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-blue-300 rounded">
                                    <label for="auto_accept_on_deposit" class="ml-3 block text-sm font-medium text-blue-700">
                                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                        Accepter automatiquement la demande si l'acompte est versé
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 ml-7">La demande sera automatiquement confirmée dès que le client paie l'acompte.</p>
                            </div>
                            
                            <!-- Info box -->
                            <div x-show="paymentReq === 'deposit'" class="bg-blue-100 border border-blue-200 rounded-lg p-3 mt-3">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Comment ça marche ?</strong><br>
                                    1. Le client fait une demande de location<br>
                                    2. Il reçoit une demande de paiement de l'acompte<br>
                                    3. Une fois l'acompte payé, la réservation est validée automatiquement (si activé)
                                </p>
                            </div>
                        </div>

                        <!-- Politique d'annulation -->
                        <div class="bg-amber-50 rounded-xl p-4 sm:p-6 border border-amber-100"
                             x-data="{ cancellationHours: {{ old('cancellation_hours', session('equipment_step2.cancellation_hours', 24)) }}, cancellationRefundPercentage: {{ old('cancellation_refund_percentage', session('equipment_step2.cancellation_refund_percentage', 100)) }} }">
                            <h3 class="text-base sm:text-lg font-semibold text-amber-900 mb-3 sm:mb-4 border-b border-amber-200 pb-2">
                                <i class="fas fa-calendar-times text-amber-600 mr-2"></i>Politique d'annulation
                            </h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="cancellation_hours" class="block text-sm font-medium text-amber-700 mb-2">
                                        Délai d'annulation gratuite (heures avant le début)
                                    </label>
                                    <input type="number" name="cancellation_hours" id="cancellation_hours" x-model="cancellationHours"
                                           value="{{ old('cancellation_hours', session('equipment_step2.cancellation_hours', 24)) }}" 
                                           min="0" max="168" step="1" 
                                           class="w-full px-3 py-2 text-sm sm:text-base border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 @error('cancellation_hours') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">24h = annulation gratuite jusqu'à 24h avant le début.</p>
                                    @error('cancellation_hours')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="cancellation_refund_percentage" class="block text-sm font-medium text-amber-700 mb-2">
                                        Pourcentage de remboursement (%)
                                    </label>
                                    <input type="number" name="cancellation_refund_percentage" id="cancellation_refund_percentage" x-model="cancellationRefundPercentage"
                                           value="{{ old('cancellation_refund_percentage', session('equipment_step2.cancellation_refund_percentage', 100)) }}" 
                                           min="0" max="100" step="1" 
                                           class="w-full px-3 py-2 text-sm sm:text-base border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 @error('cancellation_refund_percentage') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">100% = remboursement total si annulation dans les délais.</p>
                                    @error('cancellation_refund_percentage')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Info box politique d'annulation -->
                            <div class="bg-amber-100 border border-amber-200 rounded-lg p-3 mt-4">
                                <p class="text-sm text-amber-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Politique appliquée :</strong><br>
                                    • <strong>Annulation par le client ≥ <span x-text="cancellationHours || 24">24</span>h avant :</strong> Remboursement de <span x-text="cancellationRefundPercentage || 100">100</span>% de l'acompte<br>
                                    • <strong>Annulation par le client &lt; <span x-text="cancellationHours || 24">24</span>h avant :</strong> Pas de remboursement (acompte conservé)<br>
                                    • <strong>Annulation par vous ≥ <span x-text="cancellationHours || 24">24</span>h avant :</strong> Remboursement total au client
                                </p>
                            </div>
                            @endif
                        </div>

                        <!-- Options et conditions -->
                        <div>
                            <h3 class="text-lg font-semibold text-green-900 mb-4 border-b border-green-200 pb-2">Options et conditions</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input id="delivery_included" name="delivery_included" type="checkbox" value="1" {{ old('delivery_included', session('equipment_step2.delivery_included')) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="delivery_included" class="ml-3 block text-sm font-medium text-green-700">Livraison incluse dans le prix</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="license_required" id="license_required" value="1" {{ old('license_required', session('equipment_step2.license_required')) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="license_required" class="ml-3 block text-sm font-medium text-green-700">Permis ou certification requis</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available', session('equipment_step2.is_available', true)) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="is_available" class="ml-3 block text-sm font-medium text-green-700">Équipement disponible immédiatement</label>
                                </div>

                                <div>
                                    <label for="condition" class="block text-sm font-medium text-green-700 mb-2">État de l'équipement</label>
                                    <select name="condition" id="condition" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Sélectionner l'état</option>
                                        <option value="excellent" {{ old('condition', session('equipment_step2.condition')) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                        <option value="very_good" {{ old('condition', session('equipment_step2.condition')) == 'very_good' ? 'selected' : '' }}>Très bon</option>
                                        <option value="good" {{ old('condition', session('equipment_step2.condition')) == 'good' ? 'selected' : '' }}>Bon</option>
                                        <option value="fair" {{ old('condition', session('equipment_step2.condition')) == 'fair' ? 'selected' : '' }}>Correct</option>
                                        <option value="poor" {{ old('condition', session('equipment_step2.condition')) == 'poor' ? 'selected' : '' }}>Mauvais</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="rental_conditions" class="block text-sm font-medium text-green-700 mb-2">Conditions de location</label>
                                    <textarea name="rental_conditions" id="rental_conditions" rows="3" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Conditions particulières, restrictions d'usage...">{{ old('rental_conditions', session('equipment_step2.rental_conditions')) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Période de disponibilité -->
                        <div>
                            <h3 class="text-lg font-semibold text-green-900 mb-4 border-b border-green-200 pb-2">Période de disponibilité</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="available_from" class="block text-sm font-medium text-green-700 mb-2">Disponible à partir du</label>
                                    <input type="date" name="available_from" id="available_from" value="{{ old('available_from', session('equipment_step2.available_from')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('available_from') border-red-500 @enderror">
                                    @error('available_from')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-green-500 text-xs mt-1">Laissez vide si disponible immédiatement</p>
                                </div>
                                
                                <div>
                                    <label for="available_until" class="block text-sm font-medium text-green-700 mb-2">Disponible jusqu'au</label>
                                    <input type="date" name="available_until" id="available_until" value="{{ old('available_until', session('equipment_step2.available_until')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('available_until') border-red-500 @enderror">
                                    @error('available_until')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-green-500 text-xs mt-1">Laissez vide si pas de limite de temps</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="availability_start_time" class="block text-sm font-medium text-green-700 mb-2">Heure de début de disponibilité</label>
                                    <input type="time" name="availability_start_time" id="availability_start_time" value="{{ old('availability_start_time', session('equipment_step2.availability_start_time')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('availability_start_time') border-red-500 @enderror">
                                    @error('availability_start_time')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="availability_end_time" class="block text-sm font-medium text-green-700 mb-2">Heure de fin de disponibilité</label>
                                    <input type="time" name="availability_end_time" id="availability_end_time" value="{{ old('availability_end_time', session('equipment_step2.availability_end_time')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('availability_end_time') border-red-500 @enderror">
                                    @error('availability_end_time')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-green-700">
                                        <p class="font-medium mb-1">Information sur les dates de disponibilité :</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Ces dates définissent la période générale où votre équipement peut être loué</li>
                                            <li>Vous pourrez toujours bloquer des dates spécifiques plus tard</li>
                                            <li>Les clients ne pourront réserver que dans cette période</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-4 sm:pt-6 border-t border-green-200 gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.equipment.create.step1') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-2"></i>Précédent
                        </a>
                        
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <span class="hidden sm:inline">Suivant : Photos</span>
                            <span class="sm:hidden">Suivant</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Prevent form resubmission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            }
        });
    }
});
</script>
@endpush
