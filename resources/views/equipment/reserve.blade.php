@extends('layouts.app')

@section('title', 'Réserver ' . $equipment->name)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_green.css">
    <style>
        .flatpickr-day.unavailable {
            background-color: #f3f4f6;
            border-color: #f3f4f6;
            color: #d1d5db;
            cursor: not-allowed;
        }

        /* Animations et transitions */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-green {
            animation: pulseGreen 2s infinite;
        }

        @keyframes pulseGreen {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }
        }

        /* Amélioration des cartes */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Indicateur de sélection */
        .selection-indicator {
            position: relative;
            overflow: hidden;
        }

        .selection-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.2), transparent);
            transition: left 0.5s;
        }

        .selection-indicator.active::before {
            left: 100%;
        }

        /* Slot grid pour horaires 3 par ligne */
        .slot-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }

        @media (max-width: 640px) {
            .slot-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        /* Style pour boutons horaires */
        .time-slot-btn {
            transition: all 0.2s ease;
        }

        .time-slot-btn:hover:not(.selected):not(:disabled) {
            background-color: #dcfce7;
            border-color: #86efac;
            transform: scale(1.02);
        }

        .time-slot-btn.selected {
            background-color: #22c55e;
            border-color: #16a34a;
            color: white;
        }

        .time-slot-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f3f4f6;
            color: #9ca3af;
        }

        /* Amélioration du calendrier */
        #availability-calendar {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Badge de statut */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-selected {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
@endpush

@section('content')
    <div class="bg-green-50 min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 max-w-[2000px]">
            <!-- Breadcrumb -->
            <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs sm:text-sm">
                    <li class="inline-flex items-center">
                        <a href="{{ route('equipment.index') }}"
                            class="inline-flex items-center font-medium text-green-700 hover:text-green-600">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                            <span class="hidden sm:inline">Équipements</span>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-green-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 9 4-4-4-4" />
                            </svg>
                            <a href="{{ route('equipment.show', $equipment) }}"
                                class="ml-1 font-medium text-green-700 hover:text-green-600 md:ml-2 truncate max-w-32 sm:max-w-none">{{ Str::limit($equipment->name, 20) }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-green-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 9 4-4-4-4" />
                            </svg>
                            <span class="ml-1 font-medium text-green-500 md:ml-2">Réserver</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center px-2">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2 leading-tight">Réserver
                    "{{ $equipment->name }}"</h1>
                <p class="text-sm sm:text-base lg:text-lg text-green-700">Sélectionnez vos dates et confirmez votre
                    réservation</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
                <!-- Colonne de gauche : Formulaire de réservation -->
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border border-green-200 card-hover fade-in">
                    <!-- Indicateur d'étape -->
                    <div class="flex items-center mb-4 sm:mb-6">
                        <div class="flex items-center">
                            <div
                                class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold mr-2 sm:mr-3">
                                1
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700">Sélection des dates</span>
                        </div>
                        <div class="flex-1 h-px bg-gray-200 mx-2 sm:mx-4"></div>
                        <div class="flex items-center">
                            <div id="step-2"
                                class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold mr-2 sm:mr-3 transition-all duration-300">
                                2
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-gray-400">Confirmation</span>
                        </div>
                    </div>

                    <form action="{{ route('equipment.rent', $equipment) }}" method="POST">
                        @csrf
                        <input type="hidden" id="start_date" name="start_date">
                        <input type="hidden" id="end_date" name="end_date">
                        <input type="hidden" id="pickup_time" name="pickup_time" value="14:00">
                        <input type="hidden" id="return_time" name="return_time" value="17:00">

                        <div
                            class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl selection-indicator">
                            <div class="flex items-center mb-2 flex-wrap gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 mr-1 sm:mr-2 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0a1 1 0 00-1 1v10a1 1 0 001 1h6a1 1 0 001-1V8a1 1 0 00-1-1m-6 0V7">
                                    </path>
                                </svg>
                                <h3 class="text-base sm:text-lg font-semibold text-green-800 flex-1">Sélection des dates
                                </h3>
                                <span id="selection-status" class="status-badge status-available text-xs">Disponible</span>
                            </div>
                            <p class="text-xs sm:text-sm text-green-600 leading-relaxed">Choisissez vos dates de location
                                directement sur le calendrier à droite en sélectionnant une période.</p>
                            <div id="selected-dates" class="mt-3 text-xs sm:text-sm text-green-700 font-medium hidden">
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span id="date-range-text" class="break-words"></span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 sm:p-6 rounded-xl my-4 sm:my-6 border border-green-200 shadow-sm">
                            <div class="flex items-center mb-3 sm:mb-4">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <h3 class="text-base sm:text-lg font-semibold text-green-800">Résumé de la location</h3>
                            </div>
                            <div class="space-y-2 sm:space-y-3">
                                <div class="flex justify-between items-center p-2 sm:p-3 bg-white rounded-lg shadow-sm">
                                    <span class="text-sm sm:text-base text-green-600 font-medium">Prix par jour:</span>
                                    <span
                                        class="font-bold text-green-900 text-sm sm:text-lg">{{ number_format($equipment->price_per_day, 2) }}
                                        €</span>
                                </div>
                                <div class="flex justify-between items-center p-2 sm:p-3 bg-white rounded-lg shadow-sm">
                                    <span class="text-sm sm:text-base text-green-600 font-medium">Nombre de jours:</span>
                                    <span id="rental_days"
                                        class="font-bold text-green-900 text-sm sm:text-lg transition-all duration-300">0</span>
                                </div>
                                <div
                                    class="flex justify-between items-center p-3 sm:p-4 bg-gradient-to-r from-green-100 to-emerald-100 rounded-lg border-2 border-green-200">
                                    <span class="text-lg sm:text-xl font-bold text-green-800">Total estimé:</span>
                                    <span id="total_price"
                                        class="text-xl sm:text-2xl font-bold text-green-900 transition-all duration-300">0.00
                                        €</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row justify-between items-center pt-6 sm:pt-8 border-t border-green-200 gap-3 sm:gap-0">
                            <a href="{{ route('equipment.show', $equipment) }}"
                                class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                                <i class="fas fa-arrow-left mr-2"></i>Retour
                            </a>
                            <button id="submit-btn" type="submit"
                                class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                                disabled>
                                <i class="fas fa-check mr-2"></i>
                                <span id="submit-text">Sélectionnez des dates</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colonne de droite : Récap produit + Calendrier + Horaires -->
                <div class="space-y-4 sm:space-y-6 lg:sticky lg:top-4 lg:self-start">
                    <!-- Mini récap du produit -->
                    <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border border-green-200 card-hover fade-in">
                        <div class="flex flex-col gap-4">
                            <!-- Image et titre -->
                            <div class="flex items-start gap-4">

                                @if($equipment->photos && count($equipment->photos) > 0)

                                    <img src="{{ asset('storage/' . $equipment->photos[0]) }}" alt="{{ $equipment->name }}"
                                        class="w-24 h-24 object-cover rounded-lg flex-shrink-0 border-2 border-green-100">
                                @else
                                    <div
                                        class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 border-2 border-gray-300">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-green-900 mb-1">{{ $equipment->name }}</h3>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <span
                                            class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                                            {{ $equipment->description }}
                                        </span>
                                    </p>
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="text-2xl font-bold text-green-600">{{ number_format($equipment->price_per_day, 2) }}
                                            €</span>
                                        <span class="text-sm text-gray-500">/ jour</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($equipment->description)
                                <div class="pt-3 border-t border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-2">Description</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">{{ $equipment->description }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Informations sur les contraintes de location -->
                        <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                            <h4 class="text-sm font-semibold text-green-800 mb-2">Informations de location</h4>
                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="font-medium text-gray-700">Prix journalier :</span>
                                    <span
                                        class="text-green-600 font-semibold">{{ number_format($equipment->price_per_day, 2) }}
                                        €</span>
                                </div>
                                @if($equipment->min_rental_days)
                                    <div>
                                        <span class="font-medium text-gray-700">Location minimum :</span>
                                        <span class="text-green-600 font-semibold">{{ $equipment->min_rental_days }}
                                            jour{{ $equipment->min_rental_days > 1 ? 's' : '' }}</span>
                                    </div>
                                @endif
                                @if($equipment->max_rental_days)
                                    <div>
                                        <span class="font-medium text-gray-700">Location maximum :</span>
                                        <span class="text-green-600 font-semibold">{{ $equipment->max_rental_days }}
                                            jour{{ $equipment->max_rental_days > 1 ? 's' : '' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Calendrier et Horaires -->
                    <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border border-green-200 card-hover fade-in">
                        <div class="flex items-center mb-4 sm:mb-6">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 mr-2 sm:mr-3 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0a1 1 0 00-1 1v10a1 1 0 001 1h6a1 1 0 001-1V8a1 1 0 00-1-1m-6 0V7">
                                </path>
                            </svg>
                            <h3 class="text-lg sm:text-xl font-semibold text-green-900 leading-tight">Calendrier des
                                disponibilités</h3>
                        </div>

                        <!-- Affichage des dates de disponibilité -->
                        @if($availabilityPeriod['available_from'] || $availabilityPeriod['available_until'])
                            <div
                                class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                                <h4 class="text-xs sm:text-sm font-semibold text-green-800 mb-2 sm:mb-3 flex items-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="leading-tight">Période de disponibilité</span>
                                </h4>
                                <div class="space-y-2 text-xs sm:text-sm text-green-700">
                                    @if($availabilityPeriod['available_from'])
                                        <div class="flex items-start p-2 bg-white rounded-lg">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-green-500 flex-shrink-0 mt-0.5"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5 9.293 8.207a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 9.586z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="leading-relaxed"><strong>Disponible à partir du :</strong>
                                                {{ \Carbon\Carbon::parse($availabilityPeriod['available_from'])->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if($availabilityPeriod['available_until'])
                                        <div class="flex items-start p-2 bg-white rounded-lg">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-orange-500 flex-shrink-0 mt-0.5"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="leading-relaxed"><strong>Disponible jusqu'au :</strong>
                                                {{ \Carbon\Carbon::parse($availabilityPeriod['available_until'])->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Grid pour calendrier et horaires côte à côte -->
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <!-- Calendrier -->
                            <div>
                                <div id="availability-calendar" class="bg-white rounded-xl overflow-hidden"></div>
                            </div>

                            <!-- Section Horaires -->
                            <div id="time-slots-section" class="fade-in">
                                <div class="flex items-center mb-4">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-green-900">Horaires</h3>
                                </div>

                                <!-- Message par défaut -->
                                <div id="no-date-message" class="text-center py-8">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p class="text-gray-500 text-sm">Sélectionnez une date pour choisir vos horaires</p>
                                </div>

                                <!-- Conteneur des horaires (caché par défaut) -->
                                <div id="time-slots-content" class="hidden">
                                    <!-- Horaire de récupération -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">📋 Début de
                                            location</label>
                                        <div id="pickup-time-grid" class="slot-grid">
                                            <button type="button" data-time="08:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">08:00</button>
                                            <button type="button" data-time="09:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">09:00</button>
                                            <button type="button" data-time="10:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">10:00</button>
                                            <button type="button" data-time="11:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">11:00</button>
                                            <button type="button" data-time="12:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">12:00</button>
                                            <button type="button" data-time="13:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">13:00</button>
                                            <button type="button" data-time="14:00"
                                                class="time-slot-btn pickup-slot selected px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">14:00</button>
                                            <button type="button" data-time="15:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">15:00</button>
                                            <button type="button" data-time="16:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">16:00</button>
                                            <button type="button" data-time="17:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">17:00</button>
                                            <button type="button" data-time="18:00"
                                                class="time-slot-btn pickup-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">18:00</button>
                                        </div>
                                    </div>

                                    <!-- Horaire de retour -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">🔙 Fin de
                                            location</label>
                                        <div id="return-time-grid" class="slot-grid">
                                            <button type="button" data-time="08:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">08:00</button>
                                            <button type="button" data-time="09:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">09:00</button>
                                            <button type="button" data-time="10:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">10:00</button>
                                            <button type="button" data-time="11:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">11:00</button>
                                            <button type="button" data-time="12:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">12:00</button>
                                            <button type="button" data-time="13:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">13:00</button>
                                            <button type="button" data-time="14:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">14:00</button>
                                            <button type="button" data-time="15:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">15:00</button>
                                            <button type="button" data-time="16:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">16:00</button>
                                            <button type="button" data-time="17:00"
                                                class="time-slot-btn return-slot selected px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">17:00</button>
                                            <button type="button" data-time="18:00"
                                                class="time-slot-btn return-slot px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500">18:00</button>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <p class="text-xs text-blue-700">
                                            <strong>💡 Astuce :</strong> Les horaires peuvent être ajustés lors de la
                                            confirmation
                                            avec le loueur.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const unavailableDates = @json($unavailableDates);
            const availabilityPeriod = @json($availabilityPeriod);
            const pricePerDay = {{ $equipment->price_per_day }};

            // Éléments DOM
            const selectionIndicator = document.querySelector('.selection-indicator');
            const selectionStatus = document.getElementById('selection-status');
            const selectedDatesDiv = document.getElementById('selected-dates');
            const dateRangeText = document.getElementById('date-range-text');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const step2 = document.getElementById('step-2');
            const rentalDays = document.getElementById('rental_days');
            const totalPrice = document.getElementById('total_price');

            // Déterminer les dates min et max
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

            const calendar = flatpickr("#availability-calendar", {
                inline: true,
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: minDate,
                maxDate: maxDate,
                disable: unavailableDates,
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                        longhand: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
                    },
                    months: {
                        shorthand: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                        longhand: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
                    }
                },
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        // Mettre à jour les champs cachés
                        document.getElementById('start_date').value = instance.formatDate(selectedDates[0], 'Y-m-d');
                        document.getElementById('end_date').value = instance.formatDate(selectedDates[1], 'Y-m-d');

                        // Afficher les dates sélectionnées
                        const startDate = selectedDates[0].toLocaleDateString('fr-FR');
                        const endDate = selectedDates[1].toLocaleDateString('fr-FR');
                        dateRangeText.textContent = `Du ${startDate} au ${endDate}`;
                        selectedDatesDiv.classList.remove('hidden');

                        // Mettre à jour le statut
                        selectionStatus.textContent = 'Sélectionné';
                        selectionStatus.className = 'status-badge status-selected';

                        // Activer l'animation de l'indicateur
                        selectionIndicator.classList.add('active');

                        // Activer l'étape 2
                        step2.className = 'w-6 h-6 sm:w-8 sm:h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold mr-2 sm:mr-3 transition-all duration-300';
                        step2.nextElementSibling.className = 'text-xs sm:text-sm font-medium text-green-600';

                        // Afficher les horaires
                        document.getElementById('no-date-message').classList.add('hidden');
                        document.getElementById('time-slots-content').classList.remove('hidden');

                        // Mettre à jour les créneaux de retour selon les dates
                        setTimeout(() => {
                            if (typeof updateReturnSlots === 'function') {
                                updateReturnSlots();
                            }
                        }, 100);

                        // Activer le bouton de soumission
                        submitBtn.disabled = false;
                        submitBtn.classList.add('pulse-green');
                        submitText.textContent = 'Confirmer la réservation';

                    } else {
                        // Réinitialiser l'affichage
                        selectedDatesDiv.classList.add('hidden');
                        selectionStatus.textContent = 'Disponible';
                        selectionStatus.className = 'status-badge status-available';
                        selectionIndicator.classList.remove('active');

                        // Désactiver l'étape 2
                        step2.className = 'w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold mr-2 sm:mr-3 transition-all duration-300';
                        step2.nextElementSibling.className = 'text-xs sm:text-sm font-medium text-gray-400';

                        // Masquer les horaires et afficher le message
                        document.getElementById('no-date-message').classList.remove('hidden');
                        document.getElementById('time-slots-content').classList.add('hidden');

                        // Désactiver le bouton de soumission
                        submitBtn.disabled = true;
                        submitBtn.classList.remove('pulse-green');
                        submitText.textContent = 'Sélectionnez des dates';
                    }
                    updatePrice(selectedDates);
                },
                onReady: function () {
                    setTimeout(() => {
                        const calendarContainer = document.querySelector('.flatpickr-calendar');
                        if (calendarContainer) {
                            calendarContainer.style.boxShadow = 'none';
                            calendarContainer.style.border = 'none';
                        }
                    }, 100);
                }
            });

            function updatePrice(dates) {
                if (dates.length === 2 && dates[0] && dates[1]) {
                    const start = new Date(dates[0]);
                    const end = new Date(dates[1]);
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                    animateNumber(rentalDays, parseInt(rentalDays.textContent) || 0, diffDays);
                    animateNumber(totalPrice, parseFloat(totalPrice.textContent.replace(' €', '')) || 0, diffDays * pricePerDay, ' €');

                } else {
                    animateNumber(rentalDays, parseInt(rentalDays.textContent) || 0, 0);
                    animateNumber(totalPrice, parseFloat(totalPrice.textContent.replace(' €', '')) || 0, 0, ' €');
                }
            }

            function animateNumber(element, start, end, suffix = '') {
                const duration = 500;
                const startTime = performance.now();

                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    const current = start + (end - start) * easeOutCubic(progress);

                    if (suffix === ' €') {
                        element.textContent = current.toFixed(2) + suffix;
                    } else {
                        element.textContent = Math.round(current) + suffix;
                    }

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                }

                requestAnimationFrame(update);
            }

            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }

            // Animation d'entrée pour les cartes
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Gestion des clics sur les boutons horaires
            const pickupSlots = document.querySelectorAll('.pickup-slot');
            const returnSlots = document.querySelectorAll('.return-slot');
            const pickupInput = document.getElementById('pickup_time');
            const returnInput = document.getElementById('return_time');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // Fonction pour vérifier si c'est le même jour
            function isSameDay() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                return startDate && endDate && startDate === endDate;
            }

            // Fonction pour désactiver les heures de retour invalides
            function updateReturnSlots() {
                if (!isSameDay()) {
                    // Si location sur plusieurs jours, toutes les heures sont disponibles
                    returnSlots.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    });
                    return;
                }

                // Si même jour, désactiver les heures <= heure de début
                const pickupTime = pickupInput.value;
                const pickupHour = parseInt(pickupTime.split(':')[0]);

                returnSlots.forEach(btn => {
                    const returnTime = btn.getAttribute('data-time');
                    const returnHour = parseInt(returnTime.split(':')[0]);

                    if (returnHour <= pickupHour) {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        btn.classList.remove('selected');
                    } else {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                });

                // Vérifier si l'heure de retour actuelle est toujours valide
                const currentReturnTime = returnInput.value;
                const currentReturnHour = parseInt(currentReturnTime.split(':')[0]);

                if (currentReturnHour <= pickupHour) {
                    // Sélectionner automatiquement la prochaine heure disponible
                    const nextAvailableSlot = Array.from(returnSlots).find(btn => {
                        const time = btn.getAttribute('data-time');
                        const hour = parseInt(time.split(':')[0]);
                        return hour > pickupHour;
                    });

                    if (nextAvailableSlot) {
                        returnSlots.forEach(btn => btn.classList.remove('selected'));
                        nextAvailableSlot.classList.add('selected');
                        returnInput.value = nextAvailableSlot.getAttribute('data-time');
                    }
                }
            }

            pickupSlots.forEach(button => {
                button.addEventListener('click', function () {
                    pickupSlots.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    pickupInput.value = this.getAttribute('data-time');

                    // Mettre à jour les créneaux de retour
                    updateReturnSlots();
                });
            });

            returnSlots.forEach(button => {
                button.addEventListener('click', function () {
                    // Vérifier si le bouton est désactivé
                    if (this.disabled) {
                        return;
                    }

                    returnSlots.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    returnInput.value = this.getAttribute('data-time');
                });
            });
        });
    </script>
@endpush