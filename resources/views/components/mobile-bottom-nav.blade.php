{{-- resources/views/components/mobile-bottom-nav.blade.php --}}
@php
    use Illuminate\Support\Facades\Route;

    // Ne pas afficher la nav principale sur les pages driver (elles ont leur propre nav)
    $currentRoute = request()->route()?->getName() ?? '';
    if (str_starts_with($currentRoute, 'driver.')) {
        return;
    }

    $user = auth()->user();
    $homeRoute = Route::has('home') ? route('home') : url('/');
    $servicesRoute = Route::has('services.index') ? route('services.index') : null;
    $equipmentRoute = Route::has('equipment.index') ? route('equipment.index') : null;
    $videosFeedRoute = Route::has('videos.feed') ? route('videos.feed') : null;
    $foodExploreRoute = Route::has('food.explore') ? route('food.explore') : null;

    // Messagerie - détermine l'endpoint basé sur le rôle
    $messagingRoute = null;
    if ($user) {
        if ($user->hasRole('client') && Route::has('client.messaging.index')) {
            $messagingRoute = route('client.messaging.index');
        } elseif ($user->hasRole('prestataire') && Route::has('prestataire.messages.index')) {
            $messagingRoute = route('prestataire.messages.index');
        }
    }

    $prestataireAgendaRoute = null;
    $prestataireVideosCreate = null;
    if ($user && $user->hasRole('prestataire')) {
        if (Route::has('prestataire.agenda.index')) {
            $prestataireAgendaRoute = route('prestataire.agenda.index');
        }
        if (Route::has('prestataire.videos.create')) {
            $prestataireVideosCreate = route('prestataire.videos.create');
        }
    }

    $unreadMessagesCount = 0;
    if ($user && method_exists($user, 'receivedMessages')) {
        try {
            $unreadMessagesCount = $user->receivedMessages()->whereNull('read_at')->count();
        } catch (\Throwable $e) {
            $unreadMessagesCount = 0;
        }
    }

    $currentRoute = request()->route()?->getName() ?? '';
@endphp

@once
    <style>
        /* ─── MOBILE BOTTOM NAV — ESTHÉTIQUE TAPRESTATION ─── */
        #mobile-bottom-nav {
            background: rgba(237, 229, 216, 0.88) !important;
            backdrop-filter: blur(18px) saturate(1.4) !important;
            -webkit-backdrop-filter: blur(18px) saturate(1.4) !important;
            border-top: 1px solid rgba(15, 58, 134, 0.08) !important;
            box-shadow: 0 -4px 24px rgba(15, 58, 134, 0.06), 0 -1px 6px rgba(0, 0, 0, 0.03) !important;
            border-radius: 18px 18px 0 0 !important;
            overflow: hidden !important;
        }

        #mobile-bottom-nav .mobile-nav-link {
            font-family: 'Plus Jakarta Sans', -apple-system, system-ui, sans-serif;
            color: #6b7a8d !important;
            border-radius: 12px;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            min-width: 0;
        }

        #mobile-bottom-nav .mobile-nav-link:active {
            transform: scale(0.92);
        }

        /* ── État actif par couleur de thème ── */
        #mobile-bottom-nav .mobile-nav-link.is-active-blue {
            color: #0f3a86 !important;
            background: rgba(15, 58, 134, 0.10) !important;
        }

        #mobile-bottom-nav .mobile-nav-link.is-active-purple {
            color: #6c3fa0 !important;
            background: rgba(108, 63, 160, 0.10) !important;
        }

        #mobile-bottom-nav .mobile-nav-link.is-active-orange {
            color: #c46a10 !important;
            background: rgba(196, 106, 16, 0.10) !important;
        }

        #mobile-bottom-nav .mobile-nav-link.is-active-red {
            color: #b83028 !important;
            background: rgba(184, 48, 40, 0.10) !important;
        }

        /* ── Hover / focus ── */
        #mobile-bottom-nav .mobile-nav-link:not([class*="is-active"]):hover,
        #mobile-bottom-nav .mobile-nav-link:not([class*="is-active"]):focus-visible {
            color: #0f3a86 !important;
            background: rgba(15, 58, 134, 0.06) !important;
        }

        /* ── Labels ── */
        #mobile-bottom-nav .mobile-nav-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            margin-top: 3px;
            line-height: 1;
        }

        /* ── SVG icons ── */
        #mobile-bottom-nav .mobile-nav-link svg {
            width: 22px;
            height: 22px;
            stroke-width: 1.8;
            transition: transform 0.22s ease;
        }

        #mobile-bottom-nav .mobile-nav-link:active svg {
            transform: scale(0.9);
        }

        /* ── Badge (point rouge pour notifs) ── */
        #mobile-bottom-nav .mobile-nav-badge {
            width: 8px;
            height: 8px;
            background: #e74c3c;
            border: 2px solid rgba(237, 229, 216, 0.95);
            border-radius: 50%;
            position: absolute;
            top: -2px;
            right: -2px;
            box-shadow: 0 1px 4px rgba(231, 76, 60, 0.35);
        }

        /* ── Emoji sizing ── */
        #mobile-bottom-nav .mobile-nav-emoji {
            font-size: 1.35rem;
            line-height: 1;
        }

        /* ── Safe area + layout fixes ── */
        @media (max-width: 640px) {
            main {
                padding-bottom: calc(88px + env(safe-area-inset-bottom)) !important;
                scroll-padding-bottom: calc(88px + env(safe-area-inset-bottom));
            }

            #mobile-bottom-nav {
                position: fixed !important;
                left: 8px !important;
                right: 8px !important;
                bottom: -2px !important;
                padding-bottom: max(calc(env(safe-area-inset-bottom) - 4px), 0px) !important;
                width: auto !important;
                max-width: calc(100vw - 16px) !important;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
                will-change: transform;
                border-radius: 20px !important;
                box-shadow: 0 -4px 18px rgba(15, 58, 134, 0.05), 0 12px 28px rgba(0, 0, 0, 0.08) !important;
            }

            body.app-mode #mobile-bottom-nav {
                bottom: calc(0px - env(safe-area-inset-bottom)) !important;
                padding-bottom: env(safe-area-inset-bottom) !important;
            }

            #mobile-bottom-nav .mobile-nav-link {
                border-radius: 10px;
            }

            #mobile-bottom-nav .mobile-nav-link svg {
                width: 20px;
                height: 20px;
            }

            #mobile-bottom-nav .mobile-nav-label {
                font-size: 0.61rem;
            }

            #mobile-bottom-nav .mobile-nav-emoji {
                font-size: 1.2rem;
            }
        }
    </style>
@endonce

<!-- Bottom Navigation Mobile - Only on mobile -->
<nav id="mobile-bottom-nav" class="sm:hidden fixed bottom-0 left-0 right-0 z-[90]">
    <div class="flex justify-around items-center h-[60px] px-1">
        <!-- Home -->
        <a href="{{ $homeRoute }}"
            class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2 {{ request()->routeIs('home') ? 'is-active-blue' : '' }}">
            <svg fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-3m0 0l7-4 7 4M5 7v10a1 1 0 001 1h5v-5h2v5h5a1 1 0 001-1V7M9 9h6m-6 4h6"></path>
            </svg>
            <span class="mobile-nav-label">Accueil</span>
        </a>

        <!-- Videos -->
        @if($videosFeedRoute)
            <a href="{{ $videosFeedRoute }}"
                class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2 {{ request()->routeIs('videos.feed') ? 'is-active-purple' : '' }}">
                <svg fill="{{ request()->routeIs('videos.feed') ? 'currentColor' : 'none' }}" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                <span class="mobile-nav-label">Vidéos</span>
            </a>
        @endif

        <!-- Food -->
        @if($foodExploreRoute)
            <a href="{{ $foodExploreRoute }}"
                class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2 {{ request()->routeIs('food.*') ? 'is-active-orange' : '' }}">
                <span class="mobile-nav-emoji">🍽️</span>
                <span class="mobile-nav-label">Food</span>
            </a>
        @endif

        <!-- Prestataire Agenda -->
        @if($user && $user->hasRole('prestataire') && $prestataireAgendaRoute)
            <a href="{{ $prestataireAgendaRoute }}"
                class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2 {{ request()->routeIs('prestataire.agenda*') ? 'is-active-orange' : '' }}">
                <svg fill="{{ request()->routeIs('prestataire.agenda*') ? 'currentColor' : 'none' }}" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="mobile-nav-label">Agenda</span>
            </a>
        @endif

        <!-- Prestataire Videos - Direct to Create -->
        @if($user && $user->hasRole('prestataire') && $prestataireVideosCreate)
            <a href="{{ $prestataireVideosCreate }}"
                class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2 {{ request()->routeIs('prestataire.videos*') ? 'is-active-red' : '' }}">
                <div class="relative">
                    <svg fill="{{ request()->routeIs('prestataire.videos*') ? 'currentColor' : 'none' }}"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="mobile-nav-badge"></span>
                </div>
                <span class="mobile-nav-label">Vidéos</span>
            </a>
        @endif

        <!-- Login/Register for non-authenticated users only -->
        @if(!$user)
            <a href="{{ route('login') }}"
                class="mobile-nav-link flex flex-col items-center justify-center flex-1 h-full py-2">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                    </path>
                </svg>
                <span class="mobile-nav-label">Connexion</span>
            </a>
        @endif
    </div>
</nav>

<script>
    (function () {
        var nav = document.getElementById('mobile-bottom-nav');
        if (!nav) return;

        var ticking = false;
        function stabilize() {
            nav.style.transform = 'translateY(0)';
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(stabilize);
                ticking = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', function () {
            nav.style.transform = 'translateY(0)';
        }, { passive: true });
    })();
</script>