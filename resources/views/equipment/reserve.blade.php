@extends('layouts.app')

@section('title', 'Réserver ' . $equipment->name)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_green.css">
<style>
    /* Animations améliorées */
    .fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Cartes avec effet moderne */
    .card-hover {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(145deg, #ffffff 0%, #f8fdf9 100%);
    }
    
    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(22, 163, 74, 0.15), 0 8px 16px rgba(0, 0, 0, 0.08);
    }
    
    /* Bouton de réservation amélioré */
    .btn-reserve {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        box-shadow: 0 4px 15px rgba(22, 163, 74, 0.35);
        transition: all 0.3s ease;
    }
    
    .btn-reserve:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(22, 163, 74, 0.45);
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    }
    
    .btn-reserve:disabled {
        background: #9ca3af;
        box-shadow: none;
    }
    
    /* Prix card */
    .price-card {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    }
    
    /* ===== OPTIMISATIONS MOBILE ===== */
    @media (max-width: 480px) {
        /* Désactiver les effets hover sur mobile */
        .card-hover:hover {
            transform: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Animation plus légère */
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Bouton réservation optimisé touch */
        .btn-reserve {
            min-height: 52px;
            font-size: 1rem;
            border-radius: 14px;
        }
        
        /* Prix card compact */
        .price-card {
            border-radius: 10px;
        }
        
        /* Toggle switch plus grand */
        .mode-toggle button {
            min-height: 44px;
            padding: 0.5rem 1rem;
        }
    }
    
    /* Ultra petit écran */
    @media (max-width: 360px) {
        .btn-reserve {
            min-height: 48px;
            font-size: 0.9375rem;
        }
    }
    
    /* Flatpickr customization */
    .flatpickr-calendar {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
    }
    .flatpickr-days {
        width: 100% !important;
    }
    .dayContainer {
        width: 100% !important;
        min-width: unset !important;
        max-width: unset !important;
    }
    .flatpickr-day {
        max-width: unset !important;
        height: 36px !important;
        line-height: 36px !important;
    }
    
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #16a34a !important;
        border-color: #16a34a !important;
    }
    
    .flatpickr-day.inRange {
        box-shadow: -5px 0 0 #dcfce7, 5px 0 0 #dcfce7 !important;
        background: #dcfce7 !important;
        border-color: #dcfce7 !important;
        color: #166534 !important;
    }
    
    /* ===== RESPONSIVE FLATPICKR ===== */
    /* Mobile: calendrier pleine largeur */
    @media (max-width: 767px) {
        .flatpickr-calendar {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .flatpickr-innerContainer {
            max-width: 100% !important;
        }
        
        .flatpickr-rContainer {
            width: 100% !important;
        }
        
        .flatpickr-days {
            width: 100% !important;
        }
        
        .dayContainer {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
        }
        
        .flatpickr-day {
            flex-basis: 14.28% !important;
            max-width: 14.28% !important;
            height: 40px !important;
            line-height: 40px !important;
            font-size: 0.9375rem !important;
            margin: 0 !important;
            border-radius: 8px !important;
        }
        
        .flatpickr-months {
            padding: 0.5rem !important;
        }
        
        .flatpickr-current-month {
            font-size: 1rem !important;
            font-weight: 600 !important;
        }
        
        .flatpickr-weekday {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            color: #166534 !important;
        }
        
        .flatpickr-weekdays {
            padding: 0.25rem 0 !important;
        }
    }
    
    /* Très petit écran */
    @media (max-width: 360px) {
        .flatpickr-day {
            height: 36px !important;
            line-height: 36px !important;
            font-size: 0.875rem !important;
        }
        
        .flatpickr-current-month {
            font-size: 0.9375rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-green-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-2 sm:py-3 lg:py-4">
        {{-- Vérifier que l'équipement est disponible --}}
        @if(($equipment->status ?? 'active') !== 'active' || !($equipment->is_available ?? true))
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
                <h2 class="text-xl font-bold text-red-700 mb-2">Équipement non disponible</h2>
                <p class="text-red-600 mb-4">Cet équipement n'est plus disponible à la location.</p>
                <a href="{{ route('equipment.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voir les autres équipements
                </a>
            </div>
        @else
        <!-- Breadcrumb -->
        <nav class="flex mb-2 sm:mb-3 lg:mb-4 overflow-x-auto" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2 md:space-x-3 whitespace-nowrap">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-green-700 hover:text-green-600">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        <span class="hidden sm:inline">Accueil</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('equipment.index') }}" class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-700 hover:text-green-600 truncate">Matériel</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('equipment.show', $equipment) }}" class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-700 hover:text-green-600 truncate max-w-24 sm:max-w-none">{{ Str::limit($equipment->name, 20) }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-500">Réserver</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Titre -->
        <div class="bg-white rounded-xl shadow-lg p-3 sm:p-5 mb-3 sm:mb-4 border border-green-100 card-hover fade-in">
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900 leading-tight break-words flex items-center">
                <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg sm:rounded-xl flex items-center justify-center mr-2.5 sm:mr-3 shadow-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="line-clamp-2">Réserver {{ Str::limit($equipment->name, 30) }}</span>
            </h1>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
            
            <!-- LEFT COLUMN: Equipment Details -->
            <div class="xl:col-span-1 order-2 xl:order-1">
                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 sticky top-4 border border-green-100 card-hover fade-in">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Détails de l'équipement</h2>
                    
                    <!-- Image -->
                    <div class="mb-3 sm:mb-4 rounded-lg overflow-hidden">
                        @php
                            $photoPath = $equipment->main_photo ?? ($equipment->photos[0] ?? null);
                        @endphp
                        @if($photoPath)
                            <x-media-image :path="$photoPath" :alt="$equipment->name" class="w-full h-40 sm:h-48 object-cover" placeholder="https://via.placeholder.com/300x200?text=Équipement" />
                        @else
                            <div class="w-full h-40 sm:h-48 bg-gray-200 flex items-center justify-center">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Nom et note -->
                    <h3 class="font-bold text-sm sm:text-base text-gray-800 mb-2">{{ $equipment->name }}</h3>
                    
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex items-center bg-yellow-50 px-2 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="text-yellow-700 font-semibold text-xs sm:text-sm">{{ number_format($equipment->average_rating, 1) }}</span>
                        </div>
                        <span class="text-gray-500 text-xs sm:text-sm">({{ $equipment->reviews_count }} avis)</span>
                    </div>

                    <!-- Prix -->
                    <div class="price-card rounded-xl p-3 sm:p-4 space-y-2 sm:space-y-3 mb-3 sm:mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-green-800 font-semibold text-sm sm:text-base">Prix par jour</span>
                            <span class="font-extrabold text-xl sm:text-2xl text-green-700">{{ number_format($equipment->price_per_day, 2) }}€</span>
                        </div>
                        @if($equipment->price_per_hour)
                        <div class="flex justify-between items-center border-t border-green-200/50 pt-2">
                            <span class="text-green-800 font-semibold text-sm sm:text-base">Prix par heure</span>
                            <span class="font-bold text-lg sm:text-xl text-green-600">{{ number_format($equipment->price_per_hour, 2) }}€</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Propriétaire -->
                    @if($equipment->prestataire)
                    <div class="border-t border-green-100 pt-3 sm:pt-4">
                        <h4 class="font-semibold text-green-900 mb-2 sm:mb-3 text-sm sm:text-base">Propriétaire</h4>
                        <a href="{{ route('prestataires.show', $equipment->prestataire) }}" class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                            @if($equipment->prestataire->photo)
                                <x-media-image :path="$equipment->prestataire->photo" :alt="$equipment->prestataire->user->name" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-green-200" />
                            @else
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-bold text-sm sm:text-base">
                                    {{ substr($equipment->prestataire->user->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-green-900 text-sm truncate">{{ $equipment->prestataire->user->name }}</div>
                                <div class="text-xs text-green-600">Voir le profil</div>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT COLUMN: Booking Form -->
            <div class="xl:col-span-2 order-1 xl:order-2">
                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 md:p-6 border border-green-100 card-hover fade-in">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4">Sélectionner vos dates</h2>
                    
                    <form action="{{ route('equipment.rent', $equipment) }}" method="POST" x-data="bookingForm()" @submit="beforeSubmit($event)">
                        @csrf
                        <input type="hidden" id="start_date" name="start_date" :value="startDate">
                        <input type="hidden" id="end_date" name="end_date" :value="endDate">
                        <input type="hidden" name="is_hourly" :value="isHourly ? '1' : '0'">
                        <input type="hidden" name="start_time" :value="startTime">
                        <input type="hidden" name="end_time" :value="endTime">

                        <!-- Messages d'erreur -->
                        @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-red-700 font-medium text-sm">Une erreur est survenue :</p>
                                    <ul class="mt-1 text-red-600 text-sm list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if (session('error'))
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-red-700 text-sm">{{ session('error') }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Toggle Switch -->
                        @if($equipment->price_per_hour && $equipment->price_per_hour > 0)
                        <div class="flex justify-center mb-4 sm:mb-6">
                            <div class="bg-gray-100 p-1.5 rounded-xl inline-flex shadow-inner w-full sm:w-auto max-w-xs">
                                <button type="button" @click="setMode(false)" :class="{'bg-white shadow-md text-green-700': !isHourly, 'text-gray-500': isHourly}" class="flex-1 sm:flex-none px-4 sm:px-6 py-3 sm:py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center min-h-[44px]">
                                    <svg class="w-4 h-4 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Par Jour
                                </button>
                                <button type="button" @click="setMode(true)" :class="{'bg-white shadow-md text-green-700': isHourly, 'text-gray-500': !isHourly}" class="flex-1 sm:flex-none px-4 sm:px-6 py-3 sm:py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center min-h-[44px]">
                                    <svg class="w-4 h-4 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Par Heure
                                </button>
                            </div>
                        </div>
                        @endif

                        <!-- Layout: calendrier en haut, résumé en bas sur mobile -->
                        <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">
                            <!-- Calendrier -->
                            <div class="w-full">
                                <h3 class="font-semibold text-green-900 mb-3 text-base flex items-center">
                                    <span class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-2">1</span>
                                    <span x-text="isHourly ? 'Choisissez une date' : 'Choisissez vos dates'">Choisissez vos dates</span>
                                </h3>
                                <div id="availability-calendar" class="rounded-xl overflow-hidden border border-green-200 bg-white"></div>
                            </div>

                            <!-- Résumé -->
                            <div class="w-full">
                                <h3 class="font-semibold text-green-900 mb-3 text-base flex items-center">
                                    <span class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-2">2</span>
                                    Options & Résumé
                                </h3>
                                
                                <div class="bg-green-50 rounded-xl border border-green-200 p-4">
                                    
                                    <!-- Horaires (mode horaire) -->
                                    @if($equipment->price_per_hour && $equipment->price_per_hour > 0)
                                    <div x-show="isHourly" x-transition class="mb-3 sm:mb-4">
                                        <label class="block text-sm font-medium text-green-700 mb-2">Horaires</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-xs text-green-600 mb-1.5 block font-medium">Début</label>
                                                <input type="time" x-model="startTime" @change="calculateTotal()" class="w-full rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-base py-3 px-3 min-h-[48px]">
                                            </div>
                                            <div>
                                                <label class="text-xs text-green-600 mb-1.5 block font-medium">Fin</label>
                                                <input type="time" x-model="endTime" @change="calculateTotal()" class="w-full rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-base py-3 px-3 min-h-[48px]">
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Récapitulatif -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-green-700 font-medium">Durée</span>
                                            <span class="font-bold text-green-900 text-base" x-text="duration > 0 ? duration + (isHourly ? ' heure(s)' : ' jour(s)') : '-'">-</span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-green-700 font-medium">Tarif unitaire</span>
                                            <span class="font-bold text-green-900" x-text="isHourly ? '{{ number_format($equipment->price_per_hour ?? 0, 2) }}€/h' : '{{ number_format($equipment->price_per_day, 2) }}€/jour'"></span>
                                        </div>
                                        <div class="flex justify-between items-center text-lg font-bold border-t-2 border-green-200 pt-3 mt-2">
                                            <span class="text-green-800">Total à payer</span>
                                            <span class="text-green-600 text-xl" x-text="totalPrice.toFixed(2) + ' €'">0.00 €</span>
                                        </div>
                                        
                                        <button id="submit-btn" type="submit" class="w-full btn-reserve text-white py-4 rounded-xl font-bold shadow-lg transition duration-300 disabled:cursor-not-allowed disabled:opacity-50 mt-4 text-lg flex items-center justify-center gap-2 min-h-[56px]" :disabled="!isValid || isSubmitting">
                                            <template x-if="isSubmitting">
                                                <svg class="animate-spin w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="!isSubmitting">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </template>
                                            <span x-text="isSubmitting ? 'Envoi en cours...' : (isValid ? 'Confirmer la réservation' : 'Sélectionnez des dates')">Sélectionnez des dates</span>
                                        </button>
                                        
                                        <!-- Message d'aide -->
                                        <p x-show="!isValid" class="text-center text-sm text-gray-500 mt-2">
                                            <span x-show="!isHourly">Cliquez sur une date de début puis une date de fin</span>
                                            <span x-show="isHourly">Sélectionnez une date et des horaires</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/fr.js"></script>
<script>
    function bookingForm() {
        return {
            isHourly: false,
            allowHourly: {{ ($equipment->price_per_hour && $equipment->price_per_hour > 0) ? 'true' : 'false' }},
            startDate: '',
            endDate: '',
            startTime: '',
            endTime: '',
            pricePerDay: {{ $equipment->price_per_day }},
            pricePerHour: {{ $equipment->price_per_hour ?? 0 }},
            totalPrice: 0,
            duration: 0,
            calendar: null,
            isValid: false,
            isSubmitting: false,

            init() {
                this.initCalendar();
            },

            beforeSubmit(e) {
                // Empêcher double soumission
                if (this.isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                // Vérifier que les dates sont valides
                if (!this.isValid || !this.startDate || !this.endDate) {
                    e.preventDefault();
                    alert('Veuillez sélectionner des dates valides');
                    return false;
                }
                
                // Marquer comme en cours de soumission
                this.isSubmitting = true;
                
                // Laisser le formulaire se soumettre
                return true;
            },

            setMode(hourly) {
                // Ne permet pas le mode horaire si pas de prix à l'heure
                if (hourly && !this.allowHourly) {
                    return;
                }
                this.isHourly = hourly;
                this.startDate = '';
                this.endDate = '';
                this.startTime = '';
                this.endTime = '';
                this.totalPrice = 0;
                this.duration = 0;
                this.isValid = false;
                
                if (this.calendar) {
                    this.calendar.destroy();
                }
                this.initCalendar();
            },

            initCalendar() {
                const unavailableDates = @json($unavailableDates);
                const availabilityPeriod = @json($availabilityPeriod);
                
                let minDate = "today";
                let maxDate = null;
                
                if (availabilityPeriod.available_from) {
                    const availableFrom = new Date(availabilityPeriod.available_from);
                    const today = new Date();
                    minDate = availableFrom > today ? availabilityPeriod.available_from : "today";
                }
                
                if (availabilityPeriod.available_until) {
                    maxDate = availabilityPeriod.available_until;
                }

                const self = this;

                this.calendar = flatpickr("#availability-calendar", {
                    inline: true,
                    mode: this.isHourly ? "single" : "range",
                    dateFormat: "Y-m-d",
                    minDate: minDate,
                    maxDate: maxDate,
                    disable: unavailableDates,
                    locale: "fr",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (self.isHourly) {
                            if (selectedDates.length === 1) {
                                self.startDate = instance.formatDate(selectedDates[0], 'Y-m-d');
                                self.endDate = self.startDate;
                                self.calculateTotal();
                            }
                        } else {
                            if (selectedDates.length === 2) {
                                self.startDate = instance.formatDate(selectedDates[0], 'Y-m-d');
                                self.endDate = instance.formatDate(selectedDates[1], 'Y-m-d');
                                self.calculateTotal();
                            } else {
                                self.duration = 0;
                                self.totalPrice = 0;
                                self.isValid = false;
                            }
                        }
                    }
                });
            },

            calculateTotal() {
                if (this.isHourly) {
                    if (this.startDate && this.startTime && this.endTime) {
                        let start = new Date(this.startDate + 'T' + this.startTime);
                        let end = new Date(this.startDate + 'T' + this.endTime);
                        
                        if (end <= start) {
                            this.duration = 0;
                            this.totalPrice = 0;
                            this.isValid = false;
                        } else {
                            let diff = (end - start) / (1000 * 60 * 60);
                            this.duration = Math.max(1, Math.ceil(diff));
                            this.totalPrice = this.duration * this.pricePerHour;
                            this.isValid = true;
                        }
                    } else {
                        this.duration = 0;
                        this.totalPrice = 0;
                        this.isValid = false;
                    }
                } else {
                    if (this.startDate && this.endDate) {
                        const start = new Date(this.startDate);
                        const end = new Date(this.endDate);
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        this.duration = diffDays;
                        this.totalPrice = this.duration * this.pricePerDay;
                        this.isValid = true;
                    } else {
                        this.duration = 0;
                        this.totalPrice = 0;
                        this.isValid = false;
                    }
                }
            }
        }
    }
</script>
@endpush
        @endif
