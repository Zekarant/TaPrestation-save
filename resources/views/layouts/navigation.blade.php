{{-- resources/views/layouts/navigation.blade.php --}}
@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();

    // Routes optionnelles : on teste leur existence pour éviter les 500 (RouteNotFoundException)
    $homeRoute              = Route::has('home')                          ? route('home')                          : url('/');
    $servicesRoute          = Route::has('services.index')                ? route('services.index')                : null;
    $equipmentRoute         = Route::has('equipment.index')               ? route('equipment.index')               : null;
    $urgentSalesRoute       = Route::has('urgent-sales.index')            ? route('urgent-sales.index')            : null;
    $videosFeedRoute        = Route::has('videos.feed')                   ? route('videos.feed')                   : null;

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
    $clientEquipmentRentals = Route::has('client.equipment-rental-requests.index') ? route('client.equipment-rental-requests.index') : null;
    $clientFollowsRoute     = Route::has('client.prestataire-follows.index') ? route('client.prestataire-follows.index') : null;

    $profileEditRoute       = Route::has('profile.edit')                  ? route('profile.edit')                  : null;
    $profileSettingsRoute   = Route::has('profile.settings')              ? route('profile.settings')              : null;

    $loginRoute             = Route::has('login')                         ? route('login')                         : null;
    $registerRoute          = Route::has('register')                      ? route('register')                      : null;
    $logoutRoute            = Route::has('logout')                        ? route('logout')                        : null;

    // Compteurs sécurisés (try/catch pour éviter les erreurs de relations / tables)
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

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    {{-- Mobile overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black bg-opacity-25 sm:hidden"
        @click="open = false"
        style="display:none;"
    ></div>

    {{-- Barre principale --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Logo --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ $homeRoute }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity duration-200">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-handshake text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900 hidden sm:block">TaPrestation</span>
                    </a>
                </div>

                {{-- Liens principaux desktop --}}
                <div class="hidden space-x-2 sm:-my-px sm:ml-10 sm:flex items-center">
                    <x-nav-link :href="$homeRoute" :active="request()->routeIs('home')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-blue-50">
                        <i class="fas fa-home mr-2 text-xs"></i>{{ __('Accueil') }}
                    </x-nav-link>

                    @if($servicesRoute)
                        <x-nav-link :href="$servicesRoute" :active="request()->routeIs('services.*')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-blue-50">
                            <i class="fas fa-briefcase mr-2 text-xs text-blue-500"></i>{{ __('Services') }}
                        </x-nav-link>
                    @endif

                    @if($equipmentRoute)
                        <x-nav-link :href="$equipmentRoute" :active="request()->routeIs('equipment.*')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 border-b-2 border-transparent hover:border-green-600 transition-all duration-200 rounded-lg hover:bg-green-50">
                            <i class="fas fa-tools mr-2 text-xs text-green-500"></i>{{ __('Matériel à louer') }}
                        </x-nav-link>
                    @endif

                    @if($urgentSalesRoute)
                        <x-nav-link :href="$urgentSalesRoute" :active="request()->routeIs('urgent-sales.*')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-red-600 border-b-2 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-red-50">
                            <i class="fas fa-bolt mr-2 text-xs text-red-500"></i>{{ __('Annonces') }}
                        </x-nav-link>
                    @endif

                    @if($videosFeedRoute)
                        <x-nav-link :href="$videosFeedRoute" :active="request()->routeIs('videos.feed')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-purple-600 border-b-2 border-transparent hover:border-purple-600 transition-all duration-200 rounded-lg hover:bg-purple-50">
                            <i class="fas fa-video mr-2 text-xs text-purple-500"></i>{{ __('Vidéos') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            {{-- Section droite desktop (auth / non-auth) --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-3">
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
                                @if($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $clientDashboardRoute)
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
                                @if($profileEditRoute)
                                    <x-dropdown-link :href="$profileEditRoute" class="flex items-center">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ __('Mon profil') }}
                                    </x-dropdown-link>
                                @endif

                                {{-- Menu prestataire --}}
                                @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire'))
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

                                    <div class="border-t border-gray-200 mt-3"></div>
                                @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('client'))
                                    {{-- Menu client --}}
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
                                            {{ __('Mes réservations') }}
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

                                    @if($clientFollowsRoute)
                                        <x-dropdown-link :href="$clientFollowsRoute" class="flex items-center">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            {{ __('Prestataires suivis') }}
                                        </x-dropdown-link>
                                    @endif
                                @endif
                            </div>

                            {{-- Déconnexion --}}
                            <div class="border-t border-gray-100">
                                @if($logoutRoute)
                                    <form method="POST" action="{{ $logoutRoute }}">
                                        @csrf
                                        <x-dropdown-link :href="$logoutRoute" class="flex items-center text-red-600 hover:bg-red-50"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            {{ __('Déconnexion') }}
                                        </x-dropdown-link>
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
            <div class="-mr-2 flex items-center space-x-2 sm:hidden">
                @auth
                    {{-- Messagerie mobile --}}
                    @if($messagingRoute)
                        <a href="{{ $messagingRoute }}" class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
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

                    {{-- Notifications mobile (simple badge si besoin) --}}
                    <div class="relative" x-data="{ openNotif:false }">
                        <button @click="openNotif = !openNotif" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a50.002 50.002 0 00-2.5-2.5V8a6 6 0 10-12 0v2.5c-1 1-2.5 2.5-2.5 2.5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Mini dropdown mobile pour notifications --}}
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

                                                if ($isEquipmentNotification) {
                                                    $iconClass = 'fa-tools';
                                                    $colorClass = 'text-green-500';
                                                    $bgClass = 'bg-green-100';
                                                } elseif ($isServiceNotification) {
                                                    $iconClass = 'fa-cogs';
                                                    $colorClass = 'text-blue-500';
                                                    $bgClass = 'bg-blue-100';
                                                } else {
                                                    $iconClass = 'fa-bell';
                                                    $colorClass = 'text-blue-600';
                                                    $bgClass = 'bg-blue-100';
                                                }

                                                $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                                $title = $data['title'] ?? 'Notification';
                                                $message = $data['message'] ?? '';
                                            @endphp

                                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                                <div class="w-8 h-8 {{ $bgClass }} rounded-full flex items-center justify-center flex-shrink-0">
                                                    <i class="fas {{ $iconClass }} {{ $colorClass }} text-sm"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
                                                    <p class="text-sm text-gray-500 line-clamp-2">{{ $message }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
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
                    class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-800 transition duration-150 ease-in-out shadow-sm border border-gray-200"
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
        :class="{'block': open, 'hidden': ! open}"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition duration-300 ease-in-out sm:hidden overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-full"
    >
        <div class="pt-6 pb-3 space-y-1 px-4">
            {{-- Bouton close --}}
            <div class="flex justify-end mb-4">
                <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <a href="{{ $homeRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                <i class="fas fa-home mr-3 text-blue-500 text-sm"></i>
                {{ __('Accueil') }}
            </a>

            @if($servicesRoute)
                <a href="{{ $servicesRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-briefcase mr-3 text-blue-500 text-sm"></i>
                    {{ __('Services') }}
                </a>
            @endif

            @if($equipmentRoute)
                <a href="{{ $equipmentRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-green-600 border-l-3 border-transparent hover:border-green-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-tools mr-3 text-green-500 text-sm"></i>
                    {{ __('Matériel à louer') }}
                </a>
            @endif

            @if($videosFeedRoute)
                <a href="{{ $videosFeedRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-purple-600 border-l-3 border-transparent hover:border-purple-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-video mr-3 text-purple-500 text-sm"></i>
                    {{ __('Vidéos') }}
                </a>
            @endif

            @if($urgentSalesRoute)
                <a href="{{ $urgentSalesRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-red-600 border-l-3 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-bolt mr-3 text-red-500 text-sm"></i>
                    {{ __('Annonces') }}
                </a>
            @endif
        </div>

        @auth
            {{-- Section utilisateur mobile --}}
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

                <div class="space-y-1 px-4 pb-4">
                    {{-- Dashboard mobile --}}
                    @if($user && method_exists($user, 'hasRole') && $user->hasRole('client') && $clientDashboardRoute)
                        <a href="{{ $clientDashboardRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 font-medium rounded-lg hover:bg-gray-50">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500 text-sm"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                    @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire') && $prestataireDashboardRoute)
                        <a href="{{ $prestataireDashboardRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 font-medium rounded-lg hover:bg-gray-50">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500 text-sm"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                    @elseif($defaultDashboardRoute)
                        <a href="{{ $defaultDashboardRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 font-medium rounded-lg hover:bg-gray-50">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500 text-sm"></i>
                            {{ __('Tableau de bord') }}
                        </a>
                    @endif

                    <div class="border-t border-gray-200 my-2"></div>

                    @if($profileEditRoute)
                        <a href="{{ $profileEditRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-user mr-3 text-gray-500 text-sm"></i>
                            {{ __('Mon profil') }}
                        </a>
                    @endif

                    @if($user && method_exists($user, 'hasRole') && $user->hasRole('prestataire'))
                        @if($prestataireServicesIndex)
                            <a href="{{ $prestataireServicesIndex }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-briefcase mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mes services') }}
                            </a>
                        @endif
                        @if($prestataireEquipmentIndex)
                            <a href="{{ $prestataireEquipmentIndex }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-tools mr-3 text-gray-500 text-sm"></i>
                                {{ __('Matériel à louer') }}
                            </a>
                        @endif
                        @if($prestataireVideosManage)
                            <a href="{{ $prestataireVideosManage }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-red-600 border-l-3 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-video mr-3 text-red-500 text-sm"></i>
                                {{ __('Mes Vidéos') }}
                            </a>
                        @endif
                        @if($messagingRoute)
                            <a href="{{ $messagingRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-comments mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mes messages') }}
                            </a>
                        @endif
                        @if($prestataireAgendaRoute)
                            <a href="{{ $prestataireAgendaRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-calendar-alt mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mon Agenda') }}
                            </a>
                        @endif
                        @if($prestataireUrgentSalesIndex)
                            <a href="{{ $prestataireUrgentSalesIndex }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-red-600 border-l-3 border-transparent hover:border-red-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-bolt mr-3 text-red-500 text-sm"></i>
                                {{ __('Voir mes ventes actives') }}
                            </a>
                        @endif
                    @elseif($user && method_exists($user, 'hasRole') && $user->hasRole('client'))
                        @if($messagingRoute)
                            <a href="{{ $messagingRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-comments mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mes messages') }}
                            </a>
                        @endif
                        @if($clientBookingsRoute)
                            <a href="{{ $clientBookingsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-calendar-alt mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mes réservations') }}
                            </a>
                        @endif
                        @if($clientEquipmentRentals)
                            <a href="{{ $clientEquipmentRentals }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-tools mr-3 text-gray-500 text-sm"></i>
                                {{ __('Mes locations') }}
                            </a>
                        @endif
                        @if($clientFollowsRoute)
                            <a href="{{ $clientFollowsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-heart mr-3 text-gray-500 text-sm"></i>
                                {{ __('Prestataires suivis') }}
                            </a>
                        @endif
                    @endif

                    @if($profileSettingsRoute)
                        <a href="{{ $profileSettingsRoute }}" class="flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 border-l-3 border-transparent hover:border-blue-600 transition-all duration-200 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-cog mr-3 text-gray-500 text-sm"></i>
                            {{ __('Paramètres du compte') }}
                        </a>
                    @endif

                    {{-- Déconnexion mobile --}}
                    @if($logoutRoute)
                        <form method="POST" action="{{ $logoutRoute }}" class="mt-4">
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
            {{-- Mobile invité --}}
            <div class="pt-4 pb-4 border-t border-gray-200 px-4">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Si l'utilisateur est connecté, on tente d'actualiser les badges, sinon on ne fait rien.
    @if($user)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || null;
        const messagingRoute = @json($messagingRoute);

        function updateMessageBadge() {
            if (!csrfToken) return;
            fetch('/messaging/unread-count', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data) return;
                const count = data.unread_count ?? 0;

                // Desktop badge
                const desktopBadge = document.querySelector('#messaging-icon .absolute');
                if (desktopBadge) {
                    if (count > 0) {
                        desktopBadge.textContent = count > 99 ? '99+' : count;
                        desktopBadge.classList.remove('hidden');
                    } else {
                        desktopBadge.classList.add('hidden');
                    }
                }

                // Mobile badges (liens vers la messagerie)
                if (messagingRoute) {
                    const mobileBadges = document.querySelectorAll(`a[href="${messagingRoute}"] .absolute`);
                    mobileBadges.forEach(badge => {
                        if (count > 0) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    });
                }
            })
            .catch(() => {});
        }

        function updateNotificationBadge() {
            if (!csrfToken) return;
            fetch('/notifications/unread-count', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data) return;
                const count = data.count ?? 0;

                // Exemple : si tu ajoutes un badge notifs avec id="notification-badge", il sera mis à jour ici.
                const desktopBadge = document.querySelector('#notification-badge');
                if (desktopBadge) {
                    if (count > 0) {
                        desktopBadge.textContent = count > 99 ? '99+' : count;
                        desktopBadge.classList.remove('hidden');
                    } else {
                        desktopBadge.classList.add('hidden');
                    }
                }
            })
            .catch(() => {});
        }

        // Initial
        updateMessageBadge();
        updateNotificationBadge();

        // Toutes les 30s
        setInterval(() => {
            updateMessageBadge();
            updateNotificationBadge();
        }, 30000);
    @endif
});
</script>
