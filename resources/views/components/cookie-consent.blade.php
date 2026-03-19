{{-- Bandeau de consentement aux cookies --}}
{{-- Utilise localStorage pour persister le choix --}}
{{-- OPTIMISÉ: Espace réservé pour éviter CLS --}}
<div id="cookie-consent-banner"
     class="fixed left-0 right-0 z-[9999] transform translate-y-full transition-transform duration-500 ease-out"
     style="display: none; min-height: 0; will-change: transform; bottom: 0; pointer-events: auto;"
     aria-hidden="true">
    <div class="border-t border-gray-600 pb-24 sm:pb-0" style="background-color: #111827;">
        <div class="max-w-7xl mx-auto px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4">
                {{-- Texte --}}
                <div class="flex items-start space-x-3 sm:space-x-4 flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center" style="background-color: rgba(245, 158, 11, 0.2);">
                            <i class="fas fa-cookie-bite text-lg sm:text-xl" style="color: #fbbf24;"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base sm:text-lg" style="color: #ffffff;">Nous utilisons des cookies 🍪</h4>
                        <p class="text-xs sm:text-sm mt-1 leading-relaxed max-w-2xl" style="color: #d1d5db;">
                            Ce site utilise des cookies pour améliorer votre expérience.
                            <a href="{{ route('cookies') }}" class="underline ml-1" style="color: #818cf8;">
                                En savoir plus
                            </a>
                        </p>
                    </div>
                </div>
                
                {{-- Boutons - Centrés sur mobile, taille tactile suffisante --}}
                <div class="flex items-center justify-center sm:justify-end gap-3 sm:gap-3 flex-wrap" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
                    {{-- Bouton personnaliser --}}
                    <button type="button"
                            onclick="openCookieSettings()"
                            data-cookie-action="open-settings"
                            class="px-4 py-3 sm:py-2.5 rounded-xl text-sm font-medium transition-colors border min-h-[44px]"
                            style="color: #d1d5db; border-color: #4b5563; background-color: transparent; touch-action: manipulation;">
                        <i class="fas fa-cog mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Personnaliser</span>
                    </button>

                    {{-- Bouton refuser --}}
                    <button type="button"
                            onclick="declineCookies()"
                            data-cookie-action="decline"
                            class="px-5 py-3 sm:py-2.5 rounded-xl text-sm font-medium transition-colors min-h-[44px]"
                            style="background-color: #374151; color: #ffffff; touch-action: manipulation;">
                        Refuser
                    </button>

                    {{-- Bouton accepter --}}
                    <button type="button"
                            onclick="acceptAllCookies()"
                            data-cookie-action="accept"
                            class="px-6 py-3 sm:py-2.5 rounded-xl text-sm font-semibold transition-all shadow-lg min-h-[44px]"
                            style="background: linear-gradient(to right, #4f46e5, #7c3aed); color: #ffffff; touch-action: manipulation;">
                        <i class="fas fa-check mr-1 sm:mr-2"></i>
                        Accepter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal des paramètres de cookies --}}
<div id="cookie-settings-modal" 
     class="fixed inset-0 z-[10000] hidden"
     onclick="if(event.target === this) closeCookieSettings()">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-cookie-bite text-amber-500 mr-3"></i>
                        Paramètres des cookies
                    </h3>
                    <button type="button" onclick="closeCookieSettings()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            {{-- Contenu --}}
            <div class="px-6 py-5 space-y-5">
                <p class="text-gray-600 text-sm">
                    Nous utilisons différents types de cookies pour optimiser votre expérience sur notre site. 
                    Vous pouvez choisir les catégories que vous souhaitez autoriser.
                </p>
                
                {{-- Cookies essentiels (toujours actifs) --}}
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-lock text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Cookies essentiels</h4>
                                <p class="text-xs text-gray-500">Requis pour le fonctionnement du site</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            Toujours actif
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-3">
                        Ces cookies sont nécessaires au bon fonctionnement du site (session, sécurité, préférences de base).
                    </p>
                </div>
                
                {{-- Cookies analytiques --}}
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-bar text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Cookies analytiques</h4>
                                <p class="text-xs text-gray-500">Pour comprendre l'utilisation du site</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="cookie-analytics" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <p class="text-sm text-gray-600 mt-3">
                        Ces cookies nous aident à comprendre comment vous utilisez le site pour améliorer nos services.
                    </p>
                </div>
                
                {{-- Cookies marketing --}}
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bullhorn text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Cookies marketing</h4>
                                <p class="text-xs text-gray-500">Pour des publicités personnalisées</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="cookie-marketing" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <p class="text-sm text-gray-600 mt-3">
                        Ces cookies permettent de vous proposer des publicités adaptées à vos intérêts.
                    </p>
                </div>
                
                {{-- Cookies fonctionnels --}}
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-sliders-h text-amber-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Cookies fonctionnels</h4>
                                <p class="text-xs text-gray-500">Pour des fonctionnalités améliorées</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="cookie-functional" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <p class="text-sm text-gray-600 mt-3">
                        Ces cookies permettent des fonctionnalités avancées comme les cartes, vidéos intégrées, etc.
                    </p>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="sticky bottom-0 bg-gray-50 border-t border-gray-100 px-6 py-4 rounded-b-2xl">
                <div class="flex items-center justify-between">
                    <button type="button" 
                            onclick="declineCookies()"
                            data-cookie-action="decline"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 text-sm font-medium transition">
                        Refuser tout
                    </button>
                    <div class="flex items-center space-x-3">
                        <button type="button" 
                                onclick="saveCustomCookies()"
                                data-cookie-action="save-custom"
                                class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-sm font-medium transition">
                            Enregistrer mes choix
                        </button>
                        <button type="button" 
                                onclick="acceptAllCookies()"
                                data-cookie-action="accept"
                                class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl text-sm font-semibold transition shadow-lg">
                            Tout accepter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    const COOKIE_CONSENT_KEY = 'taprestation_cookie_consent';
    const COOKIE_CONSENT_VERSION = '1.0';
    let inMemoryConsent = null;

    function readCookie(name) {
        const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const match = document.cookie.match(new RegExp('(?:^|; )' + escapedName + '=([^;]*)'));

        return match ? decodeURIComponent(match[1]) : null;
    }

    function writeCookie(name, value, days) {
        const expiresAt = new Date(Date.now() + (days * 24 * 60 * 60 * 1000));
        document.cookie = [
            name + '=' + encodeURIComponent(value),
            'expires=' + expiresAt.toUTCString(),
            'path=/',
            'SameSite=Lax'
        ].join('; ');
    }

    function readStoredConsent() {
        if (inMemoryConsent) {
            return inMemoryConsent;
        }

        try {
            const consent = localStorage.getItem(COOKIE_CONSENT_KEY);

            if (consent) {
                inMemoryConsent = consent;
                return consent;
            }
        } catch (e) {}

        const cookieConsent = readCookie(COOKIE_CONSENT_KEY);

        if (cookieConsent) {
            inMemoryConsent = cookieConsent;
            return cookieConsent;
        }

        return null;
    }

    function persistConsent(consent) {
        const serializedConsent = JSON.stringify(consent);
        inMemoryConsent = serializedConsent;

        try {
            localStorage.setItem(COOKIE_CONSENT_KEY, serializedConsent);
        } catch (e) {}

        try {
            writeCookie(COOKIE_CONSENT_KEY, serializedConsent, 180);
        } catch (e) {}

        return serializedConsent;
    }
    
    // Vérifier si le consentement existe déjà
    function hasConsent() {
        try {
            const consent = readStoredConsent();
            if (!consent) return false;
            
            const data = JSON.parse(consent);
            return data.version === COOKIE_CONSENT_VERSION && data.timestamp;
        } catch (e) {
            return false;
        }
    }
    
    // Afficher le bandeau et masquer la nav mobile pour éviter le chevauchement
    function showBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        const mobileNav = document.getElementById('mobile-bottom-nav');
        if (banner) {
            banner.style.display = 'block';
            banner.setAttribute('aria-hidden', 'false');
            if (mobileNav) mobileNav.style.display = 'none';
            // Animation d'entrée
            setTimeout(() => {
                banner.classList.remove('translate-y-full');
            }, 100);
        }
    }

    // Cacher le bandeau et restaurer la nav mobile
    function hideBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        const mobileNav = document.getElementById('mobile-bottom-nav');
        if (banner) {
            banner.classList.add('translate-y-full');
            banner.setAttribute('aria-hidden', 'true');
            setTimeout(() => {
                banner.style.display = 'none';
                if (mobileNav) mobileNav.style.display = '';
            }, 500);
        }
    }
    
    // Sauvegarder le consentement
    function saveConsent(preferences) {
        const consent = {
            version: COOKIE_CONSENT_VERSION,
            timestamp: new Date().toISOString(),
            preferences: preferences
        };
        persistConsent(consent);
        
        // Émettre un événement personnalisé
        try {
            window.dispatchEvent(new CustomEvent('cookieConsentChanged', { detail: consent }));
        } catch (e) {
            try {
                const fallbackEvent = document.createEvent('CustomEvent');
                fallbackEvent.initCustomEvent('cookieConsentChanged', false, false, consent);
                window.dispatchEvent(fallbackEvent);
            } catch (ignored) {}
        }
    }

    function openCookieSettings() {
        const modal = document.getElementById('cookie-settings-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Charger les préférences existantes
            loadExistingPreferences();
        }
    }

    function closeCookieSettings() {
        const modal = document.getElementById('cookie-settings-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
    
    // Accepter tous les cookies
    window.acceptAllCookies = function() {
        saveConsent({
            essential: true,
            analytics: true,
            marketing: true,
            functional: true
        });
        hideBanner();
        closeCookieSettings();
        
        // Charger les scripts tiers si besoin (Google Analytics, etc.)
        loadThirdPartyScripts(true, true, true);
    };
    
    // Refuser les cookies non essentiels
    window.declineCookies = function() {
        saveConsent({
            essential: true,
            analytics: false,
            marketing: false,
            functional: false
        });
        hideBanner();
        closeCookieSettings();
    };
    
    // Sauvegarder les choix personnalisés
    window.saveCustomCookies = function() {
        const analyticsCheckbox = document.getElementById('cookie-analytics');
        const marketingCheckbox = document.getElementById('cookie-marketing');
        const functionalCheckbox = document.getElementById('cookie-functional');
        const analytics = analyticsCheckbox ? analyticsCheckbox.checked : false;
        const marketing = marketingCheckbox ? marketingCheckbox.checked : false;
        const functional = functionalCheckbox ? functionalCheckbox.checked : false;
        
        saveConsent({
            essential: true,
            analytics: analytics,
            marketing: marketing,
            functional: functional
        });
        
        hideBanner();
        closeCookieSettings();
        
        // Charger les scripts tiers selon les préférences
        loadThirdPartyScripts(analytics, marketing, functional);
    };
    
    // Ouvrir les paramètres
    window.openCookieSettings = openCookieSettings;
    
    // Fermer les paramètres
    window.closeCookieSettings = closeCookieSettings;
    
    // Charger les préférences existantes dans le modal
    function loadExistingPreferences() {
        try {
            const consent = readStoredConsent();
            if (consent) {
                const data = JSON.parse(consent);
                const prefs = data.preferences || {};
                
                const analyticsCheckbox = document.getElementById('cookie-analytics');
                const marketingCheckbox = document.getElementById('cookie-marketing');
                const functionalCheckbox = document.getElementById('cookie-functional');
                
                if (analyticsCheckbox) analyticsCheckbox.checked = prefs.analytics !== false;
                if (marketingCheckbox) marketingCheckbox.checked = prefs.marketing === true;
                if (functionalCheckbox) functionalCheckbox.checked = prefs.functional !== false;
            }
        } catch (e) {}
    }
    
    // Charger les scripts tiers en fonction des préférences
    function loadThirdPartyScripts(analytics, marketing, functional) {
        // Google Analytics
        if (analytics && typeof gtag === 'undefined') {
            // Décommenter et adapter selon votre ID GA
            // loadScript('https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID');
        }
        
        // Scripts marketing (Facebook Pixel, etc.)
        if (marketing) {
            // loadScript('...');
        }
        
        // Scripts fonctionnels
        if (functional) {
            // loadScript('...');
        }
    }
    
    // Helper pour charger un script
    function loadScript(src) {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        document.head.appendChild(script);
    }
    
    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        if (!hasConsent()) {
            // Délai pour une meilleure UX
            setTimeout(showBanner, 1000);
        } else {
            // Appliquer les préférences existantes
            try {
                const consent = JSON.parse(readStoredConsent());
                const prefs = consent.preferences || {};
                loadThirdPartyScripts(prefs.analytics, prefs.marketing, prefs.functional);
            } catch (e) {}
        }
    });
    
    // Exposer une fonction pour réouvrir les paramètres depuis n'importe où
    window.reopenCookieSettings = function() {
        openCookieSettings();
    };
})();
</script>

<script>
(function () {
    var COOKIE_CONSENT_KEY = 'taprestation_cookie_consent';
    var COOKIE_CONSENT_VERSION = '1.0';
    var lastCookieActionAt = 0;

    function setCookie(name, value, days) {
        var expiresAt = new Date();
        expiresAt.setTime(expiresAt.getTime() + (days * 24 * 60 * 60 * 1000));

        document.cookie = [
            name + '=' + encodeURIComponent(value),
            'expires=' + expiresAt.toUTCString(),
            'path=/',
            'SameSite=Lax'
        ].join('; ');
    }

    function persistConsent(preferences) {
        var consent = {
            version: COOKIE_CONSENT_VERSION,
            timestamp: new Date().toISOString ? new Date().toISOString() : new Date().toUTCString(),
            preferences: preferences
        };
        var serialized = JSON.stringify(consent);

        try {
            localStorage.setItem(COOKIE_CONSENT_KEY, serialized);
        } catch (e) {}

        try {
            setCookie(COOKIE_CONSENT_KEY, serialized, 180);
        } catch (e) {}

        return consent;
    }

    function closeSettingsModal() {
        var modal = document.getElementById('cookie-settings-modal');

        if (modal) {
            if (modal.classList) {
                modal.classList.add('hidden');
            } else {
                modal.style.display = 'none';
            }
        }

        document.body.style.overflow = '';
    }

    function hideConsentUi() {
        var banner = document.getElementById('cookie-consent-banner');
        var mobileNav = document.getElementById('mobile-bottom-nav');

        if (banner) {
            banner.style.display = 'none';
            banner.setAttribute('aria-hidden', 'true');
            if (banner.classList) {
                banner.classList.add('translate-y-full');
            }
        }

        if (mobileNav) {
            mobileNav.style.display = '';
        }

        closeSettingsModal();
    }

    function dispatchConsentChanged(consent) {
        try {
            if (typeof CustomEvent === 'function') {
                window.dispatchEvent(new CustomEvent('cookieConsentChanged', { detail: consent }));
                return;
            }
        } catch (e) {}

        try {
            var fallbackEvent = document.createEvent('CustomEvent');
            fallbackEvent.initCustomEvent('cookieConsentChanged', false, false, consent);
            window.dispatchEvent(fallbackEvent);
        } catch (e) {}
    }

    window.openCookieSettings = function () {
        var modal = document.getElementById('cookie-settings-modal');
        if (!modal) {
            return;
        }

        if (modal.classList) {
            modal.classList.remove('hidden');
        } else {
            modal.style.display = 'block';
        }

        document.body.style.overflow = 'hidden';
    };

    window.closeCookieSettings = function () {
        closeSettingsModal();
    };

    window.acceptAllCookies = function () {
        var consent = persistConsent({
            essential: true,
            analytics: true,
            marketing: true,
            functional: true
        });

        hideConsentUi();
        dispatchConsentChanged(consent);
    };

    window.declineCookies = function () {
        var consent = persistConsent({
            essential: true,
            analytics: false,
            marketing: false,
            functional: false
        });

        hideConsentUi();
        dispatchConsentChanged(consent);
    };

    window.saveCustomCookies = function () {
        var analyticsCheckbox = document.getElementById('cookie-analytics');
        var marketingCheckbox = document.getElementById('cookie-marketing');
        var functionalCheckbox = document.getElementById('cookie-functional');

        var consent = persistConsent({
            essential: true,
            analytics: analyticsCheckbox ? !!analyticsCheckbox.checked : false,
            marketing: marketingCheckbox ? !!marketingCheckbox.checked : false,
            functional: functionalCheckbox ? !!functionalCheckbox.checked : false
        });

        hideConsentUi();
        dispatchConsentChanged(consent);
    };

    window.reopenCookieSettings = function () {
        window.openCookieSettings();
    };

    function runCookieAction(action) {
        var now = Date.now();
        if (now - lastCookieActionAt < 350) {
            return;
        }

        lastCookieActionAt = now;

        if (action === 'open-settings') {
            window.openCookieSettings();
            return;
        }

        if (action === 'accept') {
            window.acceptAllCookies();
            return;
        }

        if (action === 'decline') {
            window.declineCookies();
            return;
        }

        if (action === 'save-custom') {
            window.saveCustomCookies();
        }
    }

    function bindCookieActions(eventName, useCapture) {
        document.addEventListener(eventName, function (event) {
            var target = event.target;
            var actionTrigger = target && target.closest ? target.closest('[data-cookie-action]') : null;

            if (!actionTrigger) {
                return;
            }

            var action = actionTrigger.getAttribute('data-cookie-action');

            if (!action) {
                return;
            }

            if (event.cancelable) {
                event.preventDefault();
            }

            event.stopPropagation();
            runCookieAction(action);
        }, useCapture);
    }

    bindCookieActions('click', true);
    bindCookieActions('touchend', true);
    bindCookieActions('pointerup', true);
})();
</script>
