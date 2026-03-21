{{--
    Composant de rappel pour activer les notifications push
    Affiche une bannière/modal insistante tant que l'utilisateur n'est pas abonné
--}}

@auth
<div id="notification-reminder-banner" 
     class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4 shadow-lg z-50 transform translate-y-full transition-transform duration-500 ease-out"
     style="display: none;">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 rounded-full p-2 animate-pulse">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm sm:text-base">🔔 Ne manquez aucune réservation !</p>
                <p class="text-xs sm:text-sm text-white/80">Activez les notifications pour être alerté instantanément.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="requestNotificationPermission()" 
                    class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-indigo-50 transition-colors shadow-md">
                ✓ Activer maintenant
            </button>
            <button onclick="dismissNotificationReminder(false)" 
                    class="text-white/70 hover:text-white p-2 text-sm">
                Plus tard
            </button>
        </div>
    </div>
</div>

{{-- Modal de rappel plus insistant --}}
<div id="notification-reminder-modal" 
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="notification-modal-content">
        <div class="text-center">
            {{-- Animation de cloche --}}
            <div class="relative w-20 h-20 mx-auto mb-4">
                <div class="absolute inset-0 bg-indigo-100 rounded-full animate-ping opacity-30"></div>
                <div class="relative bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 mb-2">Restez informé en temps réel !</h3>
            
            <div class="space-y-3 text-left bg-gray-50 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-green-500 text-lg">✓</span>
                    <span class="text-sm text-gray-700">Nouvelles réservations instantanées</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-green-500 text-lg">✓</span>
                    <span class="text-sm text-gray-700">Messages clients en direct</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-green-500 text-lg">✓</span>
                    <span class="text-sm text-gray-700">Rappels de rendez-vous</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-green-500 text-lg">✓</span>
                    <span class="text-sm text-gray-700">Alertes de paiement</span>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mb-4">
                Sans notifications, vous pourriez manquer des opportunités importantes !
            </p>
            
            <div class="space-y-2">
                <button onclick="requestNotificationPermission()" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg">
                    🔔 Activer les notifications
                </button>
                <button onclick="dismissNotificationReminder(true)" 
                        class="w-full text-gray-500 py-2 text-sm hover:text-gray-700">
                    Me rappeler plus tard
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Petit bouton flottant de rappel (discret) --}}
<div id="notification-floating-btn" 
     class="fixed bottom-20 right-4 z-40"
     style="display: none;">
    <button onclick="showNotificationModal()" 
            class="relative bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all group">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        {{-- Badge de notification --}}
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center animate-pulse">!</span>
        {{-- Tooltip --}}
        <span class="absolute right-full mr-2 bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
            Activer les notifications
        </span>
    </button>
</div>

<script>
// Définir les fonctions GLOBALEMENT avant la IIFE
window.requestNotificationPermission = async function() {
    
    try {
        // Attendre que OneSignal soit disponible
        if (typeof OneSignal === 'undefined' || !OneSignal.Notifications) {
            
            // Essayer d'utiliser OneSignalDeferred
            if (typeof OneSignalDeferred !== 'undefined') {
                OneSignalDeferred.push(async function(OneSignal) {
                    await doRequestPermission(OneSignal);
                });
            } else {
                alert('Le service de notifications n\'est pas encore prêt. Veuillez réessayer dans quelques secondes.');
            }
            return;
        }
        
        await doRequestPermission(OneSignal);
        
    } catch (e) {
        console.error('[NotifReminder] Permission error:', e);
        alert('Impossible d\'activer les notifications. Vérifiez les paramètres de votre navigateur.');
    }
};

async function doRequestPermission(OneSignal) {
    
    // Fermer le modal immédiatement pour montrer le prompt navigateur
    const modal = document.getElementById('notification-reminder-modal');
    if (modal) modal.style.display = 'none';
    
    // Demander la permission via OneSignal
    await OneSignal.Notifications.requestPermission();
    
    // Attendre un peu pour que OneSignal mette à jour le statut
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Vérifier si accepté
    const optedIn = await OneSignal.User.PushSubscription.optedIn;
    
    if (optedIn) {
        // Succès !
        hideAllReminders();
        showSuccessToast();
        localStorage.removeItem('notif_reminder');
    } else {
        // Vérifier la permission du navigateur
        const permission = await OneSignal.Notifications.permission;
        
        if (permission === 'denied') {
            alert('Les notifications sont bloquées dans votre navigateur. Pour les activer:\n\n1. Cliquez sur le cadenas dans la barre d\'adresse\n2. Autorisez les notifications\n3. Rechargez la page');
        }
    }
}

function hideAllReminders() {
    document.getElementById('notification-reminder-banner')?.remove();
    document.getElementById('notification-reminder-modal')?.remove();
    document.getElementById('notification-floating-btn')?.remove();
}

function showSuccessToast() {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[200] flex items-center gap-2';
    toast.style.animation = 'slideIn 0.3s ease-out';
    toast.innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="font-semibold">Notifications activées !</span>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

window.showNotificationModal = function() {
    const modal = document.getElementById('notification-reminder-modal');
    const content = document.getElementById('notification-modal-content');
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 50);
    }
};

window.dismissNotificationReminder = function(fromModal) {
    const STORAGE_KEY = 'notif_reminder';
    let data = {};
    try {
        data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    } catch(e) {}
    
    data.dismissCount = (data.dismissCount || 0) + 1;
    
    if (data.dismissCount >= 5 && fromModal) {
        if (confirm('Voulez-vous ne plus recevoir ces rappels ?')) {
            data.permanentDismiss = true;
        }
    }
    
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    
    // Fermer le modal
    const modal = document.getElementById('notification-reminder-modal');
    const content = document.getElementById('notification-modal-content');
    if (modal) {
        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => modal.style.display = 'none', 300);
    }
    
    // Fermer la bannière
    const banner = document.getElementById('notification-reminder-banner');
    if (banner) {
        banner.classList.remove('translate-y-0');
        banner.classList.add('translate-y-full');
        setTimeout(() => banner.style.display = 'none', 500);
    }
    
    // Montrer le bouton flottant
    if (!data.permanentDismiss) {
        const btn = document.getElementById('notification-floating-btn');
        if (btn) btn.style.display = 'block';
    }
};

// IIFE pour la logique d'affichage automatique
(function() {
    const STORAGE_KEY = 'notif_reminder';
    const REMINDER_INTERVALS = [0, 1, 3, 5, 10, 20];
    const HOURS_BETWEEN_MODAL = 24;
    const HOURS_BETWEEN_BANNER = 4;
    
    function getReminderData() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }
    
    function saveReminderData(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {}
    }
    
    async function checkAndShowReminder() {
        if (typeof OneSignal === 'undefined') {
            setTimeout(checkAndShowReminder, 1000);
            return;
        }
        
        try {
            const optedIn = await OneSignal.User.PushSubscription.optedIn;
            const permission = await OneSignal.Notifications.permission;
            
            
            if (optedIn === true) {
                hideAllReminders();
                return;
            }
            
            if (permission === 'denied') {
                const btn = document.getElementById('notification-floating-btn');
                if (btn) btn.style.display = 'block';
                return;
            }
            
            const data = getReminderData();
            if (data.permanentDismiss) {
                const btn = document.getElementById('notification-floating-btn');
                if (btn) btn.style.display = 'block';
                return;
            }
            
            data.pageViews = (data.pageViews || 0) + 1;
            saveReminderData(data);
            
            const now = Date.now();
            const hoursSinceModal = (now - (data.lastModalShown || 0)) / (1000 * 60 * 60);
            const hoursSinceBanner = (now - (data.lastBannerShown || 0)) / (1000 * 60 * 60);
            
            if (REMINDER_INTERVALS.includes(data.pageViews) && hoursSinceModal >= HOURS_BETWEEN_MODAL) {
                setTimeout(() => window.showNotificationModal(), 2000);
                data.lastModalShown = now;
                saveReminderData(data);
            } else if (hoursSinceBanner >= HOURS_BETWEEN_BANNER) {
                const banner = document.getElementById('notification-reminder-banner');
                if (banner) {
                    setTimeout(() => {
                        banner.style.display = 'block';
                        setTimeout(() => {
                            banner.classList.remove('translate-y-full');
                            banner.classList.add('translate-y-0');
                        }, 100);
                    }, 3000);
                    data.lastBannerShown = now;
                    saveReminderData(data);
                }
            } else {
                const btn = document.getElementById('notification-floating-btn');
                if (btn) btn.style.display = 'block';
            }
            
        } catch (e) {
            console.warn('[NotifReminder] Error:', e);
        }
    }
    
    // Écouter les changements
    if (typeof OneSignalDeferred !== 'undefined') {
        OneSignalDeferred.push(async function(OneSignal) {
            OneSignal.User.PushSubscription.addEventListener('change', function(event) {
                if (event.current.optedIn) {
                    hideAllReminders();
                    showSuccessToast();
                }
            });
        });
    }
    
    // Lancer la vérification
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(checkAndShowReminder, 2000));
    } else {
        setTimeout(checkAndShowReminder, 2000);
    }
})();
</script>
@endauth
