@extends('layouts.app')

@section('title', 'Détails de la location')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('client.equipment-rentals.index') }}" class="text-gray-700 hover:text-blue-600">
                        Mes locations
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Location #{{ $rental->id }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        Location #{{ $rental->id }}
                    </h1>
                    <div class="flex items-center space-x-4">
                        @if($rental->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            ⏳ À démarrer
                        </span>
                        @elseif($rental->status === 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            En cours
                        </span>
                        @elseif($rental->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            Terminée
                        </span>
                        @elseif($rental->status === 'cancelled')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Annulée
                        </span>
                        @endif
                        
                        <span class="text-sm text-gray-500">
                            Créée le {{ $rental->created_at->format('d/m/Y à H:i') }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-4 md:mt-0 flex space-x-3">
                    @if($rental->status === 'pending')
                    <form method="POST" 
                          action="{{ route('client.equipment-rentals.confirm-receipt', $rental) }}" 
                          onsubmit="return confirm('Confirmez-vous avoir reçu cet équipement ?')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Confirmer réception
                        </button>
                    </form>
                    @endif
                    
                    @if($rental->status === 'active')
                    <form method="POST" 
                          action="{{ route('client.equipment-rentals.confirm-return', $rental) }}" 
                          onsubmit="return confirm('Confirmez-vous le retour de cet équipement ?')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Confirmer retour
                        </button>
                    </form>
                    @endif
                    
                    @if($rental->status === 'completed' && !$rental->client_review)
                    <button onclick="openReviewModal()" 
                            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Laisser un avis
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations de l'équipement -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Équipement loué</h2>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            @if($rental->equipment->photos && count($rental->equipment->photos) > 0)
                            <img src="{{ Storage::url($rental->equipment->photos[0]) }}" 
                                 alt="{{ $rental->equipment->name }}"
                                 class="w-24 h-24 object-cover rounded-lg cursor-pointer"
                                 onclick="showPhotoModal('{{ Storage::url($rental->equipment->photos[0]) }}', '{{ $rental->equipment->name }}')">
                            @else
                            <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                <a href="{{ route('equipment.show', $rental->equipment) }}" 
                                   class="hover:text-blue-600 transition-colors">
                                    {{ $rental->equipment->name }}
                                </a>
                            </h3>
                            <div class="space-y-1 text-sm text-gray-600">
                                @if($rental->equipment->brand || $rental->equipment->model)
                                <p><span class="font-medium">Marque/Modèle:</span> {{ $rental->equipment->brand }} {{ $rental->equipment->model }}</p>
                                @endif
                                <p><span class="font-medium">État:</span> {{ $rental->equipment->formatted_condition }}</p>
                                <p><span class="font-medium">Prix journalier:</span> {{ number_format($rental->equipment->daily_rate, 0) }}€</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails de la location -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Détails de la location</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">Période de location</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date de début:</span>
                                    <span class="font-medium">{{ $rental->start_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date de fin:</span>
                                    <span class="font-medium">{{ $rental->end_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Durée:</span>
                                    <span class="font-medium">{{ $rental->duration_days }} jour{{ $rental->duration_days > 1 ? 's' : '' }}</span>
                                </div>
                                @if($rental->actual_start_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Début effectif:</span>
                                    <span class="font-medium">{{ $rental->actual_start_date->format('d/m/Y H:i') }}</span>
                                </div>
                                @endif
                                @if($rental->actual_end_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fin effective:</span>
                                    <span class="font-medium">{{ $rental->actual_end_date->format('d/m/Y H:i') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        

                    </div>
                    
                    @if($rental->notes)
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-900 mb-3">Notes</h3>
                        <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-700">
                            {{ $rental->notes }}
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Problèmes signalés -->
                @if($rental->issues->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Problèmes signalés</h2>
                    <div class="space-y-4">
                        @foreach($rental->issues as $issue)
                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="font-medium text-red-900">{{ $issue->title }}</h3>
                                <span class="text-xs text-red-600">{{ $issue->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-red-800 text-sm mb-2">{{ $issue->description }}</p>
                            <div class="flex items-center text-xs text-red-600">
                                <span class="mr-2">Signalé par:</span>
                                <span class="font-medium">
                                    @if($issue->reported_by_client)
                                    Vous
                                    @else
                                    {{ $rental->equipment->prestataire->company_name ?? $rental->equipment->prestataire->first_name }}
                                    @endif
                                </span>
                            </div>
                            @if($issue->resolution)
                            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded">
                                <h4 class="font-medium text-green-900 text-sm mb-1">Résolution:</h4>
                                <p class="text-green-800 text-sm">{{ $issue->resolution }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Avis client -->
                @if($rental->client_review)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">⭐ Votre avis</h2>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                <span class="text-{{ $i <= $rental->client_review->rating ? 'yellow' : 'gray' }}-400 text-lg">⭐</span>
                                @endfor
                                <span class="ml-2 font-medium text-gray-900">{{ $rental->client_review->rating }}/5</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $rental->client_review->created_at->format('d/m/Y') }}</span>
                        </div>
                        <p class="text-gray-700">{{ $rental->client_review->comment }}</p>
                    </div>
                </div>
                @endif
                
                <!-- Historique -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Historique</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Location créée</p>
                                <p class="text-xs text-gray-500">{{ $rental->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($rental->actual_start_date)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Location démarrée</p>
                                <p class="text-xs text-gray-500">{{ $rental->actual_start_date->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($rental->actual_end_date)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Location terminée</p>
                                <p class="text-xs text-gray-500">{{ $rental->actual_end_date->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($rental->client_review)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Avis publié</p>
                                <p class="text-xs text-gray-500">{{ $rental->client_review->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Récapitulatif financier -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Récapitulatif</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Location ({{ $rental->duration_days }} jour{{ $rental->duration_days > 1 ? 's' : '' }}):</span>
                            <span class="font-medium">{{ number_format($rental->rental_amount, 0) }}€</span>
                        </div>
                        

                        
                        @if($rental->equipment->deposit_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Caution:</span>
                            <span class="font-medium">{{ number_format($rental->equipment->deposit_amount, 0) }}€</span>
                        </div>
                        @endif
                        
                        <hr class="my-3">
                        
                        <div class="flex justify-between text-lg font-semibold">
                            <span class="text-gray-900">Total payé:</span>
                            <span class="text-blue-600">{{ number_format($rental->total_amount, 0) }}€</span>
                        </div>
                        
                        @if($rental->equipment->deposit_amount > 0 && $rental->status === 'completed')
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Caution restituée:</span>
                            <span class="text-green-600 font-medium">{{ number_format($rental->equipment->deposit_amount, 0) }}€</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Informations du prestataire -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Prestataire</h3>
                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold">
                                {{ substr($rental->equipment->prestataire->company_name ?? $rental->equipment->prestataire->first_name, 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">
                                {{ $rental->equipment->prestataire->company_name ?? $rental->equipment->prestataire->first_name . ' ' . $rental->equipment->prestataire->last_name }}
                            </h4>
                            @if($rental->equipment->prestataire->address)
                            <p class="text-sm text-gray-600 mt-1">{{ $rental->equipment->prestataire->address }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-4 space-y-2">
                        <a href="mailto:{{ $rental->equipment->prestataire->email }}" 
                           class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 text-center block">
                            Contacter par email
                        </a>
                        
                        @if($rental->equipment->prestataire->phone)
                        <a href="tel:{{ $rental->equipment->prestataire->phone }}" 
                           class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 text-center block">
                            Appeler
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
                    <div class="space-y-3">
                        <a href="{{ route('equipment.show', $rental->equipment) }}" 
                           class="w-full px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 text-center block">
                            Voir l'équipement
                        </a>
                        
                        @if($rental->status === 'active')
                        <button onclick="openIssueModal()" 
                                class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                            Signaler un problème
                        </button>
                        @endif
                        
                        <a href="{{ route('equipment.index') }}" 
                           class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 text-center block">
                            Parcourir le matériel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'évaluation -->
@if($rental->status === 'completed' && !$rental->client_review)
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">⭐ Évaluer la location</h3>
            <form method="POST" action="{{ route('client.equipment-rentals.review', $rental) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Équipement: <span class="font-normal">{{ $rental->equipment->name }}</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
                        Note *
                    </label>
                    <div class="flex space-x-1" id="starRating">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" 
                                class="star text-2xl text-gray-300 hover:text-yellow-400 focus:outline-none" 
                                data-rating="{{ $i }}">
                            ⭐
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                </div>
                
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        Commentaire *
                    </label>
                    <textarea id="comment" 
                              name="comment" 
                              rows="4" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Partagez votre expérience avec cet équipement..."></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" 
                            onclick="closeReviewModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Publier l'avis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal de signalement de problème -->
@if($rental->status === 'active')
<div id="issueModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Signaler un problème</h3>
            <form method="POST" action="{{ route('client.equipment-rentals.report-issue', $rental) }}">
                @csrf
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre du problème *
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Résumé du problème...">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description *
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Décrivez le problème en détail..."></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" 
                            onclick="closeIssueModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Signaler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal pour afficher les photos -->
<div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="max-w-4xl max-h-full p-4">
        <img id="modalPhoto" src="" alt="" class="max-w-full max-h-full object-contain">
        <button onclick="closePhotoModal()" 
                class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">
            Fermer
        </button>
    </div>
</div>

<script>
// Gestion des photos
function showPhotoModal(photoUrl, altText) {
    const modal = document.getElementById('photoModal');
    const modalPhoto = document.getElementById('modalPhoto');
    
    modalPhoto.src = photoUrl;
    modalPhoto.alt = altText;
    modal.classList.remove('hidden');
}

function closePhotoModal() {
    document.getElementById('photoModal').classList.add('hidden');
}

// Gestion de la modal d'évaluation
function openReviewModal() {
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

// Gestion de la modal de signalement
function openIssueModal() {
    document.getElementById('issueModal').classList.remove('hidden');
}

function closeIssueModal() {
    document.getElementById('issueModal').classList.add('hidden');
}

// Gestion des étoiles
function resetStars() {
    document.querySelectorAll('.star').forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
}

function setStars(rating) {
    document.querySelectorAll('.star').forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

// Événements pour les étoiles
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        document.getElementById('rating').value = rating;
        setStars(rating);
    });
    
    star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        setStars(rating);
    });
});

const starRating = document.getElementById('starRating');
if (starRating) {
    starRating.addEventListener('mouseleave', function() {
        const currentRating = parseInt(document.getElementById('rating').value) || 0;
        setStars(currentRating);
    });
}

// Fermer les modals en cliquant à l'extérieur
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Fermer les modals avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
});
</script>
@endsection