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

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 rounded-md mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('error') }}
                </div>
            @endif

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
                                    <div class="time-inputs-grid grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                        <div class="time-input-container bg-blue-50 rounded-lg p-2 sm:p-3 md:p-4 border border-blue-100 hover:border-blue-200 transition-colors duration-200">
                                            <label class="block text-xs sm:text-sm font-semibold text-blue-800 mb-1 sm:mb-2">Heure de début</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][start_time]" 
                                                   value="{{ $availability ? $availability->start_time?->format('H:i') : '09:00' }}"
                                                   class="time-input text-xs sm:text-sm">
                                        </div>
                                        
                                        <div class="time-input-container bg-blue-50 rounded-lg p-2 sm:p-3 md:p-4 border border-blue-100 hover:border-blue-200 transition-colors duration-200">
                                            <label class="block text-xs sm:text-sm font-semibold text-blue-800 mb-1 sm:mb-2">Heure de fin</label>
                                            <input type="time" 
                                                   name="days[{{ $dayNumber }}][end_time]" 
                                                   value="{{ $availability ? $availability->end_time?->format('H:i') : '17:00' }}"
                                                   class="time-input text-xs sm:text-sm">
                                        </div>
                                        
                                        <!-- Hidden input for slot_duration -->
                                        <input type="hidden" 
                                               name="days[{{ $dayNumber }}][slot_duration]" 
                                               value="{{ $availability ? $availability->slot_duration : 60 }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                        <div class="border-t-2 border-blue-200 pt-4 sm:pt-6 mt-6 sm:mt-8">
                            <div class="flex justify-center">
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-3 md:px-8 md:py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-xs sm:text-sm md:text-base">
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
        
        // Récupérer uniquement les horaires du jour source
        const sourceData = {
            startTime: document.querySelector(`input[name="days[${sourceDay}][start_time]"]`).value,
            endTime: document.querySelector(`input[name="days[${sourceDay}][end_time]"]`).value
        };
        
        // Appliquer ces horaires à tous les autres jours actifs
        [0, 1, 2, 3, 4, 5, 6].forEach(day => {
            if (day !== sourceDay) {
                const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
                // Ne copier que si le jour est actif
                if (checkbox && checkbox.checked) {
                    const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`);
                    const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`);
                    
                    if (startTime) startTime.value = sourceData.startTime;
                    if (endTime) endTime.value = sourceData.endTime;
                }
            }
        });
        
        updateConfigSummary();
        alert('Périodes horaires copiées avec succès !');
    }
    
    function resetToDefault() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les horaires aux valeurs par défaut ?')) {
            // Réinitialiser tous les champs aux valeurs par défaut
            const days = [1, 2, 3, 4, 5]; // Lundi à Vendredi
            days.forEach(day => {
                const checkbox = document.querySelector(`input[name="days[${day}][is_active]"]`);
                const startTime = document.querySelector(`input[name="days[${day}][start_time]"]`);
                const endTime = document.querySelector(`input[name="days[${day}][end_time]"]`);
                
                if (checkbox) checkbox.checked = true;
                if (startTime) startTime.value = '09:00';
                if (endTime) endTime.value = '17:00';
                
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