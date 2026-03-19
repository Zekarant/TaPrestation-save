{{--
    Welcome Banner Component - For first-time users or feature introductions
    Usage: <x-welcome-banner :user="$user" page="dashboard" />
--}}

@props([
    'user' => null,
    'page' => 'default',
    'dismissKey' => null
])

@php
$userName = $user?->prenom ?? $user?->name ?? 'Utilisateur';
$dismissStorageKey = $dismissKey ?? 'welcome_' . $page;

// Define welcome content for different pages
$welcomeContent = [
    'dashboard' => [
        'title' => 'Bienvenue sur votre tableau de bord ! 🎉',
        'subtitle' => 'Tout ce dont vous avez besoin pour gérer votre activité',
        'features' => [
            ['icon' => '📊', 'title' => 'Statistiques', 'desc' => 'Suivez vos performances en temps réel'],
            ['icon' => '📅', 'title' => 'Réservations', 'desc' => 'Gérez vos rendez-vous facilement'],
            ['icon' => '💬', 'title' => 'Messages', 'desc' => 'Communiquez avec vos clients'],
            ['icon' => '⭐', 'title' => 'Avis', 'desc' => 'Consultez les retours clients'],
        ]
    ],
    'services' => [
        'title' => 'Gérez vos services 🛠️',
        'subtitle' => 'Créez et personnalisez vos offres',
        'features' => [
            ['icon' => '➕', 'title' => 'Créer', 'desc' => 'Ajoutez de nouveaux services'],
            ['icon' => '✏️', 'title' => 'Modifier', 'desc' => 'Mettez à jour vos tarifs'],
            ['icon' => '📸', 'title' => 'Photos', 'desc' => 'Ajoutez des visuels attractifs'],
            ['icon' => '🎯', 'title' => 'Catégories', 'desc' => 'Organisez vos services'],
        ]
    ],
    'bookings' => [
        'title' => 'Vos réservations 📅',
        'subtitle' => 'Gérez votre planning efficacement',
        'features' => [
            ['icon' => '✅', 'title' => 'Confirmer', 'desc' => 'Acceptez les demandes'],
            ['icon' => '📱', 'title' => 'Notifier', 'desc' => 'Informez vos clients'],
            ['icon' => '🔄', 'title' => 'Reprogrammer', 'desc' => 'Modifiez les horaires'],
            ['icon' => '📋', 'title' => 'Historique', 'desc' => 'Consultez le passé'],
        ]
    ],
    'agenda' => [
        'title' => 'Votre agenda 📆',
        'subtitle' => 'Organisez votre temps de travail',
        'features' => [
            ['icon' => '🕐', 'title' => 'Horaires', 'desc' => 'Définissez vos disponibilités'],
            ['icon' => '🚫', 'title' => 'Blocage', 'desc' => 'Bloquez des créneaux'],
            ['icon' => '🔁', 'title' => 'Récurrence', 'desc' => 'Planifiez sur plusieurs semaines'],
            ['icon' => '📍', 'title' => 'Lieux', 'desc' => 'Gérez plusieurs adresses'],
        ]
    ],
    'client-dashboard' => [
        'title' => 'Bienvenue sur TaPrestation ! 👋',
        'subtitle' => 'Trouvez les meilleurs prestataires près de chez vous',
        'features' => [
            ['icon' => '🔍', 'title' => 'Rechercher', 'desc' => 'Trouvez le service idéal'],
            ['icon' => '📅', 'title' => 'Réserver', 'desc' => 'Prenez rendez-vous en ligne'],
            ['icon' => '💬', 'title' => 'Discuter', 'desc' => 'Échangez avec les pros'],
            ['icon' => '⭐', 'title' => 'Évaluer', 'desc' => 'Partagez votre expérience'],
        ]
    ]
];

$content = $welcomeContent[$page] ?? $welcomeContent['dashboard'];
@endphp

<div x-data="{ 
        show: !localStorage.getItem('{{ $dismissStorageKey }}'),
        dismiss() {
            this.show = false;
            localStorage.setItem('{{ $dismissStorageKey }}', 'true');
        }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="welcome-banner mb-6">
    
    <div class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 rounded-3xl p-6 sm:p-8 text-white overflow-hidden shadow-xl">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative z-10">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold mb-2">
                        Bonjour {{ $userName }} ! 
                    </h2>
                    <h3 class="text-xl sm:text-2xl font-semibold text-white/90">
                        {{ $content['title'] }}
                    </h3>
                    <p class="text-white/70 mt-2">{{ $content['subtitle'] }}</p>
                </div>
                
                <button @click="dismiss()" 
                        class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Features grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                @foreach($content['features'] as $feature)
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 hover:bg-white/20 transition-all duration-200 hover:transform hover:scale-105">
                    <div class="text-3xl mb-2">{{ $feature['icon'] }}</div>
                    <h4 class="font-semibold text-sm">{{ $feature['title'] }}</h4>
                    <p class="text-xs text-white/70 mt-1">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
            
            <!-- Action buttons -->
            <div class="flex flex-wrap gap-3 mt-6">
                <button @click="dismiss()" 
                        class="px-6 py-2.5 bg-white text-indigo-600 rounded-xl font-semibold text-sm hover:bg-gray-100 transition-colors duration-200 shadow-lg">
                    C'est compris ! 👍
                </button>
                <a href="{{ route('prestataire.help') ?? '#' }}" 
                   class="px-6 py-2.5 bg-white/20 text-white rounded-xl font-semibold text-sm hover:bg-white/30 transition-colors duration-200">
                    Voir le guide complet
                </a>
            </div>
        </div>
    </div>
</div>
