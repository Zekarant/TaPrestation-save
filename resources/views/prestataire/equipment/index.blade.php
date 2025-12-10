@extends('layouts.app')

@section('title', 'Mes équipements à louer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100">
    <!-- Success message -->
    @if(session('success') || session('equipment_just_created'))
    <div class="container mx-auto px-4 py-4">
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('success') ?? 'Équipement créé avec succès ! Vous ne pouvez pas revenir en arrière pour éviter les doublons.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- En-tête moderne -->
    <div class="bg-white shadow-lg border-b-4 border-green-600">
        <div class="container mx-auto px-4 py-3 sm:py-4 md:py-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-green-900 mb-1">Mes équipements</h1>
                    <p class="text-xs sm:text-sm text-green-700">Gérez vos équipements à louer</p>
                </div>
                <a href="{{ route('prestataire.equipment.create') }}" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-center text-sm sm:text-base">
                    <i class="fas fa-plus mr-1 sm:mr-2"></i>Ajouter un équipement
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-3 sm:py-4 md:py-8">
        <!-- Statistiques modernes -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-4 mb-4 sm:mb-6 md:mb-8">
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-green-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-tools text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-green-700">Total des équipements</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-green-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-green-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-green-700">Disponibles</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-green-900">{{ $stats['available'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-green-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-clock text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-green-700">En location</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-green-900">{{ $stats['rented'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-green-200 p-2 sm:p-3 md:p-4 hover:shadow-xl transition duration-200">
                <div class="flex items-center">
                    <div class="p-1.5 sm:p-2 md:p-2.5 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-euro-sign text-base sm:text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-[10px] sm:text-xs font-medium text-green-700">Revenus ce mois</p>
                        <p class="text-base sm:text-lg md:text-xl font-semibold text-green-900">{{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }}€</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres modernes -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6 md:mb-8">
            <div class="mb-3 sm:mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                <div class="text-center sm:text-left">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-700 mb-0.5">Filtrer les équipements</h2>
                    <p class="text-xs text-gray-600 hidden sm:block">Affinez votre recherche par catégories et sous-catégories</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 sm:py-2.5 sm:px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-xs sm:text-sm">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-1 sm:ml-2 text-xs" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('prestataire.equipment.index') }}" class="flex flex-wrap gap-2 sm:gap-3 md:gap-4" id="filtersForm" style="display: none;">
                <!-- Parent Category Filter -->
                <div>
                    <select name="category" id="parentCategory" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-green-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900">
                        <option value="">Catégorie principale</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Subcategory Filter -->
                <div>
                    <select name="subcategory" id="subcategory" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-green-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900">
                        <option value="">Sous-catégorie</option>
                        @if(request('category') && $subcategories)
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div>
                    <select name="status" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-green-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900">
                        <option value="">Tous les statuts</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                        <option value="rented" {{ request('status') === 'rented' ? 'selected' : '' }}>En location</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                        <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Indisponible</option>
                    </select>
                </div>
                
                <div>
                    <select name="sort" class="px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-green-300 rounded-lg focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900">
                        <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>Plus récent</option>
                        <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>Plus ancien</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nom (Z-A)</option>
                    </select>
                </div>
                
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-xs sm:text-sm">
                    <i class="fas fa-search mr-1"></i>Filtrer
                </button>
                
                @if(request()->hasAny(['status', 'category', 'subcategory', 'sort']))
                    <a href="{{ route('prestataire.equipment.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-xs sm:text-sm">
                        <i class="fas fa-times mr-1"></i>Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        <!-- Liste des équipements -->
        @if($equipment->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 md:gap-6">
                @foreach($equipment as $item)
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-green-200 hover:shadow-xl transition duration-200 flex flex-col h-full">
                        <!-- Image -->
                        <div class="relative">
                            @if($item->main_photo)
                                <img src="{{ Storage::url($item->main_photo) }}" alt="{{ $item->name }}" class="w-full h-32 sm:h-40 md:h-48 object-cover rounded-t-lg sm:rounded-t-xl">
                            @else
                                <div class="w-full h-32 sm:h-40 md:h-48 bg-green-50 rounded-t-lg sm:rounded-t-xl flex items-center justify-center">
                                    <i class="fas fa-tools text-green-400 text-2xl sm:text-3xl"></i>
                                </div>
                            @endif
                            
                            <!-- Badge statut -->
                            @php
                                $statusColors = [
                                    'available' => 'bg-green-500 text-white',
                                    'rented' => 'bg-yellow-500 text-white',
                                    'maintenance' => 'bg-red-500 text-white',
                                    'unavailable' => 'bg-gray-500 text-white'
                                ];
                            @endphp
                            <span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 {{ $statusColors[$item->availability_status] ?? 'bg-gray-500 text-white' }} px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold">
                                <i class="fas fa-circle mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ $item->formatted_availability_status }}
                            </span>
                        </div>
                        
                        <!-- Contenu -->
                        <div class="p-2.5 sm:p-3 md:p-4 flex-grow">
                            <h3 class="font-semibold text-sm sm:text-base md:text-lg text-green-900 mb-1.5 line-clamp-2">{{ $item->name }}</h3>
                            <p class="text-green-700 text-xs sm:text-sm mb-2 sm:mb-3 line-clamp-3">{{ $item->description }}</p>
                            
                            <div class="flex flex-wrap gap-1 sm:gap-1.5 mb-2 sm:mb-3">
                                @if($item->category)
                                    <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $item->category->name }}</span>
                                @endif
                                @if($item->subcategory)
                                    <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->subcategory->name }}</span>
                                @endif
                                @if(!$item->category && !$item->subcategory)
                                    <span class="text-[10px] sm:text-xs text-green-500 italic">Non catégorisé</span>
                                @endif
                            </div>

                            <div class="flex justify-between items-center text-[10px] sm:text-xs text-green-600">
                                <span><i class="fas fa-star mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ number_format($item->average_rating ?? 0, 1) }} ({{ $item->reviews_count ?? 0 }})</span>
                                <span><i class="fas fa-euro-sign mr-0.5 sm:mr-1 text-[8px] sm:text-xs"></i>{{ number_format($item->daily_rate, 0, ',', ' ') }}€/jour</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="p-2.5 sm:p-3 bg-green-50 rounded-b-lg sm:rounded-b-xl border-t border-green-200">
                            <div class="flex gap-1.5 sm:gap-2">
                                <a href="{{ route('equipment.show', $item) }}" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-1.5 sm:py-2 rounded-lg transition duration-200 text-[10px] sm:text-xs shadow-md hover:shadow-lg">
                                    <i class="fas fa-eye mr-0.5 sm:mr-1"></i>Voir
                                </a>
                                <a href="{{ route('prestataire.equipment.edit', $item) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-1.5 sm:py-2 rounded-lg transition duration-200 text-[10px] sm:text-xs shadow-md hover:shadow-lg">
                                    <i class="fas fa-edit mr-0.5 sm:mr-1"></i>Modifier
                                </a>
                                
                                <button type="button" class="delete-equipment-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 sm:px-2.5 sm:py-2 rounded-lg transition duration-200 text-[10px] sm:text-xs shadow-md hover:shadow-lg" title="Supprimer" data-equipment-id="{{ $item->id }}" data-equipment-name="{{ $item->name }}">
                                    <i class="fas fa-trash text-[10px] sm:text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-4 sm:mt-6">
                {{ $equipment->appends(request()->query())->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 md:p-8 text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto mb-4 sm:mb-6 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tools text-green-600 text-2xl sm:text-3xl"></i>
                </div>
                <h3 class="text-base sm:text-lg md:text-xl font-semibold text-green-900 mb-2">Aucun équipement trouvé</h3>
                <p class="text-green-700 text-sm mb-4 sm:mb-6">Vous n'avez pas encore ajouté d'équipement.</p>
                <a href="{{ route('prestataire.equipment.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 sm:px-5 sm:py-2.5 md:px-6 md:py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-sm sm:text-base">
                    <i class="fas fa-plus mr-1 sm:mr-2"></i>Ajouter un équipement
                </a>
            </div>
        @endif
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div id="deleteConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(16, 185, 129, 0.8);">
        <div class="bg-white rounded-lg sm:rounded-xl shadow-2xl p-4 sm:p-6 md:p-8 max-w-xs sm:max-w-md w-full mx-4 border-2 sm:border-4 border-red-500 transform transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-red-100">
                    <svg class="h-6 w-6 sm:h-10 sm:w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base sm:text-xl font-bold text-gray-900 mt-3 sm:mt-4">Confirmation de suppression</h3>
                <p class="text-xs sm:text-gray-600 mt-1 sm:mt-2">
                    Êtes-vous sûr de vouloir supprimer cet équipement ?
                </p>
                <p id="equipmentName" class="text-sm sm:text-lg font-semibold text-green-900 mt-1 sm:mt-2"></p>
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
        const equipmentNameElement = document.getElementById('equipmentName');
        let currentEquipmentId = null;
        
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
        document.querySelectorAll('.delete-equipment-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                currentEquipmentId = this.getAttribute('data-equipment-id');
                const equipmentName = this.getAttribute('data-equipment-name');
                equipmentNameElement.textContent = equipmentName;
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
        cancelDeleteBtn.addEventListener('click', function() {
            closeModal();
        });
        
        // Handle confirm delete
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentEquipmentId) {
                // Create a form dynamically and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/prestataire/equipment/${currentEquipmentId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                closeModal();
            }
        });
        
        // Function to close modal with animation
        function closeModal() {
            const modalContent = deleteModal.querySelector('.modal-show');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
            deleteModal.classList.add('opacity-0');
            
            setTimeout(() => {
                deleteModal.classList.add('hidden');
                currentEquipmentId = null;
            }, 300);
        }
    });
</script>
@endpush

@endsection

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Modal animation styles */
.modal-show {
    transition: all 0.3s ease;
}
</style>
@endpush