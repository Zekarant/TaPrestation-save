@extends('layouts.app')

@section('title', 'Mon Agenda - Prestataire')

@push('styles')
  <style>
    /* FullCalendar Core Styles */
    .fc {
      font-family: inherit;
    }
    .fc .fc-toolbar-title {
      font-size: 1.25rem;
      font-weight: 600;
    }
    .fc .fc-button {
      background-color: #3b82f6;
      border-color: #3b82f6;
      padding: 0.4rem 0.8rem;
      font-size: 0.875rem;
    }
    .fc .fc-button:hover {
      background-color: #2563eb;
      border-color: #2563eb;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active {
      background-color: #1d4ed8;
      border-color: #1d4ed8;
    }
    .fc .fc-daygrid-day-number {
      padding: 4px 8px;
      color: #374151;
    }
    .fc .fc-daygrid-day.fc-day-today {
      background-color: #fef3c7;
    }
    .fc .fc-event {
      border-radius: 4px;
      padding: 2px 4px;
      font-size: 0.75rem;
      cursor: pointer;
    }
    .fc .fc-col-header-cell-cushion {
      padding: 8px 4px;
      font-weight: 600;
      color: #374151;
    }
    .fc-theme-standard td, .fc-theme-standard th {
      border-color: #e5e7eb;
    }
    .fc-theme-standard .fc-scrollgrid {
      border-color: #e5e7eb;
    }
    
    @keyframes pulse-attention {
      0%, 100% {
        opacity: 1;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
      }
      50% {
        opacity: 0.85;
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.5);
      }
    }
    .pending-attention {
      animation: pulse-attention 2s ease-in-out infinite;
    }

    /* ===== MOBILE APP OPTIMIZATIONS ===== */
    
    /* Tab système mobile */
    .mobile-tab-btn {
      flex: 1;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      font-weight: 600;
      text-align: center;
      border-radius: 0.75rem;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    .mobile-tab-btn.active {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    .mobile-tab-btn:not(.active) {
      background: #f1f5f9;
      color: #64748b;
    }

    /* Content panels mobile */
    .mobile-panel {
      display: none;
    }
    .mobile-panel.active {
      display: block;
    }

    /* Touch-friendly calendar events */
    @media (max-width: 768px) {
      .fc-daygrid-event {
        min-height: 28px !important;
        padding: 4px 8px !important;
        margin: 2px 4px !important;
      }
      .fc-daygrid-day-frame {
        min-height: 90px !important;
      }
      .fc-col-header-cell-cushion {
        padding: 6px 2px !important;
        font-size: 0.7rem !important;
      }
      .fc-daygrid-day-number {
        font-size: 0.875rem !important;
        padding: 6px !important;
      }
      
      /* Agrandir la zone tactile des événements */
      .fc-event {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
      }
      
      /* Masquer le scroll horizontal */
      .fc-scroller {
        overflow-x: hidden !important;
      }
    }

    /* Safe area pour iOS */
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
      .mobile-safe-bottom {
        padding-bottom: calc(5rem + env(safe-area-inset-bottom)) !important;
      }
    }

    /* Floating action button mobile */
    .mobile-fab {
      position: fixed;
      bottom: calc(5rem + env(safe-area-inset-bottom, 0px));
      right: 1rem;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 40;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .mobile-fab:active {
      transform: scale(0.95);
      box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
    }

    @media (min-width: 768px) {
      .mobile-fab {
        display: none;
      }
    }

    /* Swipe indicator */
    .swipe-indicator {
      width: 40px;
      height: 4px;
      background: #cbd5e1;
      border-radius: 2px;
      margin: 0 auto 1rem;
    }
  </style>
@endpush

@section('content')
<div class="pb-24 sm:pb-12 bg-slate-50 mobile-safe-bottom">
  <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8 max-w-6xl">
    <!-- Header Mobile Optimized -->
    <div class="mb-4 sm:mb-8">
      <h1 class="text-2xl sm:text-4xl font-bold text-slate-900 mb-1 sm:mb-2">Mon Agenda</h1>
      <p class="text-sm sm:text-base text-slate-500">Gérez vos réservations</p>
    </div>

    <!-- Quick Stats - More compact on mobile -->
    <div class="grid grid-cols-4 sm:grid-cols-4 gap-1.5 sm:gap-3 mb-4 sm:mb-6">
      <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-2 sm:p-4 shadow-sm border border-slate-200">
        <div class="text-center sm:text-left">
          <div class="text-slate-500 text-[10px] sm:text-xs font-semibold uppercase mb-0.5 sm:mb-1">Total</div>
          <div class="text-lg sm:text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-2 sm:p-4 shadow-sm border border-red-300 @if(($stats['pending'] ?? 0) > 0) pending-attention @endif">
        <div class="text-center sm:text-left">
          <div class="text-red-600 text-[10px] sm:text-xs font-semibold uppercase mb-0.5 sm:mb-1">Attente</div>
          <div class="text-lg sm:text-2xl font-bold text-red-600">{{ $stats['pending'] ?? 0 }}</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-2 sm:p-4 shadow-sm border border-blue-200">
        <div class="text-center sm:text-left">
          <div class="text-blue-600 text-[10px] sm:text-xs font-semibold uppercase mb-0.5 sm:mb-1">Confirmé</div>
          <div class="text-lg sm:text-2xl font-bold text-blue-600">{{ $stats['confirmed'] ?? 0 }}</div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-2 sm:p-4 shadow-sm border border-emerald-200">
        <div class="text-center sm:text-left">
          <div class="text-emerald-600 text-[10px] sm:text-xs font-semibold uppercase mb-0.5 sm:mb-1">Terminé</div>
          <div class="text-lg sm:text-2xl font-bold text-emerald-600">{{ $stats['completed'] ?? 0 }}</div>
        </div>
      </div>
    </div>

    <!-- Mobile Tab Navigation -->
    <div class="md:hidden mb-4">
      <div class="flex gap-2 bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200">
        <button type="button" onclick="switchMobileTab('calendar')" id="tabCalendar" class="mobile-tab-btn active">
          <i class="fas fa-calendar-alt"></i>
          <span>Calendrier</span>
        </button>
        <button type="button" onclick="switchMobileTab('demands')" id="tabDemands" class="mobile-tab-btn">
          <i class="fas fa-inbox"></i>
          <span>Demandes</span>
          @if(($stats['pending'] ?? 0) > 0)
            <span class="ml-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $stats['pending'] ?? 0 }}</span>
          @endif
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
      <!-- Sidebar - Hidden on mobile, shown via tab -->
      <div class="hidden md:block lg:col-span-1" id="sidebarDesktop">
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 lg:sticky lg:top-24 max-h-[600px] flex flex-col">
          <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-3 sm:mb-4 flex items-center flex-shrink-0">
            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-2 sm:mr-3 text-xs sm:text-sm flex-shrink-0">
              <i class="fas fa-inbox"></i>
            </span>
            <span class="truncate">Demandes</span>
          </h2>

          <div class="space-y-2 overflow-y-auto flex-1 min-h-0 pr-2">
            @forelse($recentDemands as $demand)
              @php
                $dType = $demand['type'] ?? 'service';
                $dStatus = $demand['status'] ?? 'pending';
                $dStart = $demand['start_date'] ?? null;
                $dStartFmt = $dStart ? \Carbon\Carbon::parse($dStart)->format('d/m') : '--/--';
              @endphp

              <div class="booking-item-wrapper" data-demand-id="{{ $demand['id'] }}">
                <button
                  type="button"
                  onclick="showDemandDetails({{ (int) $demand['id'] }}, '{{ e($dType) }}')"
                  class="w-full text-left p-2.5 sm:p-3 rounded-xl border-2 transition-all duration-200 text-sm
                  @if($dType === 'service')
                    border-blue-200 bg-blue-50 hover:border-blue-400 hover:bg-blue-100
                  @else
                    border-green-200 bg-green-50 hover:border-green-400 hover:bg-green-100
                  @endif"
                >
                  <div class="flex items-start gap-2">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex-shrink-0 flex items-center justify-center
                      @if($dType === 'service') bg-blue-500 @else bg-green-500 @endif text-white text-xs sm:text-sm">
                      @if($dType === 'service')
                        <i class="fas fa-briefcase text-xs"></i>
                      @else
                        <i class="fas fa-tools text-xs"></i>
                      @endif
                    </div>

                    <div class="flex-1 min-w-0">
                      <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $demand['title'] ?? 'Demande' }}</h4>
                      <p class="text-xs text-slate-600 mt-0.5 truncate">{{ $demand['client_name'] ?? 'Client' }}</p>

                      <div class="flex items-center justify-between mt-1.5 gap-2">
                        <span class="text-xs text-slate-500 flex-shrink-0">
                          <i class="fas fa-calendar mr-1"></i>
                          {{ $dStartFmt }}
                        </span>

                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-bold flex-shrink-0
                          @if($dStatus === 'confirmed' || $dStatus === 'accepted') bg-emerald-100 text-emerald-700
                          @elseif($dStatus === 'pending') bg-orange-100 text-orange-700
                          @elseif($dStatus === 'completed') bg-blue-100 text-blue-700
                          @else bg-slate-100 text-slate-700 @endif">
                          {{ ucfirst($dStatus) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </button>
              </div>
            @empty
              <div class="text-center py-6">
                <i class="fas fa-inbox text-3xl text-slate-200 mb-2 block"></i>
                <p class="text-xs text-slate-500">Aucune demande</p>
              </div>
            @endforelse
          </div>

          <a
            href="{{ route('prestataire.bookings.index') }}"
            class="w-full block mt-3 sm:mt-4 py-2 sm:py-3 px-3 sm:px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs sm:text-sm transition-all text-center flex-shrink-0"
          >
            Voir tous
          </a>
        </div>
      </div>

      <!-- Mobile Demands Panel (shown via tab) -->
      <div class="md:hidden mobile-panel" id="panelDemands">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
          <div class="swipe-indicator"></div>
          <div class="space-y-3 max-h-[60vh] overflow-y-auto">
            @forelse($recentDemands as $demand)
              @php
                $dType = $demand['type'] ?? 'service';
                $dStatus = $demand['status'] ?? 'pending';
                $dStart = $demand['start_date'] ?? null;
                $dStartFmt = $dStart ? \Carbon\Carbon::parse($dStart)->format('d/m/Y') : '--/--/--';
              @endphp

              <button
                type="button"
                onclick="showDemandDetails({{ (int) $demand['id'] }}, '{{ e($dType) }}')"
                class="w-full text-left p-4 rounded-xl border-2 transition-all duration-200
                @if($dType === 'service')
                  border-blue-200 bg-blue-50 active:bg-blue-100
                @else
                  border-green-200 bg-green-50 active:bg-green-100
                @endif"
              >
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center
                    @if($dType === 'service') bg-blue-500 @else bg-green-500 @endif text-white">
                    @if($dType === 'service')
                      <i class="fas fa-briefcase"></i>
                    @else
                      <i class="fas fa-tools"></i>
                    @endif
                  </div>

                  <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-sm text-slate-900 truncate">{{ $demand['title'] ?? 'Demande' }}</h4>
                    <p class="text-xs text-slate-600 truncate">{{ $demand['client_name'] ?? 'Client' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs text-slate-500">
                        <i class="fas fa-calendar mr-1"></i>{{ $dStartFmt }}
                      </span>
                    </div>
                  </div>

                  <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold
                      @if($dStatus === 'confirmed' || $dStatus === 'accepted') bg-emerald-100 text-emerald-700
                      @elseif($dStatus === 'pending') bg-orange-100 text-orange-700
                      @elseif($dStatus === 'completed') bg-blue-100 text-blue-700
                      @else bg-slate-100 text-slate-700 @endif">
                      {{ ucfirst($dStatus) }}
                    </span>
                    <i class="fas fa-chevron-right text-slate-400 text-xs"></i>
                  </div>
                </div>
              </button>
            @empty
              <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4">
                  <i class="fas fa-inbox text-2xl text-slate-300"></i>
                </div>
                <p class="text-slate-500 font-medium">Aucune demande récente</p>
                <p class="text-xs text-slate-400 mt-1">Vos nouvelles demandes apparaîtront ici</p>
              </div>
            @endforelse
          </div>

          <a
            href="{{ route('prestataire.bookings.index') }}"
            class="w-full block mt-4 py-3 px-4 bg-slate-900 text-white font-semibold rounded-xl text-sm transition-all text-center active:bg-slate-800"
          >
            <i class="fas fa-list mr-2"></i>Voir toutes les demandes
          </a>
        </div>
      </div>

      <!-- Main Calendar -->
      <div class="lg:col-span-3 mobile-panel active" id="panelCalendar">
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-3 sm:p-6 overflow-hidden">
          <!-- Custom Toolbar - Mobile Optimized -->
          <div class="flex flex-col gap-2 sm:gap-3 mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <!-- Nav + Title -->
              <div class="flex items-center gap-2">
                <div class="inline-flex items-center gap-1 bg-slate-100 rounded-lg p-1">
                  <button type="button" onclick="calNav('prev')" class="p-2 rounded-md hover:bg-slate-200 transition-all" title="Précédent">
                    <i class="fas fa-chevron-left text-slate-700"></i>
                  </button>
                  <button type="button" onclick="calNav('today')" class="px-3 py-2 rounded-md hover:bg-slate-200 transition-all text-xs font-semibold text-slate-700" title="Aujourd’hui">
                    Aujourd’hui
                  </button>
                  <button type="button" onclick="calNav('next')" class="p-2 rounded-md hover:bg-slate-200 transition-all" title="Suivant">
                    <i class="fas fa-chevron-right text-slate-700"></i>
                  </button>
                </div>

                <div class="ml-1">
                  <div id="calendarTitle" class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">Agenda</div>
                  <div class="text-xs text-slate-500">Navigation rapide</div>
                </div>
              </div>

              <!-- Add Event Button -->
              <button type="button" onclick="openEventModal()" class="w-full sm:w-auto px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-semibold text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <i class="fas fa-plus text-xs"></i>
                <span class="hidden sm:inline">Ajouter</span>
                <span class="sm:hidden">+</span>
              </button>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
              <!-- View Buttons -->
              <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg p-1">
                <button type="button" onclick="changeView(event,'dayGridDay')" data-view="dayGridDay" class="view-btn p-2 rounded-md transition-all duration-200 text-sm font-medium flex items-center justify-center min-w-10 hover:bg-slate-200" title="Vue du jour">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7 10h10V7H7v3zm0 7h10v-3H7v3zM5 3h14c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2z"/></svg>
                </button>
                <button type="button" onclick="changeView(event,'timeGridWeek')" data-view="timeGridWeek" class="view-btn p-2 rounded-md transition-all duration-200 text-sm font-medium flex items-center justify-center min-w-10 hover:bg-slate-200" title="Vue semaine">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                </button>
                <button type="button" onclick="changeView(event,'dayGridMonth')" data-view="dayGridMonth" class="view-btn p-2 rounded-md transition-all duration-200 text-sm font-medium flex items-center justify-center min-w-10 hover:bg-slate-200" title="Vue mois">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                </button>
              </div>

              <!-- Filter Buttons -->
              <div class="flex flex-wrap items-center gap-1.5">
                <button id="allEventsBtn" type="button" onclick="filterEvents('all')" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 bg-slate-100 hover:bg-slate-200 text-slate-700">
                  Tous
                </button>
                <button id="servicesBtn" type="button" onclick="filterEvents('service')" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 bg-slate-100 hover:bg-blue-100 text-slate-700">
                  Services
                </button>
                <button id="equipmentBtn" type="button" onclick="filterEvents('equipment')" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 bg-slate-100 hover:bg-green-100 text-slate-700">
                  Équipement
                </button>
              </div>
            </div>
          </div>

          <!-- Calendar -->
          <div id="calendar" class="touch-pan-y"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile FAB (Floating Action Button) -->
  <button type="button" onclick="openEventModal()" class="mobile-fab md:hidden" aria-label="Ajouter un événement">
    <i class="fas fa-plus text-xl"></i>
  </button>

  <!-- Rotate Hint Overlay -->
  <div id="rotateHint" class="fixed inset-0 z-[60] hidden items-center justify-center p-6 bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 text-center">
      <div class="mx-auto w-20 h-20 flex items-center justify-center rounded-2xl bg-slate-100">
        <div class="rotate-phone">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" class="text-slate-900">
            <rect x="7" y="2.5" width="10" height="19" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="12" cy="18.2" r="0.9" fill="currentColor"/>
          </svg>
        </div>
      </div>

      <h3 class="mt-4 text-lg font-bold text-slate-900">Astuce affichage</h3>
      <p class="mt-1 text-sm text-slate-600">
        Tourne ton téléphone en <span class="font-semibold">horizontal</span> pour voir le planning en grand.
      </p>

      <button type="button" onclick="dismissRotateHint()" class="mt-4 w-full py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
        OK
      </button>
    </div>
  </div>

  <!-- Event Modal - Mobile Optimized -->
  <div id="eventModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-end sm:items-center justify-center z-50 p-0 sm:p-4" aria-hidden="true">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full sm:w-2/3 lg:w-1/2 max-h-[95vh] sm:max-h-[90vh] overflow-y-auto shadow-2xl">
      <!-- Mobile swipe indicator -->
      <div class="sm:hidden pt-3 pb-1">
        <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto"></div>
      </div>
      <div class="p-4 sm:p-6 border-b border-slate-200">
        <div class="flex justify-between items-center">
          <h3 class="text-xl sm:text-2xl font-bold text-slate-900">Ajouter un événement</h3>
          <button type="button" onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600 transition-all p-2 -mr-2" aria-label="Fermer">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>

      <form id="eventForm" class="p-4 sm:p-6 space-y-4">
        @csrf

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Titre</label>
          <input type="text" id="eventTitle" name="title" required class="w-full px-4 py-3 sm:py-2 border border-slate-300 rounded-xl sm:rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base" placeholder="Ex: Indisponible, Réunion...">
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
          <textarea id="eventDescription" name="description" class="w-full px-4 py-3 sm:py-2 border border-slate-300 rounded-xl sm:rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base" placeholder="Description optionnelle" rows="2"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Date/Heure début</label>
            <input type="datetime-local" id="eventStart" name="start_datetime" required class="w-full px-4 py-3 sm:py-2 border border-slate-300 rounded-xl sm:rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Date/Heure fin</label>
            <input type="datetime-local" id="eventEnd" name="end_datetime" required class="w-full px-4 py-3 sm:py-2 border border-slate-300 rounded-xl sm:rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Type</label>
            <select id="eventType" name="type" class="w-full px-4 py-3 sm:py-2 border border-slate-300 rounded-xl sm:rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base">
              <option value="other">Autre</option>
              <option value="unavailable">Indisponible</option>
              <option value="appointment">Rendez-vous</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Couleur</label>
            <input type="color" id="eventColor" name="color" value="#3788d8" class="w-full h-12 sm:h-10 border border-slate-300 rounded-xl sm:rounded-lg cursor-pointer">
          </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 pb-4 sm:pb-0">
          <button type="button" onclick="closeEventModal()" class="flex-1 px-4 py-3 sm:py-2 bg-slate-200 text-slate-700 rounded-xl sm:rounded-lg hover:bg-slate-300 active:bg-slate-400 transition-all font-semibold touch-manipulation">
            Annuler
          </button>
          <button type="submit" class="flex-1 px-4 py-3 sm:py-2 bg-blue-600 text-white rounded-xl sm:rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-all font-semibold touch-manipulation">
            <i class="fas fa-check mr-2"></i>Créer l'événement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Day Events Modal - Liste des événements d'un jour -->
<div id="dayEventsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-end sm:items-center justify-center z-50 p-0 sm:p-4" aria-hidden="true">
  <div class="w-full sm:max-w-lg bg-white sm:rounded-2xl rounded-t-3xl shadow-2xl max-h-[85vh] sm:max-h-[80vh] overflow-hidden flex flex-col animate-in slide-in-from-bottom duration-300">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
      <div>
        <h3 class="text-lg font-bold">📅 <span id="dayEventsDate">-</span></h3>
        <p class="text-blue-100 text-sm"><span id="dayEventsCount">0</span> réservation(s)</p>
      </div>
      <button type="button" onclick="closeDayEventsModal()" class="text-white/80 hover:text-white transition-all p-2 -mr-2" aria-label="Fermer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    <!-- Events List -->
    <div id="dayEventsList" class="flex-1 overflow-y-auto p-4 space-y-3">
      <!-- Events will be populated here -->
    </div>
    
    <!-- Footer -->
    <div class="p-4 border-t border-slate-200 bg-slate-50">
      <button type="button" onclick="closeDayEventsModal()" class="w-full px-4 py-3 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 active:bg-slate-400 transition-all font-semibold touch-manipulation">
        Fermer
      </button>
    </div>
  </div>
</div>

<style>
  /* FullCalendar visible (évite calendrier vide sur iOS) */
  #calendar { min-height: 400px; }
  .fc { min-height: 400px; }

  /* Desktop calendar sizes */
  @media (min-width: 640px) {
    #calendar { min-height: 520px; }
    .fc { min-height: 520px; }
  }

  /* More link styling */
  .fc-daygrid-more-link {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    color: white !important;
    padding: 4px 10px !important;
    border-radius: 20px !important;
    font-weight: 600 !important;
    font-size: 11px !important;
    margin: 2px !important;
    display: inline-block !important;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;
    transition: all 0.2s ease !important;
  }
  .fc-daygrid-more-link:hover {
    transform: scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
  }
  
  /* Day cell clickable effect */
  .fc-daygrid-day-frame {
    cursor: pointer;
    transition: background 0.2s ease;
  }
  .fc-daygrid-day-frame:hover {
    background: rgba(59, 130, 246, 0.05);
  }

  /* FullCalendar theme tokens */
  #calendar{
    --fc-border-color:#e2e8f0;
    --fc-button-bg-color:#f1f5f9;
    --fc-button-border-color:#cbd5e1;
    --fc-button-text-color:#334155;
    --fc-button-hover-bg-color:#e2e8f0;
    --fc-button-hover-border-color:#94a3b8;
    --fc-button-active-bg-color:#3b82f6;
    --fc-button-active-border-color:#3b82f6;
    --fc-button-active-text-color:#fff;
    --fc-today-bg-color:rgba(59,130,246,.05);
    --fc-daygrid-day-frame-padding:4px;
  }
  .fc-toolbar{ display:none!important; }

  .fc-daygrid-day{
    padding:0!important;
    min-height:120px;
    border:1px solid #e2e8f0!important;
    background:#fff;
    position:relative;
  }
  .fc-daygrid-day:hover{ background:#f8fafc; }

  .fc-daygrid-day-number{
    padding:.5rem!important;
    font-weight:600!important;
    color:#334155!important;
  }
  .fc-daygrid-day.fc-day-other .fc-daygrid-day-number{ color:#cbd5e1; }

  .fc-daygrid-day.fc-day-today{
    background:rgba(59,130,246,.05)!important;
  }
  .fc-daygrid-day.fc-day-today .fc-daygrid-day-number{
    color:#3b82f6!important;
    background:#fff;
    border:2px solid #3b82f6;
    border-radius:999px;
    width:32px;height:32px;
    display:flex;align-items:center;justify-content:center;
    font-weight:700!important;
  }

  .fc-event{
    border:none!important;
    padding:2px 6px!important;
    font-size:.75rem!important;
    margin:2px 0!important;
    border-radius:8px!important;
    cursor:pointer;
  }
  .fc-event-title{ font-weight:600!important; padding:2px 0!important; }

  .fc-daygrid-event{
    background:#3b82f6!important;
    border-radius:.5rem!important;
    color:#fff!important;
  }
  .fc-daygrid-event:hover{
    background:#2563eb!important;
    transform:scale(1.02);
  }

  .fc-timegrid-event{ border-radius:.75rem!important; border:none!important; }

  /* Rotate hint animation */
  @keyframes phoneWiggle{
    0%{transform:rotate(0deg) translateY(0)}
    20%{transform:rotate(-12deg) translateY(-1px)}
    40%{transform:rotate(12deg) translateY(-1px)}
    60%{transform:rotate(-10deg) translateY(0)}
    80%{transform:rotate(10deg) translateY(0)}
    100%{transform:rotate(0deg) translateY(0)}
  }
  .rotate-phone{ animation: phoneWiggle 1.2s ease-in-out infinite; }

  /* ===== MOBILE CALENDAR OPTIMIZATIONS ===== */
  @media (max-width:640px){
    #calendar, .fc { min-height: 380px; }
    
    /* Larger touch targets for day cells */
    .fc-daygrid-day{ min-height: 70px; }
    .fc-daygrid-day-frame { min-height: 70px !important; }
    
    /* Larger events for touch */
    .fc-event{ 
      font-size: 10px !important; 
      padding: 4px 6px !important; 
      min-height: 24px !important;
      touch-action: manipulation;
      -webkit-tap-highlight-color: transparent;
    }
    
    /* Compact day headers */
    .fc-col-header-cell-cushion {
      padding: 6px 2px !important;
      font-size: 0.7rem !important;
    }
    
    /* Day numbers */
    .fc-daygrid-day-number {
      font-size: 0.8rem !important;
      padding: 4px !important;
    }
    
    /* Today indicator smaller on mobile */
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number{
      width: 26px !important;
      height: 26px !important;
      font-size: 0.75rem !important;
    }
    
    /* Hide unnecessary borders on mobile for cleaner look */
    .fc-scrollgrid-sync-inner {
      padding: 2px !important;
    }
    
    /* Event list mode on mobile */
    .fc-daygrid-event-harness {
      margin: 1px 2px !important;
    }
  }
  
  /* Extra small phones */
  @media (max-width: 375px) {
    #calendar, .fc { min-height: 340px; }
    .fc-daygrid-day{ min-height: 60px; }
    .fc-event{ font-size: 9px !important; }
  }
  
  @media (min-width:1024px){
    #rotateHint{ display:none !important; }
  }
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>
<script>
  let calendar = null;
  let currentFilter = 'all';

  const backendView = @json($view ?? 'month');
  // Default to day view on mobile for better UX
  const isMobile = window.innerWidth < 768;
  const initialView = isMobile ? 'dayGridDay' : ((backendView === 'day') ? 'dayGridDay'
    : (backendView === 'week' ? 'timeGridWeek' : 'dayGridMonth'));

  document.addEventListener('DOMContentLoaded', () => {
    if (typeof FullCalendar === 'undefined') {
      console.error("FullCalendar non chargé");
      document.getElementById('calendar').innerHTML = '<div class="text-center py-8 text-gray-500">Erreur de chargement du calendrier. Rafraîchissez la page.</div>';
      return;
    }

    initCalendar();
    setActiveFilterUI('all');
    setActiveViewUI(initialView);
    initEventForm();
    initModalUX();

    // Rotate hint (show every time in portrait)
    applyRotateHint();
    window.addEventListener('resize', applyRotateHint, { passive: true });
    window.addEventListener('orientationchange', applyRotateHint);
    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', applyRotateHint, { passive: true });
    }
  });

  function initCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView,
      locale: 'fr',
      firstDay: 1,
      height: 'auto',
      expandRows: true,
      handleWindowResize: true,
      nowIndicator: true,
      dayMaxEvents: 3,
      moreLinkClick: function(info) {
        showDayEvents(info.date, info.allSegs);
        return 'none'; // Empêche le popover par défaut
      },

      events: {
        url: @json(route('prestataire.agenda.events')),
        extraParams: () => ({ filter: currentFilter }),
        failure: (err) => {
          console.error('Failed to load events:', err);
          showNotification("Erreur lors du chargement des événements", "error");
        }
      },

      dateClick: (info) => showDayEventsFromDate(info.date),
      datesSet: () => syncCalendarTitle(),
      eventClick: (info) => handleEventClick(info),
      eventDidMount: (info) => { info.el.title = info.event.title || ''; }
    });

    calendar.render();

    // Fix iOS/Safari (calendrier parfois blanc sans resize)
    setTimeout(() => {
      syncCalendarTitle();
      setActiveViewUI(calendar.view.type);
      calendar.updateSize();
    }, 0);
    setTimeout(() => calendar.updateSize(), 200);
  }

  function syncCalendarTitle() {
    const el = document.getElementById('calendarTitle');
    if (!el || !calendar) return;
    el.textContent = calendar.view.title || 'Agenda';
  }

  function calNav(action) {
    if (!calendar) return;
    if (action === 'prev') calendar.prev();
    if (action === 'next') calendar.next();
    if (action === 'today') calendar.today();
    syncCalendarTitle();
    setTimeout(() => calendar.updateSize(), 0);
  }

  function handleEventClick(info) {
    const event = info.event;
    const props = event.extendedProps || {};

    // Événement manuel - propose la suppression
    if (props.type === 'manual_event' || props.itemType === 'manual_event' || props.kind === 'manual') {
      const id = props.eventId || (typeof event.id === 'string' ? event.id.replace(/^event_/, '') : event.id);
      if (!id) return;
      if (confirm(`Événement: ${event.title}\n\nVoulez-vous supprimer cet événement ?`)) deleteManualEvent(id);
      return;
    }

    // Réservation de service - redirige vers les détails
    if (props.type === 'service') {
      const bookingId = props.id || event.id;
      const url = props.url || `/prestataire/bookings/${bookingId}`;
      window.location.href = url;
      return;
    }
    
    // Location d'équipement - redirige vers les détails
    if (props.type === 'equipment' || props.itemType === 'equipment_rental_request') {
      const rentalId = props.id || (typeof event.id === 'string' ? event.id.replace(/^equipment_/, '') : event.id);
      const url = props.rentalUrl || props.url || `/prestataire/equipment-rental-requests/${rentalId}`;
      window.location.href = url;
      return;
    }
  }

  function changeView(e, view) {
    if (!calendar) return;
    calendar.changeView(view);
    setActiveViewUI(view);
    syncCalendarTitle();
    setTimeout(() => calendar.updateSize(), 0);
    if (e && e.preventDefault) e.preventDefault();
  }

  function setActiveViewUI(view) {
    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.classList.remove('bg-blue-100','text-blue-700');
      btn.classList.add('text-slate-600');
    });
    const active = document.querySelector(`.view-btn[data-view="${view}"]`);
    if (active) {
      active.classList.add('bg-blue-100','text-blue-700');
      active.classList.remove('text-slate-600');
    }
  }

  function filterEvents(filterType) {
    currentFilter = filterType;
    setActiveFilterUI(filterType);
    if (calendar) calendar.refetchEvents();
  }

  function setActiveFilterUI(filterType) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.classList.remove('bg-gray-100','bg-blue-50','bg-green-50');
      btn.classList.add('bg-slate-100');
    });

    const btnId = (filterType === 'all') ? 'allEventsBtn' : (filterType === 'service' ? 'servicesBtn' : 'equipmentBtn');
    const activeClass = (filterType === 'all') ? 'bg-gray-100' : (filterType === 'service' ? 'bg-blue-50' : 'bg-green-50');

    const active = document.getElementById(btnId);
    if (active) {
      active.classList.add(activeClass);
      active.classList.remove('bg-slate-100');
    }
  }

  function openEventModal() {
    const modal = document.getElementById('eventModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeEventModal() {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    if (form) form.reset();
  }

  // ===== DAY EVENTS MODAL FUNCTIONS =====
  
  function showDayEvents(date, allSegs) {
    const events = allSegs.map(seg => seg.event);
    renderDayEventsModal(date, events);
  }
  
  function showDayEventsFromDate(date) {
    if (!calendar) return;
    
    // Récupérer tous les événements du calendrier pour cette date
    const allEvents = calendar.getEvents();
    const dayStart = new Date(date);
    dayStart.setHours(0, 0, 0, 0);
    const dayEnd = new Date(date);
    dayEnd.setHours(23, 59, 59, 999);
    
    const dayEvents = allEvents.filter(event => {
      const eventStart = event.start;
      const eventEnd = event.end || event.start;
      return (eventStart >= dayStart && eventStart <= dayEnd) || 
             (eventEnd >= dayStart && eventEnd <= dayEnd) ||
             (eventStart <= dayStart && eventEnd >= dayEnd);
    });
    
    if (dayEvents.length === 0) {
      // Pas d'événements, on peut ouvrir le modal de création
      return;
    }
    
    renderDayEventsModal(date, dayEvents);
  }
  
  function renderDayEventsModal(date, events) {
    const modal = document.getElementById('dayEventsModal');
    const dateEl = document.getElementById('dayEventsDate');
    const countEl = document.getElementById('dayEventsCount');
    const listEl = document.getElementById('dayEventsList');
    
    if (!modal || !listEl) return;
    
    // Formater la date
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    const formattedDate = date.toLocaleDateString('fr-FR', options);
    dateEl.textContent = formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
    countEl.textContent = events.length;
    
    // Trier les événements par heure
    events.sort((a, b) => (a.start || 0) - (b.start || 0));
    
    // Générer la liste des événements
    let html = '';
    
    if (events.length === 0) {
      html = `
        <div class="text-center py-8 text-slate-500">
          <i class="fas fa-calendar-times text-4xl mb-3 text-slate-300"></i>
          <p>Aucune réservation ce jour</p>
        </div>
      `;
    } else {
      events.forEach(event => {
        const props = event.extendedProps || {};
        const type = props.type || props.itemType || 'service';
        const status = props.status || '';
        
        // Icône et couleur selon le type
        let icon = 'fa-calendar-check';
        let bgColor = 'bg-blue-50';
        let borderColor = 'border-blue-200';
        let iconColor = 'text-blue-600';
        
        if (type === 'equipment' || type === 'equipment_rental_request') {
          icon = 'fa-tools';
          bgColor = 'bg-green-50';
          borderColor = 'border-green-200';
          iconColor = 'text-green-600';
        } else if (type === 'food' || type === 'food_order') {
          icon = 'fa-utensils';
          bgColor = 'bg-orange-50';
          borderColor = 'border-orange-200';
          iconColor = 'text-orange-600';
        } else if (type === 'manual_event') {
          icon = 'fa-star';
          bgColor = 'bg-purple-50';
          borderColor = 'border-purple-200';
          iconColor = 'text-purple-600';
        }
        
        // Badge de statut
        let statusBadge = '';
        if (status) {
          let statusColor = 'bg-gray-100 text-gray-600';
          let statusText = status;
          
          if (status === 'pending' || status === 'en_attente') {
            statusColor = 'bg-yellow-100 text-yellow-700';
            statusText = 'En attente';
          } else if (status === 'confirmed' || status === 'accepte') {
            statusColor = 'bg-green-100 text-green-700';
            statusText = 'Confirmé';
          } else if (status === 'cancelled' || status === 'annule') {
            statusColor = 'bg-red-100 text-red-700';
            statusText = 'Annulé';
          } else if (status === 'completed' || status === 'termine') {
            statusColor = 'bg-blue-100 text-blue-700';
            statusText = 'Terminé';
          }
          
          statusBadge = `<span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor}">${statusText}</span>`;
        }
        
        // Heure
        let timeStr = '';
        if (event.start) {
          const startTime = event.start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
          if (event.end && event.end.getTime() !== event.start.getTime()) {
            const endTime = event.end.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            timeStr = `${startTime} - ${endTime}`;
          } else {
            timeStr = startTime;
          }
        }
        
        // URL pour le clic
        let url = '#';
        if (type === 'service' && props.id) {
          url = props.url || `/prestataire/bookings/${props.id}`;
        } else if ((type === 'equipment' || type === 'equipment_rental_request') && props.id) {
          url = props.rentalUrl || props.url || `/prestataire/equipment-rental-requests/${props.id}`;
        }
        
        html += `
          <a href="${url}" class="block ${bgColor} ${borderColor} border rounded-xl p-4 hover:shadow-md transition-all">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-full ${bgColor} flex items-center justify-center flex-shrink-0 border ${borderColor}">
                <i class="fas ${icon} ${iconColor}"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1">
                  <h4 class="font-semibold text-slate-800 truncate">${event.title || 'Sans titre'}</h4>
                  ${statusBadge}
                </div>
                ${timeStr ? `<p class="text-sm text-slate-600"><i class="far fa-clock mr-1"></i>${timeStr}</p>` : ''}
                ${props.clientName ? `<p class="text-sm text-slate-500 mt-1"><i class="far fa-user mr-1"></i>${props.clientName}</p>` : ''}
              </div>
              <i class="fas fa-chevron-right text-slate-400 mt-3"></i>
            </div>
          </a>
        `;
      });
    }
    
    listEl.innerHTML = html;
    
    // Afficher le modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  
  function closeDayEventsModal() {
    const modal = document.getElementById('dayEventsModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function initModalUX() {
    const modal = document.getElementById('eventModal');
    if (!modal) return;

    modal.addEventListener('click', (e) => { if (e.target === modal) closeEventModal(); });
    
    // Day events modal click outside
    const dayModal = document.getElementById('dayEventsModal');
    if (dayModal) {
      dayModal.addEventListener('click', (e) => { if (e.target === dayModal) closeDayEventsModal(); });
    }
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeEventModal();
    });
  }

  function initEventForm() {
    const form = document.getElementById('eventForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) { alert('CSRF token introuvable (meta csrf-token manquant).'); return; }

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      if (data.start_datetime && data.start_datetime.length === 16) data.start_datetime += ':00';
      if (data.end_datetime && data.end_datetime.length === 16) data.end_datetime += ':00';

      if (data.start_datetime && data.end_datetime && data.end_datetime <= data.start_datetime) {
        showNotification('La date de fin doit être après la date de début', 'error');
        return;
      }

      try {
        const res = await fetch(@json(route('prestataire.agenda.events.store')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          credentials: 'include',
          body: JSON.stringify(data)
        });

        if (res.status === 419) { alert('Session expirée. Rechargement…'); window.location.reload(); return; }

        const ct = res.headers.get('content-type') || '';
        const payload = ct.includes('application/json') ? await res.json() : null;

        if (!res.ok) { showNotification(payload?.message || 'Erreur lors de la création', 'error'); return; }

        if (payload?.success) {
          showNotification("Événement créé avec succès", "success");
          closeEventModal();
          if (calendar) calendar.refetchEvents();
        } else {
          showNotification(payload?.message || 'Erreur lors de la création', 'error');
        }
      } catch (err) {
        console.error(err);
        showNotification('Une erreur est survenue', 'error');
      }
    });
  }

  function deleteManualEvent(eventId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) { alert('CSRF token introuvable (meta csrf-token manquant).'); return; }

    fetch(`/prestataire/agenda/events/${encodeURIComponent(eventId)}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
      credentials: 'include'
    })
    .then(async (res) => {
      if (res.status === 419) { alert('Session expirée. Rechargement…'); window.location.reload(); return null; }
      const ct = res.headers.get('content-type') || '';
      return ct.includes('application/json') ? res.json() : null;
    })
    .then((data) => {
      if (!data) return;
      if (data.success) {
        showNotification('Événement supprimé', 'success');
        if (calendar) calendar.refetchEvents();
      } else {
        showNotification(data.message || 'Erreur lors de la suppression', 'error');
      }
    })
    .catch((err) => {
      console.error(err);
      showNotification('Une erreur est survenue', 'error');
    });
  }

  function showDemandDetails(id, type) {
    if (type === 'service') window.location.href = `/prestataire/bookings/${id}`;
    else window.location.href = `/prestataire/equipment-rental-requests/${id}`;
  }

  // Mobile Tab Switching
  function switchMobileTab(tab) {
    const tabCalendar = document.getElementById('tabCalendar');
    const tabDemands = document.getElementById('tabDemands');
    const panelCalendar = document.getElementById('panelCalendar');
    const panelDemands = document.getElementById('panelDemands');

    if (tab === 'calendar') {
      tabCalendar?.classList.add('active');
      tabDemands?.classList.remove('active');
      panelCalendar?.classList.add('active');
      panelDemands?.classList.remove('active');
      // Refresh calendar size after tab switch
      setTimeout(() => { if (calendar) calendar.updateSize(); }, 100);
    } else {
      tabCalendar?.classList.remove('active');
      tabDemands?.classList.add('active');
      panelCalendar?.classList.remove('active');
      panelDemands?.classList.add('active');
    }
  }

  // Rotate hint logic (AFFICHE A CHAQUE FOIS EN PORTRAIT)
  let rotateHintDismissedThisPortrait = false;

  function isPortrait() {
    // iOS safe
    return window.innerHeight > window.innerWidth;
  }

  function isSmallScreen() {
    return window.innerWidth <= 1024;
  }

  function shouldShowRotateHint() {
    return isSmallScreen() && isPortrait() && !rotateHintDismissedThisPortrait;
  }

  function applyRotateHint() {
    const el = document.getElementById('rotateHint');
    if (!el) return;

    // reset dès qu’on sort du portrait
    if (!isPortrait()) rotateHintDismissedThisPortrait = false;

    if (shouldShowRotateHint()) {
      el.classList.remove('hidden');
      el.classList.add('flex');
    } else {
      el.classList.add('hidden');
      el.classList.remove('flex');
    }
  }

  function dismissRotateHint() {
    rotateHintDismissedThisPortrait = true; // masque seulement tant qu’on reste en portrait
    applyRotateHint();
  }

  function showNotification(message, type = 'info') {
    const div = document.createElement('div');
    div.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
      type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600')
    }`;
    div.textContent = message;
    document.body.appendChild(div);

    setTimeout(() => {
      div.style.opacity = '0';
      setTimeout(() => div.remove(), 300);
    }, 3000);
  }
</script>
@endpush