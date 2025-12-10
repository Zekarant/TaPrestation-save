@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-blue-50 py-8">
    <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6">
        <!-- Header Section -->
        <div class="mb-6 sm:mb-8">
            <div class="text-center mb-4 sm:mb-6">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-1 leading-tight">
                    Prestataires suivis
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-3xl mx-auto">
                    Retrouvez tous les prestataires que vous suivez
                </p>
            </div>
        </div>

        <!-- Stats and Filters Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 mb-6">
            <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1">Filtres de recherche</h3>
                    <p class="text-sm text-blue-700">Affinez votre recherche pour trouver le prestataire parfait</p>
                </div>
                <button type="button" id="toggleFilters"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 sm:py-2 sm:px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-1.5" id="filterChevron"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('client.prestataire-follows.index') }}" class="space-y-4"
                id="filtersForm" style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Tri par -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
                        <div class="relative">
                            <i class="fas fa-sort absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="sort" id="sort"
                                class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                onchange="this.form.submit()">
                                <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récents
                                </option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Plus anciens
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Recherche -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                        <div class="relative">
                            <i
                                class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Rechercher par nom..."
                                class="w-full pl-10 pr-10 py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            @if(request('search'))
                                <button type="button" onclick="clearSearch()"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex items-end">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                            Appliquer
                        </button>
                    </div>
                </div>
            </form>

            <!-- Affichage des résultats -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 border-t border-blue-200 mt-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-800">Résultats :</span>
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $prestataires->total() }} prestataire{{ $prestataires->total() > 1 ? 's' : '' }}
                        suivi{{ $prestataires->total() > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Prestataires Grid -->
        @if($prestataires->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-50">
                    <i class="fas fa-heart text-3xl text-blue-500"></i>
                </div>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Aucun abonnement pour le moment</h3>
                <p class="mt-2 text-gray-500 max-w-md mx-auto">Découvrez et suivez vos prestataires préférés pour rester
                    informé de leurs dernières activités.</p>
                <div class="mt-6">
                    <a href="{{ route('prestataires.index') }}"
                        class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                        <i class="fas fa-search mr-2"></i>
                        Découvrir des prestataires
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" style="grid-auto-rows: 1fr;">
                @foreach($prestataires as $prestataire)
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200 h-full flex flex-col">
                        <div class="p-6 flex-grow flex flex-col">
                            <!-- Prestataire Header -->
                            <div class="flex items-start flex-shrink-0">
                                <div class="flex-shrink-0 relative">
                                    @if($prestataire->photo)
                                        <img src="{{ asset('storage/' . $prestataire->photo) }}"
                                            alt="{{ $prestataire->user->name ?? 'Prestataire' }}"
                                            class="w-16 h-16 rounded-xl object-cover">
                                    @elseif($prestataire->user->avatar)
                                        <img src="{{ asset('storage/' . $prestataire->user->avatar) }}"
                                            alt="{{ $prestataire->user->name ?? 'Prestataire' }}"
                                            class="w-16 h-16 rounded-xl object-cover">
                                    @else
                                        <div
                                            class="w-16 h-16 rounded-xl bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    @endif
                                    @if($prestataire->isVerified())
                                        <div
                                            class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-4 flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 truncate">
                                                {{ $prestataire->user->name ?? 'Prestataire' }}</h3>
                                        </div>
                                        @if($prestataire->isVerified())
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Rating -->
                                    @if($prestataire->rating_average > 0)
                                        <div class="mt-1 flex items-center">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $prestataire->rating_average)
                                                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                    @else
                                                        <i class="far fa-star text-yellow-400 text-xs"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span
                                                class="ml-1 text-xs text-gray-500">{{ number_format($prestataire->rating_average, 1) }}/5</span>
                                        </div>
                                    @endif

                                    <!-- Stats -->
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <div class="bg-blue-50 rounded-lg p-2 text-center">
                                            <p class="text-xs text-gray-500">Services</p>
                                            <p class="text-sm font-semibold text-blue-700">{{ $prestataire->services->count() }}
                                            </p>
                                        </div>
                                        <div class="bg-green-50 rounded-lg p-2 text-center">
                                            <p class="text-xs text-gray-500">Équipement à louer</p>
                                            <p class="text-sm font-semibold text-green-700">
                                                {{ $prestataire->equipments->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($prestataire->description)
                                <p class="mt-4 text-sm text-gray-600 line-clamp-2 flex-grow">
                                    {{ Str::limit($prestataire->description, 100) }}
                                </p>
                            @endif

                            <!-- Spacer to push buttons to bottom -->
                            <div class="flex-grow"></div>

                            <!-- Actions -->
                            <div class="mt-6 flex flex-col sm:flex-row sm:space-x-3 space-y-3 sm:space-y-0 flex-shrink-0">
                                <a href="{{ route('prestataires.show', $prestataire->id) }}"
                                    class="w-full sm:w-1/2 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                    <i class="fas fa-user mr-2"></i>
                                    <span class="sm:hidden">Profil</span>
                                    <span class="hidden sm:inline">Voir le profil</span>
                                </a>
                                <form action="{{ route('client.prestataire-follows.unfollow', $prestataire->id) }}"
                                    method="POST" class="w-full sm:w-1/2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 unfollow-button">
                                        <i class="fas fa-times mr-2"></i>
                                        <span class="sm:hidden">Désabonner</span>
                                        <span class="hidden sm:inline">Se désabonner</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($prestataires->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $prestataires->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('toggleFilters');
        const filtersForm = document.getElementById('filtersForm');
        const buttonText = document.getElementById('filterButtonText');
        const chevron = document.getElementById('filterChevron');

        // Show filters by default if there are search parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search') || urlParams.has('sort')) {
            filtersForm.style.display = 'block';
            buttonText.textContent = 'Masquer les filtres';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        }

        toggleButton.addEventListener('click', function () {
            if (filtersForm.style.display === 'none') {
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

        // Clear search function
        window.clearSearch = function () {
            document.getElementById('search').value = '';
            // Submit the form to clear the search
            document.getElementById('filtersForm').submit();
        };

        // Clear all filters function
        window.clearFilters = function () {
            document.getElementById('search').value = '';
            document.getElementById('sort').value = 'recent';
            // Submit the form to clear all filters
            document.getElementById('filtersForm').submit();
        };

        // Handle unfollow confirmation
        const unfollowButtons = document.querySelectorAll('.unfollow-button');
        const unfollowModal = document.getElementById('unfollowModal');
        const cancelUnfollowBtn = document.getElementById('cancelUnfollowBtn');
        const confirmUnfollowBtn = document.getElementById('confirmUnfollowBtn');
        let currentForm = null;

        unfollowButtons.forEach(button => {
            button.addEventListener('click', function (e) {
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
            cancelUnfollowBtn.addEventListener('click', function () {
                closeUnfollowModal();
            });
        }

        // Handle confirm unfollow
        if (confirmUnfollowBtn) {
            confirmUnfollowBtn.addEventListener('click', function () {
                if (currentForm) {
                    currentForm.submit();
                }
                closeUnfollowModal();
            });
        }

        // Close unfollow modal when clicking outside
        if (unfollowModal) {
            unfollowModal.addEventListener('click', function (e) {
                if (e.target === unfollowModal) {
                    closeUnfollowModal();
                }
            });
        }

        // Close unfollow modal with Escape key
        document.addEventListener('keydown', function (e) {
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

<!-- Modal de confirmation de désabonnement -->
<div id="unfollowModal"
    class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
    style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
    <div
        class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de désabonnement</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir vous désabonner de ce prestataire ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelUnfollowBtn"
                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmUnfollowBtn"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    Se désabonner
                </button>
            </div>
        </div>
    </div>
</div>