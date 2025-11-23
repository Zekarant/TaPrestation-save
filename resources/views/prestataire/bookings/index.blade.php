@extends('layouts.app')

@section('title', 'Mes Demandes')

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
        <!-- En-tête -->
        <div class="mb-4 sm:mb-6 text-center">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-900 mb-1">Mes Demandes</h1>
            <p class="text-base sm:text-lg text-blue-700">Gérez toutes vos demandes de services, équipements et ventes urgentes</p>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow-md border border-blue-200 p-3 sm:p-4 mb-4 sm:mb-6">
            <div class="space-y-3">
                <!-- Boutons de filtrage par type -->
                <div class="space-y-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700">Type:</label>
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-1.5 sm:gap-2">
                        <button onclick="filterByType('all')" id="btn-all" class="filter-btn active px-2 py-1.5 sm:px-3 sm:py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200 text-xs font-medium flex items-center justify-center">
                            <i class="fas fa-list mr-1"></i><span class="hidden sm:inline">Tous</span><span class="sm:hidden">Tous</span>
                        </button>
                        <button onclick="filterByType('service')" id="btn-service" class="filter-btn px-2 py-1.5 sm:px-3 sm:py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-blue-100 hover:text-blue-700 transition duration-200 text-xs font-medium flex items-center justify-center">
                            <i class="fas fa-concierge-bell mr-1"></i><span class="hidden sm:inline">Services</span><span class="sm:hidden">Services</span>
                        </button>
                        <button onclick="filterByType('equipment')" id="btn-equipment" class="filter-btn px-2 py-1.5 sm:px-3 sm:py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-green-100 hover:text-green-700 transition duration-200 text-xs font-medium flex items-center justify-center">
                            <i class="fas fa-tools mr-1"></i><span class="hidden sm:inline">Équipements</span><span class="sm:hidden">Équip.</span>
                        </button>
                        <button onclick="filterByType('urgent_sale')" id="btn-urgent_sale" class="filter-btn px-2 py-1.5 sm:px-3 sm:py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-red-100 hover:text-red-700 transition duration-200 text-xs font-medium flex items-center justify-center">
                            <i class="fas fa-tag mr-1"></i><span class="hidden sm:inline">Annonces</span><span class="sm:hidden">Annonces</span>
                        </button>
                    </div>
                </div>

                <!-- Filtre par statut et réinitialisation -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                    <div class="flex items-center space-x-2 flex-1">
                        <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Statut:</label>
                        <select id="statusFilter" class="flex-1 px-2 py-1.5 sm:px-3 sm:py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                            <option value="all" {{ !request('status') ? 'selected' : '' }}>Tous</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Acceptées</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusées</option>
                        </select>
                    </div>

                    <!-- Bouton de réinitialisation -->
                    <button onclick="resetFilters()" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition duration-200 text-xs sm:text-sm font-medium w-full sm:w-auto">
                        <i class="fas fa-undo mr-1.5"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        @php
        // Combiner toutes les demandes
        $allRequests = collect();
        
        // Ajouter les services
        if(isset($serviceBookings) && $serviceBookings->count() > 0) {
            foreach($serviceBookings as $booking) {
                // Determine if this is a multi-slot session
                $isMultiSlot = isset($booking->is_multi_slot) && $booking->is_multi_slot;
                
                $allRequests->push((object)[
                    'id' => $booking->id ?? null,
                    'type' => 'service',
                    'title' => ($booking->service ? ($booking->service->title ?? $booking->service->name) : 'Service') ?? 'Service',
                    'client_name' => ($booking->client && $booking->client->user) ? ($booking->client->user->name ?? 'N/A') : 'N/A',
                    'status' => $booking->status ?? 'unknown',
                    'created_at' => $booking->created_at ?? now(),
                    'image' => ($booking->service && $booking->service->images && $booking->service->images->count() > 0) 
                        ? $booking->service->images->first()->image_path : null,
                    'category' => ($booking->service && $booking->service->category && $booking->service->category->first()) 
                        ? $booking->service->category->first()->name : null,
                    'price' => $isMultiSlot ? ($booking->total_session_price ?? 0) : ($booking->service ? ($booking->service->price ?? null) : null),
                    'price_type' => $booking->service ? ($booking->service->price_type ?? null) : null,
                    'route_show' => $booking->id ? route('prestataire.bookings.show', $booking->id) : '#',
                    'route_accept' => $booking->id ? route('prestataire.bookings.accept', $booking) : '#',
                    'route_reject' => $booking->id ? route('prestataire.bookings.reject', $booking) : '#',
                    'is_multi_slot' => $isMultiSlot,
                    'total_slots' => $isMultiSlot ? ($booking->total_slots ?? 1) : 1,
                    'session_duration' => $isMultiSlot ? ($booking->session_duration ?? 0) : null,
                    'original' => $booking
                ]);
            }
        }
        
        // Ajouter les équipements
        if(isset($equipmentRentalRequests) && $equipmentRentalRequests->count() > 0) {
            foreach($equipmentRentalRequests as $request) {
                $allRequests->push((object)[
                    'id' => $request->id ?? null,
                    'type' => 'equipment',
                    'title' => ($request->equipment ? ($request->equipment->name ?? 'Équipement') : 'Équipement') ?? 'Équipement',
                    'client_name' => ($request->client && $request->client->user) ? ($request->client->user->name ?? 'N/A') : 'N/A',
                    'status' => $request->status ?? 'unknown',
                    'created_at' => $request->created_at ?? now(),
                    'image' => ($request->equipment && $request->equipment->main_photo) 
                        ? $request->equipment->main_photo : (($request->equipment && $request->equipment->photos && count($request->equipment->photos) > 0) ? $request->equipment->photos[0] : null),
                    'category' => ($request->equipment && $request->equipment->category) 
                        ? $request->equipment->category->name : (($request->equipment && $request->equipment->subcategory) ? $request->equipment->subcategory->name : null),
                    'start_date' => $request->start_date ?? null,
                    'end_date' => $request->end_date ?? null,
                    'route_show' => $request->id ? route('prestataire.equipment-rental-requests.show', $request->id) : '#',
                    'route_accept' => $request->id ? route('prestataire.equipment-rental-requests.accept', $request) : '#',
                    'route_reject' => $request->id ? route('prestataire.equipment-rental-requests.reject', $request) : '#',
                    'original' => $request
                ]);
            }
        }
        
        // Ajouter les ventes urgentes
        if(isset($urgentSales) && $urgentSales->count() > 0) {
            foreach($urgentSales as $sale) {
                // Get the latest contact for display purposes
                $latestContact = $sale->contacts ? $sale->contacts->first() : null;
                $clientName = ($latestContact && $latestContact->user) ? $latestContact->user->name : 'N/A';
                
                $allRequests->push((object)[
                    'id' => $sale->id ?? null,
                    'type' => 'urgent_sale',
                    'title' => $sale->title ?? 'Vente urgente',
                    'client_name' => $clientName,
                    'status' => $sale->status ?? 'unknown',
                    'created_at' => $sale->created_at ?? now(),
                    'image' => ($sale->photos && count($sale->photos) > 0) 
                        ? $sale->photos[0] : null,
                    'category' => $sale->category ? $sale->category->name : null,
                    'price' => $sale->price ?? null,
                    'price_min' => $sale->price_min ?? null,
                    'price_max' => $sale->price_max ?? null,
                    'route_show' => $sale->id ? route('prestataire.urgent-sales.show', $sale->id) : '#',
                    'route_accept' => null, // Urgent sales don't have accept action
                    'route_reject' => null, // Urgent sales don't have reject action
                    'original' => $sale,
                    'client' => ($latestContact && $latestContact->user) ? $latestContact->user : null
                ]);
            }
        }
        
        // Trier par date de création (plus récent en premier)
        $allRequests = $allRequests->sortByDesc('created_at');
        @endphp

        <!-- Section Toutes les demandes -->
        @if($allRequests->count() > 0)
            <div class="mb-4 sm:mb-6">
                <div class="bg-white rounded-lg shadow-md border border-blue-200 p-3 sm:p-4">
                    <div class="flex items-center mb-3 sm:mb-4 border-b-2 border-blue-200 pb-2 sm:pb-3">
                        <div class="w-3 h-3 bg-blue-600 rounded-full mr-2"></div>
                        <h2 id="section-title" class="text-lg sm:text-xl font-bold text-blue-800">Toutes les demandes</h2>
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded-full">{{ $allRequests->count() }}</span>
                    </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2 sm:gap-4">
                    @foreach($allRequests as $item)
                        <div class="booking-item bg-white border-2 
                            @if($item->type === 'service') border-blue-200 hover:border-blue-300
                            @elseif($item->type === 'equipment') border-green-200 hover:border-green-300
                            @else border-red-200 hover:border-red-300
                            @endif
                            rounded-lg shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden touch-manipulation"
                            data-type="{{ $item->type }}" data-status="{{ $item->status }}">
                            
                            <a href="{{ $item->route_show }}" class="block">
                                <!-- Image -->
                                <div class="aspect-[3/2] sm:aspect-[4/3] overflow-hidden">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" 
                                             alt="{{ $item->title }}" 
                                             class="w-full h-full object-cover object-center hover:scale-105 transition-transform duration-300" loading="lazy">
                                    @else
                                        <div class="w-full h-full 
                                            @if($item->type === 'service') bg-blue-100
                                            @elseif($item->type === 'equipment') bg-green-100
                                            @else bg-red-100
                                            @endif
                                            flex items-center justify-center">
                                            @if($item->type === 'service')
                                                <i class="fas fa-concierge-bell text-blue-400 text-2xl sm:text-3xl"></i>
                                            @elseif($item->type === 'equipment')
                                                <i class="fas fa-tools text-green-400 text-2xl sm:text-3xl"></i>
                                            @else
                                                <i class="fas fa-tag text-red-400 text-2xl sm:text-3xl"></i>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Contenu -->
                                <div class="p-2 sm:p-3">
                                    <!-- Badge de type -->
                                    <div class="mb-2">
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full
                                            @if($item->type === 'service') bg-blue-100 text-blue-800
                                            @elseif($item->type === 'equipment') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            @if($item->type === 'service') SERVICE
                                            @elseif($item->type === 'equipment') ÉQUIPEMENT
                                            @else VENTE URGENTE
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Titre -->
                                    <h3 class="text-sm sm:text-base font-bold 
                                        @if($item->type === 'service') text-blue-900
                                        @elseif($item->type === 'equipment') text-green-900
                                        @else text-red-900
                                        @endif
                                        mb-1.5 line-clamp-2">{{ $item->title }}</h3>
                                    
                                    <!-- Informations -->
                                    <div class="space-y-1.5 text-xs mb-3">
                                        <!-- Client avec photo -->
                                        <div class="flex items-center space-x-2">
                                            <div class="w-4 h-4 sm:w-5 sm:h-5 rounded-full overflow-hidden flex-shrink-0">
                                                @if($item->type === 'service' && $item->original->client && $item->original->client->user)
                                                    @if($item->original->client->user->profile_photo_url)
                                                        <img src="{{ $item->original->client->user->profile_photo_url }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->original->client->user->client && $item->original->client->user->client->photo)
                                                        <img src="{{ asset('storage/' . $item->original->client->user->client->photo) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->original->client->user->avatar)
                                                        <img src="{{ asset('storage/' . $item->original->client->user->avatar) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full 
                                                            @if($item->type === 'service') bg-blue-100
                                                            @elseif($item->type === 'equipment') bg-green-100
                                                            @else bg-red-100
                                                            @endif
                                                            flex items-center justify-center">
                                                            <span class="text-xs font-medium
                                                                @if($item->type === 'service') text-blue-600
                                                                @elseif($item->type === 'equipment') text-green-600
                                                                @else text-red-600
                                                                @endif
                                                                ">{{ substr($item->client_name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                @elseif($item->type === 'equipment' && $item->original->client && $item->original->client->user)
                                                    @if($item->original->client->user->profile_photo_url)
                                                        <img src="{{ $item->original->client->user->profile_photo_url }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->original->client->user->client && $item->original->client->user->client->photo)
                                                        <img src="{{ asset('storage/' . $item->original->client->user->client->photo) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->original->client->user->avatar)
                                                        <img src="{{ asset('storage/' . $item->original->client->user->avatar) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full 
                                                            @if($item->type === 'service') bg-blue-100
                                                            @elseif($item->type === 'equipment') bg-green-100
                                                            @else bg-red-100
                                                            @endif
                                                            flex items-center justify-center">
                                                            <span class="text-xs font-medium
                                                                @if($item->type === 'service') text-blue-600
                                                                @elseif($item->type === 'equipment') text-green-600
                                                                @else text-red-600
                                                                @endif
                                                                ">{{ substr($item->client_name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                @elseif($item->type === 'urgent_sale' && $item->client)
                                                    @if($item->client->profile_photo_url)
                                                        <img src="{{ $item->client->profile_photo_url }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->client->client && $item->client->client->photo)
                                                        <img src="{{ asset('storage/' . $item->client->client->photo) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @elseif($item->client->avatar)
                                                        <img src="{{ asset('storage/' . $item->client->avatar) }}" 
                                                             alt="{{ $item->client_name }}" 
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full 
                                                            @if($item->type === 'service') bg-blue-100
                                                            @elseif($item->type === 'equipment') bg-green-100
                                                            @else bg-red-100
                                                            @endif
                                                            flex items-center justify-center">
                                                            <span class="text-xs font-medium
                                                                @if($item->type === 'service') text-blue-600
                                                                @elseif($item->type === 'equipment') text-green-600
                                                                @else text-red-600
                                                                @endif
                                                                ">{{ substr($item->client_name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="w-full h-full 
                                                        @if($item->type === 'service') bg-blue-100
                                                        @elseif($item->type === 'equipment') bg-green-100
                                                        @else bg-red-100
                                                        @endif
                                                        flex items-center justify-center">
                                                        <span class="text-xs font-medium
                                                            @if($item->type === 'service') text-blue-600
                                                            @elseif($item->type === 'equipment') text-green-600
                                                            @else text-red-600
                                                            @endif
                                                            ">{{ substr($item->client_name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="
                                                @if($item->type === 'service') text-blue-800
                                                @elseif($item->type === 'equipment') text-green-800
                                                @else text-red-800
                                                @endif
                                                font-medium truncate"><span class="hidden sm:inline">Client: </span>{{ $item->client_name }}</p>
                                        </div>
                                        
                                        @if($item->category)
                                            <p class="
                                                @if($item->type === 'service') text-blue-700
                                                @elseif($item->type === 'equipment') text-green-700
                                                @else text-red-700
                                                @endif
                                                truncate"><span class="hidden sm:inline">Catégorie: </span><span class="sm:hidden">Cat: </span>{{ $item->category }}</p>
                                        @endif
                                        
                                        @if($item->type === 'service')
                                            @if(isset($item->is_multi_slot) && $item->is_multi_slot)
                                                <!-- Multi-slot session information -->
                                                <div class="bg-blue-50 border border-blue-200 rounded-md p-1.5 mb-1.5">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="text-blue-700 font-medium flex items-center">
                                                            <i class="fas fa-calendar-alt mr-1"></i>
                                                            {{ $item->total_slots }} créneaux
                                                        </span>
                                                        @if($item->session_duration)
                                                            @php
                                                                $hours = floor($item->session_duration / 60);
                                                                $minutes = $item->session_duration % 60;
                                                            @endphp
                                                            <span class="text-blue-600">
                                                                @if($hours > 0)
                                                                    {{ $hours }}h{{ $minutes > 0 ? sprintf('%02d', $minutes) : '' }}
                                                                @else
                                                                    {{ $minutes }} min
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <p class="text-blue-700 truncate">Prix total: {{ number_format($item->price ?? 0, 2, ',', ' ') }}€</p>
                                            @elseif($item->price)
                                                <p class="text-blue-700 truncate">Prix: {{ number_format($item->price ?? 0, 0, ',', ' ') }}€{{ ($item->price_type ?? '') === 'per_hour' ? '/h' : (($item->price_type ?? '') === 'per_day' ? '/jour' : '') }}</p>
                                            @endif
                                        @elseif($item->type === 'equipment' && $item->start_date && $item->end_date)
                                            <p class="text-green-700 truncate">Période: {{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') }}</p>
                                        @elseif($item->type === 'urgent_sale')
                                            @if($item->price_min && $item->price_max)
                                                <p class="text-red-700 truncate">Prix: {{ number_format($item->price_min ?? 0, 0, ',', ' ') }}€ - {{ number_format($item->price_max ?? 0, 0, ',', ' ') }}€</p>
                                            @elseif($item->price)
                                                <p class="text-red-700 truncate">Prix: {{ number_format($item->price ?? 0, 0, ',', ' ') }}€</p>
                                            @endif
                                        @endif
                                        
                                        <p class="
                                            @if($item->type === 'service') text-blue-600
                                            @elseif($item->type === 'equipment') text-green-600
                                            @else text-red-600
                                            @endif
                                            text-xs truncate">{{ $item->created_at ? $item->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                                    </div>
                                    
                                    <!-- Statut et actions -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-1.5 sm:space-y-0">
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full self-start
                                            @if($item->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($item->status === 'accepted' || $item->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($item->status === 'rejected' || $item->status === 'refused') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($item->status === 'pending') En attente
                                            @elseif($item->status === 'accepted' || $item->status === 'confirmed') Acceptée
                                            @elseif($item->status === 'rejected' || $item->status === 'refused') Refusée
                                            @else {{ ucfirst($item->status) }}
                                            @endif
                                        </span>
                                        
                                        <a href="{{ $item->route_show }}" class="px-2 py-1.5 w-full sm:w-auto
                                            @if($item->type === 'service') bg-blue-600 hover:bg-blue-700
                                            @elseif($item->type === 'equipment') bg-green-600 hover:bg-green-700
                                            @else bg-red-600 hover:bg-red-700
                                            @endif
                                            text-white rounded-md transition-colors duration-200 text-xs font-medium min-h-[36px] flex items-center justify-center">
                                            Voir détails
                                        </a>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8 sm:py-12">
                <div class="bg-white rounded-lg shadow-md border border-blue-200 p-6 sm:p-8">
                    <div class="text-blue-400 mb-4 sm:mb-6">
                        <svg class="mx-auto h-16 w-16 sm:h-24 sm:w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-blue-900 mb-2 sm:mb-3">Aucune demande pour le moment</h3>
                    <p class="text-blue-700 text-base sm:text-lg mb-4 sm:mb-6">Vous n'avez reçu aucune demande de réservation pour vos services, équipements ou annonces.</p>
                    <div class="space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('prestataire.services.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 sm:py-3 sm:px-6 rounded-md transition duration-300 mr-0 sm:mr-4 mb-2 sm:mb-0">
                            <i class="fas fa-plus mr-1.5"></i>Ajouter un service
                        </a>
                        <a href="{{ route('prestataire.equipment.index') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 sm:py-3 sm:px-6 rounded-md transition duration-300">
                            <i class="fas fa-tools mr-1.5"></i>Ajouter un équipement
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.filter-btn.active {
    background-color: #4B5563 !important;
    color: white !important;
}

.filter-btn.active:hover {
    background-color: #374151 !important;
}

.booking-item {
    transition: all 0.3s ease;
}

.booking-item.hidden {
    display: none !important;
}
</style>

<script>
let currentFilter = 'all';

function filterByType(type) {
    currentFilter = type;
    
    // Mettre à jour le titre de la section
    const sectionTitle = document.getElementById('section-title');
    switch(type) {
        case 'service':
            sectionTitle.textContent = 'Demandes de services';
            break;
        case 'equipment':
            sectionTitle.textContent = 'Demandes d\'équipements';
            break;
        case 'urgent_sale':
            sectionTitle.textContent = 'Demandes d\'annonces';
            break;
        default:
            sectionTitle.textContent = 'Toutes les demandes';
    }
    
    // Mettre à jour l'état des boutons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('bg-gray-600', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    const activeBtn = document.getElementById('btn-' + type);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
        activeBtn.classList.add('bg-gray-600', 'text-white');
    }
    
    // Filtrer les éléments
    const bookingItems = document.querySelectorAll('.booking-item');
    bookingItems.forEach(item => {
        const itemType = item.getAttribute('data-type');
        
        if (type === 'all' || itemType === type) {
            item.classList.remove('hidden');
            item.style.display = 'block';
        } else {
            item.classList.add('hidden');
            item.style.display = 'none';
        }
    });
    
    // Appliquer aussi le filtre de statut si nécessaire
    applyStatusFilter();
}

function applyStatusFilter() {
    const statusFilter = document.getElementById('statusFilter').value;
    const bookingItems = document.querySelectorAll('.booking-item');
    
    bookingItems.forEach(item => {
        const itemStatus = item.getAttribute('data-status');
        const itemType = item.getAttribute('data-type');
        
        // Vérifier si l'élément passe les deux filtres
        const passesTypeFilter = (currentFilter === 'all' || itemType === currentFilter);
        const passesStatusFilter = (statusFilter === 'all' || itemStatus === statusFilter);
        
        if (passesTypeFilter && passesStatusFilter) {
            item.classList.remove('hidden');
            item.style.display = 'block';
        } else {
            item.classList.add('hidden');
            item.style.display = 'none';
        }
    });
}

function resetFilters() {
    currentFilter = 'all';
    if (document.getElementById('statusFilter')) {
        document.getElementById('statusFilter').value = 'all';
    }
    filterByType('all');
}

// Écouter les changements du filtre de statut
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', applyStatusFilter);
    }
});
</script>
@endsection