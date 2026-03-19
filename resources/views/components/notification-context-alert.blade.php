{{--
    Alert contextuelle pour activer les notifications après une action importante
    Usage: @include('components.notification-context-alert', ['context' => 'booking'])
    
    Contextes disponibles:
    - booking: Après une réservation
    - message: Après avoir reçu un message
    - payment: Après un paiement
    - default: Message générique
--}}

@auth
@php
    $context = $context ?? 'default';
    
    $messages = [
        'booking' => [
            'title' => '📅 Réservation confirmée !',
            'text' => 'Activez les notifications pour être alerté si le client modifie ou annule.',
            'cta' => 'Ne rien manquer'
        ],
        'message' => [
            'title' => '💬 Nouveau message !',
            'text' => 'Activez les notifications pour répondre rapidement à vos clients.',
            'cta' => 'Activer les alertes'
        ],
        'payment' => [
            'title' => '💰 Paiement reçu !',
            'text' => 'Activez les notifications pour suivre vos paiements en temps réel.',
            'cta' => 'Rester informé'
        ],
        'default' => [
            'title' => '🔔 Restez connecté !',
            'text' => 'Activez les notifications pour ne rien manquer.',
            'cta' => 'Activer'
        ]
    ];
    
    $msg = $messages[$context] ?? $messages['default'];
@endphp

<div id="notification-context-alert-{{ $context }}" 
     class="hidden fixed bottom-4 right-4 max-w-sm bg-white rounded-xl shadow-2xl border border-gray-100 p-4 z-50 animate-slide-up">
    <div class="flex items-start gap-3">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full p-2 flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-gray-900 text-sm">{{ $msg['title'] }}</h4>
            <p class="text-xs text-gray-600 mt-1">{{ $msg['text'] }}</p>
            <div class="flex items-center gap-2 mt-3">
                <button onclick="requestNotificationPermission(); this.closest('[id^=notification-context-alert]').remove();" 
                        class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    {{ $msg['cta'] }}
                </button>
                <button onclick="this.closest('[id^=notification-context-alert]').remove();" 
                        class="text-gray-400 hover:text-gray-600 text-xs">
                    Plus tard
                </button>
            </div>
        </div>
        <button onclick="this.closest('[id^=notification-context-alert]').remove();" 
                class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
(function() {
    // Afficher l'alerte seulement si pas abonné
    async function checkAndShowContextAlert() {
        if (typeof OneSignal === 'undefined') {
            setTimeout(checkAndShowContextAlert, 1000);
            return;
        }
        
        try {
            const optedIn = await OneSignal.User.PushSubscription.optedIn;
            if (!optedIn) {
                const alert = document.getElementById('notification-context-alert-{{ $context }}');
                if (alert) {
                    alert.classList.remove('hidden');
                    // Auto-hide après 15 secondes
                    setTimeout(() => alert.remove(), 15000);
                }
            }
        } catch (e) {
            console.warn('[ContextAlert] Error:', e);
        }
    }
    
    // Afficher après un délai
    setTimeout(checkAndShowContextAlert, 2000);
})();
</script>

<style>
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-up {
    animation: slide-up 0.3s ease-out forwards;
}
</style>
@endauth
