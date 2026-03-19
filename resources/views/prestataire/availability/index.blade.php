@extends('layouts.app')

@section('title', 'Gestion des disponibilités - Prestataire')

@section('styles')
<style>
    .day-card {
        transition: all 0.3s ease;
        border: 2px solid #dbeafe;
    }
    .day-card.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    .day-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    .time-input {
        border: 2px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem;
        width: 100%;
        transition: all 0.2s;
        font-weight: 500;
        color: #1e3a8a;
        background-color: #f8fafc;
    }
    .time-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }
    .time-input:hover {
        border-color: #93c5fd;
        background-color: #f1f5f9;
    }
    
    /* Labels for time inputs */
    .time-input-container label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: color 0.2s ease;
    }
    
    .time-input-container:hover label {
        color: #1e40af;
    }
    
    /* Cacher les champs de configuration par défaut */
    .day-inputs-hidden {
        display: none;
    }
    
    /* Animation pour l'affichage des champs */
    .day-inputs-container {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    /* Time input containers */
    .time-input-container {
        transition: all 0.3s ease;
    }
    
    .time-input-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    /* Focus state for time inputs */
    .time-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }
    
    /* Améliorations responsives */
    @media (max-width: 640px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .day-card {
            padding: 0.75rem;
        }
        
        .quick-config-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .quick-config-buttons button {
            width: 100%;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .time-inputs-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .config-summary {
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }
        
        .time-input {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .time-input-container {
            padding: 0.5rem;
        }
        
        .time-input-container label {
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        h3 {
            font-size: 1rem;
        }
        
        .text-sm {
            font-size: 0.75rem;
        }
        
        .text-base {
            font-size: 0.875rem;
        }
        
        .px-4 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .py-4 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .px-6 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .py-6 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .mb-6 {
            margin-bottom: 1rem;
        }
        
        .mt-8 {
            margin-top: 1rem;
        }
        
        .pt-6 {
            padding-top: 1rem;
        }
        
        .pb-6 {
            padding-bottom: 1rem;
        }
        
        .gap-4 {
            gap: 0.5rem;
        }
        
        .gap-6 {
            gap: 1rem;
        }
        
        .p-4 {
            padding: 0.75rem;
        }
        
        .p-6 {
            padding: 0.75rem;
        }
        
        .rounded-xl {
            border-radius: 0.75rem;
        }
        
        .text-xl {
            font-size: 1.125rem;
        }
        
        .text-2xl {
            font-size: 1.25rem;
        }
        
        .text-lg {
            font-size: 1rem;
        }
        
        .h-5 {
            height: 1rem;
        }
        
        .w-5 {
            width: 1rem;
        }
        
        .px-8 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .py-4 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .text-sm {
            font-size: 0.75rem;
        }
        
        .text-base {
            font-size: 0.875rem;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .time-inputs-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .py-6 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .gap-4 {
            gap: 1rem;
        }
        
        .gap-6 {
            gap: 1.25rem;
        }
        
        .p-4 {
            padding: 1rem;
        }
        
        .p-6 {
            padding: 1rem;
        }
    }
    
    /* Desktop improvements */
    @media (min-width: 1025px) {
        .time-inputs-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .day-card {
            padding: 1.5rem;
        }
        
        .time-input-container {
            padding: 1rem;
        }
        
        .time-input {
            padding: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-2 sm:px-4 py-3 sm:py-6">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête -->
            <div class="mb-4 sm:mb-6 text-center">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-blue-900 mb-1 sm:mb-2">Gestion des disponibilités</h1>
                <p class="text-xs sm:text-sm md:text-base text-blue-700 px-1 sm:px-2">Configurez vos horaires de travail et gérez vos exceptions</p>
                <div class="mt-3 sm:mt-4">
                    <a href="{{ route('prestataire.dashboard') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 md:px-6 md:py-3 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold rounded-lg transition duration-200 text-xs sm:text-sm md:text-base">
                        ← Retour au tableau de bord
                    </a>
                </div>
            </div>

            <!-- Messages de succès/erreur -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 sm:p-4 rounded-md mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-3 sm:p-4 rounded-md mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('warning') }}
                    @if(session('availability_setup_return_url'))
                        <div class="mt-3">
                            <a href="{{ session('availability_setup_return_url') }}" class="inline-flex items-center px-3 py-2 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 font-semibold rounded-lg transition duration-200">
                                ← Retour à la page précédente
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 rounded-md mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Guide explicatif avec aide -->
            <x-help-box 
                id="availability-guide" 
                type="info" 
                title="💡 Comment gérer vos disponibilités ?" 
                :dismissible="true"
            >
                <div class="space-y-3">
                    <p class="font-medium">Optimisez vos horaires pour maximiser vos réservations :</p>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs sm:text-sm">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">✓</span>
                            <span><strong>Activez vos jours :</strong> Cochez les jours où vous êtes disponible</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">✓</span>
                            <span><strong>Définissez vos horaires :</strong> Heure de début et fin de votre journée</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">✓</span>
                            <span><strong>Pause déjeuner :</strong> Indiquez votre pause pour ne pas être dérangé</span>
                        </li>
                    </ul>
                    
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-xs sm:text-sm text-amber-800">
                            <strong>💡 Astuce :</strong> Utilisez les boutons de configuration rapide pour appliquer des modèles prédéfinis (journée classique 9h-18h, matinée seulement, etc.)
                        </p>
                    </div>
                </div>
            </x-help-box>

            <!-- Actions rapides de configuration -->
            <div class="bg-white rounded-lg shadow-md border border-blue-100 p-3 sm:p-4 mb-4 sm:mb-6">
                <h3 class="text-sm sm:text-base font-bold text-blue-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Configuration rapide
                </h3>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyPreset('classic')" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs sm:text-sm font-medium rounded-lg transition">
                        🕘 Journée classique (9h-18h)
                    </button>
                    <button type="button" onclick="applyPreset('morning')" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-green-100 hover:bg-green-200 text-green-800 text-xs sm:text-sm font-medium rounded-lg transition">
                        🌅 Matinées (8h-12h)
                    </button>
                    <button type="button" onclick="applyPreset('afternoon')" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs sm:text-sm font-medium rounded-lg transition">
                        🌇 Après-midis (14h-18h)
                    </button>
                    <button type="button" onclick="applyPreset('fullweek')" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs sm:text-sm font-medium rounded-lg transition">
                        📅 Semaine complète (Lun-Sam)
                    </button>
                    <button type="button" onclick="resetToDefault()" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs sm:text-sm font-medium rounded-lg transition">
                        🔄 Réinitialiser (Lun-Ven 9h-17h)
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:gap-6">
                <!-- Section principale: Disponibilités hebdomadaires -->
                <div class="w-full">
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-blue-200">
                        <div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 border-b-2 border-blue-200">
                            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-blue-800">Disponibilités hebdomadaires</h2>
                            <p class="text-xs sm:text-sm md:text-base text-blue-700 mt-1">Définissez vos horaires de travail pour chaque jour de la semaine</p>
                        </div>
                
                <form action="{{ route('prestataire.availability.updateWeekly') }}" method="POST" class="p-3 sm:p-4 md:p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4 sm:space-y-6">
                        @php
                            $days = [
                                1 => 'Lundi',
                                2 => 'Mardi', 
                                3 => 'Mercredi',
                                4 => 'Jeudi',
                                5 => 'Vendredi',
                                6 => 'Samedi',
                                0 => 'Dimanche'
                            ];
                        @endphp
                        
                        @foreach($days as $dayNumber => $dayName)
                            @php
                                $availability = $weeklyAvailability->firstWhere('day_of_week', $dayNumber);
                            @endphp
                            
                            <div class="day-card rounded-lg sm:rounded-xl p-3 sm:p-4 md:p-6 {{ $availability && $availability->is_active ? 'active' : '' }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 gap-2 sm:gap-3">
                                    <h3 class="text-base sm:text-lg md:text-xl font-bold text-blue-900">{{ $dayName }}</h3>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" 
                                               name="days[{{ $dayNumber }}][is_active]" 
                                               value="1"
                                               {{ $availability && $availability->is_active ? 'checked' : '' }}
                                               class="form-checkbox h-4 w-4 sm:h-5 sm:w-5 text-blue-600 rounded focus:ring-blue-500 border-blue-300"
                                               onchange="toggleDayInputs({{ $dayNumber }})">
                                        <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm font-semibold text-blue-800">Actif</span>
                                    </label>
                                </div>
                                
                                <div id="day-inputs-{{ $dayNumber }}" class="day-inputs-container {{ $availability && $availability->is_active ? '' : 'day-inputs-hidden' }}">
                                    <!-- Horaires de travail -->
                                    <div class="time-inputs-grid grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 md:gap-4 mb-3">
                                        <div class="time-input-container bg-blue-50 rounded-lg p-2 sm:p-3 border border-blue-100 hover:border-blue-200 transition-colors duration-200">
                                            <label class="block text-xs font-semibold text-blue-800 mb-1">🕘 Début</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][start_time]" 
                                                   value="{{ $availability ? $availability->start_time?->format('H:i') : '09:00' }}"
                                                   class="time-input text-xs sm:text-sm">
                                        </div>
                                        
                                        <div class="time-input-container bg-blue-50 rounded-lg p-2 sm:p-3 border border-blue-100 hover:border-blue-200 transition-colors duration-200">
                                            <label class="block text-xs font-semibold text-blue-800 mb-1">🕕 Fin</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][end_time]" 
                                                   value="{{ $availability ? $availability->end_time?->format('H:i') : '17:00' }}"
                                                   class="time-input text-xs sm:text-sm">
                                        </div>
                                        
                                        <!-- Pause déjeuner -->
                                        <div class="time-input-container bg-amber-50 rounded-lg p-2 sm:p-3 border border-amber-100 hover:border-amber-200 transition-colors duration-200">
                                            <label class="block text-xs font-semibold text-amber-800 mb-1">🍽️ Pause début</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][break_start]" 
                                                   value="{{ $availability && $availability->break_start ? $availability->break_start->format('H:i') : '12:00' }}"
                                                   class="time-input text-xs sm:text-sm border-amber-200 focus:border-amber-400 focus:ring-amber-300">
                                        </div>
                                        
                                        <div class="time-input-container bg-amber-50 rounded-lg p-2 sm:p-3 border border-amber-100 hover:border-amber-200 transition-colors duration-200">
                                            <label class="block text-xs font-semibold text-amber-800 mb-1">🍽️ Pause fin</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][break_end]" 
                                                   value="{{ $availability && $availability->break_end ? $availability->break_end->format('H:i') : '14:00' }}"
                                                   class="time-input text-xs sm:text-sm border-amber-200 focus:border-amber-400 focus:ring-amber-300">
                                        </div>
                                    </div>
                                    
                                    <!-- Options -->
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <input type="hidden" name="days[{{ $dayNumber }}][slot_duration]" value="60">
                                        
                                        <button type="button" 
                                                onclick="copyDayToAll({{ $dayNumber }})" 
                                                class="text-xs px-2 py-1.5 sm:px-3 sm:py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Copier à tous
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Résumé de la configuration -->
                    <div class="bg-blue-50 rounded-lg p-3 sm:p-4 mt-4 sm:mt-6 border border-blue-200">
                        <h4 class="text-sm sm:text-base font-bold text-blue-800 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Résumé de votre configuration
                        </h4>
                        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-blue-700">
                            <div class="flex items-center gap-1">
                                <span class="font-semibold">📅 Jours actifs:</span>
                                <span id="active-days-count" class="bg-blue-200 px-2 py-0.5 rounded-full font-bold">0</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span id="config-summary" class="text-blue-600"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conseils et bonnes pratiques -->
                    <div class="bg-green-50 rounded-lg p-3 sm:p-4 mt-3 sm:mt-4 border border-green-200">
                        <h4 class="text-sm sm:text-base font-bold text-green-800 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            💡 Conseils pour optimiser vos réservations
                        </h4>
                        <ul class="text-xs sm:text-sm text-green-700 space-y-1">
                            <li>• <strong>Pause déjeuner:</strong> Protégez votre temps de repos pour rester productif</li>
                            <li>• <strong>Horaires larges:</strong> Plus de disponibilités = plus de réservations</li>
                            <li>• <strong>Samedi:</strong> Beaucoup de clients préfèrent le week-end</li>
                            <li>• <strong>Durée du service:</strong> Configurée dans chaque service (page Modifier)</li>
                        </ul>
                    </div>
                    
                        <div class="border-t-2 border-blue-200 pt-4 sm:pt-6 mt-6 sm:mt-8">
                            <div class="flex justify-center">
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-3 md:px-8 md:py-4 bg-linear-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-xs sm:text-sm md:text-base">
                                    <svg class="-ml-1 mr-1 h-4 w-4 sm:h-5 sm:w-5 md:-ml-1 md:mr-2 md:h-5 md:w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Enregistrer les disponibilités
                                </button>
                            </div>
                        </div>
                </form>
                 </div>
             </div>
         </div>
     </div>
 </div>
@endsection

@section('scripts')
<script>
    // Appliquer un preset de configuration
    function applyPreset(preset) {
        const presets = {
            classic: {
                days: [1, 2, 3, 4, 5], // Lun-Ven
                startTime: '09:00',
                endTime: '18:00',
                breakStart: '12:00',
                breakEnd: '14:00',
                slotDuration: 60
            },
            morning: {
                days: [1, 2, 3, 4, 5, 6], // Lun-Sam
                startTime: '08:00',
                endTime: '12:00',
                breakStart: null,
                breakEnd: null,
                slotDuration: 60
            },
            afternoon: {
                days: [1, 2, 3, 4, 5], // Lun-Ven
                startTime: '14:00',
                endTime: '18:00',
                breakStart: null,
                breakEnd: null,
                slotDuration: 60
            },
            fullweek: {
                days: [1, 2, 3, 4, 5, 6], // Lun-Sam
                startTime: '09:00',
                endTime: '18:00',
                breakStart: '12:00',
                breakEnd: '14:00',
                slotDuration: 60
            }
        };
        
        const config = presets[preset];
        if (!config) return;
        
        if (!confirm(`Appliquer la configuration "${preset}" ? Les horaires actuels seront remplacés.`)) {
            return;
        }
        
        // Appliquer la configuration
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
            const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`);
            const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`);
            const breakStart = document.querySelector(`input[name="days[${day}][break_start]"]`);
            const breakEnd = document.querySelector(`input[name="days[${day}][break_end]"]`);
            const slotDuration = document.querySelector(`select[name="days[${day}][slot_duration]"]`);
            
            if (config.days.includes(day)) {
                if (checkbox) checkbox.checked = true;
                if (startTime) startTime.value = config.startTime;
                if (endTime) endTime.value = config.endTime;
                if (breakStart) breakStart.value = config.breakStart || '';
                if (breakEnd) breakEnd.value = config.breakEnd || '';
                if (slotDuration) slotDuration.value = config.slotDuration;
            } else {
                if (checkbox) checkbox.checked = false;
            }
            
            toggleDayInputs(day);
        });
        
        updateConfigSummary();
        alert('✅ Configuration appliquée !');
    }

    // Effacer toutes les configurations
    function clearAll() {
        if (!confirm('Êtes-vous sûr de vouloir désactiver tous les jours ?')) {
            return;
        }
        
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
            if (checkbox) {
                checkbox.checked = false;
                toggleDayInputs(day);
            }
        });
        
        updateConfigSummary();
        alert('Tous les horaires ont été effacés !');
    }
    
    function toggleDayInputs(dayNumber) {
        const checkbox = document.querySelector(`input[name="days[${dayNumber}][is_active]"]`);
        const inputs = document.getElementById(`day-inputs-${dayNumber}`);
        const card = checkbox.closest('.day-card');
        
        if (checkbox.checked) {
            inputs.classList.remove('day-inputs-hidden');
            card.classList.add('active');
        } else {
            inputs.classList.add('day-inputs-hidden');
            card.classList.remove('active');
        }
    }
    
    // Copier les horaires d'un jour à tous les autres jours
    function copyDayToAll(sourceDay) {
        if (!confirm('Voulez-vous copier les horaires de ce jour à tous les autres jours ?')) {
            return;
        }
        
        // Récupérer tous les horaires du jour source (y compris les pauses)
        const sourceData = {
            startTime: document.querySelector(`input[name="days[${sourceDay}][start_time]"]`).value,
            endTime: document.querySelector(`input[name="days[${sourceDay}][end_time]"]`).value,
            breakStart: document.querySelector(`input[name="days[${sourceDay}][break_start]"]`)?.value || '',
            breakEnd: document.querySelector(`input[name="days[${sourceDay}][break_end]"]`)?.value || '',
            slotDuration: document.querySelector(`select[name="days[${sourceDay}][slot_duration]"]`)?.value || '60'
        };
        
        // Appliquer ces horaires à tous les autres jours actifs
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            if (day !== sourceDay) {
                const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
                // Ne copier que si le jour est actif
                if (checkbox && checkbox.checked) {
                    const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`);
                    const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`);
                    const breakStart = document.querySelector(`input[name="days[${day}][break_start]"]`);
                    const breakEnd = document.querySelector(`input[name="days[${day}][break_end]"]`);
                    const slotDuration = document.querySelector(`select[name="days[${day}][slot_duration]"]`);
                    
                    if (startTime) startTime.value = sourceData.startTime;
                    if (endTime) endTime.value = sourceData.endTime;
                    if (breakStart) breakStart.value = sourceData.breakStart;
                    if (breakEnd) breakEnd.value = sourceData.breakEnd;
                    if (slotDuration) slotDuration.value = sourceData.slotDuration;
                }
            }
        });
        
        updateConfigSummary();
        alert('✅ Horaires copiés avec succès !');
    }
    
    function resetToDefault() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les horaires aux valeurs par défaut ?')) {
            // Réinitialiser tous les champs aux valeurs par défaut
            const days = [1, 2, 3, 4, 5]; // Lundi à Vendredi
            days.forEach(day => {
                const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
                const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`);
                const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`);
                const breakStart = document.querySelector(`input[name="days[${day}][break_start]"]`);
                const breakEnd = document.querySelector(`input[name="days[${day}][break_end]"]`);
                const slotDuration = document.querySelector(`select[name="days[${day}][slot_duration]"]`);
                
                if (checkbox) checkbox.checked = true;
                if (startTime) startTime.value = '09:00';
                if (endTime) endTime.value = '17:00';
                if (breakStart) breakStart.value = '12:00';
                if (breakEnd) breakEnd.value = '14:00';
                if (slotDuration) slotDuration.value = '60';
                
                toggleDayInputs(day);
            });
            
            // Désactiver weekend
            [0, 6].forEach(day => {
                const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    toggleDayInputs(day);
                }
            });
            
            updateConfigSummary();
        }
    }
    
    // Validation du formulaire
    function validateForm() {
        const activeDays = [0, 1, 2, 3, 4, 5, 6].filter(day => {
            const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
            return checkbox && checkbox.checked;
        });
        
        if (activeDays.length === 0) {
            alert('⚠️ Vous devez activer au moins un jour de la semaine !');
            return false;
        }
        
        // Vérifier que les heures sont cohérentes pour chaque jour actif
        for (let day of activeDays) {
            const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`).value;
            const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`).value;
            
            if (startTime >= endTime) {
                const dayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                alert(`⚠️ ${dayNames[day]} : L'heure de fin doit être après l'heure de début !`);
                return false;
            }
        }
        
        return true;
    }
    
    // Mettre à jour le résumé de configuration
    function updateConfigSummary() {
        const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        const activeDays = [];
        let totalHours = 0;
        
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
            if (checkbox && checkbox.checked) {
                activeDays.push(dayNames[day]);
                
                const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`).value;
                const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`).value;
                
                if (startTime && endTime) {
                    const start = new Date(`2000-01-01T${startTime}:00`);
                    const end = new Date(`2000-01-01T${endTime}:00`);
                    let dayHours = (end - start) / (1000 * 60 * 60);
                    
                    totalHours += dayHours;
                }
            }
        });
        
        // Only update if the elements exist
        const activeDaysCountElement = document.getElementById('active-days-count');
        const configSummaryElement = document.getElementById('config-summary');
        
        if (activeDaysCountElement) {
            activeDaysCountElement.textContent = activeDays.length;
        }
        
        if (configSummaryElement) {
            if (activeDays.length === 0) {
                configSummaryElement.textContent = 'Aucun jour configuré';
            } else {
                const summary = `${activeDays.join(', ')} • ~${Math.round(totalHours)}h/semaine`;
                configSummaryElement.textContent = summary;
            }
        }
    }
    
    // Initialiser l'état des inputs au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
            if (checkbox) {
                toggleDayInputs(day);
                // Ajouter des écouteurs pour mettre à jour le résumé
                checkbox.addEventListener('change', updateConfigSummary);
                
                const inputs = ['start_time', 'end_time'];
                inputs.forEach(inputType => {
                    const input = document.querySelector(`input[name="days[${day}][${inputType}]"]`);
                    if (input) {
                        input.addEventListener('change', updateConfigSummary);
                    }
                });
            }
        });
        
        // Mettre à jour le résumé initial
        updateConfigSummary();
    });
</script>
@endsection
