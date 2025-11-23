@extends('layouts.app')

@section('title', 'Centre d\'aide Prestataires - TaPrestation')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
    <!-- En-tête -->
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Centre d'aide Prestataires</h1>
                <p class="text-gray-600 text-sm sm:text-base">Tout ce que vous devez savoir pour réussir sur TaPrestation</p>
            </div>
            <a href="{{ route('prestataire.dashboard') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="mb-4 sm:mb-6">
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" 
                       placeholder="Rechercher dans l'aide..." 
                       class="w-full px-3 py-2 sm:px-4 sm:py-3 pl-10 sm:pl-12 pr-4 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections d'aide -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Profile Management -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Gestion du profil</h3>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="space-y-3 sm:space-y-4">
                    <div class="border-l-4 border-blue-200 pl-3 sm:pl-4 hover:border-blue-400 transition-colors">
                        <a href="#profile-completion" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors mb-1 text-sm sm:text-base">Compléter votre profil</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Conseils pour créer un profil attractif et complet</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-green-200 pl-3 sm:pl-4 hover:border-green-400 transition-colors">
                        <a href="#portfolio" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-green-600 transition-colors mb-1 text-sm sm:text-base">Ajouter votre portfolio</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Comment mettre en valeur vos réalisations</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-purple-200 pl-3 sm:pl-4 hover:border-purple-400 transition-colors">
                        <a href="#certifications" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors mb-1 text-sm sm:text-base">Ajouter des certifications</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Importance des certifications pour votre crédibilité</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Management -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Gestion des services</h3>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="space-y-3 sm:space-y-4">
                    <div class="border-l-4 border-blue-200 pl-3 sm:pl-4 hover:border-blue-400 transition-colors">
                        <a href="#create-service" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors mb-1 text-sm sm:text-base">Créer un service</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Guide étape par étape pour publier vos services</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-green-200 pl-3 sm:pl-4 hover:border-green-400 transition-colors">
                        <a href="#pricing" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-green-600 transition-colors mb-1 text-sm sm:text-base">Fixer vos tarifs</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Conseils pour une tarification compétitive</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-purple-200 pl-3 sm:pl-4 hover:border-purple-400 transition-colors">
                        <a href="#packages" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors mb-1 text-sm sm:text-base">Créer des packages</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Offrir plusieurs options à vos clients</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Management -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-gray-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Gestion des réservations</h3>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="space-y-3 sm:space-y-4">
                    <div class="border-l-4 border-blue-200 pl-3 sm:pl-4 hover:border-blue-400 transition-colors">
                        <a href="#booking-process" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors mb-1 text-sm sm:text-base">Processus de réservation</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Comment gérer les demandes de réservation</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-green-200 pl-3 sm:pl-4 hover:border-green-400 transition-colors">
                        <a href="#calendar" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-green-600 transition-colors mb-1 text-sm sm:text-base">Calendrier de disponibilité</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Gérer vos disponibilités efficacement</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-purple-200 pl-3 sm:pl-4 hover:border-purple-400 transition-colors">
                        <a href="#cancellations" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors mb-1 text-sm sm:text-base">Annulations et modifications</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Politique d'annulation et gestion des imprévus</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communication -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-yellow-50 to-amber-50 border-b border-gray-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Communication</h3>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="space-y-3 sm:space-y-4">
                    <div class="border-l-4 border-blue-200 pl-3 sm:pl-4 hover:border-blue-400 transition-colors">
                        <a href="#messaging" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors mb-1 text-sm sm:text-base">Messagerie intégrée</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Communiquer efficacement avec vos clients</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-green-200 pl-3 sm:pl-4 hover:border-green-400 transition-colors">
                        <a href="#negotiation" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-green-600 transition-colors mb-1 text-sm sm:text-base">Négocier les tarifs</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Conseils pour des négociations fructueuses</p>
                        </a>
                    </div>
                    <div class="border-l-4 border-purple-200 pl-3 sm:pl-4 hover:border-purple-400 transition-colors">
                        <a href="#feedback" class="block group">
                            <h4 class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors mb-1 text-sm sm:text-base">Gérer les retours</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Répondre aux avis et améliorer votre service</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6 sm:mb-8">
        <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-orange-50 to-red-50 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Questions fréquentes</h3>
            </div>
        </div>
        
        <div class="p-4 sm:p-6">
            <div class="space-y-4 sm:space-y-6">
                <div class="border-b border-gray-200 pb-4 sm:pb-6 last:border-b-0 last:pb-0">
                    <button class="flex items-center justify-between w-full text-left focus:outline-none group" 
                            onclick="toggleFaq(0)">
                        <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors pr-2 sm:pr-4 text-sm sm:text-base">Combien de temps pour être vérifié ?</h4>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transform transition-transform duration-200" 
                             id="faq-icon-0" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="mt-2 sm:mt-3 text-gray-600 hidden" id="faq-answer-0">
                        <p class="text-xs sm:text-sm">Le processus de vérification prend généralement entre 24 et 48 heures une fois que vous avez soumis tous les documents requis. Assurez-vous de fournir des documents clairs et complets pour accélérer le processus.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4 sm:pb-6 last:border-b-0 last:pb-0">
                    <button class="flex items-center justify-between w-full text-left focus:outline-none group" 
                            onclick="toggleFaq(1)">
                        <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors pr-2 sm:pr-4 text-sm sm:text-base">Quels sont les frais de commission ?</h4>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transform transition-transform duration-200" 
                             id="faq-icon-1" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="mt-2 sm:mt-3 text-gray-600 hidden" id="faq-answer-1">
                        <p class="text-xs sm:text-sm">Nous prélevons une commission de 10% sur chaque prestation réalisée via notre plateforme. Ce taux peut varier selon les catégories de services. Les frais sont déduits automatiquement lors du paiement par le client.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4 sm:pb-6 last:border-b-0 last:pb-0">
                    <button class="flex items-center justify-between w-full text-left focus:outline-none group" 
                            onclick="toggleFaq(2)">
                    <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors pr-2 sm:pr-4 text-sm sm:text-base">Comment retirer mes gains ?</h4>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transform transition-transform duration-200" 
                             id="faq-icon-2" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="mt-2 sm:mt-3 text-gray-600 hidden" id="faq-answer-2">
                        <p class="text-xs sm:text-sm">Vous pouvez retirer vos gains via votre tableau de bord, section "Paiements". Les retraits sont effectués par virement bancaire et prennent généralement 3 à 5 jours ouvrables pour être traités.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4 sm:pb-6 last:border-b-0 last:pb-0">
                    <button class="flex items-center justify-between w-full text-left focus:outline-none group" 
                            onclick="toggleFaq(3)">
                        <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors pr-2 sm:pr-4 text-sm sm:text-base">Puis-je travailler avec plusieurs clients simultanément ?</h4>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transform transition-transform duration-200" 
                             id="faq-icon-3" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="mt-2 sm:mt-3 text-gray-600 hidden" id="faq-answer-3">
                        <p class="text-xs sm:text-sm">Oui, vous pouvez gérer plusieurs projets simultanément. Utilisez notre système de calendrier pour organiser vos disponibilités et éviter les conflits de planning.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact support -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
        <div class="px-4 py-6 sm:px-6 sm:py-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-white bg-opacity-20 text-white mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                </svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-white mb-2">Besoin d'aide supplémentaire ?</h3>
            <p class="text-blue-100 mb-4 sm:mb-6 text-sm sm:text-base">Notre équipe support est là pour vous aider à réussir</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="{{ route('messaging.index') }}" class="inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-medium text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Chat en direct
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(index) {
    const answer = document.getElementById(`faq-answer-${index}`);
    const icon = document.getElementById(`faq-icon-${index}`);
    
    if (answer.classList.contains('hidden')) {
        answer.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        answer.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection