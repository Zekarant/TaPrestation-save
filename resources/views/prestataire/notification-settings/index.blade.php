@extends('layouts.prestataire')

@section('title', 'Paramètres de notifications')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-bell text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Paramètres de notifications</h1>
                    <p class="text-gray-600 dark:text-gray-400">Configurez vos préférences de notification</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        
        <form action="{{ route('prestataire.notification-settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            {{-- ===================== NOTIFICATIONS PUSH ===================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-100 dark:border-purple-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-mobile-alt text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notifications Push</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Recevez des alertes instantanées sur votre appareil</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-full">Recommandé</span>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    {{-- Activer Push --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-power-off text-purple-600 dark:text-purple-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Activer les notifications push</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Notifications instantanées sur votre téléphone/navigateur</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="push_notifications" value="0">
                            <input type="checkbox" name="push_notifications" value="1" {{ ($settings->push_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Réservations --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-blue-600 dark:text-blue-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Réservations de services</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nouvelles réservations, confirmations, annulations</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="booking_notifications" value="0">
                            <input type="checkbox" name="booking_notifications" value="1" {{ ($settings->booking_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Messages --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-comments text-green-600 dark:text-green-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Messages</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nouveaux messages des clients</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="message_notifications" value="0">
                            <input type="checkbox" name="message_notifications" value="1" {{ ($settings->message_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Commandes Food --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-utensils text-orange-600 dark:text-orange-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Commandes Food</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nouvelles commandes de repas</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="food_order_notifications" value="0">
                            <input type="checkbox" name="food_order_notifications" value="1" {{ (data_get($settings, 'food_order_notifications', true) == true || data_get($settings, 'food_order_notifications') === null) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Location équipements --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tools text-teal-600 dark:text-teal-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Location d'équipements</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Demandes de location et confirmations</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="equipment_notifications" value="0">
                            <input type="checkbox" name="equipment_notifications" value="1" {{ (data_get($settings, 'equipment_notifications', true) == true || data_get($settings, 'equipment_notifications') === null) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Enchères --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-gavel text-amber-600 dark:text-amber-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Enchères & Appels d'offres</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nouvelles enchères et opportunités</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="auction_notifications" value="0">
                            <input type="checkbox" name="auction_notifications" value="1" {{ ($settings->auction_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Avis --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-yellow-600 dark:text-yellow-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Avis clients</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nouveaux avis sur vos services</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="review_notifications" value="0">
                            <input type="checkbox" name="review_notifications" value="1" {{ ($settings->review_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Promotions --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tag text-pink-600 dark:text-pink-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Ventes flash & Promotions</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Offres spéciales et réductions</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="promotion_notifications" value="0">
                            <input type="checkbox" name="promotion_notifications" value="1" {{ ($settings->promotion_notifications ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Bouton Test Push --}}
                    <div class="p-5 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Tester les notifications push</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Vérifiez que tout fonctionne correctement</p>
                            </div>
                            <button type="button" onclick="testPush()" id="push-test-btn" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-paper-plane"></i>
                                <span>Envoyer un test</span>
                            </button>
                        </div>
                        <div id="push-status" class="mt-4 hidden">
                            <div class="flex items-center gap-2 text-sm p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <i id="push-status-icon" class="fas fa-spinner fa-spin text-purple-600"></i>
                                <span id="push-status-text" class="text-gray-600 dark:text-gray-400">Activation en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== NOTIFICATIONS EMAIL ===================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-b border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notifications par Email</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Recevez un résumé dans votre boîte mail</p>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    {{-- Email - Activer --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-power-off text-blue-600 dark:text-blue-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Activer les notifications email</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Recevoir des emails pour les événements importants</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="email_notifications" value="0">
                            <input type="checkbox" name="email_notifications" value="1" {{ ($settings->email_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Email - Paiements --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-green-600 dark:text-green-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Paiements & Factures</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Confirmations de paiement et reçus</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="payment_notifications" value="0">
                            <input type="checkbox" name="payment_notifications" value="1" {{ ($settings->payment_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Email - Newsletter --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-newspaper text-yellow-600 dark:text-yellow-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Newsletter</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Actualités et conseils de la plateforme</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="newsletter_notifications" value="0">
                            <input type="checkbox" name="newsletter_notifications" value="1" {{ ($settings->newsletter_notifications ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>

                    {{-- Bouton Test Email --}}
                    <div class="p-5 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Tester les notifications email</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Envoyez un email test à votre adresse</p>
                            </div>
                            <button type="button" onclick="testEmail()" id="email-test-btn" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-envelope"></i>
                                <span>Envoyer un test</span>
                            </button>
                        </div>
                        <div id="email-status" class="mt-4 hidden">
                            <div class="flex items-center gap-2 text-sm p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <i id="email-status-icon" class="fas fa-spinner fa-spin text-blue-600"></i>
                                <span id="email-status-text" class="text-gray-600 dark:text-gray-400">Envoi en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== NOTIFICATIONS SMS ===================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20 border-b border-teal-100 dark:border-teal-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-sms text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notifications SMS</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Recevez des alertes par SMS</p>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    {{-- SMS - Activer --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-power-off text-teal-600 dark:text-teal-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Activer les notifications SMS</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Recevoir des SMS pour les événements urgents</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="sms_notifications" value="0">
                            <input type="checkbox" name="sms_notifications" value="1" {{ ($settings->sms_notifications ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    {{-- Numéro de téléphone --}}
                    <div class="p-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Numéro de téléphone</label>
                        <input type="tel" name="phone_number" value="{{ $settings->phone_number ?? '' }}" placeholder="+33 6 12 34 56 78"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format international recommandé</p>
                    </div>

                    {{-- Bouton Test SMS --}}
                    <div class="p-5 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Tester les notifications SMS</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Envoyez un SMS test à votre numéro</p>
                            </div>
                            <button type="button" onclick="testSMS()" id="sms-test-btn" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-sms"></i>
                                <span>Envoyer un test</span>
                            </button>
                        </div>
                        <div id="sms-status" class="mt-4 hidden">
                            <div class="flex items-center gap-2 text-sm p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <i id="sms-status-icon" class="fas fa-spinner fa-spin text-teal-600"></i>
                                <span id="sms-status-text" class="text-gray-600 dark:text-gray-400">Envoi en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== FRÉQUENCE ===================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border-b border-indigo-100 dark:border-indigo-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Fréquence des notifications</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Choisissez à quelle fréquence recevoir les résumés</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="notification_frequency" value="instant" {{ ($settings->notification_frequency ?? 'instant') == 'instant' ? 'checked' : '' }} class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bolt text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Instantané</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Immédiatement</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="notification_frequency" value="hourly" {{ ($settings->notification_frequency ?? 'instant') == 'hourly' ? 'checked' : '' }} class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-hourglass-half text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Horaire</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Toutes les heures</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="notification_frequency" value="daily" {{ ($settings->notification_frequency ?? 'instant') == 'daily' ? 'checked' : '' }} class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-calendar-day text-orange-600 dark:text-orange-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Quotidien</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Une fois par jour</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            {{-- ===================== HEURES CALMES ===================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-gray-50 to-slate-100 dark:from-gray-700 dark:to-slate-800 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-slate-700 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-moon text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Heures calmes</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Suspendre les notifications la nuit</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-5">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Activer les heures calmes</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pas de notifications pendant cette période</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="quiet_hours_enabled" value="0">
                            <input type="checkbox" name="quiet_hours_enabled" value="1" {{ ($settings->quiet_hours_enabled ?? false) ? 'checked' : '' }} id="quietHoursToggle" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    <div id="quietHoursSettings" class="grid grid-cols-2 gap-4 {{ !($settings->quiet_hours_enabled ?? false) ? 'opacity-50 pointer-events-none' : '' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">De</label>
                            <input type="time" name="quiet_start" value="{{ $settings->quiet_start ?? '22:00' }}" 
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">À</label>
                            <input type="time" name="quiet_end" value="{{ $settings->quiet_end ?? '08:00' }}" 
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== BOUTONS ===================== --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-4 pb-20">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-save"></i>
                    Enregistrer les préférences
                </button>
                <a href="{{ route('prestataire.dashboard') }}" class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold text-center transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Toggle quiet hours
    const quietHoursToggle = document.getElementById('quietHoursToggle');
    const quietHoursSettings = document.getElementById('quietHoursSettings');
    
    quietHoursToggle?.addEventListener('change', function() {
        if (this.checked) {
            quietHoursSettings.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            quietHoursSettings.classList.add('opacity-50', 'pointer-events-none');
        }
    });

    // Test Push
    async function testPush() {
        const button = document.getElementById('push-test-btn');
        const statusDiv = document.getElementById('push-status');
        const statusIcon = document.getElementById('push-status-icon');
        const statusText = document.getElementById('push-status-text');
        
        statusDiv.classList.remove('hidden');
        button.disabled = true;
        statusIcon.className = 'fas fa-spinner fa-spin text-purple-600';
        statusText.className = 'text-gray-600 dark:text-gray-400';
        
        try {
            if (!('Notification' in window)) {
                throw new Error('Les notifications ne sont pas supportées par votre navigateur.');
            }
            
            if (!('serviceWorker' in navigator)) {
                throw new Error('Les Service Workers ne sont pas supportés.');
            }
            
            statusText.textContent = 'Demande de permission...';
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                statusText.textContent = 'Activation des notifications...';
                
                if (window.TaPrestationPush) {
                    await window.TaPrestationPush.subscribe();
                }
                
                const registration = await navigator.serviceWorker.ready;
                await registration.showNotification('TaPrestation', {
                    body: '🎉 Les notifications push sont activées ! Vous recevrez vos alertes ici.',
                    icon: '/icons/icon-192x192.png',
                    badge: '/icons/icon-72x72.png',
                    vibrate: [200, 100, 200],
                    tag: 'test-notification'
                });
                
                statusIcon.className = 'fas fa-check-circle text-green-600';
                statusText.textContent = '✅ Notifications push activées avec succès !';
                statusText.className = 'text-green-600 font-medium';
                
            } else if (permission === 'denied') {
                throw new Error('Notifications bloquées. Autorisez-les dans les paramètres de votre navigateur.');
            } else {
                throw new Error('Permission non accordée.');
            }
            
        } catch (error) {
            console.error('Push test error:', error);
            statusIcon.className = 'fas fa-times-circle text-red-600';
            statusText.textContent = '❌ ' + error.message;
            statusText.className = 'text-red-600';
        } finally {
            button.disabled = false;
        }
    }

    // Test Email
    async function testEmail() {
        const button = document.getElementById('email-test-btn');
        const statusDiv = document.getElementById('email-status');
        const statusIcon = document.getElementById('email-status-icon');
        const statusText = document.getElementById('email-status-text');
        
        statusDiv.classList.remove('hidden');
        button.disabled = true;
        statusIcon.className = 'fas fa-spinner fa-spin text-blue-600';
        statusText.textContent = 'Envoi en cours...';
        statusText.className = 'text-gray-600 dark:text-gray-400';
        
        try {
            const response = await fetch('{{ route("prestataire.notification-settings.test-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                statusIcon.className = 'fas fa-check-circle text-green-600';
                statusText.textContent = '✅ Email de test envoyé avec succès !';
                statusText.className = 'text-green-600 font-medium';
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi');
            }
            
        } catch (error) {
            console.error('Email test error:', error);
            statusIcon.className = 'fas fa-times-circle text-red-600';
            statusText.textContent = '❌ ' + (error.message || 'Erreur lors de l\'envoi');
            statusText.className = 'text-red-600';
        } finally {
            button.disabled = false;
        }
    }

    // Test SMS
    async function testSMS() {
        const button = document.getElementById('sms-test-btn');
        const statusDiv = document.getElementById('sms-status');
        const statusIcon = document.getElementById('sms-status-icon');
        const statusText = document.getElementById('sms-status-text');
        const phoneInput = document.querySelector('input[name="phone_number"]');
        
        if (!phoneInput.value) {
            alert('Veuillez entrer un numéro de téléphone');
            return;
        }
        
        statusDiv.classList.remove('hidden');
        button.disabled = true;
        statusIcon.className = 'fas fa-spinner fa-spin text-teal-600';
        statusText.textContent = 'Envoi en cours...';
        statusText.className = 'text-gray-600 dark:text-gray-400';
        
        try {
            const response = await fetch('{{ route("prestataire.notification-settings.test-sms") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone_number: phoneInput.value })
            });
            
            const data = await response.json();
            
            if (data.success) {
                statusIcon.className = 'fas fa-check-circle text-green-600';
                statusText.textContent = '✅ SMS de test envoyé avec succès !';
                statusText.className = 'text-green-600 font-medium';
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi');
            }
            
        } catch (error) {
            console.error('SMS test error:', error);
            statusIcon.className = 'fas fa-times-circle text-red-600';
            statusText.textContent = '❌ ' + (error.message || 'Erreur lors de l\'envoi');
            statusText.className = 'text-red-600';
        } finally {
            button.disabled = false;
        }
    }
</script>
@endpush
@endsection
