
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1e293b">
    
    
    
    <title><?php echo $__env->yieldContent('title', 'TaPrestation - Trouvez des prestataires de services près de chez vous | Coiffeur, Ménage, Bricolage'); ?></title>
    
    
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'TaPrestation : trouvez un coiffeur, femme de ménage, bricoleur, traiteur, photographe, DJ près de chez vous. +10 000 pros en France. Réservation en ligne, paiement sécurisé, avis vérifiés ⭐'); ?>">
    
    
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'prestataire de services, trouver prestataire, coiffeur à domicile, femme de ménage Paris, bricoleur pas cher, traiteur mariage, photographe professionnel, DJ soirée, location matériel, artisan France, TaPrestation'); ?>">
    
    
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    
    
    <link rel="alternate" hreflang="fr" href="<?php echo e(url()->current()); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo e(url()->current()); ?>">
    
    
    <meta name="robots" content="<?php echo $__env->yieldContent('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'); ?>">
    <meta name="googlebot" content="<?php echo $__env->yieldContent('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'); ?>">
    <meta name="bingbot" content="<?php echo $__env->yieldContent('robots', 'index, follow'); ?>">
    
    
    <meta name="author" content="TaPrestation">
    <meta name="publisher" content="TaPrestation">
    <meta name="copyright" content="TaPrestation <?php echo e(date('Y')); ?>">
    
    
    <meta name="google-site-verification" content="<?php echo $__env->yieldContent('google_verification', ''); ?>">
    <meta name="msvalidate.01" content="<?php echo $__env->yieldContent('bing_verification', ''); ?>">
    <meta name="yandex-verification" content="">
    <meta name="p:domain_verify" content="">
    
    
    <meta property="og:type" content="<?php echo $__env->yieldContent('og_type', 'website'); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', 'TaPrestation - Trouvez des prestataires de services près de chez vous'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', 'Réservez un coiffeur, ménage, bricoleur, traiteur, photographe, DJ et +100 services. Avis vérifiés, paiement sécurisé.'); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', asset('images/og-image.png')); ?>">
    <meta property="og:image:secure_url" content="<?php echo $__env->yieldContent('og_image', asset('images/og-image.png')); ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="TaPrestation - Plateforme de services">
    <meta property="og:site_name" content="TaPrestation">
    <meta property="og:locale" content="fr_FR">
    <meta property="fb:app_id" content="">
    
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@taprestation">
    <meta name="twitter:creator" content="@taprestation">
    <meta name="twitter:url" content="<?php echo e(url()->current()); ?>">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('twitter_title', 'TaPrestation - +10 000 prestataires de services en France'); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('twitter_description', 'Trouvez un pro près de chez vous : coiffure, ménage, bricolage, événementiel, cours... Réservez en ligne !'); ?>">
    <meta name="twitter:image" content="<?php echo $__env->yieldContent('twitter_image', asset('images/og-image.png')); ?>">
    <meta name="twitter:image:alt" content="TaPrestation - Plateforme de services">
    
    
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="revisit-after" content="1 days">
    <meta name="language" content="French">
    <meta name="coverage" content="France">
    <meta name="target" content="all">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    
    
    <meta name="geo.region" content="FR">
    <meta name="geo.placename" content="France">
    <meta name="geo.position" content="46.603354;1.888334">
    <meta name="ICBM" content="46.603354, 1.888334">
    
    
    <meta name="DC.title" content="TaPrestation - Prestataires de services">
    <meta name="DC.creator" content="TaPrestation">
    <meta name="DC.subject" content="Services, Prestataires, Réservation en ligne">
    <meta name="DC.description" content="Plateforme de mise en relation clients-prestataires">
    <meta name="DC.publisher" content="TaPrestation">
    <meta name="DC.language" content="fr">
    <meta name="DC.coverage" content="France">
    
    
    <?php if (! empty(trim($__env->yieldContent('json_ld')))): ?>
        <?php echo $__env->yieldContent('json_ld'); ?>
    <?php else: ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TaPrestation",
        "url": "<?php echo e(config('app.url', 'https://taprestation.com')); ?>",
        "description": "La plateforme pour trouver tout type de prestation de services en France",
        "inLanguage": "fr-FR",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?php echo e(url('/services')); ?>?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <?php endif; ?>
    
    
    
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    
    <?php if (! empty(trim($__env->yieldContent('preload_hero')))): ?>
        <?php echo $__env->yieldContent('preload_hero'); ?>
    <?php endif; ?>
    
    

    
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    
    <link rel="manifest" href="<?php echo e(asset('manifest.webmanifest')); ?>">
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="any" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo e(asset('favicon.ico')); ?>" type="image/x-icon">
    <link rel="icon" href="<?php echo e(asset('icons/icon-48x48.png')); ?>" type="image/png" sizes="48x48">
    <link rel="icon" href="<?php echo e(asset('icons/icon-192x192.png')); ?>" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="<?php echo e(asset('icons/apple-touch-icon.png')); ?>" sizes="180x180">

    
    <style>
        /* Reset minimal + Layout critique */
        *,::after,::before{box-sizing:border-box;border:0 solid #e5e7eb}
        html{line-height:1.5;-webkit-text-size-adjust:100%;font-family:ui-sans-serif,system-ui,sans-serif}
        body{margin:0;line-height:inherit;background-color:#f8fafc;color:#0f172a;max-width:100vw}
        img,video{max-width:100%;height:auto;display:block}
        
        /* Navigation skeleton */
        .nav-skeleton{height:64px;background:linear-gradient(to right,#1e293b,#1e40af,#1e293b)}
        
        /* Hero section skeleton */
        .hero-skeleton{min-height:60vh;background:linear-gradient(135deg,#eff6ff 0%,#fff 50%,rgba(236,253,245,0.4) 100%)}
        
        /* Prevent CLS - Cookie banner placeholder */
        #cookie-consent-banner{min-height:0;transition:min-height 0.3s}
        #cookie-consent-banner.visible{min-height:120px}
        @media(min-width:640px){#cookie-consent-banner.visible{min-height:80px}}
        
        /* Font display swap */
        @font-face{font-family:Figtree;font-display:swap;src:local('Figtree')}
        
        /* Loading state */
        .loading-skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:shimmer 1.5s infinite}
        @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
        
        /* Performance: GPU-accelerated animations only */
        .group:hover .group-hover\:scale-110,.hover\:scale-105:hover,.hover\:scale-110:hover{will-change:transform}
        /* Contain style for performance (NO contain:layout/paint — they break position:sticky) */
        section{contain:style}
        .rounded-2xl,.rounded-3xl,.rounded-xl{contain:style}
    </style>

    
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
    <?php
        $mobileAppCssVersion = file_exists(public_path('css/mobile-app.css')) ? filemtime(public_path('css/mobile-app.css')) : time();
        $globalErgoCssVersion = file_exists(public_path('css/global-ergonomics.css')) ? filemtime(public_path('css/global-ergonomics.css')) : time();
        $designSystemCssVersion = file_exists(public_path('css/design-system.css')) ? filemtime(public_path('css/design-system.css')) : time();
    ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/mobile-app.css')); ?>?v=<?php echo e($mobileAppCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/global-ergonomics.css')); ?>?v=<?php echo e($globalErgoCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/design-system.css')); ?>?v=<?php echo e($designSystemCssVersion); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    
    <script>window.Laravel={csrfToken:'<?php echo e(csrf_token()); ?>'};</script>

    
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen flex flex-col">
    <div class="flex-1 min-h-screen flex flex-col">
        
        <?php if ($__env->exists('layouts.navigation')) echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <script>
            (function () {
                function setSiteNavHeightVar() {
                    var nav = document.getElementById('site-navbar');
                    if (!nav) return;
                    var h = nav.getBoundingClientRect().height;
                    if (!h || !isFinite(h)) return;
                    document.documentElement.style.setProperty('--site-nav-h', h + 'px');
                }

                function setShopHeaderHeightVar() {
                    var header = document.querySelector('.shop-header');
                    if (!header) return;
                    var h = header.getBoundingClientRect().height;
                    if (!h || !isFinite(h)) return;
                    document.documentElement.style.setProperty('--shop-header-h', h + 'px');
                }

                function updateVars() {
                    setSiteNavHeightVar();
                    setShopHeaderHeightVar();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', updateVars, { once: true });
                } else {
                    updateVars();
                }

                /* Re-mesurer après chargement complet (CSS différés: mobile-app.css, etc.) */
                window.addEventListener('load', function () {
                    requestAnimationFrame(function() {
                        updateVars();
                        setTimeout(updateVars, 300);
                    });
                }, { once: true });

                window.addEventListener('resize', function () {
                    window.requestAnimationFrame(updateVars);
                }, { passive: true });
            })();
        </script>

        
        <?php if(isset($header)): ?>
            <header class="bg-linear-to-r from-slate-900 via-blue-800 to-slate-900 shadow-xl border-b border-blue-700/30">
                <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                    <div class="text-blue-200 text-sm sm:text-base">
                        <?php echo e($header); ?>

                    </div>
                </div>
            </header>
        <?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalbb0843bd48625210e6e530f88101357e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb0843bd48625210e6e530f88101357e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-message','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash-message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb0843bd48625210e6e530f88101357e)): ?>
<?php $attributes = $__attributesOriginalbb0843bd48625210e6e530f88101357e; ?>
<?php unset($__attributesOriginalbb0843bd48625210e6e530f88101357e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb0843bd48625210e6e530f88101357e)): ?>
<?php $component = $__componentOriginalbb0843bd48625210e6e530f88101357e; ?>
<?php unset($__componentOriginalbb0843bd48625210e6e530f88101357e); ?>
<?php endif; ?>

        
        <main class="flex-1 pb-24 sm:pb-8 lg:pb-0 md:pb-0">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        
        <?php if ($__env->exists('components.mobile-bottom-nav')) echo $__env->make('components.mobile-bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        
        <?php echo $__env->make('components.cookie-consent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(auth()->guard()->check()): ?>
            <?php if(in_array((string) (auth()->user()->role ?? ''), ['client', 'prestataire'], true)): ?>
                <?php if ($__env->exists('components.guidance-assistant')) echo $__env->make('components.guidance-assistant', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>

    
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    
    
    <script defer>
        (function(){var n=!1,u='',t=0;document.addEventListener('click',function(e){var a=e.target.closest('a[href]');if(!a)return;var h=a.getAttribute('href');if(!h||h==='#'||h.startsWith('javascript:')||h.startsWith('mailto:')||h.startsWith('tel:')||a.closest('form'))return;var w=Date.now();if(n||(h===u&&w-t<2e3)){e.preventDefault();e.stopPropagation();e.stopImmediatePropagation();return!1}n=!0;u=h;t=w;setTimeout(function(){n=!1},3e3)},!0);window.addEventListener('beforeunload',function(){n=!1})})();
    </script>
    
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
    
    
    <script>
        window.loadJQuery = function(callback) {
            if (window.jQuery) { callback(); return; }
            var s = document.createElement('script');
            s.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
            s.onload = callback;
            document.head.appendChild(s);
        };
    </script>

    
    <?php
        $currentHost = request()->getHost();

        // Récupérer l'allowed_host depuis la config ou utiliser APP_URL
        $allowed = config('onesignal.allowed_host');
        if (empty($allowed)) {
            $allowed = config('app.url');
        }
        if (!empty($allowed) && !str_contains($allowed, '://')) {
            $allowed = 'https://' . $allowed;
        }

        $allowedHost = parse_url($allowed, PHP_URL_HOST) ?? $currentHost;

        // Comparer sans www pour supporter les deux variantes
        $normalizedCurrent = preg_replace('/^www\./', '', $currentHost);
        $normalizedAllowed = preg_replace('/^www\./', '', $allowedHost ?? '');

        // Activer OneSignal si configuré ET (host correspond OU on est en production avec un app_id valide)
        $enableOneSignalHere = config('onesignal.enabled')
            && !empty(config('onesignal.app_id'))
            && ($normalizedCurrent === $normalizedAllowed || str_contains($currentHost, 'taprestation'));
    ?>

    <?php if($enableOneSignalHere): ?>
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            try {
                await OneSignal.init({
                    appId: "<?php echo e(config('onesignal.app_id')); ?>",
                    safari_web_id: "<?php echo e(config('onesignal.safari_web_id', '')); ?>",
                    notifyButton: {
                        enable: false,
                        size: 'medium',
                        position: 'bottom-right',
                        prenotify: true,
                        showCredit: false,
                        text: {
                            'tip.state.unsubscribed': 'Activer les notifications',
                            'tip.state.subscribed': 'Notifications activées',
                            'tip.state.blocked': 'Notifications bloquées',
                            'message.prenotify': 'Cliquez pour activer les notifications',
                            'message.action.subscribed': 'Merci d\'avoir activé les notifications!',
                            'message.action.resubscribed': 'Notifications réactivées',
                            'message.action.unsubscribed': 'Vous ne recevrez plus de notifications',
                            'dialog.main.title': 'Gérer les notifications',
                            'dialog.main.button.subscribe': 'Activer',
                            'dialog.main.button.unsubscribe': 'Désactiver',
                        }
                    },
                    welcomeNotification: {
                        title: 'TaPrestation',
                        message: 'Notifications activées! Vous serez informé des nouvelles réservations.',
                    },
                    promptOptions: {
                        slidedown: {
                            prompts: [{
                                type: 'push',
                                autoPrompt: false,
                                text: {
                                    actionMessage: 'Activez les notifications pour être informé des réservations et messages.',
                                    acceptButton: 'Activer',
                                    cancelButton: 'Plus tard',
                                },
                                delay: {
                                    pageViews: 1,
                                    timeDelay: 5
                                }
                            }]
                        }
                    },
                    serviceWorkerParam: { scope: "/" },
                    serviceWorkerPath: "/OneSignalSDKWorker.js",
                    allowLocalhostAsSecureOrigin: true,
                });
                
                console.log('[OneSignal] Initialized successfully');
                
                // Vérifier le statut d'abonnement
                const permission = await OneSignal.Notifications.permission;
                console.log('[OneSignal] Permission status:', permission);
                
                // Lier l'utilisateur Laravel à OneSignal
                <?php if(auth()->guard()->check()): ?>
                try {
                    await OneSignal.login("<?php echo e(auth()->id()); ?>");
                    console.log('[OneSignal] User linked:', <?php echo e(auth()->id()); ?>);
                    
                    // Vérifier si abonné
                    const subscribed = await OneSignal.User.PushSubscription.optedIn;
                    console.log('[OneSignal] Subscribed:', subscribed);
                    
                    if (!subscribed && permission !== 'denied') {
                        // Demander la permission si pas encore abonné
                        console.log('[OneSignal] Requesting subscription...');
                    }
                } catch (e) {
                    console.warn('[OneSignal] User link error:', e.message);
                }
                <?php endif; ?>
                
            } catch (error) {
                console.error('[OneSignal] Init error:', error);
            }
        });
    </script>
    <?php endif; ?>

    
    <?php if(!config('onesignal.app_id')): ?>
    <script>
        (function() {
            // Register Service Worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('[App] Service Worker registered:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('[App] Service Worker registration failed:', error);
                    });
            }

            // Push Notifications Setup
            window.TaPrestationPush = {
                vapidPublicKey: '<?php echo e(config("services.webpush.vapid_public_key", "")); ?>',
                
                async requestPermission() {
                    if (!('Notification' in window)) {
                        console.log('[Push] Notifications not supported');
                        return false;
                    }
                    
                    if (!('serviceWorker' in navigator)) {
                        console.log('[Push] Service Worker not supported');
                        return false;
                    }
                    
                    const permission = await Notification.requestPermission();
                    console.log('[Push] Permission:', permission);
                    
                    if (permission === 'granted') {
                        await this.subscribe();
                        return true;
                    }
                    return false;
                },
                
                async subscribe() {
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        
                        let subscription = await registration.pushManager.getSubscription();
                        
                        if (!subscription && this.vapidPublicKey) {
                            subscription = await registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
                            });
                        }
                        
                        if (subscription) {
                            // Send subscription to server
                            const response = await fetch('/push/subscribe', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(subscription.toJSON())
                            });
                            
                            const data = await response.json();
                            console.log('[Push] Subscribed:', data);
                        }
                    } catch (error) {
                        console.log('[Push] Subscribe error:', error);
                    }
                },
                
                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                }
            };
            
            // Auto-request permission for logged-in users on PWA
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                // Running as PWA - request permission after a short delay
                setTimeout(function() {
                    if (Notification.permission === 'default') {
                        window.TaPrestationPush.requestPermission();
                    }
                }, 3000);
            }

            // ============================================================
            // PUSH NOTIFICATION POLLING SYSTEM
            // Récupère périodiquement les nouvelles notifications et les affiche
            // comme notifications natives via le service-worker
            // ============================================================
            <?php if(auth()->guard()->check()): ?>
            window.TaPrestationNotificationPolling = {
                interval: null,
                pollInterval: 2000, // 2 secondes
                
                start() {
                    // Ne pas démarrer si les notifications ne sont pas supportées ou refusées
                    if (!('Notification' in window) || Notification.permission !== 'granted') {
                        console.log('[NotifPolling] Notifications non supportées ou non autorisées');
                        return;
                    }
                    
                    console.log('[NotifPolling] Démarrage du polling...');
                    
                    // Premier check immédiat
                    this.checkForNotifications();
                    
                    // Polling toutes les 2 secondes
                    this.interval = setInterval(() => {
                        this.checkForNotifications();
                    }, this.pollInterval);
                },
                
                stop() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                },
                
                async checkForNotifications() {
                    try {
                        const response = await fetch('<?php echo e(route('notifications.unpushed')); ?>', {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) return;
                        
                        const data = await response.json();
                        
                        if (data.notifications && data.notifications.length > 0) {
                            console.log('[NotifPolling] Nouvelles notifications:', data.notifications.length);
                            
                            for (const notif of data.notifications) {
                                await this.showNotification(notif);
                            }
                        }
                    } catch (error) {
                        console.log('[NotifPolling] Erreur:', error);
                    }
                },
                
                async showNotification(notif) {
                    try {
                        // Utiliser le service worker pour afficher la notification
                        const registration = await navigator.serviceWorker.ready;
                        
                        await registration.showNotification(notif.title, {
                            body: notif.body,
                            icon: notif.icon || '/icons/icon-192x192.png',
                            badge: notif.badge || '/icons/icon-72x72.png',
                            tag: notif.tag || 'notification-' + notif.id,
                            vibrate: [200, 100, 200],
                            data: {
                                url: notif.url || '/notifications',
                                notificationId: notif.id
                            },
                            actions: [
                                { action: 'view', title: 'Voir' },
                                { action: 'dismiss', title: 'Ignorer' }
                            ]
                        });
                        
                        console.log('[NotifPolling] Notification affichée:', notif.title);
                    } catch (error) {
                        console.log('[NotifPolling] Erreur affichage notification:', error);
                    }
                }
            };
            
            // Démarrer le polling au chargement de la page
            document.addEventListener('DOMContentLoaded', function() {
                // Délai de 2 secondes pour laisser le temps à l'app de se charger
                setTimeout(function() {
                    window.TaPrestationNotificationPolling.start();
                }, 2000);
            });
            
            // Arrêter le polling quand l'onglet est caché, reprendre quand visible
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    window.TaPrestationNotificationPolling.stop();
                } else {
                    window.TaPrestationNotificationPolling.start();
                }
            });
            <?php endif; ?>
        })();
    </script>
    <?php endif; ?>

    
    

    <?php if (! $__env->hasRenderedOnce('5f24d2c9-e659-4547-a670-6250533d711e')): $__env->markAsRenderedOnce('5f24d2c9-e659-4547-a670-6250533d711e'); ?>
    <script>
        (function () {
            function isRunningAsApp() {
                try {
                    return !!window.Capacitor ||
                        (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
                        !!window.navigator.standalone;
                } catch (e) {
                    return false;
                }
            }

            function swipeNavEnabled() {
                return document.body.classList.contains('app-mode') || isRunningAsApp();
            }

            function isInteractiveTarget(target) {
                if (!target || !(target instanceof Element)) return false;
                return !!target.closest('input, textarea, select, button, a, [contenteditable="true"], [data-swipe-nav-ignore]');
            }

            function isWithinHorizontalScrollArea(target) {
                var el = (target instanceof Element) ? target : null;
                while (el && el !== document.body && el !== document.documentElement) {
                    try {
                        var style = window.getComputedStyle(el);
                        var overflowX = style && style.overflowX;
                        if ((overflowX === 'auto' || overflowX === 'scroll') && (el.scrollWidth > el.clientWidth + 5)) {
                            return true;
                        }
                    } catch (e) {
                        // ignore
                    }
                    el = el.parentElement;
                }
                return false;
            }

            var startX = 0;
            var startY = 0;
            var startTime = 0;
            var tracking = false;
            var lastSwipeNavAt = 0;
            var startEdge = null; // 'left' | 'right' | null

            function edgeForStartX(x) {
                var edge = 32; // px
                var w = window.innerWidth || 0;
                if (x <= edge) return 'left';
                if (w && x >= (w - edge)) return 'right';
                return null;
            }

            document.addEventListener('touchstart', function (e) {
                if (!swipeNavEnabled()) return;
                if (!e.touches || e.touches.length !== 1) return;
                if (isInteractiveTarget(e.target)) return;
                if (isWithinHorizontalScrollArea(e.target)) return;

                var t = e.touches[0];
                startX = t.clientX;
                startY = t.clientY;
                startTime = Date.now();
                startEdge = edgeForStartX(startX);
                tracking = true;
            }, { passive: true });

            document.addEventListener('touchcancel', function () {
                tracking = false;
                startEdge = null;
            }, { passive: true });

            document.addEventListener('touchend', function (e) {
                if (!tracking) return;
                tracking = false;
                if (!swipeNavEnabled()) return;
                if (!e.changedTouches || e.changedTouches.length !== 1) return;

                var t = e.changedTouches[0];
                var dx = t.clientX - startX;
                var dy = t.clientY - startY;
                var adx = Math.abs(dx);
                var ady = Math.abs(dy);
                var dt = Date.now() - startTime;

                // Anti double-trigger
                var now = Date.now();
                if (now - lastSwipeNavAt < 900) return;

                // Garde-fous: swipe horizontal franc, rapide, pas un scroll
                if (adx < 90) return;
                if (ady > adx * 0.5) return;
                if (dt > 700) return;

                // Sens demandé: swipe droite => retour (page précédente)
                if (dx > 0) {
                    // Exiger un swipe depuis le bord gauche (edge-swipe)
                    if (startEdge !== 'left') return;
                    lastSwipeNavAt = now;

                    var globalBackBtn = document.getElementById('app-global-back-btn');
                    var swipeFallback = globalBackBtn ? globalBackBtn.getAttribute('data-fallback') : '/';

                    if (window.TaPrestationNavigation && typeof window.TaPrestationNavigation.goBack === 'function') {
                        window.TaPrestationNavigation.goBack({ fallback: swipeFallback });
                        return;
                    }

                    if (window.history && window.history.length > 1) {
                        window.history.back();
                        return;
                    }

                    if (document.referrer) {
                        window.location.href = document.referrer;
                    }
                    return;
                }

                // swipe gauche => page suivante si elle existe dans l'historique (forward)
                // Exiger un swipe depuis le bord droit
                if (startEdge !== 'right') return;
                lastSwipeNavAt = now;
                if (window.history && typeof window.history.forward === 'function') {
                    window.history.forward();
                }
            }, { passive: true });
        })();
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH D:\wamp64\www\TaPrestation-master - Copie\resources\views/layouts/app.blade.php ENDPATH**/ ?>