@extends('layouts.app')

@section('content')
<style>
/* Mobile-first PWA optimized styles */
.booking-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding-bottom: env(safe-area-inset-bottom, 20px);
}

.service-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    overflow: hidden;
}

.slot-btn {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    -webkit-tap-highlight-color: transparent;
}

.slot-btn:active {
    transform: scale(0.95);
}

.slot-btn.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.slot-btn.booked {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.slot-btn.pending {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

/* Calendar mobile optimization */
.calendar-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.calendar-wrapper::-webkit-scrollbar {
    display: none;
}

.date-pill {
    min-width: 60px;
    transition: all 0.2s ease;
    -webkit-tap-highlight-color: transparent;
}

.date-pill:active {
    transform: scale(0.95);
}

.date-pill.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.date-pill.has-slots {
    border-color: #10b981;
}

.date-pill.has-slots::after {
    content: '';
    position: absolute;
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
}

/* Floating action button style */
.confirm-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.5);
    transition: all 0.3s ease;
}

.confirm-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
}

.confirm-btn:disabled {
    background: #d1d5db;
    box-shadow: none;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .booking-page {
        padding-top: 0;
    }
    
    .slots-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 641px) {
    .slots-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Pull-to-refresh safe area */
@supports (padding-top: env(safe-area-inset-top)) {
    .booking-header {
        padding-top: calc(env(safe-area-inset-top) + 16px);
    }
}

/* Smooth scrolling for the whole page */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar for slots */
.slots-scroll::-webkit-scrollbar {
    width: 4px;
}
.slots-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.slots-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}
</style>

<div class="booking-page" x-data="bookingApp()">
    <!-- Header compact mobile -->
    <div class="booking-header bg-white/10 backdrop-blur-sm sticky top-0 z-50 px-4 py-3">
        <div class="max-w-lg mx-auto flex items-center justify-between">
            <a href="{{ url()->previous() }}" class="text-white p-2 -ml-2 rounded-full hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-white font-bold text-lg">Réservation</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <div class="px-4 pb-32 max-w-lg mx-auto">
        @if(session('error'))
            <div class="bg-red-500/90 backdrop-blur text-white p-4 rounded-xl mb-4 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Service Card Compact -->
        <div class="service-card p-4 mb-4 shadow-xl">
            <div class="flex items-start gap-4">
                <!-- Prestataire Avatar -->
                <a href="{{ route('prestataires.show', $prestataire) }}" class="flex-shrink-0">
                    @if($prestataire->photo)
                        <img src="{{ Storage::url($prestataire->photo) }}" 
                             alt="{{ $prestataire->user->name }}" 
                             class="w-14 h-14 rounded-xl object-cover shadow-md">
                    @elseif($prestataire->user->avatar)
                        <img src="{{ Storage::url($prestataire->user->avatar) }}" 
                             alt="{{ $prestataire->user->name }}" 
                             class="w-14 h-14 rounded-xl object-cover shadow-md">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-xl">{{ substr($prestataire->user->name, 0, 1) }}</span>
                        </div>
                    @endif
                </a>
                
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-gray-900 text-lg leading-tight truncate">{{ $service->name }}</h2>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $prestataire->user->name }}</p>
                    
                    <div class="flex items-center gap-4 mt-2">
                        <div class="flex items-center gap-1 text-purple-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-bold">{{ number_format($service->price, 2) }} €</span>
                        </div>
                        
                        @if($service->duration)
                        <div class="flex items-center gap-1 text-gray-500 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $service->duration }} min</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Selector - Horizontal Scroll -->
        <div class="service-card p-4 mb-4 shadow-xl">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-sm font-bold">1</span>
                Choisir une date
            </h3>
            
            <div class="calendar-wrapper -mx-4 px-4">
                <div class="flex gap-2 pb-2" id="date-scroll">
                    <template x-for="date in availableDates" :key="date.value">
                        <button type="button"
                                @click="selectDate(date.value)"
                                class="date-pill relative flex flex-col items-center px-3 py-2 rounded-xl border-2 border-gray-200"
                                :class="{
                                    'active': selectedDate === date.value,
                                    'has-slots': date.hasSlots && selectedDate !== date.value
                                }">
                            <span class="text-xs uppercase font-medium" x-text="date.day"></span>
                            <span class="text-xl font-bold" x-text="date.date"></span>
                            <span class="text-xs" x-text="date.month"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Time Slots -->
        <div class="service-card p-4 mb-4 shadow-xl">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-sm font-bold">2</span>
                Choisir un horaire
            </h3>

            <!-- No date selected -->
            <div x-show="!selectedDate" class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Sélectionnez une date ci-dessus</p>
            </div>

            <!-- No slots available -->
            <div x-show="selectedDate && slotsForDate.length === 0" class="text-center py-8" style="display: none;">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Aucun créneau disponible</p>
                <p class="text-gray-400 text-xs mt-1">Essayez une autre date</p>
            </div>

            <!-- Slots Grid -->
            <div x-show="selectedDate && slotsForDate.length > 0" 
                 class="slots-scroll max-h-64 overflow-y-auto -mx-1 px-1" 
                 style="display: none;">
                <div class="slots-grid grid gap-2">
                    <template x-for="slot in slotsForDate" :key="slot.datetime">
                        <button type="button"
                                @click="toggleSlot(slot)"
                                :disabled="slot.is_booked"
                                class="slot-btn p-3 rounded-xl border-2 border-gray-200 text-center font-medium text-sm"
                                :class="{
                                    'selected': isSelected(slot),
                                    'booked': slot.is_booked,
                                    'pending': slot.has_pending && !slot.is_booked && !isSelected(slot)
                                }">
                            <span x-text="formatTime(slot.datetime)"></span>
                            <span class="block text-xs opacity-70 mt-0.5" x-show="slot.is_booked">Réservé</span>
                            <span class="block text-xs opacity-70 mt-0.5" x-show="slot.has_pending && !slot.is_booked">En attente</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Selected Slots Summary -->
        <div x-show="selectedSlots.length > 0" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="service-card p-4 mb-4 shadow-xl" 
             style="display: none;">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Créneaux sélectionnés
            </h3>
            
            <div class="space-y-2">
                <template x-for="slot in selectedSlots" :key="slot.datetime">
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="formatFullDate(slot.datetime)"></p>
                                <p class="text-sm text-gray-500" x-text="formatTime(slot.datetime) + ' - ' + formatTime(slot.end_datetime)"></p>
                            </div>
                        </div>
                        <button type="button" @click="removeSlot(slot)" class="p-2 text-gray-400 hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-2xl" 
         style="padding-bottom: max(env(safe-area-inset-bottom), 16px);">
        <div class="max-w-lg mx-auto">
            <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                <input type="hidden" name="prestataire_id" value="{{ $prestataire->id }}">
                <template x-for="slot in selectedSlots" :key="slot.datetime">
                    <input type="hidden" name="selected_slots[]" :value="slot.datetime">
                </template>
                
                <div class="flex items-center justify-between mb-3" x-show="selectedSlots.length > 0">
                    <div>
                        <p class="text-gray-500 text-sm">Total</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="calculateTotal() + ' €'"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-purple-600 font-medium" x-text="selectedSlots.length + ' créneau' + (selectedSlots.length > 1 ? 'x' : '')"></p>
                    </div>
                </div>
                
                <button type="submit" 
                        :disabled="selectedSlots.length === 0"
                        class="confirm-btn w-full py-4 rounded-xl text-white font-bold text-lg">
                    <span x-show="selectedSlots.length === 0">Sélectionnez un créneau</span>
                    <span x-show="selectedSlots.length > 0">Confirmer la réservation</span>
                </button>
            </form>
        </div>
    </div>
</div>

@php
    $jsSlots = array_map(function($s) {
        return [
            'datetime' => $s['datetime']->toIso8601String(),
            'end_datetime' => $s['end_datetime']->toIso8601String(),
            'date' => $s['datetime']->format('Y-m-d'),
            'is_booked' => $s['is_booked'] ?? false,
            'has_pending' => $s['has_pending'] ?? false
        ];
    }, $availableSlots ?? []);
@endphp

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingApp', () => ({
        allSlots: {!! json_encode($jsSlots) !!},
        selectedDate: null,
        selectedSlots: [],
        servicePrice: {!! $service->price !!},
        
        get availableDates() {
            const dates = [];
            const today = new Date();
            const daysOfWeek = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            
            for (let i = 0; i < 14; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() + i);
                const dateStr = date.toISOString().split('T')[0];
                const hasSlots = this.allSlots.some(s => s.date === dateStr && !s.is_booked);
                
                dates.push({
                    value: dateStr,
                    day: daysOfWeek[date.getDay()],
                    date: date.getDate(),
                    month: months[date.getMonth()],
                    hasSlots: hasSlots
                });
            }
            return dates;
        },
        
        get slotsForDate() {
            if (!this.selectedDate) return [];
            return this.allSlots.filter(s => s.date === this.selectedDate);
        },
        
        selectDate(date) {
            this.selectedDate = date;
        },
        
        toggleSlot(slot) {
            if (slot.is_booked) return;
            
            const index = this.selectedSlots.findIndex(s => s.datetime === slot.datetime);
            if (index > -1) {
                this.selectedSlots.splice(index, 1);
            } else {
                this.selectedSlots.push(slot);
            }
        },
        
        isSelected(slot) {
            return this.selectedSlots.some(s => s.datetime === slot.datetime);
        },
        
        removeSlot(slot) {
            this.selectedSlots = this.selectedSlots.filter(s => s.datetime !== slot.datetime);
        },
        
        formatTime(iso) {
            return new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },
        
        formatFullDate(iso) {
            return new Date(iso).toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
        },
        
        calculateTotal() {
            return (this.selectedSlots.length * this.servicePrice).toFixed(2);
        }
    }));
});
</script>
@endsection
