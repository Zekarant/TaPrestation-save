{{-- resources/views/layouts/navigation.blade.php --}}
@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    // If the user has an active prestataire relation but their role wasn't updated,
    // temporarily mark them as 'prestataire' for view rendering so nav/buttons match.
    try {
        if ($user && isset($user->prestataire) && $user->prestataire && ($user->prestataire->is_active ?? false)) {
            if (!method_exists($user, 'hasRole') || !$user->hasRole('prestataire')) {
                $user->role = 'prestataire';
            }
        }
    } catch (\Throwable $e) {
        // If anything fails while accessing relation, ignore and keep original role
    }
    
    // Détecter le MODE actuel basé sur la route (pas le rôle principal de l'utilisateur)
    // Un utilisateur peut avoir les deux rôles mais naviguer sur un mode spécifique
    $isOnPrestataireRoutes = request()->routeIs('prestataire.*');
    $isOnClientRoutes = request()->routeIs('client.*');
    
    // Mode actuel basé sur les routes
    $currentMode = $isOnPrestataireRoutes ? 'prestataire' : ($isOnClientRoutes ? 'client' : null);
    
    // Variables de compatibilité pour le reste de la navigation
    $isPrestataireInClientMode = $isOnClientRoutes && $user && method_exists($user, 'hasRole') && $user->hasRole('prestataire');
    $isClientInPrestataireMode = $isOnPrestataireRoutes && $user && method_exists($user, 'hasRole') && $user->hasRole('client');

    // Routes optionnelles : on teste leur existence pour éviter les 500 (RouteNotFoundException)
    $homeRoute              = Route::has('home')                          ? route('home')                          : url('/');
    $servicesRoute          = Route::has('services.index')                ? route('services.index')                : null;
    $equipmentRoute         = Route::has('equipment.index')               ? route('equipment.index')               : null;
    $urgentSalesRoute       = Route::has('urgent-sales.index')            ? route('urgent-sales.index')            : null;
    $videosFeedRoute        = Route::has('videos.feed')                   ? route('videos.feed')                   : null;
    $foodExploreRoute       = Route::has('food.explore')                  ? route('food.explore')                  : null;
    
    // État actif fiable même si le nom de route est absent/altéré
    $isServicesActive = request()->routeIs('services.*') || request()->is('services') || request()->is('services/*');
    $isEquipmentActive = request()->routeIs('equipment.*') || request()->is('equipment') || request()->is('equipment/*');
    $isUrgentSalesActive = request()->routeIs('urgent-sales.*') || request()->is('urgent-sales') || request()->is('urgent-sales/*');

    $messagingRoute         = Route::has('messaging.index')               ? route('messaging.index')               : null;
    $notificationsIndex     = Route::has('notifications.index')           ? route('notifications.index')           : null;
    $notificationsMarkAll   = Route::has('notifications.mark-all-read')   ? route('notifications.mark-all-read')   : null;

    $clientDashboardRoute   = Route::has('client.dashboard')              ? route('client.dashboard')              : null;
    $prestataireDashboardRoute = Route::has('prestataire.dashboard')      ? route('prestataire.dashboard')         : null;
    $defaultDashboardRoute  = Route::has('dashboard')                     ? route('dashboard')                     : null;

    $prestataireAgendaRoute = Route::has('prestataire.agenda.index')      ? route('prestataire.agenda.index')      : null;
    $prestataireServicesIndex = Route::has('prestataire.services.index')  ? route('prestataire.services.index')    : null;
    $prestataireServicesCreate = Route::has('prestataire.services.create')? route('prestataire.services.create')   : null;
    $prestataireEquipmentIndex = Route::has('prestataire.equipment.index')? route('prestataire.equipment.index')   : null;
    $prestataireEquipmentCreate = Route::has('prestataire.equipment.create')? route('prestataire.equipment.create'): null;
    $prestataireUrgentSalesIndex = Route::has('prestataire.urgent-sales.index') ? route('prestataire.urgent-sales.index') : null;
    $prestataireUrgentSalesCreate = Route::has('prestataire.urgent-sales.create') ? route('prestataire.urgent-sales.create') : null;
    $prestataireVideosManage = Route::has('prestataire.videos.manage')    ? route('prestataire.videos.manage')     : null;

    $clientBookingsRoute    = Route::has('client.bookings.index')         ? route('client.bookings.index')         : null;
    $clientBookingsHistoryRoute = Route::has('client.bookings.history')   ? route('client.bookings.history')       : null;
    $clientReservationsRoute = Route::has('my-reservations.index')        ? route('my-reservations.index')         : null;
    $clientEquipmentRentals = Route::has('client.equipment-rental-requests.index') ? route('client.equipment-rental-requests.index') : null;
    $clientFollowsRoute     = Route::has('client.prestataire-follows.index') ? route('client.prestataire-follows.index') : null;
    $clientCartRoute         = Route::has('client.cart.index')            ? route('client.cart.index')             : null;
    
    // Nouvelles fonctionnalités client
    $clientPaymentsRoute        = Route::has('client.payments.index')         ? route('client.payments.index')         : null;
    $clientSubscriptionsRoute   = Route::has('client.subscriptions.index')    ? route('client.subscriptions.index')    : null;
    $clientAuctionsRoute        = Route::has('client.auctions.index')         ? route('client.auctions.index')         : null;
    $clientDeliveryRoute        = Route::has('client.delivery.index')         ? route('client.delivery.index')         : null;
    $clientAddressBookRoute     = Route::has('client.address-book.index')     ? route('client.address-book.index')     : null;
    $clientNotificationSettingsRoute = Route::has('client.notification-settings.index') ? route('client.notification-settings.index') : null;
    
    // Nouvelles fonctionnalités prestataire
    $prestataireBookingsRoute   = Route::has('prestataire.bookings.index')   ? route('prestataire.bookings.index')    : null;
    $prestataireBookingsHistoryRoute = Route::has('prestataire.bookings.history') ? route('prestataire.bookings.history') : null;
    $prestataireReservationsRoute = Route::has('prestataire.reservations.index') ? route('prestataire.reservations.index') : null;
    $prestatairePaymentsRoute   = Route::has('prestataire.payments.index')    ? route('prestataire.payments.index')    : null;
    $prestataireSubscriptionsRoute = Route::has('prestataire.subscriptions.index') ? route('prestataire.subscriptions.index') : null;
    $prestataireAuctionsRoute   = Route::has('prestataire.auctions.index')    ? route('prestataire.auctions.index')    : null;
    $prestataireDeliveryRoute   = Route::has('prestataire.delivery.index')    ? route('prestataire.delivery.index')    : null;
    $prestataireInventoryRoute  = Route::has('prestataire.inventory.index')   ? route('prestataire.inventory.index')   : null;
    $prestataireAddressBookRoute = Route::has('prestataire.address-book.index') ? route('prestataire.address-book.index') : null;
    $prestataireNotificationSettingsRoute = Route::has('prestataire.notification-settings.index') ? route('prestataire.notification-settings.index') : null;

    $profileEditRoute       = Route::has('profile.edit')                  ? route('profile.edit')                  : null;
    
    // Paramètres : route dynamique selon le MODE actuel (basé sur les routes, pas le rôle principal)
    $clientProfileEditRoute = Route::has('client.profile.edit') ? route('client.profile.edit') : null;
    $prestataireProfileEditRoute = Route::has('prestataire.profile.edit') ? route('prestataire.profile.edit') : null;
    
    // PRIORITÉ 1: Mode actuel basé sur la route (client.* ou prestataire.*)
    if ($isOnPrestataireRoutes && $prestataireProfileEditRoute) {
        $profileSettingsRoute = $prestataireProfileEditRoute;
    } elseif ($isOnClientRoutes && $clientProfileEditRoute) {
        $profileSettingsRoute = $clientProfileEditRoute;
    }
    elseif ($user && isset($user->prestataire) && $user->prestataire && ($user->prestataire->is_active ?? false) && $prestataireProfileEditRoute) {
        $profileSettingsRoute = $prestataireProfileEditRoute;
    } elseif ($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && $prestataireProfileEditRoute) {
        $profileSettingsRoute = $prestataireProfileEditRoute;
    } elseif ($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $clientProfileEditRoute) {
        $profileSettingsRoute = $clientProfileEditRoute;
    } else {
        $profileSettingsRoute = Route::has('profile.settings') ? route('profile.settings') : null;
    }

    $loginRoute             = Route::has('login')                         ? route('login')                         : null;
    $registerRoute          = Route::has('register')                      ? route('register')                      : null;
    $logoutRoute            = Route::has('logout')                        ? route('logout')                        : null;

    // Compteurs sécurisés
    $unreadMessagesCount = 0;
    $unreadNotificationsCount = 0;
    $recentNotifications = collect();

    if ($user) {
        try {
            if (method_exists($user, 'receivedMessages')) {
                $unreadMessagesCount = $user->receivedMessages()->whereNull('read_at')->count();
            }
        } catch (\Throwable $e) {
            $unreadMessagesCount = 0;
        }

        try {
            if (method_exists($user, 'notifications')) {
                $unreadNotificationsCount = $user->notifications()->whereNull('read_at')->count();
                $recentNotifications = $user->notifications()
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
            }
        } catch (\Throwable $e) {
            $unreadNotificationsCount = 0;
            $recentNotifications = collect();
        }
    }
@endphp

@once
    <style>
        /* ─── POLICE ─────────────────────────────────────────── */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        /* ─── NAVBAR PRINCIPALE ──────────────────────────────── */
        #site-navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            overflow-x: clip !important;
        }

        /* Fond givré beige — cohérent avec login/register */
        #site-navbar > div > div.flex {
            background: rgba(237, 229, 216, 0.88) !important;
            backdrop-filter: blur(18px) !important;
            -webkit-backdrop-filter: blur(18px) !important;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
            box-shadow: 0 1px 24px rgba(15, 23, 42, 0.07) !important;
        }

        /* Remplacement de la div bg-white border-b */
        #site-navbar .bg-white.border-b {
            background: rgba(237, 229, 216, 0.88) !important;
            backdrop-filter: blur(18px) !important;
            -webkit-backdrop-filter: blur(18px) !important;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
            box-shadow: 0 1px 24px rgba(15, 23, 42, 0.07) !important;
        }

        body {
            padding-top: 64px !important;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }
        @media (min-width: 640px) {
            body { padding-top: 80px !important; }
        }
        @supports (padding-top: env(safe-area-inset-top)) {
            body.app-mode #site-navbar {
                top: 0 !important;
            }

            body.app-mode #site-navbar > div {
                padding-top: max(calc(env(safe-area-inset-top) - 14px), 0px) !important;
            }

            body.app-mode {
                padding-top: calc(52px + max(calc(env(safe-area-inset-top) - 14px), 0px)) !important;
            }
        }

        /* ─── LOGO ───────────────────────────────────────────── */
        #site-navbar .site-navbar-brand-mark {
            background: linear-gradient(145deg, #06162c, #0b2447) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 20px rgba(7, 23, 47, 0.24) !important;
            overflow: hidden !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
        }
        #site-navbar .site-navbar-brand-mark img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }
        /* Texte TaPrestation */
        #site-navbar #site-navbar-brand-text {
            font-weight: 800 !important;
            font-size: 15px !important;
            letter-spacing: -0.02em !important;
            color: #0f172a !important;
        }

        /* ─── LIENS DESKTOP ──────────────────────────────────── */
        #site-navbar #site-navbar-desktop-links .x-nav-link,
        #site-navbar #site-navbar-desktop-links a,
        #site-navbar #site-navbar-desktop-links button {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            border-radius: 9px !important;
            border-bottom: none !important;
            transition: color .15s, background .15s !important;
        }
        #site-navbar #site-navbar-desktop-links a:hover,
        #site-navbar #site-navbar-desktop-links button:hover {
            color: #0f172a !important;
            background: rgba(255, 255, 255, 0.55) !important;
        }
        /* Lien actif */
        #site-navbar #site-navbar-desktop-links a[class*="border-blue"],
        #site-navbar #site-navbar-desktop-links a[class*="text-blue"] {
            color: #0f3a86 !important;
            background: rgba(255, 255, 255, 0.65) !important;
            box-shadow: inset 0 0 0 1px rgba(15, 58, 134, 0.14) !important;
            border-bottom: none !important;
        }

        /* ─── BOUTON RETOUR ──────────────────────────────────── */
        #app-global-back-btn {
            background: rgba(255, 255, 255, 0.55) !important;
            border: none !important;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.10) !important;
            border-radius: 10px !important;
            color: #0f3a86 !important;
        }
        #app-global-back-btn:hover {
            background: rgba(255, 255, 255, 0.80) !important;
        }

        #site-navbar-main-row {
            min-height: 58px !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }

        #site-navbar-main-row > .flex:first-child {
            min-width: 0 !important;
            flex: 1 1 auto !important;
            align-items: center !important;
        }

        #site-navbar-mobile-actions {
            flex: 0 0 auto !important;
            min-width: fit-content !important;
            align-items: center !important;
        }

        /* ─── ICÔNES (messagerie, notifs, agenda) ────────────── */
        #site-navbar .p-2.text-gray-500 {
            border-radius: 10px !important;
            transition: color .15s, background .15s !important;
        }
        #site-navbar .p-2.text-gray-500:hover {
            background: rgba(255, 255, 255, 0.55) !important;
            color: #0f172a !important;
        }

        /* ─── BOUTON USER (avatar + prénom) ─────────────────── */
        #site-navbar button.flex.items-center.px-3.py-2.text-sm {
            background: rgba(255, 255, 255, 0.55) !important;
            border: none !important;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.09) !important;
            border-radius: 999px !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            transition: background .18s, box-shadow .18s !important;
        }
        #site-navbar button.flex.items-center.px-3.py-2.text-sm:hover {
            background: rgba(255, 255, 255, 0.80) !important;
            box-shadow: inset 0 0 0 1px rgba(15, 58, 134, 0.18) !important;
        }

        /* ─── DROPDOWN UTILISATEUR ───────────────────────────── */
        /* Fond du dropdown */
        #site-navbar .absolute.right-0.mt-2 > div,
        .x-dropdown-content,
        [x-transition] > div[class*="rounded"] {
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-radius: 18px !important;
            border: none !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.13), inset 0 0 0 1px rgba(15, 23, 42, 0.07) !important;
        }

        /* Items du dropdown */
        #site-navbar .absolute.right-0.mt-2 a,
        #site-navbar .absolute.right-0.mt-2 button {
            font-size: 12px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            transition: background .12s !important;
        }
        #site-navbar .absolute.right-0.mt-2 a:hover {
            background: rgba(15, 23, 42, 0.04) !important;
        }

        /* En-tête dropdown */
        #site-navbar .px-4.py-3.border-b {
            background: transparent !important;
        }

        /* Labels de section dans le dropdown */
        #site-navbar .px-4.py-3.text-xs.font-semibold.text-gray-600.uppercase {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            font-size: 9px !important;
            letter-spacing: 0.18em !important;
            color: rgba(15, 23, 42, 0.35) !important;
            background: transparent !important;
        }

        /* Boutons mode client / prestataire */
        #site-navbar a.flex.items-center.px-4.py-3.text-sm.font-semibold.text-green-700 {
            border-radius: 10px !important;
            font-size: 12px !important;
        }
        #site-navbar a.flex.items-center.px-4.py-3.text-sm.font-semibold.text-purple-700 {
            border-radius: 10px !important;
            font-size: 12px !important;
        }

        /* ─── BOUTONS AUTH (Connexion / Inscription) ─────────── */
        #site-navbar #site-navbar-desktop-actions a[href*="login"],
        #site-navbar #site-navbar-desktop-actions a[href*="connexion"] {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            border-radius: 9px !important;
            padding: 7px 12px !important;
            transition: color .15s, background .15s !important;
        }
        #site-navbar #site-navbar-desktop-actions a[href*="login"]:hover,
        #site-navbar #site-navbar-desktop-actions a[href*="connexion"]:hover {
            color: #0f172a !important;
            background: rgba(255, 255, 255, 0.55) !important;
        }
        #site-navbar #site-navbar-desktop-actions a.bg-blue-600,
        #site-navbar #site-navbar-desktop-actions a[href*="register"],
        #site-navbar #site-navbar-desktop-actions a[href*="inscription"] {
            background: #0f3a86 !important;
            color: #fff !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            border-radius: 9px !important;
            padding: 7px 14px !important;
            box-shadow: 0 4px 12px rgba(15, 58, 134, 0.28) !important;
            transition: background .15s, box-shadow .15s !important;
        }
        #site-navbar #site-navbar-desktop-actions a.bg-blue-600:hover,
        #site-navbar #site-navbar-desktop-actions a[href*="register"]:hover {
            background: #0c2f6d !important;
            box-shadow: 0 6px 16px rgba(15, 58, 134, 0.36) !important;
        }

        /* ─── BURGER ─────────────────────────────────────────── */
        #site-navbar button.inline-flex.items-center.justify-center.p-2 {
            background: rgba(255, 255, 255, 0.55) !important;
            border: none !important;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.09) !important;
            border-radius: 10px !important;
            color: #0f172a !important;
            transition: background .15s !important;
        }
        #site-navbar button.inline-flex.items-center.justify-center.p-2:hover {
            background: rgba(255, 255, 255, 0.80) !important;
        }

        /* ─── OVERLAY MOBILE ─────────────────────────────────── */
        #site-navbar + div[class*="fixed inset-0"],
        div[x-show="open"][class*="fixed inset-0 z-"] {
            backdrop-filter: blur(3px) !important;
            -webkit-backdrop-filter: blur(3px) !important;
            background: rgba(15, 23, 42, 0.30) !important;
        }

        /* ─── DRAWER MOBILE ──────────────────────────────────── */
        /* Fond du panel latéral */
        .fixed.inset-y-0.left-0.z-\[80\],
        div[class*="fixed inset-y-0 left-0"][class*="bg-white"] {
            background: rgba(237, 229, 216, 0.97) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.55) !important;
            box-shadow: 4px 0 40px rgba(15, 23, 42, 0.11) !important;
        }

        /* Block utilisateur mobile */
        .fixed.inset-y-0.left-0 .flex.items-center.space-x-3 {
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.45) !important;
            border-radius: 14px !important;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08) !important;
        }

        /* Liens dans le drawer */
        .mobile-menu a {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 11px !important;
            transition: background .14s !important;
        }
        .mobile-menu a:hover {
            background: rgba(255, 255, 255, 0.60) !important;
        }

        /* Bouton dashboard mobile (gradient bleu) */
        .mobile-menu a.bg-gradient-to-r.from-blue-600 {
            background: linear-gradient(90deg, #0f3a86, #2d67c8) !important;
            border-radius: 12px !important;
            box-shadow: 0 6px 16px rgba(15, 58, 134, 0.26) !important;
        }

        /* Grille catégories mobile */
        .mobile-menu .grid.grid-cols-4 a {
            background: rgba(255, 255, 255, 0.45) !important;
            border-radius: 10px !important;
        }
        .mobile-menu .grid.grid-cols-4 a:hover {
            background: rgba(255, 255, 255, 0.70) !important;
        }
        .mobile-menu .grid.grid-cols-4 span {
            font-size: 9px !important;
            font-weight: 700 !important;
        }

        /* Boutons auth mobile (Connexion / Inscription) */
        .mobile-menu .space-y-3 a {
            border-radius: 11px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
        }
        .mobile-menu .space-y-3 a.bg-blue-600 {
            background: #0f3a86 !important;
            box-shadow: 0 4px 12px rgba(15, 58, 134, 0.26) !important;
        }

        /* Bouton déconnexion */
        .mobile-menu button[type="submit"],
        #site-navbar .absolute.right-0 button[type="submit"] {
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        /* Séparateurs */
        .mobile-menu .border-t,
        #site-navbar .border-t {
            border-color: rgba(15, 23, 42, 0.08) !important;
        }

        /* Dropdown "Plus" desktop */
        #site-navbar .absolute.right-0.mt-2.w-64,
        #site-navbar [class*="absolute"][class*="w-64"][class*="rounded-lg"] {
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-radius: 14px !important;
            border: none !important;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12), inset 0 0 0 1px rgba(15, 23, 42, 0.07) !important;
        }

        /* Dropdown notifications mobile */
        #site-navbar .absolute.right-0.mt-2.w-80 {
            background: rgba(255, 255, 255, 0.97) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-radius: 16px !important;
            border: none !important;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.13), inset 0 0 0 1px rgba(15, 23, 42, 0.07) !important;
        }

        /* Badge compteur (rouge) */
        #site-navbar .absolute.-top-1.-right-1 {
            box-shadow: 0 0 0 2px rgba(237, 229, 216, 0.9) !important;
        }

        @media (max-width: 640px) {
            #site-navbar > div {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            #site-navbar-main-row {
                min-height: 52px !important;
                height: 52px !important;
            }

            #app-global-back-btn {
                width: 2.5rem !important;
                height: 2.5rem !important;
                min-width: 2.5rem !important;
                min-height: 2.5rem !important;
                margin-right: 0.5rem !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                border-radius: 0.875rem !important;
                align-self: center !important;
            }

            #site-navbar-mobile-actions {
                margin-right: 0 !important;
                gap: 0.375rem !important;
            }

            #site-navbar-mobile-actions > a,
            #site-navbar-mobile-actions > div > button,
            #site-navbar-mobile-actions > button {
                width: 2.5rem !important;
                height: 2.5rem !important;
                min-width: 2.5rem !important;
                min-height: 2.5rem !important;
                padding: 0 !important;
                flex-shrink: 0 !important;
                border-radius: 0.875rem !important;
            }

            #site-navbar-mobile-actions svg,
            #app-global-back-btn svg {
                width: 1.2rem !important;
                height: 1.2rem !important;
            }

            #site-navbar .site-navbar-brand-mark {
                width: 2.5rem !important;
                height: 2.5rem !important;
                flex-shrink: 0 !important;
            }

            .mobile-menu a {
                padding: 0.6rem 0.875rem !important;
            }
            .mobile-menu i {
                margin-right: 0.6rem !important;
            }
            body.app-mode .pwa-inline-back {
                display: none !important;
            }
        }
    </style>
@endonce

<nav id="site-navbar" x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm fixed top-0 left-0 right-0 z-[9999]">
    {{-- Mobile overlay --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[70] bg-black/40 lg:hidden"
        @click="open = false"
    ></div>

    {{-- Barre principale --}}
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div id="site-navbar-main-row" class="flex justify-between h-14 sm:h-16">
            <div class="flex items-center min-w-0 flex-1">
                {{-- Bouton retour global (App mobile/PWA + Web mobile) --}}
                <button
                    id="app-global-back-btn"
                    type="button"
                    data-fallback="{{ $homeRoute }}"
                    class="hidden mr-2 w-10 h-10 rounded-xl bg-white shadow-sm border border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors items-center justify-center shrink-0"
                    aria-label="Retour"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Logo --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ $homeRoute }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity duration-200">
                        <div class="site-navbar-brand-mark w-10 h-10 flex items-center justify-center">
                            <img src="{{ asset('icons/icon-96x96.png') }}" alt="Logo TaPrestation" width="40" height="40">
                        </div>
                        <span id="site-navbar-brand-text" class="text-xl font-bold text-gray-900 hidden sm:block">TaPrestation</span>
                    </a>
                </div>

                {{-- Liens principaux desktop --}}
                <div id="site-navbar-desktop-links" class="hidden space-x-1 lg:-my-px lg:ml-10 lg:flex items-center overflow-x-auto">
                    <x-nav-link :href="$homeRoute" :active="request()->routeIs('home')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-blue-50 whitespace-nowrap">
                        <i class="fas fa-home mr-1 text-xs"></i>Accueil
                    </x-nav-link>

                    @if($servicesRoute)
                        <x-nav-link :href="$servicesRoute" :active="$isServicesActive" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-blue-50 whitespace-nowrap">
                            <i class="fas fa-briefcase mr-1 text-xs text-blue-500"></i>Services
                        </x-nav-link>
                    @endif

                    @if($equipmentRoute)
                        <x-nav-link :href="$equipmentRoute" :active="$isEquipmentActive" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-green-600 border-b-2 border-transparent hover:border-green-600 transition-all duration-200 rounded-lg hover:bg-green-50 whitespace-nowrap">
                            <i class="fas fa-tools mr-1 text-xs text-green-500"></i>Matériel
                        </x-nav-link>
                    @endif

                    @if($urgentSalesRoute)
                        <x-nav-link :href="$urgentSalesRoute" :active="$isUrgentSalesActive" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-red-600 border-b-2 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-red-50 whitespace-nowrap">
                            <i class="fas fa-bolt mr-1 text-xs text-red-500"></i>Annonces
                        </x-nav-link>
                    @endif

                    @if($videosFeedRoute)
                        <x-nav-link :href="$videosFeedRoute" :active="request()->routeIs('videos.feed')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-purple-600 border-b-2 border-transparent hover:border-purple-600 transition-all duration-200 rounded-lg hover:bg-purple-50 whitespace-nowrap">
                            <i class="fas fa-video mr-1 text-xs text-purple-500"></i>Vidéos
                        </x-nav-link>
                    @endif

                    @if($foodExploreRoute)
                        <x-nav-link :href="$foodExploreRoute" :active="request()->routeIs('food.explore')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-orange-600 border-b-2 border-transparent hover:border-orange-600 transition-all duration-200 rounded-lg hover:bg-orange-50 whitespace-nowrap">
                            <i class="fas fa-utensils mr-1 text-xs text-orange-500"></i>Food
                        </x-nav-link>
                    @endif

                    {{-- Plus menu for additional links --}}
                    <div class="relative" x-data="{ openMore: false }">
                        <button @click="openMore = !openMore" @click.outside="openMore = false" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 whitespace-nowrap flex items-center">
                            <i class="fas fa-ellipsis-h mr-1"></i>Plus
                        </button>
                        <div x-show="openMore" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg z-[100] border border-gray-100"
                             style="display: none;">
                            @auth
                                @if($user && method_exists($user, 'hasRole') && $user->hasRole('client'))
                                    @if($clientCartRoute)
                                        <a href="{{ $clientCartRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 first:rounded-t-lg">
                                            <i class="fas fa-shopping-cart mr-2 text-gray-500"></i>Panier
                                        </a>
                                    @endif
                                    @if($clientPaymentsRoute && function_exists('payment_feature_enabled') && payment_feature_enabled())
                                        <a href="{{ $clientPaymentsRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-wallet mr-2 text-gray-500"></i>Paiements
                                        </a>
                                    @endif
                                    @if($clientDeliveryRoute)
                                        <a href="{{ $clientDeliveryRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-truck mr-2 text-gray-500"></i>Livraisons
                                        </a>
                                    @endif
                                    @if($clientAddressBookRoute)
                                        <a href="{{ $clientAddressBookRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-address-book mr-2 text-gray-500"></i>Carnet d'adresses
                                        </a>
                                    @endif
                                    @if($clientNotificationSettingsRoute)
                                        <a href="{{ $clientNotificationSettingsRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 last:rounded-b-lg">
                                            <i class="fas fa-bell mr-2 text-gray-500"></i>Notifications
                                        </a>
                                    @endif
                                @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire'))
                                    @if($prestatairePaymentsRoute && function_exists('payment_feature_enabled') && payment_feature_enabled())
                                        <a href="{{ $prestatairePaymentsRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 first:rounded-t-lg">
                                            <i class="fas fa-wallet mr-2 text-gray-500"></i>Paiements
                                        </a>
                                    @endif
                                    @if($prestataireInventoryRoute)
                                        <a href="{{ $prestataireInventoryRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-boxes mr-2 text-gray-500"></i>Inventaire
                                        </a>
                                    @endif
                                    @if($prestataireDeliveryRoute)
                                        <a href="{{ $prestataireDeliveryRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-truck mr-2 text-gray-500"></i>Livraison
                                        </a>
                                    @endif
                                    @if($prestataireNotificationSettingsRoute)
                                        <a href="{{ $prestataireNotificationSettingsRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 last:rounded-b-lg">
                                            <i class="fas fa-bell mr-2 text-gray-500"></i>Notifications
                                        </a>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite desktop (auth / non-auth) --}}
            <div id="site-navbar-desktop-actions" class="hidden lg:flex lg:items-center lg:ml-6 space-x-3">
                @auth
                    {{-- Icônes rapides --}}
                    <div class="flex items-center space-x-2">
                        {{-- Messagerie --}}
                        @if($messagingRoute)
                            <a href="{{ $messagingRoute }}" class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200" id="messaging-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                @if($unreadMessagesCount > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        {{-- Notifications desktop (composant existant si dispo) --}}
                        @if(View::exists('components.notification-dropdown'))
                            <x-notification-dropdown />
                        @endif

                        {{-- Agenda prestataire --}}
                        @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && $prestataireAgendaRoute)
                            <a href="{{ $prestataireAgendaRoute }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </a>
                        @endif
                    </div>

                    {{-- Dropdown utilisateur --}}
                    <x-dropdown align="right" width="80">
                        <x-slot name="trigger">
                            <button class="flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                                {{-- Avatar --}}
                                <div class="flex-shrink-0 mr-2">
                                    @php
                                        $avatarUrl = null;
                                        if ($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $user->client && $user->client->avatar) {
                                            $avatarUrl = asset('storage/' . $user->client->avatar);
                                        } elseif ($user && $user->profile_photo_path) {
                                            $avatarUrl = asset('storage/' . $user->profile_photo_path);
                                        } elseif ($user && $user->profile_photo_url) {
                                            $avatarUrl = $user->profile_photo_url;
                                        }
                                    @endphp

                                    @if($avatarUrl)
                                        <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-200" src="{{ $avatarUrl }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold border-2 border-gray-200">
                                            {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'T' }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Prénom --}}
                                <div class="text-left hidden md:block">
                                    <div class="font-medium text-gray-800">{{ $user ? explode(' ', $user->name)[0] : '' }}</div>
                                </div>

                                {{-- Chevron --}}
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            {{-- En-tête du dropdown --}}
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="flex items-center">
                                    @if($avatarUrl)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $avatarUrl }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                            {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'T' }}
                                        </div>
                                    @endif
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Liens --}}
                            <div class="py-1">
                                {{-- Tableau de bord --}}
                                @if($isPrestataireInClientMode && $clientDashboardRoute)
                                    <x-dropdown-link :href="$clientDashboardRoute" class="flex items-center font-medium">
                                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        {{ __('Tableau de bord') }}
                                    </x-dropdown-link>
                                @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $clientDashboardRoute)
                                    <x-dropdown-link :href="$clientDashboardRoute" class="flex items-center font-medium">
                                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        {{ __('Tableau de bord') }}
                                    </x-dropdown-link>
                                @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && $prestataireDashboardRoute)
                                    <x-dropdown-link :href="$prestataireDashboardRoute" class="flex items-center font-medium">
                                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        {{ __('Tableau de bord') }}
                                    </x-dropdown-link>
                                @elseif($defaultDashboardRoute)
                                    <x-dropdown-link :href="$defaultDashboardRoute" class="flex items-center font-medium">
                                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        {{ __('Tableau de bord') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="border-t border-gray-100 my-1"></div>

                                {{-- Mon profil --}}
                                @if($profileSettingsRoute)
                                    <x-dropdown-link :href="$profileSettingsRoute" class="flex items-center">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ __('Mon profil') }}
                                    </x-dropdown-link>
                                @endif

                                @if($user && $user->role === 'prestataire' && !$isPrestataireInClientMode)
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <x-dropdown-link :href="route('client.dashboard')" class="flex items-center px-4 py-3 text-sm font-semibold text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200 rounded-lg mx-2 my-2 border border-green-200">
                                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        {{ __('Mode Client') }}
                                        <svg class="w-4 h-4 ml-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </x-dropdown-link>
                                    <p class="px-4 py-1 text-xs text-gray-500 italic">Réservez chez d'autres prestataires</p>
                                @endif
                                
                                @if($isPrestataireInClientMode)
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <x-dropdown-link :href="route('prestataire.dashboard')" class="flex items-center px-4 py-3 text-sm font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors duration-200 rounded-lg mx-2 my-2 border border-purple-200">
                                        <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ __('Mode Prestataire') }}
                                        <svg class="w-4 h-4 ml-auto text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </x-dropdown-link>
                                    <p class="px-4 py-1 text-xs text-gray-500 italic">Retour à votre espace prestataire</p>
                                @endif

                                @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && !$isPrestataireInClientMode)
                                    <div class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200">
                                        Mes services
                                    </div>

                                    @if($prestataireServicesCreate)
                                        <x-dropdown-link :href="$prestataireServicesCreate" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            {{ __('Ajouter un service') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireServicesIndex)
                                        <x-dropdown-link :href="$prestataireServicesIndex" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                            </svg>
                                            {{ __('Gérer mes prestations') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireVideosManage)
                                        <x-dropdown-link :href="$prestataireVideosManage" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002 2V8a2 2 0 00-2 2V18a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                            </svg>
                                            {{ __('Mes Vidéos') }}
                                        </x-dropdown-link>
                                    @endif

                                    <div class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200 mt-3">
                                        📅 Réservations
                                    </div>

                                    @if($prestataireBookingsRoute)
                                        <x-dropdown-link :href="$prestataireBookingsRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('Réservations services') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireReservationsRoute)
                                        <x-dropdown-link :href="$prestataireReservationsRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-orange-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                            {{ __('Réservations produits') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireAgendaRoute)
                                        <x-dropdown-link :href="$prestataireAgendaRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ __('Mon agenda') }}
                                        </x-dropdown-link>
                                    @endif

                                    <div class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200 mt-3">
                                        Location de matériel
                                    </div>

                                    @if($prestataireEquipmentCreate)
                                        <x-dropdown-link :href="$prestataireEquipmentCreate" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-green-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            {{ __('Ajouter un équipement') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireEquipmentIndex)
                                        <x-dropdown-link :href="$prestataireEquipmentIndex" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-green-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            {{ __('Voir mes équipements') }}
                                        </x-dropdown-link>
                                    @endif

                                    <div class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200 mt-3">
                                        Annonces
                                    </div>

                                    @if($prestataireUrgentSalesCreate)
                                        <x-dropdown-link :href="$prestataireUrgentSalesCreate" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            {{ __('Mettre un produit en vente') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($prestataireUrgentSalesIndex)
                                        <x-dropdown-link :href="$prestataireUrgentSalesIndex" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            {{ __('Voir mes ventes actives') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    <div class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200 mt-3">
                                        Gestion financière
                                    </div>
                                    
                                    @if($prestatairePaymentsRoute && function_exists('payment_feature_enabled') && payment_feature_enabled())
                                        <x-dropdown-link :href="$prestatairePaymentsRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-green-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ __('Mes revenus') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($prestataireSubscriptionsRoute && function_exists('feature_enabled') && feature_enabled('subscription_enabled'))
                                        <x-dropdown-link :href="$prestataireSubscriptionsRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-yellow-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                            </svg>
                                            {{ __('Mon abonnement') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($prestataireInventoryRoute)
                                        <x-dropdown-link :href="$prestataireInventoryRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            {{ __('Mon inventaire') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($prestataireDeliveryRoute)
                                        <x-dropdown-link :href="$prestataireDeliveryRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"></path>
                                            </svg>
                                            {{ __('Mes livraisons') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($prestataireNotificationSettingsRoute)
                                        <x-dropdown-link :href="$prestataireNotificationSettingsRoute" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-orange-600 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ __('Paramètres notifications') }}
                                        </x-dropdown-link>
                                    @endif

                                    <div class="border-t border-gray-200 mt-3"></div>
                                @endif
                                
                                @if(($user && method_exists($user, 'hasRole') && $user->hasRole('client')) || $isPrestataireInClientMode)
                                    @if($messagingRoute)
                                        <x-dropdown-link :href="$messagingRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                            {{ __('Mes messages') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($clientBookingsRoute)
                                        <x-dropdown-link :href="$clientBookingsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('Réservations services') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($clientReservationsRoute)
                                        <x-dropdown-link :href="$clientReservationsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                            {{ __('Réservations produits') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($clientCartRoute)
                                        <x-dropdown-link :href="$clientCartRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m2-9h12m-6 9a1 1 0 100-2 1 1 0 000 2zm-6 0a1 1 0 100-2 1 1 0 000 2z"></path>
                                            </svg>
                                            {{ __('Panier') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($clientEquipmentRentals)
                                        <x-dropdown-link :href="$clientEquipmentRentals" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            {{ __('Mes locations') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if(Route::has('food.orders'))
                                        <x-dropdown-link :href="route('food.orders')" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                            {{ __('Mes commandes food') }}
                                        </x-dropdown-link>
                                    @endif

                                    @if($clientFollowsRoute)
                                        <x-dropdown-link :href="$clientFollowsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            {{ __('Prestataires suivis') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b border-gray-200 mt-2">
                                        Gestion
                                    </div>
                                    
                                    @if($clientPaymentsRoute && function_exists('payment_feature_enabled') && payment_feature_enabled())
                                        <x-dropdown-link :href="$clientPaymentsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            {{ __('Mes paiements') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($clientSubscriptionsRoute && function_exists('feature_enabled') && feature_enabled('subscription_enabled'))
                                        <x-dropdown-link :href="$clientSubscriptionsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                            </svg>
                                            {{ __('Abonnements') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($clientDeliveryRoute)
                                        <x-dropdown-link :href="$clientDeliveryRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"></path>
                                            </svg>
                                            {{ __('Mes livraisons') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($clientAddressBookRoute)
                                        <x-dropdown-link :href="$clientAddressBookRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ __('Carnet d\'adresses') }}
                                        </x-dropdown-link>
                                    @endif
                                    
                                    @if($clientNotificationSettingsRoute)
                                        <x-dropdown-link :href="$clientNotificationSettingsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ __('Paramètres notifications') }}
                                        </x-dropdown-link>
                                    @endif
                                @endif
                            </div>

                            {{-- Déconnexion --}}
                            <div class="border-t border-gray-100">
                                @if($logoutRoute)
                                    <form action="{{ $logoutRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" data-no-force-nav="1" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            {{ __('Déconnexion') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Visiteur non connecté --}}
                    <div class="flex items-center space-x-3">
                        @if($loginRoute)
                            <a href="{{ $loginRoute }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200">
                                {{ __('Connexion') }}
                            </a>
                        @endif

                        @if($registerRoute)
                            <a href="{{ $registerRoute }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-all duration-200 shadow-sm flex items-center">
                                {{ __('Inscription') }}
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            {{-- Mobile (icônes + burger) --}}
            <div id="site-navbar-mobile-actions" class="flex items-center space-x-2 lg:hidden shrink-0">
                @auth
                    {{-- Messagerie mobile --}}
                    @if($messagingRoute)
                        <a href="{{ $messagingRoute }}" class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            @if($unreadMessagesCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                    {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    {{-- Notifications mobile --}}
                    <div class="relative" x-data="{ openNotif:false }">
                        <button @click="openNotif = !openNotif" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-lg shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a50.002 50.002 0 00-2.5-2.5V8a6 6 0 10-12 0v2.5c-1 1-2.5 2.5-2.5 2.5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>

                        <div
                            x-show="openNotif"
                            @click.away="openNotif = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                            style="display:none;"
                        >
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                                    @if($unreadNotificationsCount > 0 && $notificationsMarkAll)
                                        <form action="{{ $notificationsMarkAll }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                                                Tout marquer comme lu
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if($recentNotifications->isEmpty())
                                    <div class="text-center py-6">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a50.002 50.002 0 00-2.5-2.5V8a6 6 0 10-12 0v2.5c-1 1-2.5 2.5-2.5 2.5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Aucune nouvelle notification</p>
                                    </div>
                                @else
                                    <div class="space-y-3 max-h-64 overflow-y-auto">
                                        @foreach($recentNotifications as $notification)
                                            @php
                                                $type = $notification->type;
                                                $isEquipmentNotification = strpos($type, 'Equipment') !== false;
                                                $isServiceNotification = strpos($type, 'Booking') !== false;
                                                if ($isEquipmentNotification) { $iconClass = 'fa-tools'; $colorClass = 'text-green-500'; $bgClass = 'bg-green-100'; }
                                                elseif ($isServiceNotification) { $iconClass = 'fa-cogs'; $colorClass = 'text-blue-500'; $bgClass = 'bg-blue-100'; }
                                                else { $iconClass = 'fa-bell'; $colorClass = 'text-blue-600'; $bgClass = 'bg-blue-100'; }
                                                $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                                $title = $data['title'] ?? 'Notification';
                                                $message = $data['message'] ?? '';
                                                $notifUrl = Route::has('notifications.redirect') ? route('notifications.redirect', $notification->id) : ($data['url'] ?? ($data['action_url'] ?? '#'));
                                            @endphp
                                            <a href="{{ $notifUrl }}" class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer block">
                                                <div class="w-8 h-8 {{ $bgClass }} rounded-full flex items-center justify-center flex-shrink-0">
                                                    <i class="fas {{ $iconClass }} {{ $colorClass }} text-sm"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
                                                    <p class="text-sm text-gray-500 line-clamp-2">{{ $message }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    @if($notificationsIndex)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <a href="{{ $notificationsIndex }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800">
                                                Voir toutes les notifications
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Burger menu --}}
                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-800 transition duration-150 ease-in-out shadow-sm border border-gray-200 shrink-0"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu latéral mobile --}}
    <div
        x-cloak
        x-show="open"
        @keydown.window.escape="open = false"
        class="fixed inset-y-0 left-0 z-[80] w-72 max-w-[90vw] bg-white shadow-2xl border-r border-gray-100 transform transition duration-300 ease-in-out lg:hidden overflow-y-auto px-4 pt-[calc(1.25rem+env(safe-area-inset-top))] pb-[calc(1.75rem+env(safe-area-inset-bottom))]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-full"
    >
        <div class="space-y-1 mobile-menu">
            {{-- Bouton close --}}
            <div class="flex justify-end mb-4">
                <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4 mb-4">
                    <div class="flex items-center space-x-3">
                        @if($avatarUrl)
                            <img class="h-12 w-12 rounded-full object-cover border-2 border-gray-200" src="{{ $avatarUrl }}" alt="{{ $user->name }}" />
                        @else
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-lg border-2 border-gray-200">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                            <div class="font-medium text-sm text-gray-500 truncate max-w-[180px]">{{ $user->email }}</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 px-4 pb-4 mobile-menu">
                    @if($isPrestataireInClientMode && $clientDashboardRoute)
                        <a href="{{ $clientDashboardRoute }}" class="flex items-center px-4 py-4 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg mb-4 font-semibold text-base">
                            <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                        @if($clientBookingsRoute)
                        <a href="{{ $clientBookingsRoute }}" class="flex items-center px-4 py-3 mb-2 text-blue-700 bg-blue-50 border border-blue-200 rounded-xl font-semibold hover:bg-blue-100 transition-all duration-200">
                            <i class="fas fa-calendar-check mr-3 text-blue-600 text-lg"></i>
                            {{ __('Réservations services') }}
                            <i class="fas fa-chevron-right ml-auto text-blue-500"></i>
                        </a>
                        @endif
                        @if($clientReservationsRoute)
                        <a href="{{ $clientReservationsRoute }}" class="flex items-center px-4 py-3 mb-3 text-orange-700 bg-orange-50 border border-orange-200 rounded-xl font-semibold hover:bg-orange-100 transition-all duration-200">
                            <i class="fas fa-shopping-bag mr-3 text-orange-600 text-lg"></i>
                            {{ __('Réservations produits') }}
                            <i class="fas fa-chevron-right ml-auto text-orange-500"></i>
                        </a>
                        @endif
                    @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $clientDashboardRoute)
                        <a href="{{ $clientDashboardRoute }}" class="flex items-center px-4 py-4 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg mb-4 font-semibold text-base">
                            <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                        @if($clientBookingsRoute)
                        <a href="{{ $clientBookingsRoute }}" class="flex items-center px-4 py-3 mb-2 text-blue-700 bg-blue-50 border border-blue-200 rounded-xl font-semibold hover:bg-blue-100 transition-all duration-200">
                            <i class="fas fa-calendar-check mr-3 text-blue-600 text-lg"></i>
                            {{ __('Réservations services') }}
                            <i class="fas fa-chevron-right ml-auto text-blue-500"></i>
                        </a>
                        @endif
                        @if($clientReservationsRoute)
                        <a href="{{ $clientReservationsRoute }}" class="flex items-center px-4 py-3 mb-3 text-orange-700 bg-orange-50 border border-orange-200 rounded-xl font-semibold hover:bg-orange-100 transition-all duration-200">
                            <i class="fas fa-shopping-bag mr-3 text-orange-600 text-lg"></i>
                            {{ __('Réservations produits') }}
                            <i class="fas fa-chevron-right ml-auto text-orange-500"></i>
                        </a>
                        @endif
                    @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && $prestataireDashboardRoute)
                        <a href="{{ $prestataireDashboardRoute }}" class="flex items-center px-4 py-4 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg mb-4 font-semibold text-base">
                            <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                    @elseif($defaultDashboardRoute)
                        <a href="{{ $defaultDashboardRoute }}" class="flex items-center px-4 py-4 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg mb-4 font-semibold text-base">
                            <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                    @endif

                    @if($user && $user->role === 'prestataire' && !$isPrestataireInClientMode)
                        <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-3 mb-3 text-green-700 bg-green-50 border border-green-200 rounded-xl font-semibold hover:bg-green-100 transition-all duration-200">
                            <i class="fas fa-shopping-bag mr-3 text-green-600 text-lg"></i>
                            {{ __('Mode Client') }}
                            <i class="fas fa-chevron-right ml-auto text-green-500"></i>
                        </a>
                    @endif
                    
                    @if($isPrestataireInClientMode)
                        <a href="{{ route('prestataire.dashboard') }}" class="flex items-center px-4 py-3 mb-3 text-purple-700 bg-purple-50 border border-purple-200 rounded-xl font-semibold hover:bg-purple-100 transition-all duration-200">
                            <i class="fas fa-briefcase mr-3 text-purple-600 text-lg"></i>
                            {{ __('Mode Prestataire') }}
                            <i class="fas fa-chevron-right ml-auto text-purple-500"></i>
                        </a>
                        <div class="grid grid-cols-4 gap-1 mb-4 px-2">
                            @if(Route::has('services.index'))
                            <a href="{{ route('services.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-concierge-bell text-blue-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Services</span>
                            </a>
                            @endif
                            @if(Route::has('equipment.index'))
                            <a href="{{ route('equipment.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-green-600 rounded-lg hover:bg-green-50 transition">
                                <i class="fas fa-tools text-green-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Matériel</span>
                            </a>
                            @endif
                            @if(Route::has('urgent-sales.index'))
                            <a href="{{ route('urgent-sales.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-yellow-600 rounded-lg hover:bg-yellow-50 transition">
                                <i class="fas fa-bolt text-yellow-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Annonces</span>
                            </a>
                            @endif
                            @if(Route::has('food.explore'))
                            <a href="{{ route('food.explore') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                <i class="fas fa-utensils text-red-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Food</span>
                            </a>
                            @endif
                        </div>
                    @endif

                    @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && !$isPrestataireInClientMode)
                        @if($prestataireBookingsRoute)
                        <a href="{{ $prestataireBookingsRoute }}" class="flex items-center px-4 py-3 mb-2 text-purple-700 bg-purple-50 border border-purple-200 rounded-xl font-semibold hover:bg-purple-100 transition-all duration-200">
                            <i class="fas fa-calendar-check mr-3 text-purple-600 text-lg"></i>
                            {{ __('Réservations services') }}
                            <i class="fas fa-chevron-right ml-auto text-purple-500"></i>
                        </a>
                        @endif
                        @if($prestataireReservationsRoute)
                        <a href="{{ $prestataireReservationsRoute }}" class="flex items-center px-4 py-3 mb-3 text-orange-700 bg-orange-50 border border-orange-200 rounded-xl font-semibold hover:bg-orange-100 transition-all duration-200">
                            <i class="fas fa-shopping-bag mr-3 text-orange-600 text-lg"></i>
                            {{ __('Réservations produits') }}
                            <i class="fas fa-chevron-right ml-auto text-orange-500"></i>
                        </a>
                        @endif
                        <div class="grid grid-cols-4 gap-1 mb-4 px-2">
                            @if(Route::has('services.index'))
                            <a href="{{ route('services.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-concierge-bell text-blue-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Services</span>
                            </a>
                            @endif
                            @if(Route::has('equipment.index'))
                            <a href="{{ route('equipment.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-green-600 rounded-lg hover:bg-green-50 transition">
                                <i class="fas fa-tools text-green-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Matériel</span>
                            </a>
                            @endif
                            @if(Route::has('urgent-sales.index'))
                            <a href="{{ route('urgent-sales.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-yellow-600 rounded-lg hover:bg-yellow-50 transition">
                                <i class="fas fa-bolt text-yellow-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Annonces</span>
                            </a>
                            @endif
                            @if(Route::has('food.explore'))
                            <a href="{{ route('food.explore') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                <i class="fas fa-utensils text-red-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Food</span>
                            </a>
                            @endif
                        </div>
                    @endif

                    @if($user && method_exists($user, 'hasRole') && $user->hasRole('client'))
                        <div class="grid grid-cols-4 gap-1 mb-4 px-2">
                            @if(Route::has('services.index'))
                            <a href="{{ route('services.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-concierge-bell text-blue-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Services</span>
                            </a>
                            @endif
                            @if(Route::has('equipment.index'))
                            <a href="{{ route('equipment.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-green-600 rounded-lg hover:bg-green-50 transition">
                                <i class="fas fa-tools text-green-600 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Matériel</span>
                            </a>
                            @endif
                            @if(Route::has('urgent-sales.index'))
                            <a href="{{ route('urgent-sales.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-yellow-600 rounded-lg hover:bg-yellow-50 transition">
                                <i class="fas fa-bolt text-yellow-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Annonces</span>
                            </a>
                            @endif
                            @if(Route::has('food.explore'))
                            <a href="{{ route('food.explore') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                <i class="fas fa-utensils text-red-500 text-lg mb-1"></i>
                                <span class="text-[10px] font-medium">Food</span>
                            </a>
                            @endif
                        </div>
                    @endif

                    <div class="border-t border-gray-200 my-3"></div>

                    @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && !$isPrestataireInClientMode && $prestataireNotificationSettingsRoute)
                        <a href="{{ $prestataireNotificationSettingsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bell mr-3 text-gray-500 text-sm"></i>
                            {{ __('Notifications') }}
                        </a>
                    @elseif(($user && method_exists($user, 'hasRole') && $user->hasRole('client') || $isPrestataireInClientMode) && $clientNotificationSettingsRoute)
                        <a href="{{ $clientNotificationSettingsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bell mr-3 text-gray-500 text-sm"></i>
                            {{ __('Notifications') }}
                        </a>
                    @endif

                    @if($profileSettingsRoute)
                        <a href="{{ $profileSettingsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-cog mr-3 text-gray-500 text-sm"></i>
                            {{ __('Paramètres') }}
                        </a>
                    @endif

                    @if($logoutRoute)
                        <form action="{{ $logoutRoute }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-3 text-red-600 hover:text-red-700 border-l-3 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3 text-sm"></i>
                                {{ __('Déconnexion') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gray-200 px-4 mobile-menu">
                <div class="grid grid-cols-4 gap-1 mb-4 px-2">
                    @if(Route::has('services.index'))
                    <a href="{{ route('services.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-concierge-bell text-blue-600 text-lg mb-1"></i>
                        <span class="text-[10px] font-medium">Services</span>
                    </a>
                    @endif
                    @if(Route::has('equipment.index'))
                    <a href="{{ route('equipment.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-green-600 rounded-lg hover:bg-green-50 transition">
                        <i class="fas fa-tools text-green-600 text-lg mb-1"></i>
                        <span class="text-[10px] font-medium">Matériel</span>
                    </a>
                    @endif
                    @if(Route::has('urgent-sales.index'))
                    <a href="{{ route('urgent-sales.index') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-yellow-600 rounded-lg hover:bg-yellow-50 transition">
                        <i class="fas fa-bolt text-yellow-500 text-lg mb-1"></i>
                        <span class="text-[10px] font-medium">Annonces</span>
                    </a>
                    @endif
                    @if(Route::has('food.explore'))
                    <a href="{{ route('food.explore') }}" class="flex flex-col items-center py-2 text-gray-700 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                        <i class="fas fa-utensils text-red-500 text-lg mb-1"></i>
                        <span class="text-[10px] font-medium">Food</span>
                    </a>
                    @endif
                </div>

                <div class="border-t border-gray-200 my-3"></div>

                <div class="space-y-3">
                    @if($loginRoute)
                        <a href="{{ $loginRoute }}" class="flex items-center justify-center px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 border border-gray-200 font-medium">
                            {{ __('Connexion') }}
                        </a>
                    @endif
                    @if($registerRoute)
                        <a href="{{ $registerRoute }}" class="flex items-center justify-center px-4 py-3 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all duration-200 font-medium shadow-sm">
                            {{ __('Inscription') }}
                        </a>
                    @endif
                </div>
            </div>
        @endauth
    </div>
</nav>

@once
<script>
    (function () {
        var APP_NAV_KEY = 'taprestation_nav_stack';
        var APP_PENDING_LOGICAL_BACK_KEY = 'taprestation_pending_logical_back';
        var MAX_STACK_SIZE = 50;

        function isRunningAsApp() {
            try {
                return !!window.Capacitor ||
                    (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
                    !!window.navigator.standalone;
            } catch (e) {
                return false;
            }
        }

        function safeFallbackUrl(raw) {
            try {
                if (!raw) return '/';
                var url = new URL(raw, window.location.origin);
                return url.origin === window.location.origin ? url.href : '/';
            } catch (e) {
                return '/';
            }
        }

        function toSameOriginUrl(raw) {
            try {
                if (!raw) return null;
                var url = new URL(raw, window.location.origin);
                return url.origin === window.location.origin ? url : null;
            } catch (e) {
                return null;
            }
        }

        function normalizeComparableUrl(raw) {
            var url = toSameOriginUrl(raw);
            if (!url) return null;
            return normalizePath(url.pathname) + (url.search || '');
        }

        function getNavStack() {
            try {
                return JSON.parse(sessionStorage.getItem(APP_NAV_KEY) || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveNavStack(stack) {
            try {
                sessionStorage.setItem(APP_NAV_KEY, JSON.stringify(stack.slice(-MAX_STACK_SIZE)));
            } catch (e) {}
        }

        function getPendingLogicalBack() {
            try {
                return JSON.parse(sessionStorage.getItem(APP_PENDING_LOGICAL_BACK_KEY) || 'null');
            } catch (e) {
                return null;
            }
        }

        function clearPendingLogicalBack() {
            try {
                sessionStorage.removeItem(APP_PENDING_LOGICAL_BACK_KEY);
            } catch (e) {}
        }

        function queueLogicalBackArrival(rawUrl) {
            var comparable = normalizeComparableUrl(rawUrl);
            if (!comparable || isDisallowedBackTarget(comparable)) {
                return;
            }
            try {
                sessionStorage.setItem(APP_PENDING_LOGICAL_BACK_KEY, JSON.stringify({
                    target: comparable,
                    expiresAt: Date.now() + 30000
                }));
            } catch (e) {}
        }

        function setCurrentHistoryLogicalBack(enabled) {
            try {
                var currentState = window.history.state;
                var nextState = (currentState && typeof currentState === 'object') ? currentState : {};
                nextState = Object.assign({}, nextState, { taprestationLogicalBack: enabled === true });
                window.history.replaceState(nextState, '', window.location.href);
                return true;
            } catch (e) {
                return false;
            }
        }

        function applyPendingLogicalBackState() {
            var pending = getPendingLogicalBack();
            if (!pending || !pending.target) return false;
            if (pending.expiresAt && pending.expiresAt < Date.now()) { clearPendingLogicalBack(); return false; }
            if (pending.target !== getCurrentComparableUrl()) return false;
            clearPendingLogicalBack();
            return setCurrentHistoryLogicalBack(true);
        }

        function consumeLogicalBackState() {
            try {
                var currentState = window.history.state;
                var shouldUseLogical = !!(currentState && typeof currentState === 'object' && currentState.taprestationLogicalBack === true);
                if (shouldUseLogical) setCurrentHistoryLogicalBack(false);
                return shouldUseLogical;
            } catch (e) {
                return false;
            }
        }

        function pushToNavStack(url) {
            var normalizedUrl = normalizeComparableUrl(url);
            if (!normalizedUrl) return;
            var stack = getNavStack();
            if (stack.length === 0 || normalizeComparableUrl(stack[stack.length - 1]) !== normalizedUrl) {
                stack.push(normalizedUrl);
                saveNavStack(stack);
            }
        }

        function popFromNavStack() {
            var stack = getNavStack();
            var current = getCurrentComparableUrl();
            while (stack.length > 0 && normalizeComparableUrl(stack[stack.length - 1]) === current) stack.pop();
            var prev = null;
            while (stack.length > 0) {
                var candidate = normalizeComparableUrl(stack.pop());
                if (!candidate || candidate === current || isDisallowedBackTarget(candidate)) continue;
                prev = candidate;
                break;
            }
            saveNavStack(stack);
            return prev || null;
        }

        function trimCurrentFromNavStack() {
            var stack = getNavStack();
            var current = getCurrentComparableUrl();
            var mutated = false;
            while (stack.length > 0 && normalizeComparableUrl(stack[stack.length - 1]) === current) { stack.pop(); mutated = true; }
            if (mutated) saveNavStack(stack);
        }

        function peekPreviousFromNavStack() {
            var stack = getNavStack();
            var current = getCurrentComparableUrl();
            for (var i = stack.length - 1; i >= 0; i--) {
                var candidate = normalizeComparableUrl(stack[i]);
                if (!candidate || candidate === current || isDisallowedBackTarget(candidate)) continue;
                return candidate;
            }
            return null;
        }

        function normalizePath(path) {
            if (!path) return '/';
            var normalized = path.replace(/\/{2,}/g, '/');
            if (normalized.length > 1 && normalized.endsWith('/')) normalized = normalized.slice(0, -1);
            return normalized || '/';
        }

        function getCurrentComparableUrl() {
            return normalizeComparableUrl(window.location.pathname + window.location.search) || '/';
        }

        function isDisallowedBackTarget(raw) {
            var comparable = normalizeComparableUrl(raw);
            if (!comparable) return true;
            var path = normalizePath((comparable.split('?')[0] || '/'));
            return path === '/login' || path === '/register' || path === '/logout' || path === '/403' || path === '/404' || path === '/500' || path.indexOf('/errors/') === 0;
        }

        function getSafeReferrerUrl() {
            var current = getCurrentComparableUrl();
            var referrer = normalizeComparableUrl(document.referrer || '');
            if (!referrer || referrer === current || isDisallowedBackTarget(referrer)) return null;
            return referrer;
        }

        function navigateToBackTarget(rawUrl, options) {
            options = options || {};
            var target = safeFallbackUrl(rawUrl);
            if (!target) return false;
            if (isDisallowedBackTarget(target) || normalizeComparableUrl(target) === getCurrentComparableUrl()) return false;
            if (options.queueLogical === true) queueLogicalBackArrival(target);
            window.location.href = target;
            return true;
        }

        function isBackLinkCandidate(anchor) {
            if (!anchor) return false;
            if ((anchor.dataset.smartBack || '').toLowerCase() === 'false') return false;
            if ((anchor.dataset.smartBack || '').toLowerCase() === 'true') return true;
            var hrefAttr = (anchor.getAttribute('href') || '').trim();
            if (!hrefAttr || hrefAttr === '#' || hrefAttr.startsWith('javascript:') || hrefAttr.startsWith('mailto:') || hrefAttr.startsWith('tel:')) return false;
            var hrefUrl = toSameOriginUrl(hrefAttr);
            if (!hrefUrl) return false;
            var text = ((anchor.textContent || '') + ' ' + (anchor.getAttribute('aria-label') || '') + ' ' + (anchor.getAttribute('title') || '')).toLowerCase();
            return text.indexOf('retour') !== -1 || text.indexOf('précédent') !== -1 || text.indexOf('precedent') !== -1;
        }

        function resolveSectionRoot(rootPath) {
            switch (rootPath) {
                case '/prestataire': return '{{ Route::has("prestataire.dashboard") ? route("prestataire.dashboard") : "/prestataire/dashboard" }}';
                case '/prestataire/food': return '{{ Route::has("prestataire.food-orders.dashboard") ? route("prestataire.food-orders.dashboard") : "/prestataire/food/food-orders/dashboard" }}';
                case '/client': return '{{ Route::has("client.dashboard") ? route("client.dashboard") : "/client/dashboard" }}';
                case '/driver': return '{{ Route::has("driver.map") ? route("driver.map") : "/driver/map" }}';
                case '/admin': return '/admin/dashboard';
                default: return null;
            }
        }

        function getLogicalParentUrl() {
            var path = normalizePath(window.location.pathname);
            if (path.match(/\/commandes-food\/commande\/\d+$/) || path.match(/\/food\/commande\/\d+$/)) return '{{ Route::has("food.orders") ? route("food.orders") : "/commandes-food/mes-commandes" }}';
            if (path.match(/\/food\/orders/) || path.match(/\/mes-commandes/)) return '{{ Route::has("food.index") ? route("food.index") : "/food" }}';
            if (path.match(/\/food\/prestataire\/\d+$/)) return '{{ Route::has("food.index") ? route("food.index") : "/food" }}';
            if (path.match(/\/food\/cart$/)) return '{{ Route::has("food.index") ? route("food.index") : "/food" }}';
            if (path.match(/\/services\/\d+$/)) return '{{ Route::has("services.index") ? route("services.index") : "/services" }}';
            if (path.match(/\/prestataires\/\d+$/)) return '{{ Route::has("services.index") ? route("services.index") : "/services" }}';
            if (path.match(/\/equipment\/\d+$/)) return '/equipment';
            if (path.match(/\/reservations\/\d+$/)) return '/reservations';
            if (path.match(/\/prestataire\/food\/food-orders\/\d+$/) || path.match(/\/prestataire\/food-orders\/\d+$/)) return '{{ Route::has("prestataire.food-orders.index") ? route("prestataire.food-orders.index") : "/prestataire/food/food-orders" }}';
            if (path.match(/\/prestataire\/bookings\/\d+$/)) return '{{ Route::has("prestataire.agenda.index") ? route("prestataire.agenda.index") : "/prestataire/agenda" }}';
            if (path.match(/\/prestataire\/services\/\d+$/)) return '{{ Route::has("prestataire.services.index") ? route("prestataire.services.index") : "/prestataire/services" }}';
            if (path.match(/\/delivery\/orders\/\d+$/)) return '/delivery/orders';
            if (path.match(/\/driver\/deliveries\/\d+$/)) return '{{ Route::has("driver.deliveries") ? route("driver.deliveries") : "/driver/deliveries" }}';
            if (path.match(/\/driver\/navigate\/\d+$/)) return '{{ Route::has("driver.map") ? route("driver.map") : "/driver/map" }}';
            if (path.match(/\/notifications/)) return '/';
            if (path.match(/\/messaging\/\d+$/)) return '/messaging';
            if (path === '/') return '/';
            var segments = path.split('/').filter(Boolean);
            if (segments.length <= 1) return '/';
            segments.pop();
            var parent = '/' + segments.join('/');
            var sectionParent = resolveSectionRoot(parent);
            return sectionParent || parent;
        }

        function getArchitectureParentFromPath(pathname) {
            var path = normalizePath(pathname || window.location.pathname);
            if (path === '/') return '/';
            var segments = path.split('/').filter(Boolean);
            if (segments.length <= 1) return '/';
            segments.pop();
            var parent = '/' + segments.join('/');
            var sectionParent = resolveSectionRoot(parent);
            return sectionParent || parent;
        }

        function patchHardcodedBackLinks() {
            var current = normalizePath(window.location.pathname);
            if (!current.startsWith('/prestataire/') && !current.startsWith('/client/') && !current.startsWith('/driver/')) return;
            var candidates = document.querySelectorAll('a[href]');
            if (!candidates || !candidates.length) return;
            var parentUrl = getArchitectureParentFromPath(current);
            if (!parentUrl || parentUrl === current) return;
            candidates.forEach(function (a) {
                try {
                    var hrefAttr = (a.getAttribute('href') || '').trim();
                    if (!hrefAttr) return;
                    var text = ((a.textContent || '') + ' ' + (a.getAttribute('aria-label') || '')).toLowerCase();
                    var looksLikeBack = text.indexOf('retour') !== -1 || text.indexOf('précédent') !== -1 || text.indexOf('precedent') !== -1;
                    if (!looksLikeBack) return;
                    var hrefUrl = new URL(hrefAttr, window.location.origin);
                    if (hrefUrl.origin !== window.location.origin) return;
                    var hrefPath = normalizePath(hrefUrl.pathname);
                    var isDashboardTarget = hrefPath === '/prestataire/dashboard' || hrefPath === '/client/dashboard' || hrefPath === '/driver/dashboard';
                    if (!isDashboardTarget) return;
                    a.setAttribute('href', parentUrl + (hrefUrl.search || ''));
                    a.setAttribute('data-back-patched', '1');
                    a.setAttribute('data-smart-back', 'true');
                    a.setAttribute('data-fallback', parentUrl + (hrefUrl.search || ''));
                } catch (e) {}
            });
        }

        function recordPageVisit() {
            var currentUrl = getCurrentComparableUrl();
            pushToNavStack(currentUrl);
        }

        function goBack(options) {
            options = options || {};
            var forceLogical = options.forceLogical === true || consumeLogicalBackState();
            if (!forceLogical) {
                var previousUrl = popFromNavStack();
                if (previousUrl && navigateToBackTarget(previousUrl, { queueLogical: true })) return true;
                trimCurrentFromNavStack();
                var referrerUrl = getSafeReferrerUrl();
                if (referrerUrl && navigateToBackTarget(referrerUrl, { queueLogical: true })) return true;
            }
            trimCurrentFromNavStack();
            var logicalParent = getLogicalParentUrl();
            if (logicalParent && navigateToBackTarget(logicalParent, { queueLogical: true })) return true;
            return navigateToBackTarget(options.fallback || '/');
        }

        window.TaPrestationNavigation = window.TaPrestationNavigation || {};
        window.TaPrestationNavigation.goBack = goBack;
        window.TaPrestationNavigation.peekPreviousUrl = peekPreviousFromNavStack;
        window.TaPrestationNavigation.getLogicalParentUrl = getLogicalParentUrl;
        window.TaPrestationNavigation.isBackLinkCandidate = isBackLinkCandidate;

        document.addEventListener('DOMContentLoaded', function () {
            applyPendingLogicalBackState();

            var btn = document.getElementById('app-global-back-btn');
            if (btn) {
                var isMobileView = window.innerWidth < 640;
                if (isRunningAsApp()) document.body.classList.add('app-mode');
                if (isMobileView) { btn.classList.remove('hidden'); btn.classList.add('flex'); }
                window.addEventListener('resize', function() {
                    var mobile = window.innerWidth < 640;
                    if (mobile) { btn.classList.remove('hidden'); btn.classList.add('flex'); }
                    else { btn.classList.add('hidden'); btn.classList.remove('flex'); }
                });
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    goBack({ fallback: btn.getAttribute('data-fallback') });
                });
            }

            recordPageVisit();
            patchHardcodedBackLinks();

            document.addEventListener('click', function (e) {
                var link = e.target.closest('a[href]');
                if (!link || !isBackLinkCandidate(link)) return;
                if (link.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                e.preventDefault();
                e.stopPropagation();
                var fallback = link.getAttribute('data-fallback') || link.getAttribute('href') || '/';
                goBack({ fallback: fallback });
            });
        });

        window.addEventListener('popstate', function() {
            setTimeout(recordPageVisit, 100);
        });
    })();
</script>
@endonce

<div
    id="nav-js-config"
    class="hidden"
    data-auth="{{ ($user ?? null) ? '1' : '0' }}"
    data-messaging-route="{{ $messagingRoute ?? '' }}"
></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navConfig = document.getElementById('nav-js-config');
    const isAuthenticated = navConfig?.dataset?.auth === '1';
    if (!isAuthenticated) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || null;
    const messagingRoute = (navConfig?.dataset?.messagingRoute || '').trim() || null;

    function updateMessageBadge() {
        if (!csrfToken) return;
        fetch('{{ route('messaging.unread-count') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'include'
        })
        .then(response => { if (response.status === 401 || response.status === 419) return null; return response.ok ? response.json() : null; })
        .then(data => {
            if (!data) return;
            const count = data.unread_count ?? 0;
            const desktopBadge = document.querySelector('#messaging-icon .absolute');
            if (desktopBadge) {
                if (count > 0) { desktopBadge.textContent = count > 99 ? '99+' : count; desktopBadge.classList.remove('hidden'); }
                else { desktopBadge.classList.add('hidden'); }
            }
            if (messagingRoute) {
                try {
                    const mobileBadges = document.querySelectorAll(`a[href="${messagingRoute}"] .absolute`);
                    if (mobileBadges && mobileBadges.length > 0) {
                        mobileBadges.forEach(badge => {
                            if (count > 0) { badge.textContent = count > 99 ? '99+' : count; badge.classList.remove('hidden'); }
                            else { badge.classList.add('hidden'); }
                        });
                    }
                } catch (e) {}
            }
        })
        .catch(() => {});
    }

    function updateNotificationBadge() {
        if (!csrfToken) return;
        fetch('{{ route('notifications.unread-count') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'include'
        })
        .then(response => { if (response.status === 401 || response.status === 419) return null; return response.ok ? response.json() : null; })
        .then(data => {
            if (!data) return;
            const count = data.count ?? 0;
            const desktopBadge = document.querySelector('#notification-badge');
            if (desktopBadge) {
                try {
                    if (count > 0) { desktopBadge.textContent = count > 99 ? '99+' : count; desktopBadge.classList.remove('hidden'); }
                    else { desktopBadge.classList.add('hidden'); }
                } catch (e) {}
            }
        })
        .catch(() => {});
    }

    setTimeout(function() { updateMessageBadge(); updateNotificationBadge(); }, 3000);
    setInterval(() => { updateMessageBadge(); updateNotificationBadge(); }, 60000);
});
</script>
