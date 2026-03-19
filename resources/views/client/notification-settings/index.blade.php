@extends('layouts.app')

@section('title', 'Paramètres de notifications')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-bell text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Paramètres de notifications</h1>
                    <p class="text-gray-600">Configurez vos préférences de notification</p>
                </div>
            </div>
        </div>
        
        <form action="{{ route('client.notification-settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            {{-- ===================== NOTIFICATIONS PUSH ===================== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-mobile-alt text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-900">Notifications Push</h2>
                            <p class="text-sm text-gray-600">Recevez des alertes instantanées sur votre appareil</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Recommandé</span>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100">
                    {{-- Activer Push --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-power-off text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Activer les notifications push</h3>
                                <p class="text-sm text-gray-500">Notifications instantanées sur votre téléphone/navigateur</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_enabled" value="1" {{ ($settings->push_enabled ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Réservations --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Réservations de services</h3>
                                <p class="text-sm text-gray-500">Confirmations, rappels et mises à jour</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_bookings" value="1" {{ ($settings->push_bookings ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Messages --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-comments text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Messages</h3>
                                <p class="text-sm text-gray-500">Nouveaux messages des prestataires</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_messages" value="1" {{ ($settings->push_messages ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Commandes Food --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-utensils text-orange-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Commandes Food</h3>
                                <p class="text-sm text-gray-500">Statut de vos commandes de repas</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_food_orders" value="1" {{ ($settings->push_food_orders ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Location équipements --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tools text-teal-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Location d'équipements</h3>
                                <p class="text-sm text-gray-500">Confirmations et rappels de location</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_equipment" value="1" {{ ($settings->push_equipment ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Push - Promotions --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tag text-pink-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Ventes flash & Promotions</h3>
                                <p class="text-sm text-gray-500">Offres spéciales et réductions</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_promotions" value="1" {{ ($settings->push_promotions ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    
                    {{-- Bouton Test Push --}}
                    <div class="p-5 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Tester les notifications push</h3>
                                <p class="text-sm text-gray-500">Vérifiez que tout fonctionne correctement</p>
                            </div>
                            <button type="button" onclick="testPush()" id="push-test-btn" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-purple-200">
                                <i class="fas fa-paper-plane"></i>
                                <span>Envoyer un test</span>
                            </button>
                        </div>
                        <div id="push-status" class="mt-4 hidden">
                            <div class="flex items-center gap-2 text-sm p-3 rounded-lg bg-white border border-gray-200">
                                <i id="push-status-icon" class="fas fa-spinner fa-spin text-purple-600"></i>
                                <span id="push-status-text" class="text-gray-600">Activation en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== NOTIFICATIONS EMAIL ===================== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Notifications par Email</h2>
                            <p class="text-sm text-gray-600">Recevez un résumé dans votre boîte mail</p>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100">
                    {{-- Email - Réservations --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Réservations</h3>
                                <p class="text-sm text-gray-500">Confirmations et rappels de réservation</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_bookings" value="1" {{ ($settings->email_bookings ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    {{-- Email - Paiements --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Paiements & Factures</h3>
                                <p class="text-sm text-gray-500">Confirmations de paiement et reçus</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_payments" value="1" {{ ($settings->email_payments ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    {{-- Email - Newsletter --}}
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-newspaper text-yellow-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Newsletter</h3>
                                <p class="text-sm text-gray-500">Actualités et conseils de la plateforme</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_newsletter" value="1" {{ ($settings->email_newsletter ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
            
            {{-- ===================== HEURES CALMES ===================== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-gray-50 to-slate-100 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-slate-700 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-moon text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Heures calmes</h2>
                            <p class="text-sm text-gray-600">Suspendre les notifications la nuit</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-5">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-semibold text-gray-900">Activer les heures calmes</h3>
                            <p class="text-sm text-gray-500">Pas de notifications pendant cette période</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="quiet_hours_enabled" value="1" {{ ($settings->quiet_hours_enabled ?? false) ? 'checked' : '' }} id="quietHoursToggle" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-600"></div>
                        </label>
                    </div>
                    
                    <div id="quietHoursSettings" class="grid grid-cols-2 gap-4 {{ !($settings->quiet_hours_enabled ?? false) ? 'opacity-50' : '' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">De</label>
                            <input type="time" name="quiet_start" value="{{ $settings->quiet_start ?? '22:00' }}" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">À</label>
                            <input type="time" name="quiet_end" value="{{ $settings->quiet_end ?? '08:00' }}" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ===================== BOUTONS ===================== --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-200">
                    <i class="fas fa-save"></i>
                    Enregistrer les préférences
                </button>
                <a href="{{ route('client.dashboard') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-center transition-colors">
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
        quietHoursSettings.classList.toggle('opacity-50', !this.checked);
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
        statusText.className = 'text-gray-600';
        
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
</script>
@endpush
@endsection
