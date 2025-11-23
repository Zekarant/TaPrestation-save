@extends('layouts.app')

@section('content')
<style>
/* Enhanced blue color scheme and styling from services/index.blade.php */
.filter-button {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.filter-button:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

.prestataire-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.prestataire-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
    border-color: #93c5fd;
}

.specialty-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background-color: #dbeafe;
    color: #1e40af;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.75rem;
}

.prestataire-avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 9999px;
    overflow: hidden;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #bfdbfe;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

@media (max-width: 640px) {
    .prestataire-avatar {
        width: 3rem;
        height: 3rem;
    }
}

.filter-container {
    background-color: white;
    border-radius: 1rem;
    border: 1px solid #bfdbfe;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

/* Enhanced button styles */
.btn-primary {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease-in-out;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.btn-primary:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background-color: #e5e7eb;
    color: #374151;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    border: none;
}

.btn-secondary:hover {
    background-color: #d1d5db;
    transform: translateY(-1px);
}

/* Enhanced toggle button */
#toggleFilters {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

#toggleFilters:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

/* Enhanced rating stars */
.rating-star {
    transition: all 0.2s ease;
}

.rating-star:hover {
    transform: scale(1.1);
}

/* Enhanced action buttons */
.action-button {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Verified badge enhancement */
.verified-badge {
    background-color: #10b981;
    color: white;
    font-weight: 600;
    border-radius: 9999px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Empty state enhancement */
.empty-state {
    background-color: #f0f9ff;
    border-radius: 1rem;
    border: 2px dashed #93c5fd;
    padding: 2rem;
    text-align: center;
}

/* Pagination enhancement */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination a:hover {
    background-color: #dbeafe;
    color: #1e40af;
}

.pagination .active {
    background-color: #3b82f6;
    color: white;
}
</style>

<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-12">
        <div class="mb-8 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-blue-900 mb-2">Nos prestataires de services</h1>
            <p class="text-lg text-blue-700">Trouvez le professionnel parfait pour vos besoins</p>
        </div>
    
    <!-- Filtres -->
    <div class="filter-container p-5 sm:p-6 mb-8">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-left">
                <h2 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1">Filtrer les prestataires</h2>
                <p class="text-sm text-blue-600">Affinez votre recherche pour trouver le prestataire parfait</p>
            </div>
            <button type="button" id="toggleFilters" class="px-5 py-3 rounded-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center text-base font-bold">
                <span id="filterButtonText">Afficher les filtres</span>
                <i class="fas fa-chevron-down ml-2" id="filterChevron"></i>
            </button>
        </div>
        
        <form action="{{ route('prestataires.index') }}" method="GET" class="space-y-4 sm:space-y-6" id="filtersForm" style="display: none;">
            <!-- Première ligne de filtres -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <!-- Recherche par nom -->
                <div>
                    <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Nom</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="name" id="name" value="{{ request('name') }}" 
                            placeholder="Rechercher par nom" 
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                    </div>
                </div>
                
                <!-- Catégorie principale -->
                <div>
                    <label for="category" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Catégorie</label>
                    <div class="relative">
                        <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <select name="category" id="category" 
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Sous-catégorie -->
                <div>
                    <label for="subcategory" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Sous-catégorie</label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <select name="subcategory" id="subcategory" 
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base" 
                            {{ !request('category') ? 'disabled' : '' }}>
                            <option value="">Toutes les sous-catégories</option>
                            @if(isset($subcategories))
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                
                <!-- Ville -->
                <div>
                    <label for="city" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Ville</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="city" id="city" value="{{ request('city') }}" 
                            placeholder="Rechercher par ville" 
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                    </div>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-blue-200">
                <button type="submit" class="flex-1 filter-button px-5 py-3">
                    <i class="fas fa-search mr-2"></i>Filtrer
                </button>
                
                @if(request()->anyFilled(['name', 'category', 'subcategory', 'city']))
                    <a href="{{ route('prestataires.index') }}" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-undo mr-2"></i>Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    <!-- Liste des prestataires -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" style="grid-auto-rows: 1fr;">
        @forelse($prestataires as $prestataire)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200 h-full flex flex-col">
                <div class="p-6 flex-grow flex flex-col">
                    <div class="flex items-center mb-4 flex-shrink-0">
                        <div class="flex-shrink-0 relative">
                            @if($prestataire->photo)
                                <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @elseif($prestataire->user->avatar)
                                <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @elseif($prestataire->user->profile_photo_url)
                                <img src="{{ $prestataire->user->profile_photo_url }}" alt="{{ $prestataire->user->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            @if($prestataire->isVerified())
                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $prestataire->user->name }}</h3>
                                </div>
                                @if($prestataire->isVerified())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Vérifié
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Rating -->
                            @php
                                $rating = isset($prestataire->reviews) ? $prestataire->reviews->avg('rating') : 0;
                                $rating = round($rating * 2) / 2;
                            @endphp
                            @if($rating > 0)
                                <div class="mt-1 flex items-center flex-shrink-0">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $rating)
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            @else
                                                <i class="far fa-star text-yellow-400 text-xs"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ml-1 text-xs text-gray-500">{{ number_format($rating, 1) }}/5 ({{ isset($prestataire->reviews) ? $prestataire->reviews->count() : 0 }})</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-600 line-clamp-2 flex-grow">{{ $prestataire->description }}</p>
                    
                    <!-- Spacer to push content to bottom -->
                    <div class="flex-grow"></div>
                    
                    <div class="mt-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 flex-shrink-0">
                        <div>
                            @if($prestataire->city || $prestataire->address || $prestataire->postal_code)
                                <div class="flex items-start text-sm text-blue-600 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div>
                                        @if($prestataire->address)
                                            <div>{{ $prestataire->address }}</div>
                                        @endif
                                        @if($prestataire->city)
                                            <div>
                                                {{ $prestataire->city }}
                                                @if($prestataire->postal_code)
                                                    , {{ $prestataire->postal_code }}
                                                @endif
                                            </div>
                                        @elseif($prestataire->postal_code)
                                            <div>{{ $prestataire->postal_code }}</div>
                                        @endif
                                        @if($prestataire->country && $prestataire->country !== 'France')
                                            <div>{{ $prestataire->country }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('prestataires.show', $prestataire) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 flex-shrink-0">
                            <i class="fas fa-user mr-2"></i>
                            <span class="sm:hidden">Profil</span>
                            <span class="hidden sm:inline">Voir le profil</span>
                        </a>
                    </div>
                    
                    @auth
                        @if(auth()->user()->isClient())
                            <div class="mt-4 flex flex-col sm:flex-row sm:space-x-3 space-y-3 sm:space-y-0 flex-shrink-0">
                                <a href="{{ route('client.messaging.start', $prestataire) }}" class="w-full sm:w-1/2 inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex-shrink-0">
                                    <svg class="-ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span class="sm:hidden">Contact</span>
                                    <span class="hidden sm:inline">Contacter</span>
                                </a>
                                @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                                    <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST" class="w-full sm:w-1/2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex-shrink-0 unfollow-button">
                                            <i class="fas fa-times mr-2"></i>
                                            <span class="sm:hidden">Désabonner</span>
                                            <span class="hidden sm:inline">Se désabonner</span>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST" class="w-full sm:w-1/2">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex-shrink-0">
                                            <i class="fas fa-heart mr-2"></i>
                                            <span class="sm:hidden">Suivre</span>
                                            <span class="hidden sm:inline">Suivre</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        @empty
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 empty-state">
                <div class="text-blue-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-blue-900 mb-2">Aucun prestataire trouvé</h3>
                <p class="text-blue-800 mb-6">Aucun prestataire ne correspond à vos critères de recherche.</p>
                @if(request()->anyFilled(['name', 'category', 'subcategory', 'city']))
                    <a href="{{ route('prestataires.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                        Réinitialiser les filtres
                    </a>
                @endif
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        <div class="pagination">
            {{ $prestataires->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggleFilters');
        const filtersForm = document.getElementById('filtersForm');
        const buttonText = document.getElementById('filterButtonText');
        const chevron = document.getElementById('filterChevron');
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');
        
        let filtersVisible = false;
        
        toggleButton.addEventListener('click', function() {
            filtersVisible = !filtersVisible;
            
            if (filtersVisible) {
                filtersForm.style.display = 'block';
                buttonText.textContent = 'Masquer les filtres';
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            } else {
                filtersForm.style.display = 'none';
                buttonText.textContent = 'Afficher les filtres';
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
        });
        
        // Handle category change to load subcategories
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            
            // Enable/disable subcategory select
            if (categoryId) {
                subcategorySelect.disabled = false;
                subcategorySelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
                
                // Fetch subcategories via AJAX
                fetch(`/api/categories/${categoryId}/subcategories`)
                    .then(response => response.json())
                    .then(subcategories => {
                        subcategories.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching subcategories:', error);
                    });
            } else {
                subcategorySelect.disabled = true;
                subcategorySelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
            }
        });
        
        // Handle unfollow confirmation
        const unfollowButtons = document.querySelectorAll('.unfollow-button');
        const unfollowModal = document.getElementById('unfollowModal');
        const cancelUnfollowBtn = document.getElementById('cancelUnfollowBtn');
        const confirmUnfollowBtn = document.getElementById('confirmUnfollowBtn');
        let currentForm = null;
        
        unfollowButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                currentForm = this.closest('form');
                unfollowModal.classList.remove('hidden');
                
                // Add animation classes
                setTimeout(() => {
                    unfollowModal.classList.remove('opacity-0');
                    const modalContent = unfollowModal.querySelector('.modal-show');
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                    modalContent.classList.remove('opacity-0');
                }, 10);
            });
        });
        
        // Handle cancel unfollow
        if (cancelUnfollowBtn) {
            cancelUnfollowBtn.addEventListener('click', function() {
                closeUnfollowModal();
            });
        }
        
        // Handle confirm unfollow
        if (confirmUnfollowBtn) {
            confirmUnfollowBtn.addEventListener('click', function() {
                if (currentForm) {
                    currentForm.submit();
                }
                closeUnfollowModal();
            });
        }
        
        // Close unfollow modal when clicking outside
        if (unfollowModal) {
            unfollowModal.addEventListener('click', function(e) {
                if (e.target === unfollowModal) {
                    closeUnfollowModal();
                }
            });
        }
        
        // Close unfollow modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && unfollowModal && !unfollowModal.classList.contains('hidden')) {
                closeUnfollowModal();
            }
        });
        
        // Function to close unfollow modal with animation
        function closeUnfollowModal() {
            const modalContent = unfollowModal.querySelector('.modal-show');
            if (modalContent) {
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                modalContent.classList.add('opacity-0');
            }
            if (unfollowModal) {
                unfollowModal.classList.add('opacity-0');
                
                setTimeout(() => {
                    unfollowModal.classList.add('hidden');
                }, 300);
            }
        }
    });
</script>
@endpush

<!-- Modal de confirmation de désabonnement -->
<div id="unfollowModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300 scale-95 opacity-0 modal-show">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de désabonnement</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir vous désabonner de ce prestataire ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelUnfollowBtn" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmUnfollowBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    Se désabonner
                </button>
            </div>
        </div>
    </div>
</div>