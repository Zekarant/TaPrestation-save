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
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 9999px;
    overflow: hidden;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #bfdbfe;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@media (max-width: 640px) {
    .prestataire-avatar {
        width: 5.5rem;
        height: 5.5rem;
    }
}

@media (max-width: 480px) {
    .prestataire-avatar {
        width: 6rem;
        height: 6rem;
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

/* Enhanced toggle buttons for sections */
.toggle-services-btn, .toggle-equipments-btn, .toggle-sales-btn {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    padding: 0.5rem 1rem;
    margin-top: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.toggle-services-btn:hover, .toggle-equipments-btn:hover, .toggle-sales-btn:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

.toggle-services-btn:active, .toggle-equipments-btn:active, .toggle-sales-btn:active {
    transform: translateY(0);
}

.toggle-services-btn.rotated .arrow-icon, .toggle-equipments-btn.rotated .arrow-icon, .toggle-sales-btn.rotated .arrow-icon {
    transform: rotate(180deg);
}

/* Loading spinner */
.loading-spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    display: none;
    margin-left: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Highlight effect for sections */
@keyframes highlight {
    0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}

.highlight-section {
    animation: highlight 1s ease-in-out;
}

/* Enhanced action buttons */
.action-button {
    border-radius: 0.75rem;
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
    border-radius: 0.75rem;
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

/* Additional styles for the show page */
.service-card, .equipment-card, .sale-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.service-card:hover, .equipment-card:hover, .sale-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
    border-color: #93c5fd;
}

.item-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.75rem;
}

.service-badge {
    background-color: #dbeafe;
    color: #1e40af;
}

.equipment-badge {
    background-color: #dcfce7;
    color: #166534;
}

.sale-badge {
    background-color: #fee2e2;
    color: #991b1b;
}

.contact-info-card {
    background-color: #f0f9ff;
    border-radius: 0.75rem;
    border: 1px solid #bfdbfe;
    padding: 0.75rem;
}

.rating-card {
    background-color: #eff6ff;
    border-radius: 0.75rem;
    border: 1px solid #c7d2fe;
    padding: 0.75rem;
    text-align: center;
}

.pulse-highlight {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
}

/* Mobile layout adjustment for contact info and reviews */
@media (max-width: 768px) {
    .contact-info-rating-container {
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
    }
    
    .contact-info-card, .rating-card {
        flex: 1;
        min-width: 0;
    }
    
    .contact-info-card .text-gray-700, .rating-card .text-blue-700 {
        font-size: 0.75rem;
    }
    
    .contact-info-card .font-bold, .rating-card .font-bold {
        font-size: 0.875rem;
    }
}

/* Mobile grid layout for services - 2 columns, 3 rows = 6 services */
@media (max-width: 640px) {
    #limited-services {
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(3, 1fr);
        overflow: hidden;
        max-height: 630px; /* Adjust based on card height for 3 rows */
    }
    
    #limited-services .service-card:nth-child(n+7) {
        display: none;
    }
    
    /* Ensure images display properly on mobile */
    #limited-services .service-card .relative.h-40 {
        height: 160px;
    }
    
    #limited-services .service-card img {
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
    
    /* Mobile grid layout for services - 2 columns, 3 rows = 6 services */
    @media (max-width: 640px) {
        #limited-services, #limited-equipments, #limited-sales {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(3, 1fr);
            overflow: hidden;
            max-height: 630px; /* Adjust based on card height for 3 rows */
        }
        
        #limited-services .service-card:nth-child(n+7),
        #limited-equipments .equipment-card:nth-child(n+7),
        #limited-sales .sale-card:nth-child(n+7) {
            display: none;
        }
        
        /* Ensure images display properly on mobile */
        #limited-services .service-card .relative.h-40,
        #limited-equipments .equipment-card .relative.h-40,
        #limited-sales .sale-card .relative.h-40 {
            height: 160px;
        }
        
        #limited-services .service-card img,
        #limited-equipments .equipment-card img,
        #limited-sales .sale-card img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
    }

    /* Desktop grid layout - keep existing behavior */
    @media (min-width: 641px) {
        #limited-services, #limited-equipments, #limited-sales {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }
    }

    /* All containers - always use responsive grid */
    #all-services, #all-equipments, #all-sales {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }

    /* Ensure consistent image display */
    .service-card .relative.h-40, 
    .equipment-card .relative.h-40, 
    .sale-card .relative.h-40 {
        height: 160px;
    }

    .service-card img, 
    .equipment-card img, 
    .sale-card img {
        object-fit: cover;
        width: 100%;
        height: 100%;
    }

    /* Toggle button styles */
    .toggle-services-btn, .toggle-equipments-btn, .toggle-sales-btn {
        background-color: #3b82f6;
        color: white;
        font-weight: 600;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        padding: 0.5rem 1rem;
        margin-top: 1rem;
    }

    .toggle-services-btn:hover, .toggle-equipments-btn:hover, .toggle-sales-btn:hover {
        background-color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
    }
}

/* Desktop grid layout - keep existing behavior */
@media (min-width: 641px) {
    #limited-services {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    #limited-equipments {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    #limited-sales {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
}

/* All containers - always use responsive grid */
#all-services, #all-equipments, #all-sales {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
}

/* Ensure consistent image display */
.service-card .relative.h-40, 
.equipment-card .relative.h-40, 
.sale-card .relative.h-40 {
    height: 160px;
}

.service-card img, 
.equipment-card img, 
.sale-card img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

/* Toggle button styles */
.toggle-services-btn, .toggle-equipments-btn, .toggle-sales-btn {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    padding: 0.5rem 1rem;
    margin-top: 1rem;
}

.toggle-services-btn:hover, .toggle-equipments-btn:hover, .toggle-sales-btn:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}
</style>

<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <!-- En-tête du profil prestataire -->
        <div class="prestataire-card mb-6">
            <div class="p-4 sm:p-5">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Colonne gauche : Photo -->
                    <div class="flex flex-col items-center lg:items-start">
                        <div class="prestataire-avatar relative flex-shrink-0 mb-4">
                            @if($prestataire->photo)
                                <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}" class="h-full w-full object-cover rounded-full">
                            @elseif($prestataire->user->avatar)
                                <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}" class="h-full w-full object-cover rounded-full">
                            @elseif($prestataire->user->profile_photo_url)
                                <img src="{{ $prestataire->user->profile_photo_url }}" alt="{{ $prestataire->user->name }}" class="h-full w-full object-cover rounded-full">
                            @else
                                <div class="h-full w-full flex items-center justify-center bg-blue-100 text-blue-500 rounded-full">
                                    <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Colonne centrale : Informations -->
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
                            <h1 class="text-xl sm:text-2xl font-bold text-blue-900">{{ $prestataire->user->name }}</h1>
                            @if($prestataire->isVerified())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Vérifié
                                </span>
                            @endif
                        </div>
                        <span class="specialty-badge mb-5">{{ $prestataire->secteur_activite }}</span>
                        
                        <p class="text-gray-700 mb-6">{{ $prestataire->description }}</p>
                        
                        <!-- Informations de contact et Évaluations -->
                        <div class="contact-info-rating-container grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <!-- Informations de contact -->
                            <div class="contact-info-card">
                                <h3 class="font-bold text-blue-900 mb-1 text-sm">Informations de contact</h3>
                                <div class="space-y-1">
                                    @if($prestataire->phone)
                                        <div class="flex items-center text-gray-700">
                                            <svg class="w-5 h-5 mr-3 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span>{{ $prestataire->phone }}</span>
                                        </div>
                                    @endif
                                    @if($prestataire->address || $prestataire->city)
                                        <div class="flex items-start text-gray-700">
                                            <svg class="w-5 h-5 mr-3 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <div>
                                                @if($prestataire->address)
                                                    <div>{{ $prestataire->address }}</div>
                                                @endif
                                                @if($prestataire->city)
                                                    <div>{{ $prestataire->city }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Évaluations -->
                            @php
                                $totalReviews = $allReviews->count();
                                $averageRating = $totalReviews > 0 ? $allReviews->avg('rating') : 0;
                                $roundedRating = round($averageRating, 1);
                            @endphp
                            
                            <div class="rating-card cursor-pointer" onclick="scrollToReviews(); return false;">
                                <h3 class="font-bold text-blue-900 mb-1 text-sm flex items-center justify-between">
                                    <span>Évaluations</span>
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </h3>
                                @if($totalReviews > 0)
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-blue-900 mb-0">{{ $roundedRating }}</div>
                                        <div class="flex justify-center items-center mb-0">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($averageRating))
                                                    <svg class="w-4 h-4 text-yellow-400 fill-current rating-star" viewBox="0 0 24 24">
                                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                </svg>
                                                @elseif($i - 0.5 <= $averageRating)
                                                    <svg class="w-4 h-4 text-yellow-400 fill-current rating-star" viewBox="0 0 24 24">
                                                        <path d="M12 15.4V6.1L13.71 10.13L18.09 10.5L14.77 13.39L15.76 17.67M22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27L18.18 21L16.54 13.97L22 9.24Z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300 fill-current rating-star" viewBox="0 0 24 24">
                                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                        <p class="text-blue-700 text-xs">{{ $totalReviews }} avis</p>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <div class="flex justify-center items-center mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 text-gray-300 fill-current rating-star" viewBox="0 0 24 24">
                                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                        <p class="text-blue-700 text-xs">Aucun avis</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        @auth
                            @if(auth()->user()->isClient())
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('client.messaging.start', $prestataire) }}" class="action-button flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                        <svg class="-ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        Contacter
                                    </a>
                                    @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                                        <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST" class="flex-1 w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-button w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                                <svg class="-ml-1 mr-1 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4 4 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                Abonné(e)
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST" class="flex-1 w-full">
                                            @csrf
                                            <button type="submit" class="action-button w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                                <svg class="-ml-1 mr-1 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4 4 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                S'abonner
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Structure à deux colonnes -->
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Colonne gauche (70%) - Contenus métier -->
            <div class="lg:w-[70%] w-full space-y-6">
                <!-- Bloc 1: Services proposés -->
                <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center mb-5">
                        <h2 class="text-xl sm:text-2xl font-bold text-blue-900 mb-2 sm:mb-0">Services proposés</h2>
                        @if($allServices->count() > 0)
                            <span class="sm:ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 self-start">
                                {{ $allServices->count() }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Services grid with limited display -->
                    <div id="services-container">
                        <!-- Limited services display (10 services max) -->
                        <div id="limited-services" class="grid gap-4">
                            @forelse($limitedServices as $service)
                                <div class="service-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-blue-100 flex flex-col h-full" onclick="window.location='{{ route('services.show', $service) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if($service->images && $service->images->count() > 0)
                                            <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" alt="{{ $service->title }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                            @if($service->images->count() > 1)
                                                <div class="absolute top-2 right-2 bg-black bg-opacity-60 text-white px-1.5 py-1 rounded-full text-xs">
                                                    <i class="fas fa-images mr-1"></i>
                                                    {{ $service->images->count() }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-blue-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <p class="text-blue-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($service->price)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($service->price, 0, ',', ' ') }} €</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-blue-900 mb-2 line-clamp-2">{{ $service->title }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($service->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $service->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun service disponible</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas encore publié de services.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- All services display (hidden by default) -->
                        <div id="all-services" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" style="display: none;">
                            @forelse($allServices as $service)
                                <div class="service-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-blue-100 flex flex-col h-full" onclick="window.location='{{ route('services.show', $service) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if($service->images && $service->images->count() > 0)
                                            <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" alt="{{ $service->title }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                            @if($service->images->count() > 1)
                                                <div class="absolute top-2 right-2 bg-black bg-opacity-60 text-white px-1.5 py-1 rounded-full text-xs">
                                                    <i class="fas fa-images mr-1"></i>
                                                    {{ $service->images->count() }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-blue-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <p class="text-blue-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($service->price)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($service->price, 0, ',', ' ') }} €</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-blue-900 mb-2 line-clamp-2">{{ $service->title }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($service->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $service->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun service disponible</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas encore publié de services.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- Toggle button -->
                        @if($allServices->count() > 10)
                            <div class="text-center mt-4">
                                <button id="toggle-services-btn" class="toggle-services-btn flex items-center justify-center mx-auto px-4 py-2" aria-expanded="false" aria-controls="all-services">
                                    <span class="button-text">Voir tout</span>
                                    <svg class="arrow-icon ml-2 w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <div class="loading-spinner ml-2" role="status" aria-hidden="true"></div>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bloc 2: Équipements disponibles à la location -->
                <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center mb-5">
                        <h2 class="text-xl sm:text-2xl font-bold text-blue-900 mb-2 sm:mb-0">Équipements disponibles à la location</h2>
                        @if(isset($prestataire->equipments) && $allEquipments->count() > 0)
                            <span class="sm:ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 self-start">
                                {{ $allEquipments->count() }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Equipments grid with limited display -->
                    <div id="equipments-container">
                        <!-- Limited equipments display (6 equipments max) -->
                        <div id="limited-equipments" class="grid gap-4">
                            @forelse($limitedEquipments as $equipment)
                                <div class="equipment-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-green-100 flex flex-col h-full" onclick="window.location='{{ route('equipment.show', $equipment) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if($equipment->main_photo)
                                            <img src="{{ asset('storage/' . $equipment->main_photo) }}" alt="{{ $equipment->name }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-green-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                    <p class="text-green-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($equipment->price_per_day)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($equipment->price_per_day, 0, ',', ' ') }} €/jour</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ $equipment->name }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($equipment->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $equipment->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun équipement disponible</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas d'équipements à louer pour le moment.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- All equipments display (hidden by default) -->
                        <div id="all-equipments" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" style="display: none;">
                            @forelse($allEquipments as $equipment)
                                <div class="equipment-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-green-100 flex flex-col h-full" onclick="window.location='{{ route('equipment.show', $equipment) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if($equipment->main_photo)
                                            <img src="{{ asset('storage/' . $equipment->main_photo) }}" alt="{{ $equipment->name }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-green-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                    <p class="text-green-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($equipment->price_per_day)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($equipment->price_per_day, 0, ',', ' ') }} €/jour</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ $equipment->name }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($equipment->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $equipment->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun équipement disponible</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas d'équipements à louer pour le moment.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- Toggle button -->
                        @if($allEquipments->count() > 6)
                            <div class="text-center mt-4">
                                <button id="toggle-equipments-btn" class="toggle-equipments-btn flex items-center justify-center mx-auto px-4 py-2" aria-expanded="false" aria-controls="all-equipments">
                                    <span class="button-text">Voir tout</span>
                                    <svg class="arrow-icon ml-2 w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <div class="loading-spinner ml-2" role="status" aria-hidden="true"></div>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bloc 3: Offres en vente urgente -->
                <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center mb-5">
                        <h2 class="text-xl sm:text-2xl font-bold text-blue-900 mb-2 sm:mb-0">Offres en vente urgente</h2>
                        @if(isset($prestataire->urgentSales) && $allUrgentSales->count() > 0)
                            <span class="sm:ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 self-start">
                                {{ $allUrgentSales->count() }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Urgent sales grid with limited display -->
                    <div id="sales-container">
                        <!-- Limited urgent sales display (6 sales max) -->
                        <div id="limited-sales" class="grid gap-4">
                            @forelse($limitedUrgentSales as $sale)
                                <div class="sale-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-red-100 flex flex-col h-full" onclick="window.location='{{ route('urgent-sales.show', $sale) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if(is_array($sale->photos) && count($sale->photos) > 0)
                                            <img src="{{ asset('storage/' . $sale->photos[0]) }}" alt="{{ $sale->title }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                            @if(count($sale->photos) > 1)
                                                <div class="absolute top-2 right-2 bg-black bg-opacity-60 text-white px-1.5 py-1 rounded-full text-xs">
                                                    <i class="fas fa-images mr-1"></i>
                                                    {{ count($sale->photos) }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-red-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                    <p class="text-red-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($sale->price)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($sale->price, 0, ',', ' ') }} €</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ $sale->title }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($sale->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $sale->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune vente urgente</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas d'annonces en cours.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- All urgent sales display (hidden by default) -->
                        <div id="all-sales" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" style="display: none;">
                            @forelse($allUrgentSales as $sale)
                                <div class="sale-card cursor-pointer bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-red-100 flex flex-col h-full" onclick="window.location='{{ route('urgent-sales.show', $sale) }}'">
                                    <!-- Image -->
                                    <div class="relative h-40 overflow-hidden">
                                        @if(is_array($sale->photos) && count($sale->photos) > 0)
                                            <img src="{{ asset('storage/' . $sale->photos[0]) }}" alt="{{ $sale->title }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                            @if(count($sale->photos) > 1)
                                                <div class="absolute top-2 right-2 bg-black bg-opacity-60 text-white px-1.5 py-1 rounded-full text-xs">
                                                    <i class="fas fa-images mr-1"></i>
                                                    {{ count($sale->photos) }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-red-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                    <p class="text-red-600 font-medium text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if($sale->price)
                                            <div class="absolute bottom-2 right-2">
                                                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-2 py-1 rounded-lg shadow-md">
                                                    <span class="text-sm font-bold">{{ number_format($sale->price, 0, ',', ' ') }} €</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-3 flex flex-col flex-grow">
                                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ $sale->title }}</h3>
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3 text-xs flex-grow">
                                            {{ Str::limit($sale->description, 80) }}
                                        </p>
                                        
                                        <div class="flex items-center text-xs text-gray-500 mt-auto pt-2 border-t border-gray-100">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $sale->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-5 text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune vente urgente</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ce prestataire n'a pas d'annonces en cours.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- Toggle button -->
                        @if($allUrgentSales->count() > 6)
                            <div class="text-center mt-4">
                                <button id="toggle-sales-btn" class="toggle-sales-btn flex items-center justify-center mx-auto px-4 py-2" aria-expanded="false" aria-controls="all-sales">
                                    <span class="button-text">Voir tout</span>
                                    <svg class="arrow-icon ml-2 w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <div class="loading-spinner ml-2" role="status" aria-hidden="true"></div>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Bloc 4: Section Avis -->
                <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6">
                    <!-- Bouton pour afficher le formulaire d'avis -->
                    @auth
                        @if(auth()->user()->isClient())
                            @php
                                $existingReview = auth()->user()->client->reviews()->where('prestataire_id', $prestataire->id)->first();
                                
                                // Vérifier si l'utilisateur a déjà interagi avec ce prestataire
                                $hasInteracted = false;
                                
                                // Vérifier les messages envoyés
                                $hasMessages = \App\Models\Message::where('sender_id', auth()->id())
                                    ->where('receiver_id', $prestataire->user_id)
                                    ->exists();
                                
                                // Vérifier les réservations/bookings
                                $hasBookings = \App\Models\Booking::where('client_id', auth()->user()->client->id)
                                    ->where('prestataire_id', $prestataire->id)
                                    ->exists();
                                
                                $hasInteracted = $hasMessages || $hasBookings;
                            @endphp
                            
                            @if(!$existingReview && $hasInteracted)
                                <!-- Bouton pour afficher le formulaire -->
                                <div class="mb-6">
                                    <button id="show-review-form" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center space-x-2 w-full sm:w-auto">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                        <span class="text-base">Laisser un avis</span>
                                    </button>
                                </div>
                                
                                <!-- Formulaire caché par défaut -->
                                <div id="review-form" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200" style="display: none;">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-bold text-gray-800">Laisser un avis</h3>
                                        <button id="hide-review-form" class="text-gray-500 hover:text-gray-700">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <form action="{{ route('reviews.store') }}" method="POST" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="prestataire_id" value="{{ $prestataire->id }}">
                                        
                                        <!-- Note en étoiles -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                                            <div class="flex items-center space-x-1" id="star-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="{{ $i }}">
                                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                        </svg>
                                                    </button>
                                                @endfor
                                            </div>
                                            <input type="hidden" name="rating" id="rating-input" required>
                                        </div>
                                        
                                        <!-- Commentaire -->
                                        <div>
                                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                                            <textarea name="comment" id="comment" rows="3" maxlength="300" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Partagez votre expérience avec ce prestataire (200-300 caractères)"></textarea>
                                            <div class="text-sm text-gray-500 mt-1 character-count">
                                                <span id="char-count">0</span>/300 caractères
                                            </div>
                                        </div>
                                        
                                        <!-- Bouton d'envoi -->
                                        <button type="submit" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium w-full sm:w-auto text-base">
                                            Envoyer mon avis
                                        </button>
                                    </form>
                                </div>
                            @elseif(!$existingReview && !$hasInteracted)
                                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <p class="text-yellow-800 text-base">Vous devez d'abord interagir avec ce prestataire (envoyer un message ou réserver un service) pour pouvoir laisser un avis.</p>
                                    </div>
                                </div>
                            @elseif($existingReview)
                                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-green-800 text-base">Vous avez déjà évalué ce prestataire.</p>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-gray-600 text-base">Vous devez être <a href="{{ route('login') }}" class="text-blue-600 hover:underline">connecté</a> pour laisser un avis.</p>
                        </div>
                    @endauth
                    
                    <!-- Liste des avis reçus -->
                    <div id="reviews-section">
                        <h3 class="text-xl font-bold text-blue-900 mb-5">Avis clients ({{ $allReviews->count() }})</h3>
                        
                        @if($allReviews->count() > 0)
                            <div class="space-y-4">
                                @foreach($allReviews as $review)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                        <div class="flex items-start space-x-4">
                                            <!-- Avatar de l'auteur -->
                                            <div class="flex-shrink-0">
                                                @if($review->client)
                                                    @if($review->client->profile_photo_url)
                                                        <img src="{{ $review->client->profile_photo_url }}" 
                                                            alt="{{ $review->client->name }}" 
                                                            class="w-10 h-10 rounded-full object-cover">
                                                    @else
                                                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                            <span class="text-gray-600 font-medium text-sm">
                                                                {{ substr($review->client->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                        <span class="text-gray-600 font-medium text-sm">?</span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Contenu de l'avis -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                                    <div class="mb-2 sm:mb-0">
                                                        <h4 class="font-medium text-gray-800 text-base">
                                                            {{ $review->client ? $review->client->name : 'Utilisateur supprimé' }}
                                                        </h4>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <div class="flex items-center space-x-1">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-sm">
                                                                        ⭐
                                                                    </span>
                                                                @endfor
                                                            </div>
                                                            <span class="text-xs font-medium text-gray-600">{{ number_format($review->rating, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $review->created_at->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                
                                                @if($review->comment)
                                                    <p class="text-gray-700 text-sm leading-relaxed">
                                                        {{ $review->comment }}
                                                    </p>
                                                @endif
                                                
                                                <!-- Photos des avis -->
                                                @if($review->photos && count($review->photos) > 0)
                                                    <div class="mt-3">
                                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                                            @foreach($review->photos as $index => $photo)
                                                                <div class="relative group cursor-pointer overflow-hidden rounded-lg" 
                                                                     onclick="openReviewPhotoModal('{{ asset('storage/' . $photo) }}', 'Photo de l\'avis')">
                                                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                                                        <img src="{{ asset('storage/' . $photo) }}" 
                                                                             alt="Photo de l'avis {{ $index + 1 }}"
                                                                             class="w-full h-full object-cover transition duration-200 group-hover:scale-105">
                                                                    </div>
                                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition duration-200 flex items-center justify-center">
                                                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-400 text-4xl mb-4">💬</div>
                                <p class="text-gray-500 text-base">Aucun avis pour le moment</p>
                                <p class="text-xs text-gray-400 mt-1">Soyez le premier à laisser un avis!</p>
                            </div>
                        @endif
                     </div>
                </div>
            </div>
            <!-- Colonne droite (30%) - Vidéos -->
            <div class="lg:w-[30%] w-full">
                <div class="lg:sticky lg:top-8 space-y-6">
                    <!-- Section Vidéos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6">
                        <h2 class="text-xl font-bold text-blue-900 mb-5 flex items-center">
                             <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                             </svg>
                             <span class="text-xl">Vidéos</span>
                             <span class="ml-2 bg-purple-100 text-purple-600 text-xs font-medium px-2 py-1 rounded-full">{{ $prestataire->videos->count() }}</span>
                         </h2>
                         
                         <!-- Swiper automatique pour vidéos -->
                         @if($prestataire->videos->count() > 0)
                             @php $limitedVideos = $prestataire->videos->take(3); @endphp
                             <div class="video-swiper-container relative w-full">
                                 <!-- Container principal -->
                                 <div class="video-swiper overflow-hidden rounded-xl w-full" style="aspect-ratio: 16/9;">
                                     <div class="video-slides flex transition-transform duration-500 ease-in-out h-full" style="width: {{ $limitedVideos->count() * 100 }}%;">
                                         @foreach($limitedVideos as $index => $video)
                                             <div class="video-slide flex-shrink-0 h-full" style="width: {{ 100 / $limitedVideos->count() }}%;" data-video-index="{{ $index }}">
                                                 <div class="relative bg-black w-full h-full">
                                                     <video 
                                                         class="video-player w-full h-full object-cover" 
                                                         src="{{ asset('storage/' . $video->video_path) }}"
                                                         {{ $index === 0 ? 'autoplay' : '' }}
                                                         muted
                                                         loop
                                                         playsinline
                                                     ></video>
                                                     
                                                     <!-- Overlay avec contrôles -->
                                                     <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                                                         <!-- Bouton play/pause -->
                                                         <button class="play-pause-btn absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white opacity-0 transition-opacity duration-200">
                                                             <svg class="play-icon w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                                                 <path d="M8 5v14l11-7z"/>
                                                             </svg>
                                                             <svg class="pause-icon w-8 h-8 hidden" fill="currentColor" viewBox="0 0 24 24">
                                                                 <path d="M12 15.4V6.1L13.71 10.13L18.09 10.5L14.77 13.39L15.76 17.67M22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.45 13.97L5.82 21L12 17.27L18.18 21L16.54 13.97L22 9.24Z"></path>
                                                             </svg>
                                                         </button>
                                                         
                                                         <!-- Informations vidéo -->
                                                         <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                                             <h4 class="font-semibold text-sm mb-1 line-clamp-2">{{ $video->title }}</h4>
                                                             <div class="flex items-center text-xs text-gray-300 mb-2">
                                                                 <span class="flex items-center mr-4">
                                                                     <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                     </svg>
                                                                     {{ number_format($video->views_count ?? 0) }}
                                                                 </span>
                                                                 <span class="flex items-center">
                                                                     <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                                         <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                                     </svg>
                                                                     {{ number_format($video->likes_count ?? 0) }}
                                                                 </span>
                                                             </div>
                                                             <p class="text-xs text-gray-300">{{ $video->created_at->diffForHumans() }}</p>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         @endforeach
                                     </div>
                                 </div>
                                 
                                 <!-- Contrôles de navigation -->
                                 @if($prestataire->videos->count() > 1)
                                     <!-- Boutons précédent/suivant -->
                                     <button class="swiper-btn-prev absolute left-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-10">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                         </svg>
                                     </button>
                                     <button class="swiper-btn-next absolute right-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-10">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                         </svg>
                                     </button>
                                     
                                     <!-- Indicateurs de pagination -->
                                     <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
                                         @foreach($limitedVideos as $index => $video)
                                             <button class="swiper-dot w-2 h-2 rounded-full transition-colors {{ $index === 0 ? 'bg-white' : 'bg-white/50' }}" data-slide="{{ $index }}"></button>
                                         @endforeach
                                     </div>
                                     
                                     <!-- Contrôle de lecture automatique -->
                                     <button class="autoplay-toggle absolute top-4 right-4 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-10">
                                         <svg class="autoplay-on w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                             <path d="M8 5v14l11-7z"/>
                                         </svg>
                                         <svg class="autoplay-off w-5 h-5 hidden" fill="currentColor" viewBox="0 0 24 24">
                                             <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                         </svg>
                                     </button>
                                 @endif
                             </div>
                         @else
                             <div class="text-center py-8">
                                 <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                 </svg>
                                 <h3 class="text-sm font-medium text-gray-700 mb-1">Aucune vidéo</h3>
                                 <p class="text-xs text-gray-500">Ce prestataire n'a pas encore publié de vidéos.</p>
                             </div>
                         @endif
                     </div>
                 </div>
             </div>
         </div>
     </div>

@push('scripts')
<script>
// Move scrollToReviews to global scope so it can be called from inline onclick handlers
function scrollToReviews() {
    console.log('scrollToReviews function called');
    const reviewsSection = document.getElementById('reviews-section');
    console.log('Reviews section found:', reviewsSection);
    
    if (reviewsSection) {
        // Scroll to the reviews section
        reviewsSection.scrollIntoView({ behavior: 'smooth' });
        console.log('Scrolled to reviews section');
        
        // If user is authenticated and can leave a review, show the review form
        const showReviewFormBtn = document.getElementById('show-review-form');
        console.log('Show review form button found:', showReviewFormBtn);
        
        if (showReviewFormBtn) {
            // Add a slight delay to ensure scrolling is complete before showing form
            setTimeout(() => {
                showReviewFormBtn.classList.add('pulse-highlight');
                console.log('Added pulse highlight to review button');
                // Remove highlight after 2 seconds
                setTimeout(() => {
                    showReviewFormBtn.classList.remove('pulse-highlight');
                    console.log('Removed pulse highlight from review button');
                }, 2000);
            }, 500);
        } else {
            console.log('Review form button not found - user may have already reviewed or not interacted');
            // Try to find any review-related elements to highlight
            const reviewElements = document.querySelectorAll('.bg-yellow-50, .bg-green-50, .text-yellow-800, .text-green-800');
            if (reviewElements.length > 0) {
                console.log('Found', reviewElements.length, 'review-related elements to highlight');
                // Highlight the first relevant element
                const elementToHighlight = reviewElements[0];
                elementToHighlight.classList.add('pulse-highlight');
                setTimeout(() => {
                    elementToHighlight.classList.remove('pulse-highlight');
                }, 2000);
            }
        }
    } else {
        console.log('Reviews section not found');
    }
}

// Fonction pour ouvrir le modal des photos d'avis - déplacée dans la portée globale
function openReviewPhotoModal(imageSrc, caption) {
    // Créer le modal s'il n'existe pas
    let modal = document.getElementById('reviewPhotoModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'reviewPhotoModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="relative max-w-6xl max-h-full w-full h-full flex items-center justify-center">
                <button onclick="closeReviewPhotoModal()" 
                        class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center transition duration-200">
                    ×
                </button>
                <div class="relative w-full h-full flex items-center justify-center">
                    <img id="reviewModalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
                </div>
                <div id="reviewModalCaption" class="absolute bottom-4 left-0 right-0 text-white text-center text-lg"></div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Fermer en cliquant à l'extérieur de l'image
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeReviewPhotoModal();
            }
        });
    }
    
    // Remplir le modal avec les données
    const modalImage = document.getElementById('reviewModalImage');
    const modalCaption = document.getElementById('reviewModalCaption');
    
    modalImage.src = imageSrc;
    modalImage.alt = caption;
    modalCaption.textContent = caption;
    modal.classList.remove('hidden');
    
    // Fermer avec Escape
    const closeOnEscape = function(e) {
        if (e.key === 'Escape') {
            closeReviewPhotoModal();
            document.removeEventListener('keydown', closeOnEscape);
        }
    };
    document.addEventListener('keydown', closeOnEscape);
}

// Fonction pour fermer le modal des photos d'avis - déplacée dans la portée globale
function closeReviewPhotoModal() {
    const modal = document.getElementById('reviewPhotoModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage du formulaire d'avis
    const showFormBtn = document.getElementById('show-review-form');
    const hideFormBtn = document.getElementById('hide-review-form');
    const reviewForm = document.getElementById('review-form');
    
    if (showFormBtn && reviewForm) {
        showFormBtn.addEventListener('click', function() {
            reviewForm.style.display = 'block';
            showFormBtn.style.display = 'none';
            // Scroll vers le formulaire
            reviewForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
    
    if (hideFormBtn && reviewForm && showFormBtn) {
        hideFormBtn.addEventListener('click', function() {
            reviewForm.style.display = 'none';
            showFormBtn.style.display = 'block';
        });
    }
    
    // Système d'étoiles interactif
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-input');
    
    if (starButtons.length > 0 && ratingInput) {
        starButtons.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                ratingInput.value = rating;
                
                // Mettre à jour l'affichage des étoiles
                starButtons.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    }
                });
            });
            
            // Effet de survol
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                starButtons.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-300');
                    }
                });
            });
            
            star.addEventListener('mouseleave', function() {
                const currentRating = parseInt(ratingInput.value) || 0;
                starButtons.forEach((s, i) => {
                    s.classList.remove('text-yellow-300');
                    if (i < currentRating) {
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    }
                });
            });
        });
    }
    
    // Compteur de caractères
    const commentTextarea = document.getElementById('comment');
    const charCount = document.getElementById('char-count');
    
    if (commentTextarea && charCount) {
        commentTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            // Changer la couleur selon la limite
            if (count > 250) {
                charCount.classList.add('text-orange-500');
                charCount.classList.remove('text-gray-500', 'text-red-500');
            } else if (count > 280) {
                charCount.classList.add('text-red-500');
                charCount.classList.remove('text-gray-500', 'text-orange-500');
            } else {
                charCount.classList.add('text-gray-500');
                charCount.classList.remove('text-orange-500', 'text-red-500');
            }
        });
    }
    
    // Gestion du swiper automatique pour les vidéos
    const videoSwiper = document.querySelector('.video-swiper-container');
    if (videoSwiper) {
        const videoSlides = document.querySelector('.video-slides');
        const totalSlides = document.querySelectorAll('.video-slide').length;
        const prevBtn = document.querySelector('.swiper-btn-prev');
        const nextBtn = document.querySelector('.swiper-btn-next');
        const autoplayToggle = document.querySelector('.autoplay-toggle');
        const dots = document.querySelectorAll('.swiper-dot');
        const videos = document.querySelectorAll('.video-player');
        
        let currentSlide = 0;
        let autoplayInterval;
        let isAutoplayActive = true;
        
        // Fonction pour aller à une slide spécifique
        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            const translateX = -(currentSlide * (100 / totalSlides));
            videoSlides.style.transform = `translateX(${translateX}%)`;
            
            // Mettre à jour les dots
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.remove('bg-white/50');
                    dot.classList.add('bg-white');
                } else {
                    dot.classList.remove('bg-white');
                    dot.classList.add('bg-white/50');
                }
            });
            
            // Gérer la lecture des vidéos
            videos.forEach((video, index) => {
                if (index === currentSlide) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }
        
        // Fonction pour aller à la slide suivante
        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            goToSlide(next);
        }
        
        // Fonction pour aller à la slide précédente
        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            goToSlide(prev);
        }
        
        // Démarrer l'autoplay
        function startAutoplay() {
            if (totalSlides > 1) {
                autoplayInterval = setInterval(nextSlide, 5000); // Change toutes les 5 secondes
            }
        }
        
        // Arrêter l'autoplay
        function stopAutoplay() {
            clearInterval(autoplayInterval);
        }
        
        // Toggle autoplay
        function toggleAutoplay() {
            isAutoplayActive = !isAutoplayActive;
            const autoplayOnIcon = autoplayToggle.querySelector('.autoplay-on');
            const autoplayOffIcon = autoplayToggle.querySelector('.autoplay-off');
            
            if (isAutoplayActive) {
                startAutoplay();
                autoplayOnIcon.classList.remove('hidden');
                autoplayOffIcon.classList.add('hidden');
            } else {
                stopAutoplay();
                autoplayOnIcon.classList.add('hidden');
                autoplayOffIcon.classList.remove('hidden');
            }
        }
        
        // Event listeners
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                if (isAutoplayActive) {
                    stopAutoplay();
                    startAutoplay(); // Redémarrer le timer
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                if (isAutoplayActive) {
                    stopAutoplay();
                    startAutoplay(); // Redémarrer le timer
                }
            });
        }
        
        if (autoplayToggle) {
            autoplayToggle.addEventListener('click', toggleAutoplay);
        }
        
        // Event listeners pour les dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
                if (isAutoplayActive) {
                    stopAutoplay();
                    startAutoplay(); // Redémarrer le timer
                }
            });
        });
        
        // Gestion des boutons play/pause pour chaque vidéo
        videos.forEach((video, index) => {
            const videoSlide = video.closest('.video-slide');
            const playPauseBtn = videoSlide.querySelector('.play-pause-btn');
            const playIcon = playPauseBtn.querySelector('.play-icon');
            const pauseIcon = playPauseBtn.querySelector('.pause-icon');
            
            // Afficher le bouton au survol
            videoSlide.addEventListener('mouseenter', () => {
                playPauseBtn.style.opacity = '1';
            });
            
            videoSlide.addEventListener('mouseleave', () => {
                playPauseBtn.style.opacity = '0';
            });
            
            // Toggle play/pause
            playPauseBtn.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                    playIcon.classList.add('hidden');
                    pauseIcon.classList.remove('hidden');
                } else {
                    video.pause();
                    playIcon.classList.remove('hidden');
                    pauseIcon.classList.add('hidden');
                }
            });
            
            // Mettre à jour les icônes selon l'état de la vidéo
            video.addEventListener('play', () => {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
            });
            
            video.addEventListener('pause', () => {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            });
        });
        
        // Démarrer l'autoplay au chargement
        if (totalSlides > 1) {
            startAutoplay();
        }
        
        // Pause autoplay quand l'utilisateur quitte la page
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoplay();
            } else if (isAutoplayActive) {
                startAutoplay();
            }
        });
    }
    
    // Check if we should automatically show the review form (e.g., from a link)
    if (window.location.hash === '#review') {
        scrollToReviews();
    }
    
    // Add event listener to the rating card to ensure click is working
    const ratingCard = document.querySelector('.rating-card.cursor-pointer');
    if (ratingCard) {
        console.log('Rating card found and clickable');
        ratingCard.addEventListener('click', function(e) {
            console.log('Rating card clicked');
            scrollToReviews();
        });
    } else {
        console.log('Rating card not found');
    }
    
    // Services toggle functionality
    const toggleServicesBtn = document.getElementById('toggle-services-btn');
    const limitedServicesContainer = document.getElementById('limited-services');
    const allServicesContainer = document.getElementById('all-services');
    
    if (toggleServicesBtn && limitedServicesContainer && allServicesContainer) {
        // Add mobile grid class for mobile devices
        function updateMobileGrid() {
            if (window.innerWidth <= 640) {
                limitedServicesContainer.classList.add('mobile-grid');
            } else {
                limitedServicesContainer.classList.remove('mobile-grid');
            }
        }
        
        // Initialize on load
        updateMobileGrid();
        
        // Update on resize
        window.addEventListener('resize', updateMobileGrid);
        
        // Click event
        toggleServicesBtn.addEventListener('click', function() {
            toggleServices();
        });
        
        // Keyboard event
        toggleServicesBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleServices();
            }
        });
        
        function toggleServices() {
            const buttonText = toggleServicesBtn.querySelector('.button-text');
            const arrowIcon = toggleServicesBtn.querySelector('.arrow-icon');
            const loadingSpinner = toggleServicesBtn.querySelector('.loading-spinner');
            
            // Show loading spinner
            if (loadingSpinner) {
                loadingSpinner.style.display = 'block';
            }
            
            // Disable button during transition
            toggleServicesBtn.disabled = true;
            
            if (limitedServicesContainer.style.display === 'none') {
                // Show limited services, hide all services
                limitedServicesContainer.style.display = 'grid';
                allServicesContainer.style.display = 'none';
                allServicesContainer.classList.remove('show');
                buttonText.textContent = 'Voir tout';
                toggleServicesBtn.classList.remove('rotated');
            } else {
                // Show all services, hide limited services
                limitedServicesContainer.style.display = 'none';
                allServicesContainer.style.display = 'grid';
                // Trigger reflow to ensure the display change takes effect before adding show class
                allServicesContainer.offsetHeight;
                allServicesContainer.classList.add('show');
                buttonText.textContent = 'Voir moins';
                toggleServicesBtn.classList.add('rotated');
                
                // Add highlight effect to the container
                const servicesContainer = document.querySelector('#services-container');
                servicesContainer.classList.add('highlight-section');
                setTimeout(() => {
                    servicesContainer.classList.remove('highlight-section');
                }, 2000);
                
                // Smooth scroll to the top of the services section
                setTimeout(() => {
                    servicesContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start'
                    });
                }, 100);
            }
            
            // Update ARIA attributes
            toggleServicesBtn.setAttribute('aria-expanded', limitedServicesContainer.style.display === 'none' ? 'false' : 'true');
            
            // Hide loading spinner after a short delay to simulate loading
            setTimeout(() => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                toggleServicesBtn.disabled = false;
            }, 300);
        }
    }
    
    // Equipments toggle functionality
    const toggleEquipmentsBtn = document.getElementById('toggle-equipments-btn');
    const limitedEquipmentsContainer = document.getElementById('limited-equipments');
    const allEquipmentsContainer = document.getElementById('all-equipments');
    
    if (toggleEquipmentsBtn && limitedEquipmentsContainer && allEquipmentsContainer) {
        // Click event
        toggleEquipmentsBtn.addEventListener('click', function() {
            toggleEquipments();
        });
        
        // Keyboard event
        toggleEquipmentsBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleEquipments();
            }
        });
        
        function toggleEquipments() {
            const buttonText = toggleEquipmentsBtn.querySelector('.button-text');
            const arrowIcon = toggleEquipmentsBtn.querySelector('.arrow-icon');
            const loadingSpinner = toggleEquipmentsBtn.querySelector('.loading-spinner');
            
            // Show loading spinner
            if (loadingSpinner) {
                loadingSpinner.style.display = 'block';
            }
            
            // Disable button during transition
            toggleEquipmentsBtn.disabled = true;
            
            if (limitedEquipmentsContainer.style.display === 'none') {
                // Show limited equipments, hide all equipments
                limitedEquipmentsContainer.style.display = 'grid';
                allEquipmentsContainer.style.display = 'none';
                allEquipmentsContainer.classList.remove('show');
                buttonText.textContent = 'Voir tout';
                toggleEquipmentsBtn.classList.remove('rotated');
            } else {
                // Show all equipments, hide limited equipments
                limitedEquipmentsContainer.style.display = 'none';
                allEquipmentsContainer.style.display = 'grid';
                // Trigger reflow to ensure the display change takes effect before adding show class
                allEquipmentsContainer.offsetHeight;
                allEquipmentsContainer.classList.add('show');
                buttonText.textContent = 'Voir moins';
                toggleEquipmentsBtn.classList.add('rotated');
                
                // Add highlight effect to the container
                const equipmentsContainer = document.querySelector('#equipments-container');
                equipmentsContainer.classList.add('highlight-section');
                setTimeout(() => {
                    equipmentsContainer.classList.remove('highlight-section');
                }, 2000);
                
                // Smooth scroll to the top of the equipments section
                setTimeout(() => {
                    equipmentsContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start'
                    });
                }, 100);
            }
            
            // Update ARIA attributes
            toggleEquipmentsBtn.setAttribute('aria-expanded', limitedEquipmentsContainer.style.display === 'none' ? 'false' : 'true');
            
            // Hide loading spinner after a short delay to simulate loading
            setTimeout(() => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                toggleEquipmentsBtn.disabled = false;
            }, 300);
        }
    }
    
    // Urgent sales toggle functionality
    const toggleSalesBtn = document.getElementById('toggle-sales-btn');
    const limitedSalesContainer = document.getElementById('limited-sales');
    const allSalesContainer = document.getElementById('all-sales');
    
    if (toggleSalesBtn && limitedSalesContainer && allSalesContainer) {
        // Click event
        toggleSalesBtn.addEventListener('click', function() {
            toggleSales();
        });
        
        // Keyboard event
        toggleSalesBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleSales();
            }
        });
        
        function toggleSales() {
            const buttonText = toggleSalesBtn.querySelector('.button-text');
            const arrowIcon = toggleSalesBtn.querySelector('.arrow-icon');
            const loadingSpinner = toggleSalesBtn.querySelector('.loading-spinner');
            
            // Show loading spinner
            if (loadingSpinner) {
                loadingSpinner.style.display = 'block';
            }
            
            // Disable button during transition
            toggleSalesBtn.disabled = true;
            
            if (limitedSalesContainer.style.display === 'none') {
                // Show limited sales, hide all sales
                limitedSalesContainer.style.display = 'grid';
                allSalesContainer.style.display = 'none';
                allSalesContainer.classList.remove('show');
                buttonText.textContent = 'Voir tout';
                toggleSalesBtn.classList.remove('rotated');
            } else {
                // Show all sales, hide limited sales
                limitedSalesContainer.style.display = 'none';
                allSalesContainer.style.display = 'grid';
                // Trigger reflow to ensure the display change takes effect before adding show class
                allSalesContainer.offsetHeight;
                allSalesContainer.classList.add('show');
                buttonText.textContent = 'Voir moins';
                toggleSalesBtn.classList.add('rotated');
                
                // Add highlight effect to the container
                const salesContainer = document.querySelector('#sales-container');
                salesContainer.classList.add('highlight-section');
                setTimeout(() => {
                    salesContainer.classList.remove('highlight-section');
                }, 2000);
                
                // Smooth scroll to the top of the sales section
                setTimeout(() => {
                    salesContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start'
                    });
                }, 100);
            }
            
            // Update ARIA attributes
            toggleSalesBtn.setAttribute('aria-expanded', limitedSalesContainer.style.display === 'none' ? 'false' : 'true');
            
            // Hide loading spinner after a short delay to simulate loading
            setTimeout(() => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                toggleSalesBtn.disabled = false;
            }, 300);
        }
    }
});
</script>
@endpush

@endsection