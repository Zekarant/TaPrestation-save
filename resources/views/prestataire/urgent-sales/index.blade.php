@extends('layouts.app')

@section('title', 'Mes Annonces')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100">
    <!-- Success message -->
    @if(session('success') || session('urgent_sale_just_created'))
    <div class="container mx-auto px-4 py-4">
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        {{ session('success') ?? 'Annonce créée avec succès ! Vous ne pouvez pas revenir en arrière pour éviter les doublons.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- En-tête moderne -->
    <div class="bg-white shadow-lg border-b-4 border-red-600">
        <div class="container mx-auto px-4 py-3 sm:py-4 md:py-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-red-900 mb-1">Mes Annonces</h1>
                    <p class="text-xs sm:text-sm text-red-700">Gérez vos annonces et opportunités</p>
                </div>
                <a href="{{ route('prestataire.urgent-sales.create') }}" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-center text-sm sm:text-base">
                    <i class="fas fa-plus mr-1 sm:mr-2"></i>Ajouter une vente
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-3 sm:py-4 md:py-8">
        <!-- Statistiques modernes -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4 mb-4 sm:mb-6 md:mb-8">
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-red-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-tag text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-red-700">Total des annonces</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-red-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-red-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-check-circle text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-red-700">Actives</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-red-900">{{ $stats['active'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-red-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-eye text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-red-700">Vues totales</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-red-900">{{ $stats['total_views'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-red-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-envelope text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-red-700">Contacts reçus</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-red-900">{{ $stats['total_contacts'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres modernes -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6 md:mb-8">
            <div class="mb-3 sm:mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                <div class="text-center sm:text-left">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-700 mb-0.5">Filtrer les ventes urgentes</h2>
                    <p class="text-xs text-gray-600 hidden sm:block">Affinez votre recherche par catégories et sous-catégories</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 sm:py-2.5 sm:px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-xs sm:text-sm">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-1 sm:ml-2 text-xs" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('prestataire.urgent-sales.index') }}" class="flex flex-wrap gap-2 sm:gap-3 md:gap-4" id="filtersForm" style="display: none;">
                <!-- Parent Category Filter -->
                <div>
                    <select name="category" id="parentCategory" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-red-500 focus:border-red-500 text-red-900">
                        <option value="">Catégorie principale</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Subcategory Filter -->
                <div>
                    <select name="subcategory" id="subcategory" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-red-500 focus:border-red-500 text-red-900">
                        <option value="">Sous-catégorie</option>
                        @if(request('category') && $subcategories)
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div>
                    <select name="condition" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-red-500 focus:border-red-500 text-red-900">
                        <option value="">Toutes les conditions</option>
                        <option value="excellent" {{ request('condition') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                        <option value="very_good" {{ request('condition') === 'very_good' ? 'selected' : '' }}>Très bon</option>
                        <option value="good" {{ request('condition') === 'good' ? 'selected' : '' }}>Bon</option>
                        <option value="fair" {{ request('condition') === 'fair' ? 'selected' : '' }}>Correct</option>
                        <option value="poor" {{ request('condition') === 'poor' ? 'selected' : '' }}>Mauvais</option>
                    </select>
                </div>
                
                <div>
                    <select name="sort" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-red-500 focus:border-red-500 text-red-900">
                        <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>Plus récent</option>
                        <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>Plus ancien</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="views_desc" {{ request('sort') === 'views_desc' ? 'selected' : '' }}>Plus vues</option>
                    </select>
                </div>
                
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-xs sm:text-sm">
                    <i class="fas fa-search mr-1"></i>Filtrer
                </button>
                
                @if(request()->hasAny(['category', 'subcategory', 'condition', 'sort']))
                    <a href="{{ route('prestataire.urgent-sales.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-xs sm:text-sm">
                        <i class="fas fa-times mr-1"></i>Réinitialiser
                    </a>
                @endif
            </form>
        </div>

    <!-- Liste des ventes modernes -->
    @if($urgentSales->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 md:gap-6">
            @foreach($urgentSales as $sale)
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-red-200 hover:shadow-xl transition duration-200 flex flex-col h-full">
                    <!-- Image -->
                    <div class="relative">
                        @if($sale->photos && count($sale->photos ?? []) > 0)
                            <img src="{{ Storage::url($sale->photos[0]) }}" 
                                 alt="{{ $sale->title }}" 
                                 class="w-full h-32 sm:h-40 md:h-48 object-cover rounded-t-lg sm:rounded-t-xl"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRkVGMkYyIi8+CjxwYXRoIGQ9Ik0xNzUgMTI1SDE4NVYxMzVIMTc1VjEyNVoiIGZpbGw9IiNGQ0E1QTUiLz4KPHA+dGggZD0iTTE2NSAxNDVIMjM1VjE1NUgxNjVWMTQ1WiIgZmlsbD0iI0ZDQTVBNSIvPgo8cGF0aCBkPSJNMTg1IDEwNUMxOTEuNjI3IDEwNSAxOTcgMTEwLjM3MyAxOTcgMTE3QzE5NyAxMjMuNjI3IDE5MS42MjcgMTI5IDE4NSAxMjlDMTc4LjM3MyAxMjkgMTczIDEyMy42MjcgMTczIDExN0MxNzMgMTEwLjM3MyAxNzguMzczIDEwNSAxODUgMTA1WiIgZmlsbD0iI0ZDQTVBNSIvPgo8L3N2Zz4K'; this.classList.add('opacity-75');">
                            
                            <!-- Nombre de photos -->
                            @if(count($sale->photos ?? []) > 1)
                                <div class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 bg-black/70 text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-medium backdrop-blur-sm">
                                    <i class="fas fa-images mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ count($sale->photos ?? []) }}
                                </div>
                            @endif
                        @else
                            <div class="w-full h-32 sm:h-40 md:h-48 bg-red-50 rounded-t-lg sm:rounded-t-xl flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-image text-red-300 text-2xl sm:text-3xl mb-1 sm:mb-2"></i>
                                    <p class="text-red-400 text-[10px] sm:text-xs">Aucune image</p>
                                </div>
                            </div>
                        @endif
                        
                    </div>
                    
                    <!-- Contenu -->
                    <div class="p-2.5 sm:p-3 md:p-4 flex flex-col flex-grow">
                        <h3 class="font-semibold text-sm sm:text-base md:text-lg text-red-900 mb-1.5 line-clamp-2">{{ $sale->title }}</h3>
                        <p class="text-red-700 text-xs sm:text-sm mb-2 sm:mb-3 line-clamp-2">{{ $sale->description }}</p>
                        
                        <div class="flex justify-between items-center mb-2 sm:mb-3">
                            <span class="text-lg sm:text-xl md:text-2xl font-bold text-red-600">{{ number_format($sale->price, 0, ',', ' ') }} EUR</span>
                            <span class="text-[10px] sm:text-xs text-red-700 bg-red-100 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-lg border border-red-200">
                                {{ $sale->condition_label }}
                            </span>
                        </div>
                        
                        <!-- Statistiques -->
                        <div class="flex justify-between text-[10px] sm:text-xs text-red-600 mb-3 sm:mb-4">
                            <span><i class="fas fa-eye mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ $sale->views_count }} vues</span>
                            <span><i class="fas fa-envelope mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ $sale->contacts_count }} contacts</span>
                            <span><i class="fas fa-map-marker-alt mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ $sale->city }}</span>
                        </div>
                        
                        <!-- Actions -->
                        <div class="mt-auto pt-2 sm:pt-3 border-t border-red-100 flex gap-1.5 sm:gap-2">
                            <a href="{{ route('prestataire.urgent-sales.show', $sale) }}" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-1.5 sm:py-2 px-2 sm:px-3 rounded-lg text-[10px] sm:text-xs transition duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-eye mr-0.5 sm:mr-1"></i>Voir
                            </a>
                            
                            <a href="{{ route('prestataire.urgent-sales.edit', $sale) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-1.5 sm:py-2 px-2 sm:px-3 rounded-lg text-[10px] sm:text-xs transition duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-edit mr-0.5 sm:mr-1"></i>Modifier
                            </a>
                            
                            <form action="{{ route('prestataire.urgent-sales.destroy', $sale) }}" method="POST" class="inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="delete-urgent-sale-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 sm:px-2.5 sm:py-2 rounded-lg text-[10px] sm:text-xs transition duration-200 shadow-md hover:shadow-lg" title="Supprimer" data-sale-id="{{ $sale->id }}" data-sale-title="{{ $sale->title }}">
                                    <i class="fas fa-trash text-[10px] sm:text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination moderne -->
        <div class="mt-4 sm:mt-6">
            {{ $urgentSales->appends(request()->query())->links() }}
        </div>
    @else
        <!-- État vide moderne -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 md:p-8 text-center">
            <div class="mb-4 sm:mb-6">
                <i class="fas fa-tag text-red-300 text-3xl sm:text-4xl md:text-5xl"></i>
            </div>
            <h3 class="text-base sm:text-lg md:text-xl font-semibold text-red-900 mb-2">Aucune vente urgente trouvée</h3>
            <p class="text-red-700 text-sm mb-4 sm:mb-6 max-w-md mx-auto">Vous n'avez pas encore créé de vente urgente ou aucune ne correspond à vos critères de recherche.</p>
            <a href="{{ route('prestataire.urgent-sales.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 sm:px-5 sm:py-2.5 md:px-6 md:py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg inline-flex items-center text-sm sm:text-base">
                <i class="fas fa-plus mr-1 sm:mr-2"></i>Créer ma première vente
            </a>
        </div>
    @endif

    <!-- Modal de confirmation de suppression -->
    <div id="deleteConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
        <div class="bg-white rounded-lg sm:rounded-xl shadow-2xl p-4 sm:p-6 md:p-8 max-w-xs sm:max-w-md w-full mx-4 border-2 sm:border-4 border-red-500 transform transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-red-100">
                    <svg class="h-6 w-6 sm:h-10 sm:w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base sm:text-xl font-bold text-gray-900 mt-3 sm:mt-4">Confirmation de suppression</h3>
                <p class="text-xs sm:text-gray-600 mt-1 sm:mt-2">
                    Êtes-vous sûr de vouloir supprimer cette vente urgente ?
                </p>
                <p id="urgentSaleTitle" class="text-sm sm:text-lg font-semibold text-red-900 mt-1 sm:mt-2"></p>
                <div class="mt-4 sm:mt-6 flex flex-col gap-2 sm:gap-3">
                    <button id="cancelDeleteBtn" class="flex-1 px-3 py-2 sm:px-4 sm:py-2.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium text-sm sm:text-base">
                        Annuler
                    </button>
                    <button id="confirmDeleteBtn" class="flex-1 px-3 py-2 sm:px-4 sm:py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium text-sm sm:text-base">
                        Supprimer
                    </button>
                </div>
            </div>
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
        const parentCategorySelect = document.getElementById('parentCategory');
        const subcategorySelect = document.getElementById('subcategory');
        
        // Modal elements
        const deleteModal = document.getElementById('deleteConfirmationModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const urgentSaleTitleElement = document.getElementById('urgentSaleTitle');
        let currentSaleId = null;
        
        let filtersVisible = false;
        
        toggleButton.addEventListener('click', function() {
            filtersVisible = !filtersVisible;
            
            if (filtersVisible) {
                filtersForm.style.display = 'flex';
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
        
        // Handle parent category change to update subcategories
        if (parentCategorySelect && subcategorySelect) {
            parentCategorySelect.addEventListener('change', function() {
                const parentId = this.value;
                
                // Clear subcategory options
                subcategorySelect.innerHTML = '<option value="">Sous-catégorie</option>';
                
                if (parentId) {
                    // In a real implementation, we would make an AJAX call to get subcategories
                    // For now, we'll just submit the form to refresh the page with the new options
                    this.form.submit();
                }
            });
        }
        
        // Handle delete button clicks
        document.querySelectorAll('.delete-urgent-sale-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentSaleId = this.getAttribute('data-sale-id');
                const saleTitle = this.getAttribute('data-sale-title');
                urgentSaleTitleElement.textContent = saleTitle;
                deleteModal.classList.remove('hidden');
                
                // Add animation classes
                setTimeout(() => {
                    deleteModal.classList.remove('opacity-0');
                    const modalContent = deleteModal.querySelector('.modal-show');
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                    modalContent.classList.remove('opacity-0');
                }, 10);
            });
        });
        
        // Handle cancel delete
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function() {
                closeModal();
            });
        }
        
        // Handle confirm delete
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (currentSaleId) {
                    // Find the form associated with this sale and submit it
                    const form = document.querySelector(`.delete-form button[data-sale-id="${currentSaleId}"]`).closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
        
        // Close modal when clicking outside
        if (deleteModal) {
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    closeModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
                closeModal();
            }
        });
        
        // Function to close modal with animation
        function closeModal() {
            const modalContent = deleteModal.querySelector('.modal-show');
            if (modalContent) {
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                modalContent.classList.add('opacity-0');
            }
            if (deleteModal) {
                deleteModal.classList.add('opacity-0');
                
                setTimeout(() => {
                    deleteModal.classList.add('hidden');
                    currentSaleId = null;
                }, 300);
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush