@extends('layouts.app')

@section('content')
<style>
/* Mobile-first PWA optimized styles */
.booking-page {
    min-height: 100vh;
    min-height: 100dvh;
    background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
    padding-bottom: 180px;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
}

.service-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* Calendar Grid */
.calendar-container {
    background: white;
    border-radius: 16px;
    overflow: hidden;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.calendar-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.calendar-nav-btn:hover,
.calendar-nav-btn:active {
    background: rgba(255,255,255,0.3);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    padding: 8px 4px;
    border-bottom: 1px solid #e2e8f0;
}

.calendar-weekday {
    text-align: center;
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    padding: 8px 4px;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    font-size: 13px;
    min-width: 0;
    min-height: 36px;
}

@media (min-width: 400px) {
    .calendar-day {
        font-size: 14px;
        min-height: 40px;
    }
    .calendar-weekday {
        font-size: 11px;
    }
    .calendar-days {
        gap: 4px;
        padding: 12px 8px;
    }
}

.calendar-day:hover:not(.disabled):not(.empty) {
    background: #f1f5f9;
}

.calendar-day.empty {
    cursor: default;
}

.calendar-day.disabled {
    color: #cbd5e1;
    cursor: not-allowed;
}

.calendar-day.today {
    border: 2px solid #667eea;
    color: #667eea;
}

.calendar-day.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.calendar-day.has-slots::after {
    content: '';
    position: absolute;
    bottom: 4px;
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
}

.calendar-day.selected.has-slots::after {
    background: white;
}

/* Slots */
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

.slots-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

@media (min-width: 360px) {
    .slots-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 480px) {
    .slots-grid {
        grid-template-columns: repeat(4, 1fr);
    }
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

/* Header */
.booking-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 12px 16px;
    padding-top: max(env(safe-area-inset-top), 12px);
}

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

/* Mobile optimizations */
@media (max-width: 380px) {
    .booking-page {
        padding-bottom: 160px;
    }
    
    .service-card {
        border-radius: 12px;
    }
    
    .service-card p,
    .service-card span {
        font-size: 13px;
    }
    
    .calendar-container {
        border-radius: 12px;
    }
    
    .calendar-header h3 {
        font-size: 15px;
    }
    
    .slot-btn {
        padding: 10px 6px !important;
        font-size: 12px !important;
    }
}
</style>

<div class="booking-page" x-data="bookingApp()">
    <!-- Header compact mobile -->
    <div class="booking-header bg-white/10 backdrop-blur-sm px-3 py-3">
        <div class="max-w-lg mx-auto flex items-center justify-between">
            <a href="{{ url()->previous() }}" class="text-white p-2 -ml-2 rounded-full hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-white font-bold text-base sm:text-lg">Réservation</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <div class="px-3 sm:px-4 pt-2 max-w-lg mx-auto">
        @if(session('error'))
            <div class="bg-red-500/90 backdrop-blur text-white p-4 rounded-xl mb-4 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Service Card Compact -->
        <div class="service-card p-3 sm:p-4 mb-4 shadow-xl">
            <div class="flex items-start gap-3">
                <!-- Prestataire Avatar -->
                <a href="{{ route('prestataires.show', $prestataire) }}" class="flex-shrink-0">
                    @if($prestataire->photo)
                        <img src="{{ Storage::url($prestataire->photo) }}" 
                             alt="{{ $prestataire->user->name }}" 
                             class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover shadow-md">
                    @elseif($prestataire->user->avatar)
                        <img src="{{ Storage::url($prestataire->user->avatar) }}" 
                             alt="{{ $prestataire->user->name }}" 
                             class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover shadow-md">
                    @else
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-lg sm:text-xl">{{ substr($prestataire->user->name, 0, 1) }}</span>
                        </div>
                    @endif
                </a>
                
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-gray-900 text-base sm:text-lg leading-tight truncate">{{ $service->name }}</h2>
                    <p class="text-gray-500 text-xs sm:text-sm mt-0.5 truncate">{{ $prestataire->user->name }}</p>
                    
                    <div class="flex items-center gap-3 sm:gap-4 mt-2 flex-wrap">
                        <div class="flex items-center gap-1 text-purple-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                            @if($service->price)
                                <span class="font-bold text-sm sm:text-base">{{ number_format($service->price, 2) }} €</span>
                            @else
                                <span class="font-bold text-sm sm:text-base">Sur devis</span>
                            @endif
                        </div>
                        
                        @if($service->duration)
                        <div class="flex items-center gap-1 text-gray-500 text-xs sm:text-sm">
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

        <!-- Calendar -->
        <div class="calendar-container mb-4 shadow-xl">
            <div class="calendar-header">
                <button type="button" @click="prevMonth()" class="calendar-nav-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h3 class="font-bold text-lg capitalize" x-text="currentMonthName + ' ' + currentYear"></h3>
                <button type="button" @click="nextMonth()" class="calendar-nav-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            
            <div class="calendar-weekdays">
                <template x-for="day in weekDays">
                    <div class="calendar-weekday" x-text="day"></div>
                </template>
            </div>
            
            <div class="calendar-days">
                <template x-for="day in calendarDays" :key="day.key">
                    <button type="button"
                            @click="day.value && !day.disabled && selectDate(day.value)"
                            :disabled="day.disabled || !day.value"
                            class="calendar-day"
                            :class="{
                                'empty': !day.value,
                                'disabled': day.disabled,
                                'today': day.isToday,
                                'selected': selectedDate === day.value,
                                'has-slots': day.hasSlots && selectedDate !== day.value
                            }">
                        <span x-text="day.date"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Time Slots -->
        <div class="service-card p-4 mb-4 shadow-xl">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Horaires disponibles
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
    <div class="fixed left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-2xl z-40" 
         style="bottom: 70px; padding-bottom: max(env(safe-area-inset-bottom), 10px);">
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
                        <p class="text-gray-500 text-xs sm:text-sm">Total</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900" x-text="calculateTotal() + ' €'"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-purple-600 font-medium text-sm sm:text-base" x-text="selectedSlots.length + ' créneau' + (selectedSlots.length > 1 ? 'x' : '')"></p>
                    </div>
                </div>
                
                <button type="submit" 
                        :disabled="selectedSlots.length === 0"
                        class="confirm-btn w-full py-3 sm:py-4 rounded-xl text-white font-bold text-base sm:text-lg">
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
        servicePrice: {!! json_encode($service->price ?? 0) !!},
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        
        weekDays: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        
        get currentMonthName() {
            return this.monthNames[this.currentMonth];
        },
        
        get calendarDays() {
            const days = [];
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Get day of week (0=Mon, 6=Sun)
            let startDay = firstDay.getDay() - 1;
            if (startDay < 0) startDay = 6;
            
            // Empty cells before first day
            for (let i = 0; i < startDay; i++) {
                days.push({ key: 'empty-' + i, value: null, date: '' });
            }
            
            // Days of month
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const date = new Date(this.currentYear, this.currentMonth, d);
                const dateStr = date.toISOString().split('T')[0];
                const isToday = date.getTime() === today.getTime();
                const isPast = date < today;
                const hasSlots = this.allSlots.some(s => s.date === dateStr && !s.is_booked);
                
                days.push({
                    key: dateStr,
                    value: dateStr,
                    date: d,
                    isToday: isToday,
                    disabled: isPast,
                    hasSlots: hasSlots
                });
            }
            
            return days;
        },
        
        get slotsForDate() {
            if (!this.selectedDate) return [];
            return this.allSlots.filter(s => s.date === this.selectedDate);
        },
        
        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },
        
        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
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
            if (!this.servicePrice) return 'Sur devis';
            return (this.selectedSlots.length * this.servicePrice).toFixed(2);
        }
    }));
});
</script>
@endsection
