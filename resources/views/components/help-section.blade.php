{{-- 
    Composant d'aide contextuelle
    Usage: <x-help-section page="driver.dashboard" />
    
    L'aide est masquée automatiquement pour les utilisateurs ayant passé plus de 150 heures sur le site.
    Le temps est tracké via le middleware TrackUserTime.
--}}
@props(['page' => '', 'collapsed' => true, 'forceShow' => false])

@php
use App\Http\Middleware\TrackUserTime;

// Vérifier si l'utilisateur expérimenté (150h+) doit voir l'aide masquée
$shouldHideHelp = false;
if (auth()->check() && !$forceShow) {
    $shouldHideHelp = TrackUserTime::shouldHideHelp();
}

// Si l'aide doit être masquée, ne pas afficher le composant
if ($shouldHideHelp) {
    return;
}

$helpContent = [
    // ========================================
    // PAGES LIVREUR
    // ========================================
    'driver.dashboard' => [
        'title' => '🏠 Dashboard Livreur',
        'description' => 'Votre tableau de bord central pour gérer vos livraisons en temps réel.',
        'features' => [
            '📍 Voir votre position en temps réel sur la carte',
            '📦 Consulter les commandes disponibles à proximité',
            '🔔 Recevoir des notifications de nouvelles commandes',
            '💰 Suivre vos gains de la journée',
            '🔄 Basculer votre disponibilité ON/OFF',
        ],
        'tips' => [
            '💡 Activez votre statut "Disponible" aux heures de pointe (12h-14h, 19h-21h) pour maximiser vos courses',
            '💡 Restez proche des zones avec beaucoup de restaurants pour recevoir plus de commandes',
            '💡 Acceptez rapidement les commandes - les prestataires préfèrent les livreurs réactifs',
            '💡 Gardez votre téléphone chargé et la géolocalisation activée',
        ],
        'links' => [
            ['name' => 'Voir la carte', 'route' => 'driver.map', 'icon' => '🗺️'],
            ['name' => 'Mes tarifs', 'route' => 'driver.pricing', 'icon' => '💰'],
            ['name' => 'Statistiques', 'route' => 'driver.stats', 'icon' => '📊'],
        ],
    ],
    
    'driver.map' => [
        'title' => '🗺️ Carte Interactive',
        'description' => 'Visualisez les commandes disponibles et planifiez vos itinéraires.',
        'features' => [
            '📍 Voir les restaurants avec commandes en attente',
            '🛣️ Calculer l\'itinéraire optimal vers le client',
            '⏱️ Estimer le temps de trajet',
            '📦 Voir les détails de chaque commande sur la carte',
        ],
        'tips' => [
            '💡 Utilisez le bouton de recentrage pour revenir à votre position',
            '💡 Zoomez pour voir les détails des zones denses',
            '💡 Cliquez sur un marqueur pour voir les infos de la commande',
            '💡 Activez le mode navigation pour suivre l\'itinéraire en temps réel',
        ],
    ],
    
    'driver.pricing' => [
        'title' => '💰 Mes Tarifs',
        'description' => 'Définissez vos propres tarifs de livraison pour vous démarquer.',
        'features' => [
            '💵 Frais de prise en charge (montant fixe par course)',
            '📏 Tarif au kilomètre',
            '⚡ Majoration heures de pointe (multiplicateur)',
            '🎁 Acceptation des pourboires',
        ],
        'tips' => [
            '💡 Un tarif compétitif augmente vos chances d\'être choisi',
            '💡 Activez les majorations heures de pointe pour gagner plus le soir',
            '💡 Les pourboires peuvent représenter jusqu\'à 20% de vos revenus',
            '💡 Consultez vos statistiques pour ajuster vos tarifs',
        ],
        'examples' => [
            'Tarif économique' => 'Base 2€ + 0.40€/km = Idéal pour beaucoup de courses courtes',
            'Tarif standard' => 'Base 3€ + 0.50€/km = Bon équilibre volume/rentabilité',
            'Tarif premium' => 'Base 4€ + 0.70€/km = Moins de courses mais mieux payées',
        ],
    ],
    
    'driver.stats' => [
        'title' => '📊 Statistiques',
        'description' => 'Analysez vos performances et optimisez votre activité.',
        'features' => [
            '📈 Graphiques de revenus par jour/semaine/mois',
            '🏆 Nombre de livraisons effectuées',
            '⭐ Votre note moyenne',
            '⏱️ Temps moyen de livraison',
            '📍 Zones les plus rentables',
        ],
        'tips' => [
            '💡 Comparez vos semaines pour identifier vos meilleures périodes',
            '💡 Une note élevée (4.5+) vous donne la priorité sur les commandes',
            '💡 Analysez vos zones rentables pour y rester plus souvent',
        ],
    ],
    
    'driver.deliveries' => [
        'title' => '📦 Historique des Livraisons',
        'description' => 'Consultez toutes vos livraisons passées et en cours.',
        'features' => [
            '📋 Liste complète de vos courses',
            '🔍 Filtrer par statut (livrées, en cours, échouées)',
            '💰 Voir le détail des gains par course',
            '📍 Revoir les adresses de livraison',
        ],
        'tips' => [
            '💡 Utilisez les filtres pour retrouver rapidement une livraison',
            '💡 Analysez vos courses échouées pour vous améliorer',
            '💡 Gardez trace de vos meilleures journées',
        ],
    ],
    
    'driver.deliveries.show' => [
        'title' => '📋 Détail Livraison',
        'description' => 'Toutes les informations sur une livraison spécifique.',
        'features' => [
            '📍 Adresse complète du client',
            '📞 Contact du client',
            '📦 Détail de la commande',
            '💰 Montant gagné',
            '⏱️ Temps de livraison',
        ],
    ],
    
    // ========================================
    // PAGES PRESTATAIRE - FOOD ORDERS
    // ========================================
    'prestataire.food-orders.dashboard' => [
        'title' => '🍽️ Dashboard Commandes',
        'description' => 'Gérez toutes vos commandes food en temps réel.',
        'features' => [
            '📊 Vue d\'ensemble des commandes du jour',
            '🔔 Alertes pour nouvelles commandes',
            '⏱️ Temps de préparation estimé',
            '🚗 Statut des livraisons en cours',
        ],
        'tips' => [
            '💡 Acceptez les commandes rapidement pour satisfaire vos clients',
            '💡 Marquez "Prêt" dès que la commande est emballée',
            '💡 Utilisez les notes client pour personnaliser les commandes',
            '💡 En cas de rush, priorisez les commandes les plus anciennes',
        ],
        'workflow' => [
            '1️⃣ Nouvelle commande → Accepter',
            '2️⃣ Préparer la commande',
            '3️⃣ Marquer "Prêt"',
            '4️⃣ Attribution livreur (auto ou manuel)',
            '5️⃣ Livreur récupère → En route',
            '6️⃣ Livré → Terminé ✅',
        ],
    ],
    
    'prestataire.food-orders.unified' => [
        'title' => '📋 Gestion Unifiée des Commandes',
        'description' => 'Interface tout-en-un pour gérer vos commandes et livraisons.',
        'features' => [
            '📊 Tableau de bord temps réel',
            '🔄 Mise à jour automatique des statuts',
            '👥 Voir les livreurs disponibles',
            '📍 Suivi GPS des livraisons',
        ],
        'tips' => [
            '💡 Utilisez les raccourcis clavier pour aller plus vite',
            '💡 Cliquez sur une commande pour voir tous les détails',
            '💡 Le code couleur indique l\'urgence (rouge = en retard)',
        ],
    ],
    
    'prestataire.food-orders.show' => [
        'title' => '📝 Détail Commande',
        'description' => 'Toutes les informations sur une commande spécifique.',
        'features' => [
            '👤 Informations client',
            '📦 Liste des articles commandés',
            '📍 Adresse de livraison',
            '💬 Notes spéciales du client',
            '🚗 Suivi du livreur',
        ],
        'actions' => [
            'Accepter' => 'Confirmez la prise en charge de la commande',
            'Préparer' => 'Indiquez que vous commencez la préparation',
            'Prêt' => 'La commande est prête, en attente du livreur',
            'Annuler' => 'Annuler la commande (avec motif obligatoire)',
        ],
    ],
    
    // ========================================
    // PAGES PRESTATAIRE - FOOD DELIVERY SETTINGS
    // ========================================
    'prestataire.food-delivery.settings' => [
        'title' => '⚙️ Paramètres de Livraison',
        'description' => 'Configurez comment vous souhaitez gérer vos livraisons.',
        'features' => [
            '🏠 Mode de livraison (interne/externe/les deux)',
            '⭐ Note minimum des livreurs',
            '💰 Taux de commission livreurs externes',
            '🔄 Attribution automatique ou manuelle',
        ],
        'modes' => [
            'Interne uniquement' => 'Seuls vos livreurs employés peuvent livrer. Contrôle total mais capacité limitée.',
            'Externe uniquement' => 'Utilisez les livreurs de la plateforme. Plus de flexibilité, moins de contrôle.',
            'Les deux' => 'Mixte : priorité aux internes, puis externes en renfort. Recommandé pour les périodes de rush.',
        ],
        'tips' => [
            '💡 Commencez en mode "externe" si vous n\'avez pas de livreurs',
            '💡 Passez en mode "les deux" quand vous embauchez vos premiers livreurs',
            '💡 Mettez une note minimum de 4.0 pour garantir la qualité',
            '💡 L\'attribution auto est idéale en période calme, manuelle en rush',
        ],
    ],
    
    // ========================================
    // PAGES PRESTATAIRE - GESTION LIVREURS
    // ========================================
    'prestataire.drivers.index' => [
        'title' => '👥 Gestion des Livreurs',
        'description' => 'Gérez vos relations avec les livreurs de la plateforme.',
        'features' => [
            '⭐ Noter les livreurs après chaque course',
            '✅ Ajouter des livreurs en favoris (whitelist)',
            '🚫 Bloquer les livreurs problématiques (blacklist)',
            '👔 Embaucher des livreurs comme employés internes',
            '📊 Voir les statistiques de chaque livreur',
        ],
        'tips' => [
            '💡 Notez les livreurs après chaque livraison pour améliorer le matching',
            '💡 Ajoutez en favoris les livreurs ponctuels et professionnels',
            '💡 Un livreur bloqué ne pourra plus prendre vos commandes',
            '💡 Embaucher un livreur = il devient prioritaire pour vos courses',
        ],
        'ratings' => [
            '⭐⭐⭐⭐⭐ (5)' => 'Excellent - À mettre en favoris !',
            '⭐⭐⭐⭐ (4)' => 'Très bien - Livreur fiable',
            '⭐⭐⭐ (3)' => 'Correct - Peut s\'améliorer',
            '⭐⭐ (2)' => 'Insuffisant - À surveiller',
            '⭐ (1)' => 'Problématique - Envisager le blocage',
        ],
    ],
    
    'prestataire.drivers.show' => [
        'title' => '👤 Profil Livreur',
        'description' => 'Consultez le profil complet d\'un livreur et gérez votre relation.',
        'features' => [
            '📊 Statistiques avec votre établissement',
            '⭐ Historique de vos notations',
            '📦 Liste des livraisons effectuées pour vous',
            '🚗 Type de véhicule',
            '📍 Zones de livraison préférées',
        ],
        'actions' => [
            'Noter' => 'Donnez une note après une livraison',
            'Ajouter aux favoris' => 'Ce livreur sera prioritaire pour vos commandes',
            'Bloquer' => 'Ce livreur ne pourra plus prendre vos commandes',
            'Embaucher' => 'Proposez-lui de devenir livreur interne',
        ],
    ],
    
    // ========================================
    // PAGES PRESTATAIRE - LOGISTICS
    // ========================================
    'prestataire.logistics.dashboard' => [
        'title' => '🚚 Dashboard Logistique',
        'description' => 'Vue d\'ensemble de toutes vos opérations de livraison.',
        'features' => [
            '📊 KPIs en temps réel',
            '🗺️ Carte des livraisons en cours',
            '👥 Livreurs disponibles',
            '📈 Statistiques de performance',
        ],
        'tips' => [
            '💡 Surveillez le temps moyen de livraison',
            '💡 Identifiez les livreurs les plus performants',
            '💡 Analysez les zones avec le plus de demande',
        ],
    ],
    
    'prestataire.logistics.index' => [
        'title' => '📦 Liste des Livraisons',
        'description' => 'Gérez toutes vos livraisons en cours et passées.',
        'features' => [
            '📋 Liste complète des livraisons',
            '🔍 Filtres par statut/date/livreur',
            '⚡ Actions rapides (marquer livré, etc.)',
            '📍 Suivi GPS en temps réel',
        ],
        'statuses' => [
            '🔵 En attente' => 'Commande prête, en attente d\'un livreur',
            '🟡 Attribuée' => 'Un livreur a accepté la course',
            '🟠 En route' => 'Le livreur est parti vers le client',
            '🟢 Livrée' => 'Livraison terminée avec succès',
            '🔴 Échouée' => 'Problème lors de la livraison',
        ],
    ],
    
    'prestataire.logistics.show' => [
        'title' => '📋 Détail Livraison',
        'description' => 'Suivi détaillé d\'une livraison spécifique.',
        'features' => [
            '📍 Suivi GPS en direct',
            '👤 Infos client et livreur',
            '📞 Contact direct',
            '📜 Historique des événements',
        ],
        'actions' => [
            'Réattribuer' => 'Changer de livreur si nécessaire',
            'Marquer livré' => 'Confirmer manuellement la livraison',
            'Signaler problème' => 'Documenter un incident',
        ],
    ],
    
    'prestataire.logistics.create' => [
        'title' => '➕ Nouvelle Livraison',
        'description' => 'Créez une demande de livraison manuelle.',
        'features' => [
            '📍 Adresse de retrait et livraison',
            '📦 Description du colis',
            '⏰ Créneau souhaité',
            '💰 Estimation du coût',
        ],
        'tips' => [
            '💡 Soyez précis sur l\'adresse pour éviter les retards',
            '💡 Indiquez le poids/taille pour un meilleur matching véhicule',
            '💡 Ajoutez des instructions spéciales si nécessaire',
        ],
    ],
    
    'prestataire.logistics.drivers' => [
        'title' => '👥 Mes Livreurs',
        'description' => 'Gérez votre équipe de livreurs.',
        'features' => [
            '📋 Liste des livreurs disponibles',
            '🟢 Statut en temps réel',
            '📊 Performance de chaque livreur',
            '📍 Position actuelle',
        ],
    ],
    
    'prestataire.logistics.zones' => [
        'title' => '📍 Zones de Livraison',
        'description' => 'Définissez vos zones de couverture.',
        'features' => [
            '🗺️ Dessiner des zones sur la carte',
            '💰 Tarifs par zone',
            '⏱️ Temps de livraison estimé',
            '🚫 Zones exclues',
        ],
        'tips' => [
            '💡 Commencez par une zone proche de votre établissement',
            '💡 Élargissez progressivement selon votre capacité',
            '💡 Augmentez les tarifs pour les zones éloignées',
        ],
    ],
    
    'prestataire.logistics.reports' => [
        'title' => '📈 Rapports',
        'description' => 'Analysez vos performances de livraison.',
        'features' => [
            '📊 Graphiques de performance',
            '💰 Revenus et coûts',
            '⏱️ Temps moyens',
            '📥 Export des données',
        ],
    ],
    
    // ========================================
    // PAGES PRESTATAIRE - DELIVERY (Externe)
    // ========================================
    'prestataire.delivery.index' => [
        'title' => '📦 Expéditions',
        'description' => 'Gérez vos envois via transporteurs externes (Colissimo, UPS, etc.).',
        'features' => [
            '📋 Liste des expéditions',
            '🔍 Suivi des colis',
            '🏷️ Génération d\'étiquettes',
            '💰 Comparaison des tarifs',
        ],
        'tips' => [
            '💡 Comparez les transporteurs avant d\'expédier',
            '💡 Utilisez le suivi pour informer vos clients',
            '💡 Imprimez les étiquettes directement',
        ],
        'note' => '⚠️ Cette section concerne les colis/marchandises envoyés par transporteur externe, pas la livraison food.',
    ],
    
    'prestataire.delivery.create' => [
        'title' => '➕ Nouvelle Expédition',
        'description' => 'Créez une nouvelle expédition via transporteur.',
        'features' => [
            '📍 Adresses expéditeur/destinataire',
            '📦 Dimensions et poids',
            '🚚 Choix du transporteur',
            '🏷️ Génération étiquette',
        ],
    ],
];

$content = $helpContent[$page] ?? null;
@endphp

@if($content)
<div x-data="{ open: {{ $collapsed ? 'false' : 'true' }} }" class="mb-6">
    {{-- Bouton toggle --}}
    <button @click="open = !open" 
            class="w-full flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 border border-blue-200 rounded-xl px-4 py-3 transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="text-2xl">💡</span>
            <span class="font-medium text-blue-900">Aide & Astuces</span>
            @if(isset($content['title']))
                <span class="text-blue-600 text-sm hidden sm:inline">- {{ $content['title'] }}</span>
            @endif
        </div>
        <svg class="w-5 h-5 text-blue-500 transform transition-transform duration-200" 
             :class="{ 'rotate-180': open }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    {{-- Contenu --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="mt-3 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        
        <div class="p-5 space-y-5">
            {{-- Description --}}
            @if(isset($content['description']))
            <div class="flex items-start gap-3">
                <span class="text-xl">📖</span>
                <p class="text-gray-700">{{ $content['description'] }}</p>
            </div>
            @endif
            
            {{-- Note importante --}}
            @if(isset($content['note']))
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                <p class="text-amber-800 text-sm">{{ $content['note'] }}</p>
            </div>
            @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Fonctionnalités --}}
                @if(isset($content['features']))
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <span>🎯</span> Fonctionnalités
                    </h4>
                    <ul class="space-y-2">
                        @foreach($content['features'] as $feature)
                        <li class="text-sm text-gray-600 flex items-start gap-2">
                            <span class="text-green-500 mt-0.5">✓</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                {{-- Astuces --}}
                @if(isset($content['tips']))
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                        <span>💡</span> Astuces
                    </h4>
                    <ul class="space-y-2">
                        @foreach($content['tips'] as $tip)
                        <li class="text-sm text-blue-700">{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            
            {{-- Workflow --}}
            @if(isset($content['workflow']))
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-semibold text-green-900 mb-3 flex items-center gap-2">
                    <span>🔄</span> Processus
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($content['workflow'] as $step)
                    <span class="bg-white px-3 py-1 rounded-full text-sm text-green-700 border border-green-200">
                        {{ $step }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Modes --}}
            @if(isset($content['modes']))
            <div class="bg-purple-50 rounded-lg p-4">
                <h4 class="font-semibold text-purple-900 mb-3 flex items-center gap-2">
                    <span>🔧</span> Modes disponibles
                </h4>
                <div class="space-y-3">
                    @foreach($content['modes'] as $mode => $desc)
                    <div class="bg-white rounded-lg p-3 border border-purple-100">
                        <p class="font-medium text-purple-800">{{ $mode }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Statuts --}}
            @if(isset($content['statuses']))
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <span>📊</span> Statuts
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($content['statuses'] as $status => $desc)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-medium">{{ $status }}</span>
                        <span class="text-gray-500">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Ratings --}}
            @if(isset($content['ratings']))
            <div class="bg-yellow-50 rounded-lg p-4">
                <h4 class="font-semibold text-yellow-900 mb-3 flex items-center gap-2">
                    <span>⭐</span> Guide des notes
                </h4>
                <div class="space-y-2">
                    @foreach($content['ratings'] as $rating => $desc)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-yellow-800">{{ $rating }}</span>
                        <span class="text-gray-600">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Actions --}}
            @if(isset($content['actions']))
            <div class="bg-indigo-50 rounded-lg p-4">
                <h4 class="font-semibold text-indigo-900 mb-3 flex items-center gap-2">
                    <span>⚡</span> Actions disponibles
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($content['actions'] as $action => $desc)
                    <div class="bg-white rounded-lg p-3 border border-indigo-100">
                        <p class="font-medium text-indigo-700">{{ $action }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Exemples --}}
            @if(isset($content['examples']))
            <div class="bg-teal-50 rounded-lg p-4">
                <h4 class="font-semibold text-teal-900 mb-3 flex items-center gap-2">
                    <span>📋</span> Exemples
                </h4>
                <div class="space-y-2">
                    @foreach($content['examples'] as $example => $desc)
                    <div class="bg-white rounded-lg p-3 border border-teal-100">
                        <p class="font-medium text-teal-700">{{ $example }}</p>
                        <p class="text-sm text-gray-600">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Liens utiles --}}
            @if(isset($content['links']))
            <div class="border-t border-gray-200 pt-4">
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <span>🔗</span> Liens utiles
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($content['links'] as $link)
                    @if(Route::has($link['route']))
                    <a href="{{ route($link['route']) }}" 
                       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors">
                        <span>{{ $link['icon'] ?? '→' }}</span>
                        {{ $link['name'] }}
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
