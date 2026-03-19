@extends('layouts.app')
@section('title', 'Carte interne des livraisons')

@push('styles')
<style>
.im-page {
    background: #f3f6fb;
    min-height: calc(100vh - var(--site-nav-h, 70px));
    padding: 12px;
}
.im-page.map-fullscreen {
    background: transparent;
    min-height: 100vh;
    padding: 0;
}
.im-page.map-fullscreen .im-header,
.im-page.map-fullscreen .im-controls,
.im-page.map-fullscreen .im-sidebar,
.im-page.map-fullscreen .im-internal-driver-note {
    display: none;
}
.im-page.map-fullscreen .im-layout {
    margin-top: 0;
    min-height: 100vh;
}
.im-header {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.im-title-wrap h1 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
}
.im-title-wrap p {
    margin: 4px 0 0;
    color: #6b7280;
    font-size: 0.84rem;
}
.im-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.im-btn {
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #111827;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.im-btn.active {
    background: #111827;
    color: #ffffff;
    border-color: #111827;
}
.im-btn.primary {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
.im-controls {
    margin-top: 10px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 10px;
    display: grid;
    grid-template-columns: 1fr 1.4fr auto auto;
    gap: 8px;
}
.im-control {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 9px 11px;
    font-size: 0.86rem;
    color: #111827;
    background: #ffffff;
}
.im-layout {
    margin-top: 10px;
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 10px;
    min-height: calc(100vh - 230px);
}
.im-sidebar {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.im-side-head {
    padding: 10px 12px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.im-side-head strong {
    font-size: 0.9rem;
    color: #111827;
}
.im-side-head span {
    font-size: 0.78rem;
    color: #6b7280;
    font-weight: 700;
}
.im-side-summary {
    padding: 8px 10px;
    border-bottom: 1px solid #e5e7eb;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px;
}
.im-summary-card {
    border: 1px solid #dbe2ef;
    background: #f8fbff;
    border-radius: 8px;
    padding: 6px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.im-summary-label {
    font-size: 0.68rem;
    color: #6b7280;
    font-weight: 700;
}
.im-summary-value {
    font-size: 0.9rem;
    color: #111827;
    font-weight: 800;
}
.im-driver-legend {
    padding: 8px 10px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.im-driver-chip {
    border: 1px solid #d1d5db;
    border-radius: 999px;
    padding: 4px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #111827;
    background: #f9fafb;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.im-driver-chip.active {
    border-color: #111827;
    background: #111827;
    color: #ffffff;
}
.im-color-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    display: inline-block;
}
.im-order-list {
    padding: 10px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.im-order-card {
    border: 1px solid #e5e7eb;
    border-left-width: 4px;
    border-radius: 10px;
    background: #ffffff;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.im-order-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.im-order-top strong {
    font-size: 0.85rem;
    color: #111827;
}
.im-order-top span {
    font-size: 0.72rem;
    font-weight: 700;
    color: #4b5563;
}
.im-order-meta {
    font-size: 0.76rem;
    color: #374151;
}
.im-order-priority {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.im-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: .01em;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    color: #374151;
}
.im-pill.prio-1 { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.im-pill.prio-2 { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
.im-pill.prio-3 { background: #ecfeff; color: #155e75; border-color: #67e8f9; }
.im-pill.prio-4 { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
.im-pill.eta { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
.im-pill.route { background: #dcfce7; color: #166534; border-color: #86efac; }
.im-order-addr {
    font-size: 0.75rem;
    color: #6b7280;
}
.im-order-assign {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 6px;
    align-items: center;
}
.im-order-assign .im-btn {
    padding: 8px 10px;
    font-size: 0.72rem;
    min-width: 88px;
}
.im-assign-select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 7px 8px;
    font-size: 0.75rem;
    color: #111827;
    background: #ffffff;
}
.im-order-lock {
    font-size: 0.72rem;
    color: #b91c1c;
    font-weight: 700;
}
.im-order-note {
    font-size: 0.71rem;
    color: #6b7280;
}
.im-order-actions {
    display: flex;
    gap: 6px;
}
.im-order-actions .im-btn {
    flex: 1;
    text-align: center;
    padding: 7px 9px;
    font-size: 0.74rem;
}
.im-order-verify {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 6px;
    align-items: center;
}
.im-code-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 9px;
    font-size: 0.78rem;
    color: #111827;
    background: #ffffff;
    letter-spacing: .06em;
    font-weight: 700;
}
.im-code-input:disabled {
    background: #f3f4f6;
    color: #9ca3af;
}
.im-btn.success {
    background: #16a34a;
    color: #ffffff;
    border-color: #16a34a;
}
.im-btn.success:disabled {
    background: #bbf7d0;
    border-color: #86efac;
    color: #166534;
    cursor: not-allowed;
}
.im-map-wrap {
    position: relative;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    background: #e5e7eb;
}
.im-map-wrap.is-fullscreen {
    position: fixed;
    top: var(--site-nav-h, 70px);
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 9999;
    border-radius: 0;
    border: 0;
}
#internalMapCanvas {
    width: 100%;
    height: 100%;
    min-height: calc(100vh - 230px);
    touch-action: pan-x pan-y;
}
.im-map-wrap.is-fullscreen #internalMapCanvas {
    min-height: calc(100vh - var(--site-nav-h, 70px));
    height: calc(100vh - var(--site-nav-h, 70px));
}
.im-map-empty {
    position: absolute;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: #374151;
    font-weight: 700;
    background: rgba(243, 244, 246, 0.9);
}
.im-map-empty.show {
    display: flex;
}
.im-gps-panel {
    position: absolute;
    left: 50%;
    right: auto;
    transform: translateX(-50%);
    width: min(520px, calc(100% - 16px));
    bottom: 8px;
    z-index: 28;
    background: rgba(17, 24, 39, 0.92);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    padding: 7px 8px;
    display: none;
    gap: 6px;
    align-items: center;
    justify-content: space-between;
}
.im-gps-panel.show {
    display: flex;
}
.im-gps-meta {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.im-gps-meta .ttl {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: .01em;
    line-height: 1.1;
}
.im-gps-meta .sub {
    font-size: 0.64rem;
    opacity: 0.95;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.1;
}
.im-gps-badges {
    display: inline-flex;
    gap: 4px;
    align-items: center;
}
.im-gps-badge {
    font-size: 0.62rem;
    font-weight: 800;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 999px;
    padding: 2px 6px;
}
.im-gps-actions {
    display: flex;
    flex-direction: row;
    gap: 4px;
    align-items: stretch;
}
.im-gps-recenter,
.im-gps-stop {
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 0.64rem;
    font-weight: 800;
    cursor: pointer;
    min-height: 30px;
    white-space: nowrap;
}
.im-gps-recenter.follow-on {
    border-color: rgba(16, 185, 129, 0.8);
    background: rgba(16, 185, 129, 0.2);
}
.im-gps-stop {
    border-color: rgba(248, 113, 113, 0.7);
    background: rgba(239, 68, 68, 0.22);
}
.im-map-hud {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 22;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: flex-end;
    max-width: min(360px, calc(100% - 20px));
}
.im-hud-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(17, 24, 39, 0.78);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: .01em;
    backdrop-filter: blur(4px);
}
.im-hud-chip.live {
    background: rgba(16, 185, 129, 0.84);
}
.im-live-toast {
    position: absolute;
    left: 50%;
    bottom: 16px;
    transform: translateX(-50%) translateY(20px);
    z-index: 35;
    opacity: 0;
    pointer-events: none;
    min-width: 220px;
    max-width: min(420px, calc(100% - 30px));
    border-radius: 12px;
    background: rgba(17, 24, 39, 0.95);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 16px 34px rgba(0, 0, 0, 0.3);
    padding: 10px 12px;
    transition: opacity .22s ease, transform .22s ease;
}
.im-live-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
.im-live-toast.success {
    background: rgba(22, 163, 74, 0.95);
}
.im-live-toast.warn {
    background: rgba(217, 119, 6, 0.95);
}
.im-live-toast .ttl {
    display: block;
    font-size: 0.78rem;
    font-weight: 800;
}
.im-live-toast .sub {
    display: block;
    margin-top: 3px;
    font-size: 0.7rem;
    opacity: 0.92;
}
.im-internal-driver-note {
    margin-top: 10px;
    background: #ecfeff;
    border: 1px solid #a5f3fc;
    border-radius: 12px;
    padding: 10px 12px;
    color: #0f766e;
    font-size: 0.85rem;
    font-weight: 700;
}
.im-map-steps {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    width: min(360px, calc(100% - 20px));
    max-height: 42%;
    overflow: auto;
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 8px 10px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.18);
}
.im-map-wrap.is-fullscreen .im-map-steps {
    top: 12px;
    left: 12px;
    width: min(420px, calc(100% - 24px));
    max-height: 44vh;
}
.im-fs-exit {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 30;
    display: none;
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #111827;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.22);
}
.im-map-wrap.is-fullscreen .im-fs-exit {
    display: inline-flex;
}
.im-fullscreen-lock {
    overflow: hidden !important;
}
.im-map-steps strong {
    display: block;
    font-size: 0.8rem;
    color: #111827;
    margin-bottom: 6px;
}
.im-step-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-top: 6px;
}
.im-step-num {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.im-step-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #111827;
}
.im-step-small {
    display: block;
    font-size: 0.72rem;
    color: #6b7280;
    margin-top: 2px;
}
.im-iw {
    min-width: 220px;
    max-width: 320px;
}
.im-iw h4 {
    margin: 0 0 6px;
    font-size: 0.95rem;
    color: #111827;
}
.im-iw-row {
    font-size: 0.8rem;
    color: #374151;
    margin: 3px 0;
}
.im-iw-row b {
    color: #111827;
}
.im-iw a {
    display: inline-block;
    margin-top: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #2563eb;
    text-decoration: none;
}

@media (max-width: 1100px) {
    .im-controls {
        grid-template-columns: 1fr;
    }
    .im-layout {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .im-map-wrap {
        order: 1;
        min-height: 70vh;
    }
    .im-map-hud {
        top: 8px;
        right: 8px;
    }
    .im-sidebar {
        order: 2;
        max-height: 42vh;
    }
    #internalMapCanvas {
        height: 70vh;
        min-height: 70vh;
    }
    .im-map-steps {
        display: none;
    }
    .im-driver-legend {
        display: none;
    }
    .im-gps-panel {
        left: 50%;
        width: min(460px, calc(100% - 12px));
        bottom: 8px;
    }
    .im-gps-meta .sub {
        display: none;
    }
}

@media (max-width: 640px) {
    .im-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .im-header-actions {
        width: 100%;
    }
    .im-header-actions .im-btn {
        flex: 1;
        text-align: center;
    }
    .im-gps-panel {
        width: calc(100% - 10px);
        padding: 6px 7px;
        gap: 5px;
    }
    .im-gps-meta .ttl {
        font-size: 0.64rem;
    }
    .im-gps-badge {
        font-size: 0.58rem;
        padding: 2px 5px;
    }
    .im-gps-recenter,
    .im-gps-stop {
        font-size: 0.58rem;
        padding: 4px 6px;
        min-height: 28px;
    }
    .im-order-verify {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="im-page" id="internalMapPage">
    <div class="im-header">
        <div class="im-title-wrap">
            <h1>Carte interne pro des livraisons</h1>
            <p>Points de livraison, detail au clic, recherche commande, et suivi par livreur</p>
        </div>
        <div class="im-header-actions">
            @if(!($internalDriverMode ?? false))
                <a class="im-btn" href="{{ route('prestataire.food-orders.dashboard', ['tab' => 'delivery']) }}">Retour livraisons</a>
                <a class="im-btn" href="{{ route('prestataire.drivers.index') }}">Livreurs</a>
            @endif
            @if($internalDriverMode ?? false)
                <button class="im-btn" type="button" id="gpsModeBtn">Mode GPS</button>
            @endif
            <button class="im-btn" type="button" id="toggleFullMapBtn">Plein ecran</button>
            <button class="im-btn primary" type="button" id="refreshMapBtn">Rafraichir</button>
        </div>
    </div>
    @if($internalDriverMode ?? false)
        <div class="im-internal-driver-note">
            Mode livreur interne: vous voyez uniquement votre tournee.
        </div>
    @endif

    <div class="im-controls">
        @if(!($internalDriverMode ?? false))
            <select class="im-control" id="driverFilter">
                <option value="">Tous les livreurs</option>
            </select>
        @else
            <select class="im-control" id="driverFilter" disabled>
                <option value="">Ma tournee</option>
            </select>
        @endif
        <input class="im-control" type="text" id="orderSearch" placeholder="Rechercher commande, client ou adresse" value="{{ $search }}">
        <button class="im-btn" type="button" id="searchBtn">Chercher</button>
        <button class="im-btn" type="button" id="resetBtn">Reset</button>
    </div>

    <div class="im-layout">
        <aside class="im-sidebar">
            <div class="im-side-head">
                <strong>Courses livreurs</strong>
                <span id="listCount">0</span>
            </div>
            <div class="im-side-summary" id="orderSummary"></div>
            <div class="im-driver-legend" id="driverLegend"></div>
            <div class="im-order-list" id="orderList"></div>
        </aside>

        <div class="im-map-wrap">
            <div id="internalMapCanvas"></div>
            <button class="im-fs-exit" type="button" id="fullMapExitBtn">Fermer</button>
            <div class="im-map-empty" id="mapEmpty">Aucun point de livraison a afficher</div>
            <div class="im-gps-panel" id="gpsPanel">
                <div class="im-gps-meta">
                    <span class="ttl" id="gpsPanelTitle">GPS actif</span>
                    <span class="sub" id="gpsPanelTarget">Recherche du prochain point...</span>
                    <div class="im-gps-badges">
                        <span class="im-gps-badge" id="gpsPanelDistance">- km</span>
                        <span class="im-gps-badge" id="gpsPanelEta">ETA -</span>
                    </div>
                </div>
                <div class="im-gps-actions">
                    <button class="im-gps-recenter" type="button" id="gpsRecenterBtn">Recentrer</button>
                    <button class="im-gps-stop" type="button" id="gpsStopBtn">Stop</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const MAP_INITIAL_DATA = @json($mapPayload);
const MAP_DATA_URL = @json(route('prestataire.food-orders.internal-map.data'));
const INITIAL_SELECTED_DRIVER_ID = @json($selectedDriverId);
const INITIAL_FOCUS_ORDER_ID = @json((int) ($initialFocusOrderId ?? 0));
const GOOGLE_MAPS_KEY_PRESENT = @json(!empty($googleMapsKey));
const INTERNAL_DRIVER_MODE = @json((bool) ($internalDriverMode ?? false));
const CSRF_TOKEN = @json(csrf_token());
const DRIVER_UPDATE_LOCATION_URL = @json(route('driver.update-location'));
const OSRM_BASE_URL = 'https://router.project-osrm.org';
const COMPACT_MOBILE_MODE = window.matchMedia('(max-width: 1100px)').matches;
const SHOW_ROUTE_ARROWS = false;
const LIVE_REFRESH_MS = INTERNAL_DRIVER_MODE ? 5000 : 15000;
const INTERNAL_GPS_MIN_PUSH_MS = 2500;

let mapData = MAP_INITIAL_DATA || {};
let map;
let infoWindow;
let directionsService = null;
let directionsAvailable = true;
let directionsDisabledReason = '';
let directionsFallbackNotified = false;
let osrmFallbackNotified = false;
let markerRefs = [];
let polylineRefs = [];
let directionsRefs = [];
let selectedDriverId = INITIAL_SELECTED_DRIVER_ID || null;
let searchTerm = '';
let routeMetricsByOrder = {};
let routeStepByPointKey = {};
let routeOriginPoints = [];
let renderToken = 0;
let pendingInitialFocusOrderId = Number(INITIAL_FOCUS_ORDER_ID || 0);
const ORDER_COLOR_PALETTE = ['#ef4444', '#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#0ea5e9', '#e11d48', '#14b8a6', '#f97316', '#6366f1'];
const MARKER_OVERLAP_GRID_DEGREES = 0.00018;
const MARKER_OVERLAP_BASE_RADIUS_M = 18;
const MARKER_OVERLAP_RING_STEP_M = 12;
const MARKER_OVERLAP_RING_CAPACITY = 8;
const STRICT_ROAD_GEOMETRY = true;
const ROUTE_OVERLAP_OFFSET_STEP_M = 6;
const ROUTE_OVERLAP_SLOTS = [-3, -2, -1, 1, 2, 3];
const orderColorCache = {};
let activeOrderColorMap = {};

const orderSearchInput = document.getElementById('orderSearch');
const driverFilterEl = document.getElementById('driverFilter');
const orderListEl = document.getElementById('orderList');
const listCountEl = document.getElementById('listCount');
const orderSummaryEl = document.getElementById('orderSummary');
const driverLegendEl = document.getElementById('driverLegend');
const mapEmptyEl = document.getElementById('mapEmpty');
const mapStepsEl = document.getElementById('mapSteps');
const mapWrapEl = document.querySelector('.im-map-wrap');
const toggleFullMapBtn = document.getElementById('toggleFullMapBtn');
const mapPageEl = document.getElementById('internalMapPage');
const fullMapExitBtn = document.getElementById('fullMapExitBtn');
const gpsModeBtn = document.getElementById('gpsModeBtn');
const gpsPanelEl = document.getElementById('gpsPanel');
const gpsPanelTitleEl = document.getElementById('gpsPanelTitle');
const gpsPanelTargetEl = document.getElementById('gpsPanelTarget');
const gpsPanelDistanceEl = document.getElementById('gpsPanelDistance');
const gpsPanelEtaEl = document.getElementById('gpsPanelEta');
const gpsRecenterBtn = document.getElementById('gpsRecenterBtn');
const gpsStopBtn = document.getElementById('gpsStopBtn');
const hudLiveClockEl = document.getElementById('hudLiveClock');
const hudOrdersCountEl = document.getElementById('hudOrdersCount');
const hudDriversCountEl = document.getElementById('hudDriversCount');
const hudGainTotalEl = document.getElementById('hudGainTotal');
const hudRouteTotalEl = document.getElementById('hudRouteTotal');
const imLiveToastEl = document.getElementById('imLiveToast');
const imLiveToastTitleEl = document.getElementById('imLiveToastTitle');
const imLiveToastSubEl = document.getElementById('imLiveToastSub');
let mapFullscreenEnabled = false;
let refreshIntervalId = null;
let refreshInFlight = false;
let internalGpsWatchId = null;
let internalGpsLastPushAt = 0;
let internalGpsLastPosition = null;
let lastDataFingerprint = '';
let lastMapFingerprint = '';
let gpsNavigationMode = false;
let gpsLiveMarker = null;
let gpsNavLineUnderlay = null;
let gpsNavLine = null;
let gpsRouteLastRefreshAt = 0;
let gpsRouteInFlight = false;
let gpsActiveTargetKey = '';
let gpsLastRouteOrigin = null;
let gpsFollowEnabled = false;
let gpsCameraLockUntil = 0;
let liveToastTimer = null;
let realtimeSnapshot = new Map();
let realtimeReady = false;
const verifyCodeDrafts = {};

if (!GOOGLE_MAPS_KEY_PRESENT) {
    mapEmptyEl.classList.add('show');
    mapEmptyEl.textContent = 'Google Maps non configure (cle API manquante).';
}

function norm(v) {
    return String(v || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function escapeHtml(v) {
    return String(v || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getOrders() {
    return Array.isArray(mapData.orders) ? mapData.orders : [];
}

function getPoints() {
    return Array.isArray(mapData.points) ? mapData.points : [];
}

function getDrivers() {
    return Array.isArray(mapData.drivers) ? mapData.drivers : [];
}

function getDriverById(driverId) {
    return getDrivers().find(driver => Number(driver.id) === Number(driverId));
}

function getOrderById(orderId) {
    return getOrders().find(o => Number(o.id) === Number(orderId));
}

function buildDataFingerprint(data) {
    const orders = (Array.isArray(data?.orders) ? data.orders : []).map(order => ([
        Number(order?.id || 0),
        Number(order?.driver_id || 0),
        String(order?.status || ''),
        String(order?.delivery_status || ''),
        String(order?.updated_at_iso || ''),
        String(order?.code_verified_at || ''),
    ]));

    const points = (Array.isArray(data?.points) ? data.points : []).map(point => ([
        String(point?.id || ''),
        String(point?.type || ''),
        Number(point?.order_id || 0),
        Number(point?.driver_id || 0),
        point?.type === 'driver' ? 'dyn' : Number(point?.lat || 0).toFixed(5),
        point?.type === 'driver' ? 'dyn' : Number(point?.lng || 0).toFixed(5),
        Number(point?.sequence || 0),
    ]));

    const drivers = (Array.isArray(data?.drivers) ? data.drivers : []).map(driver => ([
        Number(driver?.id || 0),
        'dyn',
        'dyn',
        String(driver?.status || ''),
        Number(driver?.active_orders_count || 0),
        Number(driver?.remaining_points_count || 0),
    ]));

    const selectedDriver = Number(data?.filters?.selected_driver_id || 0);
    return JSON.stringify([orders, points, drivers, selectedDriver]);
}

function buildMapFingerprint(data) {
    const orders = (Array.isArray(data?.orders) ? data.orders : [])
        .map(order => ([
            Number(order?.id || 0),
            Number(order?.driver_id || 0),
            String(order?.status || ''),
            String(order?.delivery_status || ''),
            Number(order?.sequence || 0),
            Number(order?.show_pickup_point ? 1 : 0),
            Number(order?.pickup_lat || 0).toFixed(5),
            Number(order?.pickup_lng || 0).toFixed(5),
            Number(order?.dropoff_lat || 0).toFixed(5),
            Number(order?.dropoff_lng || 0).toFixed(5),
            String(order?.driver_color || ''),
        ]))
        .sort((a, b) => a[0] - b[0]);

    const points = (Array.isArray(data?.points) ? data.points : [])
        .filter(point => point?.type !== 'driver')
        .map(point => ([
            String(point?.id || ''),
            String(point?.type || ''),
            Number(point?.order_id || 0),
            Number(point?.driver_id || 0),
            Number(point?.lat || 0).toFixed(5),
            Number(point?.lng || 0).toFixed(5),
            Number(point?.sequence || 0),
            String(point?.color || ''),
        ]));

    const selectedDriver = Number(data?.filters?.selected_driver_id || 0);
    return JSON.stringify([orders, points, selectedDriver]);
}

function captureVerifyCodeDrafts() {
    if (!orderListEl) return;
    orderListEl.querySelectorAll('input[data-verify-code]').forEach(input => {
        const orderId = Number(input.getAttribute('data-verify-code'));
        if (!orderId) return;
        verifyCodeDrafts[orderId] = String(input.value || '').replace(/\D+/g, '').slice(0, 4);
    });
}

function isUserEditingMapForm() {
    const active = document.activeElement;
    if (!active) return false;
    if (active.id === 'orderSearch') return true;
    if (active.matches('input[data-verify-code]')) return true;
    if (active.matches('select.im-assign-select')) return true;
    return false;
}

function buildVisibleOverview(orders = []) {
    const list = Array.isArray(orders) ? orders : [];
    const routeTotalByDriver = new Map();
    const totals = list.reduce((acc, order) => {
        const metrics = routeMetricsByOrder[Number(order.id)] || null;
        acc.distanceKm += resolveOrderDistanceKm(order, metrics);
        acc.deliveryFees += Number(order.delivery_fee || 0);

        const driverId = Number(order.driver_id || 0);
        if (driverId > 0 && metrics) {
            const routeKm = Number(
                metrics.routeTotalWithReturnKm
                || metrics.routeDistanceKm
                || metrics.distanceKm
                || 0
            );
            if (routeKm > 0) {
                const prev = Number(routeTotalByDriver.get(driverId) || 0);
                routeTotalByDriver.set(driverId, Math.max(prev, routeKm));
            }
        }
        return acc;
    }, { distanceKm: 0, deliveryFees: 0 });

    const routeKmOptimized = routeTotalByDriver.size
        ? [...routeTotalByDriver.values()].reduce((sum, km) => sum + Number(km || 0), 0)
        : totals.distanceKm;

    return {
        ordersCount: list.length,
        deliveryFees: Number(totals.deliveryFees || 0),
        distanceKm: Number(totals.distanceKm || 0),
        routeKmOptimized: Number(routeKmOptimized || 0),
    };
}

function updateHud(data = mapData, orders = null) {
    const stats = data?.stats || {};
    const fallbackOrders = Number(stats?.active_orders_total || (Array.isArray(data?.orders) ? data.orders.length : 0) || 0);
    const overview = buildVisibleOverview(Array.isArray(orders) ? orders : getVisibleOrders());
    const ordersCount = overview.ordersCount || fallbackOrders;
    const driversCount = INTERNAL_DRIVER_MODE
        ? 1
        : Number(stats?.drivers_on_mission || (Array.isArray(data?.drivers) ? data.drivers.length : 0) || 0);

    if (hudOrdersCountEl) {
        hudOrdersCountEl.textContent = `${ordersCount} course${ordersCount > 1 ? 's' : ''}`;
    }
    if (hudDriversCountEl) {
        hudDriversCountEl.textContent = INTERNAL_DRIVER_MODE
            ? 'Mode interne'
            : `${driversCount} livreur${driversCount > 1 ? 's' : ''}`;
    }
    if (hudGainTotalEl) {
        hudGainTotalEl.textContent = `Gain ${formatEuro(overview.deliveryFees)}`;
    }
    if (hudRouteTotalEl) {
        hudRouteTotalEl.textContent = `Tournee ${formatKm(overview.routeKmOptimized)}`;
    }
    if (hudLiveClockEl) {
        const now = new Date();
        hudLiveClockEl.textContent = `Live ${now.toLocaleTimeString('fr-FR', { hour12: false })}`;
    }
}

function setRouteOverlayVisibility(visible) {
    const targetMap = visible ? map : null;
    polylineRefs.forEach(line => line.setMap(targetMap));
    directionsRefs.forEach(renderer => renderer.setMap(targetMap));
}

function clearGpsNavigationOverlay() {
    if (gpsNavLineUnderlay) {
        gpsNavLineUnderlay.setMap(null);
        gpsNavLineUnderlay = null;
    }
    if (gpsNavLine) {
        gpsNavLine.setMap(null);
        gpsNavLine = null;
    }
}

function setGpsPanelVisible(visible) {
    if (!gpsPanelEl) return;
    gpsPanelEl.classList.toggle('show', Boolean(visible));
}

function setGpsFollowEnabled(enabled) {
    gpsFollowEnabled = Boolean(enabled);
    if (gpsPanelTitleEl) {
        gpsPanelTitleEl.textContent = gpsFollowEnabled ? 'GPS actif (suivi ON)' : 'GPS actif (manuel)';
    }
    if (!gpsRecenterBtn) return;
    gpsRecenterBtn.classList.toggle('follow-on', gpsFollowEnabled);
    gpsRecenterBtn.textContent = gpsFollowEnabled ? 'Suivi' : 'Centrer';
}

function centerMapOnGpsPosition(position, zoomFloor = 16) {
    if (!map || !position) return;
    gpsCameraLockUntil = Date.now() + 900;
    map.panTo(position);
    if (map.getZoom() < zoomFloor) {
        map.setZoom(zoomFloor);
    }
}

function formatMetersOrKm(distanceMeters) {
    const meters = Number(distanceMeters || 0);
    if (!Number.isFinite(meters) || meters <= 0) return '-';
    if (meters < 1000) return `${Math.round(meters)} m`;
    return `${(meters / 1000).toFixed(1)} km`;
}

function updateGpsPanel(targetPoint = null, distanceMeters = 0, etaSeconds = 0) {
    if (!gpsPanelEl) return;

    if (gpsPanelTitleEl) {
        gpsPanelTitleEl.textContent = gpsFollowEnabled ? 'GPS actif (suivi ON)' : 'GPS actif (manuel)';
    }
    if (gpsPanelTargetEl) {
        if (!targetPoint) {
            gpsPanelTargetEl.textContent = 'Recherche du prochain point...';
        } else {
            const order = targetPoint.order_id ? getOrderById(targetPoint.order_id) : null;
            const label = targetPoint.type === 'pickup' ? 'Recup' : 'Livraison';
            const orderNo = order?.order_number || targetPoint.order_number || '#-';
            const addr = targetPoint.address || '-';
            gpsPanelTargetEl.textContent = `${label} ${orderNo} · ${addr}`;
        }
    }
    if (gpsPanelDistanceEl) {
        gpsPanelDistanceEl.textContent = formatMetersOrKm(distanceMeters);
    }
    if (gpsPanelEtaEl) {
        const mins = Math.max(1, Math.round(Number(etaSeconds || 0) / 60));
        gpsPanelEtaEl.textContent = Number.isFinite(mins) ? `ETA ${mins} min` : 'ETA -';
    }
}

function getCurrentGpsPositionFallback() {
    if (internalGpsLastPosition && Number.isFinite(Number(internalGpsLastPosition.lat)) && Number.isFinite(Number(internalGpsLastPosition.lng))) {
        return {
            lat: Number(internalGpsLastPosition.lat),
            lng: Number(internalGpsLastPosition.lng),
        };
    }

    const preferredDriverId = Number(selectedDriverId || INITIAL_SELECTED_DRIVER_ID || 0);
    const driverEntry = markerRefs.find(entry => (
        entry?.point?.type === 'driver'
        && (!preferredDriverId || Number(entry.point.driver_id || 0) === preferredDriverId)
    )) || markerRefs.find(entry => entry?.point?.type === 'driver');

    const pos = driverEntry?.marker?.getPosition?.();
    if (!pos) return null;
    return { lat: Number(pos.lat()), lng: Number(pos.lng()) };
}

function updateGpsLiveMarker(position, heading = 0) {
    if (!map || !window.google?.maps || !position) return;
    const lat = Number(position.lat);
    const lng = Number(position.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    const icon = {
        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
        scale: 5,
        fillColor: '#2563eb',
        fillOpacity: 0.95,
        strokeColor: '#ffffff',
        strokeWeight: 1.8,
        rotation: Number.isFinite(Number(heading)) ? Number(heading) : 0,
    };

    if (!gpsLiveMarker) {
        gpsLiveMarker = new google.maps.Marker({
            position: { lat, lng },
            map: gpsNavigationMode ? map : null,
            zIndex: 500,
            title: 'Position GPS',
            icon,
        });
        return;
    }

    gpsLiveMarker.setPosition({ lat, lng });
    gpsLiveMarker.setIcon(icon);
    gpsLiveMarker.setMap(gpsNavigationMode ? map : null);
}

function getVisibleRoutePoints() {
    const visibleOrders = getVisibleOrders();
    const visibleOrderIds = new Set(visibleOrders.map(order => Number(order.id)));
    let visiblePoints = getPoints()
        .filter(point => isPointVisible(point, visibleOrderIds, visibleOrders))
        .filter(point => point.type === 'pickup' || point.type === 'dropoff');

    if (INTERNAL_DRIVER_MODE) {
        const dropoffOrderIds = new Set(
            visiblePoints
                .filter(point => point.type === 'dropoff' && point.order_id)
                .map(point => Number(point.order_id))
        );
        visiblePoints = visiblePoints.filter(point => {
            if (point.type !== 'pickup' || !point.order_id) return true;
            return !dropoffOrderIds.has(Number(point.order_id));
        });
    }

    return visiblePoints;
}

function getNextGpsTargetPoint() {
    const candidates = getVisibleRoutePoints();
    if (!candidates.length) return null;

    const withStep = candidates
        .map(point => ({ point, step: Number(getRouteMetaForPoint(point)?.step || 0) }))
        .filter(item => item.step > 0)
        .sort((a, b) => a.step - b.step);

    if (withStep.length) {
        return withStep[0].point;
    }

    return [...candidates].sort((a, b) => Number(a.sequence || 9999) - Number(b.sequence || 9999))[0] || null;
}

function drawGpsNavigationPath(path) {
    clearGpsNavigationOverlay();
    if (!map || !Array.isArray(path) || path.length < 2) return;

    gpsNavLineUnderlay = new google.maps.Polyline({
        path,
        geodesic: false,
        strokeColor: '#ffffff',
        strokeOpacity: 0.9,
        strokeWeight: 8,
        zIndex: 450,
        map: gpsNavigationMode ? map : null,
    });

    gpsNavLine = new google.maps.Polyline({
        path,
        geodesic: false,
        strokeColor: '#2563eb',
        strokeOpacity: 0.95,
        strokeWeight: 5,
        zIndex: 451,
        map: gpsNavigationMode ? map : null,
    });
}

async function updateGpsNavigation(position, options = {}) {
    if (!gpsNavigationMode || !map || !position) return;

    const nowTs = Date.now();
    const force = Boolean(options?.force);
    const movedKmSinceLast = gpsLastRouteOrigin
        ? haversineDistanceKm(gpsLastRouteOrigin, position)
        : Number.POSITIVE_INFINITY;
    if (!force && movedKmSinceLast < 0.05 && (nowTs - gpsRouteLastRefreshAt) < 12000) return;
    if (gpsRouteInFlight) return;

    const nextStop = getNextGpsTargetPoint();
    if (!nextStop) {
        clearGpsNavigationOverlay();
        updateGpsPanel(null, 0, 0);
        return;
    }

    const origin = {
        lat: Number(position.lat),
        lng: Number(position.lng),
    };
    const destination = {
        lat: Number(nextStop.lat),
        lng: Number(nextStop.lng),
    };

    if (!Number.isFinite(origin.lat) || !Number.isFinite(origin.lng) || !Number.isFinite(destination.lat) || !Number.isFinite(destination.lng)) {
        clearGpsNavigationOverlay();
        updateGpsPanel(nextStop, 0, 0);
        return;
    }

    const targetKey = `${nextStop.type}-${Number(nextStop.order_id || 0)}`;
    if (!force && targetKey === gpsActiveTargetKey && movedKmSinceLast < 0.03 && (nowTs - gpsRouteLastRefreshAt) < 15000) {
        const linearMeters = haversineDistanceKm(origin, destination) * 1000;
        updateGpsPanel(nextStop, linearMeters, Math.max(120, Math.round((linearMeters / 1000) / 28 * 3600)));
        return;
    }

    gpsRouteLastRefreshAt = nowTs;
    gpsLastRouteOrigin = origin;
    gpsActiveTargetKey = targetKey;
    gpsRouteInFlight = true;

    try {
        let path = [];
        let distanceMeters = haversineDistanceKm(origin, destination) * 1000;
        let etaSeconds = Math.max(120, Math.round((distanceMeters / 1000) / 28 * 3600));
        let roadRouteFound = false;

        // Priorite OSRM pour eviter le mode "vol d'oiseau" si Google Directions est indisponible.
        try {
            const osrm = await osrmRoute(origin, [destination]);
            const osrmPath = Array.isArray(osrm?.geometry)
                ? osrm.geometry.filter(p => Number.isFinite(Number(p?.lat)) && Number.isFinite(Number(p?.lng)))
                    .map(p => ({ lat: Number(p.lat), lng: Number(p.lng) }))
                : [];
            const firstOsrmLeg = Array.isArray(osrm?.legs) ? osrm.legs[0] : null;
            const osrmDistance = Number(firstOsrmLeg?.distance || 0);
            const osrmDuration = Number(firstOsrmLeg?.duration || 0);

            if (osrmPath.length > 1) {
                path = osrmPath;
                roadRouteFound = true;
            }
            if (Number.isFinite(osrmDistance) && osrmDistance > 0) {
                distanceMeters = osrmDistance;
            }
            if (Number.isFinite(osrmDuration) && osrmDuration > 0) {
                etaSeconds = osrmDuration;
            }
        } catch (_osrmError) {
            // Fallback Google juste apres.
        }

        if (!roadRouteFound && directionsService && directionsAvailable) {
            try {
                const result = await directionsRoute({
                    origin,
                    destination,
                    travelMode: google.maps.TravelMode.DRIVING,
                    drivingOptions: {
                        departureTime: new Date(),
                        trafficModel: 'bestguess',
                    },
                });

                const overview = result?.routes?.[0]?.overview_path || [];
                const routePath = Array.isArray(overview)
                    ? overview.map(p => ({ lat: Number(p.lat()), lng: Number(p.lng()) }))
                    : [];
                const firstLeg = result?.routes?.[0]?.legs?.[0];
                distanceMeters = Number(firstLeg?.distance?.value || distanceMeters || 0);
                etaSeconds = Number(firstLeg?.duration_in_traffic?.value || firstLeg?.duration?.value || etaSeconds || 0);

                if (routePath.length > 1) {
                    path = routePath;
                    roadRouteFound = true;
                }
            } catch (_directionsError) {
                // On garde juste les metriques estimees sans tracer en ligne droite.
            }
        }

        if (roadRouteFound && path.length > 1) {
            drawGpsNavigationPath(path);
        } else {
            clearGpsNavigationOverlay();
        }
        updateGpsPanel(nextStop, distanceMeters, etaSeconds);
    } catch (_e) {
        clearGpsNavigationOverlay();
        const fallbackMeters = haversineDistanceKm(origin, destination) * 1000;
        const fallbackEta = Math.max(120, Math.round((fallbackMeters / 1000) / 28 * 3600));
        updateGpsPanel(nextStop, fallbackMeters, fallbackEta);
    } finally {
        gpsRouteInFlight = false;
    }
}

function setGpsModeEnabled(enabled) {
    const nextState = Boolean(enabled);
    if (gpsNavigationMode === nextState) return;
    gpsNavigationMode = nextState;

    if (gpsModeBtn) {
        gpsModeBtn.classList.toggle('active', gpsNavigationMode);
        gpsModeBtn.textContent = gpsNavigationMode ? 'Mode GPS ON' : 'Mode GPS';
    }

    if (!gpsNavigationMode) {
        setRouteOverlayVisibility(true);
        clearGpsNavigationOverlay();
        if (gpsLiveMarker) {
            gpsLiveMarker.setMap(null);
        }
        setGpsFollowEnabled(false);
        gpsActiveTargetKey = '';
        gpsLastRouteOrigin = null;
        setGpsPanelVisible(false);
        return;
    }

    if (!('geolocation' in navigator)) {
        alert('Mode GPS indisponible sur cet appareil.');
        gpsNavigationMode = false;
        if (gpsModeBtn) {
            gpsModeBtn.classList.remove('active');
            gpsModeBtn.textContent = 'Mode GPS';
        }
        return;
    }

    setRouteOverlayVisibility(false);
    setGpsPanelVisible(true);
    setGpsFollowEnabled(true);
    startInternalGpsLivePush();

    const pos = getCurrentGpsPositionFallback();
    if (pos) {
        centerMapOnGpsPosition(pos, 16);
        updateGpsLiveMarker(pos, 0);
        void updateGpsNavigation(pos, { force: true });
    } else if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(gpos => {
            const lat = Number(gpos?.coords?.latitude);
            const lng = Number(gpos?.coords?.longitude);
            const heading = Number(gpos?.coords?.heading || 0);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            internalGpsLastPosition = { lat, lng };
            centerMapOnGpsPosition({ lat, lng }, 16);
            updateGpsLiveMarker({ lat, lng }, heading);
            void updateGpsNavigation({ lat, lng }, { force: true });
        }, () => {
            updateGpsPanel(null, 0, 0);
        }, {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 0,
        });
    }
}

function showLiveToast(title, subtitle = '', variant = '') {
    if (!imLiveToastEl || !imLiveToastTitleEl || !imLiveToastSubEl) return;

    imLiveToastTitleEl.textContent = String(title || 'Mise a jour');
    imLiveToastSubEl.textContent = String(subtitle || '');
    imLiveToastEl.classList.remove('success', 'warn', 'show');
    if (variant === 'success' || variant === 'warn') {
        imLiveToastEl.classList.add(variant);
    }

    void imLiveToastEl.offsetWidth;
    imLiveToastEl.classList.add('show');

    if (liveToastTimer) {
        clearTimeout(liveToastTimer);
    }
    liveToastTimer = setTimeout(() => {
        imLiveToastEl.classList.remove('show');
    }, 2800);
}

function buildRealtimeSnapshot(data) {
    const snap = new Map();
    (Array.isArray(data?.orders) ? data.orders : []).forEach(order => {
        const orderId = Number(order?.id || 0);
        if (!orderId) return;
        snap.set(orderId, {
            orderNumber: String(order?.order_number || `#${orderId}`),
            status: String(order?.delivery_status || ''),
            driverId: Number(order?.driver_id || 0),
            updatedAt: String(order?.updated_at_iso || ''),
        });
    });
    return snap;
}

function notifyRealtimeChanges(nextData, enabled = true) {
    const next = buildRealtimeSnapshot(nextData);
    if (!enabled || !realtimeReady) {
        realtimeSnapshot = next;
        realtimeReady = true;
        return;
    }

    const newOrders = [];
    const statusChanges = [];
    const assignChanges = [];

    next.forEach((curr, id) => {
        const prev = realtimeSnapshot.get(id);
        if (!prev) {
            newOrders.push(curr.orderNumber);
            return;
        }
        if (prev.status !== curr.status) {
            statusChanges.push(curr);
        } else if (prev.driverId !== curr.driverId) {
            assignChanges.push(curr);
        }
    });

    realtimeSnapshot = next;

    if (newOrders.length > 0) {
        showLiveToast(
            `Nouvelle${newOrders.length > 1 ? 's' : ''} course${newOrders.length > 1 ? 's' : ''}`,
            newOrders.slice(0, 2).join(', '),
            'success'
        );
        return;
    }

    if (assignChanges.length > 0) {
        const change = assignChanges[0];
        showLiveToast(
            `Affectation mise a jour`,
            `${change.orderNumber} reassignee`,
            'warn'
        );
        return;
    }

    if (statusChanges.length > 0) {
        const change = statusChanges[0];
        showLiveToast(
            `Statut mis a jour`,
            `${change.orderNumber} -> ${deliveryStatusLabel(change.status)}`
        );
    }
}

function syncLiveDriverPositions(data) {
    const driverPosById = new Map();

    (Array.isArray(data?.points) ? data.points : []).forEach(point => {
        if (point?.type !== 'driver') return;
        const driverId = Number(point?.driver_id || 0);
        const lat = Number(point?.lat);
        const lng = Number(point?.lng);
        if (!driverId || !Number.isFinite(lat) || !Number.isFinite(lng)) return;
        driverPosById.set(driverId, { lat, lng });
    });

    (Array.isArray(data?.drivers) ? data.drivers : []).forEach(driver => {
        const driverId = Number(driver?.id || 0);
        const lat = Number(driver?.current_lat);
        const lng = Number(driver?.current_lng);
        if (!driverId || !Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (!driverPosById.has(driverId)) {
            driverPosById.set(driverId, { lat, lng });
        }
    });

    if (!driverPosById.size) return;

    markerRefs.forEach(entry => {
        if (!entry?.point || !entry?.marker) return;
        if (entry.point.type !== 'driver') return;
        const pos = driverPosById.get(Number(entry.point.driver_id || 0));
        if (!pos) return;
        entry.point.lat = pos.lat;
        entry.point.lng = pos.lng;
        entry.marker.setPosition(pos);
    });
}

function getOrderColor(orderId) {
    const key = Number(orderId || 0);
    if (!key) return '#374151';
    if (activeOrderColorMap[key]) {
        return activeOrderColorMap[key];
    }
    if (!orderColorCache[key]) {
        const idx = Math.abs(key) % ORDER_COLOR_PALETTE.length;
        orderColorCache[key] = ORDER_COLOR_PALETTE[idx];
    }
    return orderColorCache[key];
}

function setActiveOrderColors(orders) {
    const uniqueOrderIds = [...new Set(
        (Array.isArray(orders) ? orders : [])
            .map(order => Number(order?.id || 0))
            .filter(id => id > 0)
    )];

    activeOrderColorMap = {};
    uniqueOrderIds.forEach((orderId, index) => {
        const color = ORDER_COLOR_PALETTE[index % ORDER_COLOR_PALETTE.length];
        activeOrderColorMap[orderId] = color;
        orderColorCache[orderId] = color;
    });
}

function setDriverFilterOptions() {
    if (INTERNAL_DRIVER_MODE) {
        driverFilterEl.innerHTML = '<option value="">Ma tournee</option>';
        const forcedId = Number(INITIAL_SELECTED_DRIVER_ID || mapData?.filters?.selected_driver_id || 0);
        if (forcedId > 0) {
            selectedDriverId = forcedId;
            driverFilterEl.innerHTML = `<option value="${forcedId}">Ma tournee</option>`;
            driverFilterEl.value = String(forcedId);
        } else {
            selectedDriverId = null;
        }
        driverFilterEl.disabled = true;
        return;
    }

    driverFilterEl.disabled = false;
    const currentValue = driverFilterEl.value;
    const drivers = getDrivers();
    const driverIds = new Set(drivers.map(driver => Number(driver.id)));

    if (selectedDriverId && !driverIds.has(Number(selectedDriverId))) {
        selectedDriverId = null;
    }

    driverFilterEl.innerHTML = '<option value="">Tous les livreurs</option>';
    drivers.forEach(driver => {
        const option = document.createElement('option');
        option.value = String(driver.id);
        option.textContent = `${driver.name} (${driver.active_orders_count || 0} courses)`;
        driverFilterEl.appendChild(option);
    });

    if (selectedDriverId) {
        driverFilterEl.value = String(selectedDriverId);
    } else if (currentValue) {
        driverFilterEl.value = currentValue;
    }
}

function renderDriverLegend() {
    if (!driverLegendEl) {
        return;
    }

    if (INTERNAL_DRIVER_MODE) {
        driverLegendEl.innerHTML = '';
        return;
    }

    const drivers = getDrivers();
    driverLegendEl.innerHTML = '';

    if (!drivers.length) {
        driverLegendEl.innerHTML = '<span style="font-size:.75rem;color:#6b7280;">Aucun livreur interne detecte. Les commandes en attente d\'affectation restent visibles.</span>';
        return;
    }

    drivers.forEach(driver => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'im-driver-chip' + (Number(selectedDriverId) === Number(driver.id) ? ' active' : '');
        chip.innerHTML = `
            <span class="im-color-dot" style="background:${driver.color || '#2563eb'}"></span>
            <span>${escapeHtml(driver.name)}</span>
            <span>(${driver.remaining_points_count || 0} pts)</span>
        `;
        chip.addEventListener('click', () => {
            if (Number(selectedDriverId) === Number(driver.id)) {
                selectedDriverId = null;
                driverFilterEl.value = '';
            } else {
                selectedDriverId = Number(driver.id);
                driverFilterEl.value = String(driver.id);
            }
            applyFilters(false);
        });
        driverLegendEl.appendChild(chip);
    });
}

function pointMatchesSearch(point, term) {
    if (!term) return true;

    const order = point.order_id ? getOrderById(point.order_id) : null;
    const haystack = [
        point.order_number,
        point.address,
        point.title,
        point.subtitle,
        point.driver_name,
        order ? order.client_name : '',
        order ? order.items_preview : '',
    ].join(' ');

    return norm(haystack).includes(norm(term));
}

function orderMatchesSearch(order, term) {
    if (!term) return true;
    const haystack = [
        order.order_number,
        order.client_name,
        order.client_phone,
        order.dropoff_address,
        order.pickup_address,
        order.driver_name,
        order.items_preview,
    ].join(' ');
    return norm(haystack).includes(norm(term));
}

function getSelectedDriverIds() {
    const fallback = selectedDriverId ? [Number(selectedDriverId)] : [];
    const raw = mapData?.filters?.selected_driver_ids;
    if (!Array.isArray(raw) || !raw.length) {
        return fallback;
    }

    const ids = raw
        .map(v => Number(v))
        .filter(v => Number.isFinite(v) && v > 0);

    return ids.length ? ids : fallback;
}

function matchesSelectedDriver(driverId) {
    if (!selectedDriverId) return true;
    const selectedIds = getSelectedDriverIds();
    const numericDriverId = Number(driverId || 0);
    return selectedIds.includes(numericDriverId);
}

function getVisibleOrders() {
    return getOrders()
        .filter(order => {
            if (!matchesSelectedDriver(order.driver_id)) {
                return false;
            }
            if (searchTerm && !orderMatchesSearch(order, searchTerm)) {
                return false;
            }
            return true;
        })
        .sort((a, b) => {
            const rankDiff = Number(a.priority_rank || 4) - Number(b.priority_rank || 4);
            if (rankDiff !== 0) return rankDiff;

            const scoreDiff = Number(b.priority_score || 0) - Number(a.priority_score || 0);
            if (scoreDiff !== 0) return scoreDiff;

            const dueA = Date.parse(a.priority_due_at || '');
            const dueB = Date.parse(b.priority_due_at || '');
            if (Number.isFinite(dueA) && Number.isFinite(dueB) && dueA !== dueB) {
                return dueA - dueB;
            }

            return Number(a.sequence || 9999) - Number(b.sequence || 9999);
        });
}

function isPointVisible(point, visibleOrderIds, visibleOrders) {
    if (!matchesSelectedDriver(point.driver_id)) {
        return false;
    }

    if (point.order_id) {
        if (!visibleOrderIds.has(Number(point.order_id))) {
            return false;
        }
    } else if (searchTerm && point.type === 'driver') {
        const hasVisibleOrderForDriver = visibleOrders.some(order => Number(order.driver_id) === Number(point.driver_id));
        if (!hasVisibleOrderForDriver) {
            return false;
        }
    } else if (searchTerm && !pointMatchesSearch(point, searchTerm)) {
        return false;
    }

    return true;
}

function routePointKey(kind, orderId) {
    return `${String(kind || '')}-${Number(orderId || 0)}`;
}

function getRouteMetaForPoint(point) {
    if (!point || !point.order_id) return null;
    return routeStepByPointKey[routePointKey(point.type, point.order_id)] || null;
}

function pointTypeLabel(point) {
    if (!point) return '-';
    if (point.type === 'origin') return 'Depart';
    if (point.type === 'pickup') return 'Pickup restaurant';
    if (point.type === 'dropoff') return 'Livraison client';
    if (point.type === 'driver') return 'Position livreur';
    return 'Point';
}

function getStopColor(point, routeMeta = null) {
    if (point.type === 'origin') return '#16a34a';
    if (point.type === 'driver') return point.color || '#0ea5e9';
    if (routeMeta && routeMeta.color) return routeMeta.color;
    if (point.color) return point.color;
    if (point.order_id) return getOrderColor(point.order_id);
    return '#2563eb';
}

function markerIcon(point, routeMeta = null) {
    const color = getStopColor(point, routeMeta);
    let scale = 10;
    let strokeWeight = 2.6;

    if (point.type === 'dropoff') {
        scale = INTERNAL_DRIVER_MODE ? 13 : 12;
        strokeWeight = 3;
    } else if (point.type === 'pickup') {
        scale = 10;
    } else if (point.type === 'driver') {
        scale = 10;
    } else if (point.type === 'origin') {
        scale = 11;
    }

    return {
        path: google.maps.SymbolPath.CIRCLE,
        scale,
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight,
    };
}

function markerLabel(point, routeMeta = null) {
    if (point.type === 'driver') return '';
    if (point.type === 'origin') return 'D';
    if (COMPACT_MOBILE_MODE) {
        if (INTERNAL_DRIVER_MODE && routeMeta && routeMeta.step) {
            return `${routeMeta.step}`;
        }
        return '';
    }

    if (routeMeta && routeMeta.step) {
        const prefix = point.type === 'pickup' ? 'P' : (point.type === 'dropoff' ? 'L' : '');
        return `${prefix}${routeMeta.step}`;
    }
    if (point.type === 'pickup') return 'P';
    return 'L';
}

function pointTypeWeight(type) {
    if (type === 'origin') return 1;
    if (type === 'pickup') return 2;
    if (type === 'dropoff') return 3;
    if (type === 'driver') return 4;
    return 5;
}

function markerBaseZIndex(point) {
    if (point.type === 'dropoff') return 130;
    if (point.type === 'origin') return 120;
    if (point.type === 'driver') return 105;
    if (point.type === 'pickup') return 95;
    return 85;
}

function pointDisplayId(point, index) {
    return [
        String(point?.type || 'point'),
        Number(point?.order_id || 0),
        Number(point?.driver_id || 0),
        Number(point?.sequence || 0),
        Number(point?.id || 0),
        Number(index || 0),
    ].join(':');
}

function overlapGroupKey(lat, lng) {
    const latKey = Math.round(Number(lat) / MARKER_OVERLAP_GRID_DEGREES);
    const lngKey = Math.round(Number(lng) / MARKER_OVERLAP_GRID_DEGREES);
    return `${latKey}:${lngKey}`;
}

function metersToLat(meters) {
    return Number(meters) / 111320;
}

function metersToLng(meters, lat) {
    const cosLat = Math.cos((Number(lat) * Math.PI) / 180);
    const safeCos = Math.abs(cosLat) < 0.000001 ? 0.000001 : cosLat;
    return Number(meters) / (111320 * safeCos);
}

function hashString(value) {
    const str = String(value || '');
    let hash = 0;
    for (let i = 0; i < str.length; i += 1) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
    }
    return Math.abs(hash);
}

function routeOffsetMetersForOrder(driverId, orderId, enabled = true) {
    if (STRICT_ROAD_GEOMETRY) return 0;
    if (!enabled) return 0;
    const key = `${Number(driverId || 0)}:${Number(orderId || 0)}`;
    const slot = ROUTE_OVERLAP_SLOTS[hashString(key) % ROUTE_OVERLAP_SLOTS.length] || 0;
    return slot * ROUTE_OVERLAP_OFFSET_STEP_M;
}

function buildParallelOffsetPath(path, offsetMeters = 0) {
    if (!Array.isArray(path) || path.length < 2 || Math.abs(Number(offsetMeters || 0)) < 0.01) {
        return path;
    }

    const latToMeters = 111320;
    return path.map((point, index) => {
        const prev = path[index - 1] || point;
        const next = path[index + 1] || point;

        const latRef = Number(point?.lat || 0);
        const metersPerLng = 111320 * Math.max(0.000001, Math.cos((latRef * Math.PI) / 180));

        const dx = (Number(next?.lng || 0) - Number(prev?.lng || 0)) * metersPerLng;
        const dy = (Number(next?.lat || 0) - Number(prev?.lat || 0)) * latToMeters;
        const length = Math.sqrt((dx * dx) + (dy * dy));
        if (!Number.isFinite(length) || length < 0.001) {
            return { lat: Number(point.lat), lng: Number(point.lng) };
        }

        // Perpendicular normal to draw parallel lines and reduce visual overlap.
        const nx = -dy / length;
        const ny = dx / length;
        const offsetX = nx * Number(offsetMeters);
        const offsetY = ny * Number(offsetMeters);

        const latOffset = offsetY / latToMeters;
        const lngOffset = offsetX / metersPerLng;

        return {
            lat: Number(point.lat) + latOffset,
            lng: Number(point.lng) + lngOffset,
        };
    });
}

function buildMarkerDisplayPositions(markerEntries) {
    const groups = new Map();
    const positions = new Map();

    (Array.isArray(markerEntries) ? markerEntries : []).forEach(entry => {
        const lat = Number(entry?.point?.lat);
        const lng = Number(entry?.point?.lng);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const key = overlapGroupKey(lat, lng);
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push({ ...entry, lat, lng });
    });

    groups.forEach(group => {
        if (!Array.isArray(group) || !group.length) return;

        group.sort((a, b) => {
            const typeDiff = pointTypeWeight(a.point?.type) - pointTypeWeight(b.point?.type);
            if (typeDiff !== 0) return typeDiff;
            const orderDiff = Number(a.point?.order_id || 0) - Number(b.point?.order_id || 0);
            if (orderDiff !== 0) return orderDiff;
            return String(a.point?.order_number || '').localeCompare(String(b.point?.order_number || ''));
        });

        if (group.length === 1) {
            positions.set(group[0].id, { lat: group[0].lat, lng: group[0].lng });
            return;
        }

        group.forEach((entry, idx) => {
            const ring = Math.floor(idx / MARKER_OVERLAP_RING_CAPACITY) + 1;
            const ringStart = (ring - 1) * MARKER_OVERLAP_RING_CAPACITY;
            const ringCount = Math.min(MARKER_OVERLAP_RING_CAPACITY, group.length - ringStart);
            const ringSlot = idx - ringStart;
            const angle = (Math.PI * 2 * ringSlot) / Math.max(1, ringCount);
            const radiusMeters = MARKER_OVERLAP_BASE_RADIUS_M + ((ring - 1) * MARKER_OVERLAP_RING_STEP_M);
            const latOffset = metersToLat(radiusMeters * Math.sin(angle));
            const lngOffset = metersToLng(radiusMeters * Math.cos(angle), entry.lat);

            positions.set(entry.id, {
                lat: entry.lat + latOffset,
                lng: entry.lng + lngOffset,
            });
        });
    });

    return positions;
}

function priorityClass(rank) {
    const intRank = Number(rank || 4);
    if (intRank <= 1) return 'prio-1';
    if (intRank === 2) return 'prio-2';
    if (intRank === 3) return 'prio-3';
    return 'prio-4';
}

function formatEta(minutes) {
    const m = Number(minutes || 0);
    if (!m || m < 1) return '-';
    if (m < 60) return `${m} min`;
    const h = Math.floor(m / 60);
    const rem = m % 60;
    return rem ? `${h}h${String(rem).padStart(2, '0')}` : `${h}h`;
}

function formatEuro(amount) {
    return `${Number(amount || 0).toFixed(2)}€`;
}

function formatKm(distanceKm) {
    const km = Number(distanceKm || 0);
    if (!Number.isFinite(km) || km <= 0) return '-';
    return `${km.toFixed(1)} km`;
}

function resolveOrderDistanceKm(order, routeMetrics = null) {
    const liveLegKm = Number(routeMetrics?.legDistanceKm || 0);
    if (Number.isFinite(liveLegKm) && liveLegKm > 0) {
        return liveLegKm;
    }

    const staticKm = Number(order?.delivery_distance_km || 0);
    if (Number.isFinite(staticKm) && staticKm > 0) {
        return staticKm;
    }

    return 0;
}

function renderOrderSummary(orders) {
    if (!orderSummaryEl) return;

    const overview = buildVisibleOverview(orders);

    orderSummaryEl.innerHTML = `
        <div class="im-summary-card">
            <span class="im-summary-label">Distance livraisons</span>
            <span class="im-summary-value">${escapeHtml(formatKm(overview.distanceKm))}</span>
        </div>
        <div class="im-summary-card">
            <span class="im-summary-label">Gain livraison</span>
            <span class="im-summary-value">${escapeHtml(formatEuro(overview.deliveryFees))}</span>
        </div>
        <div class="im-summary-card">
            <span class="im-summary-label">Tournee A/R optimisee</span>
            <span class="im-summary-value">${escapeHtml(formatKm(overview.routeKmOptimized))}</span>
        </div>
    `;
}

function deliveryStatusLabel(status) {
    switch (String(status || '')) {
        case 'pending': return 'A affecter';
        case 'assigned': return 'A recuperer';
        case 'picked_up': return 'Recuperee';
        case 'in_transit': return 'En livraison';
        case 'delivered': return 'Livree';
        default: return status || '-';
    }
}

function openPointInfo(point, marker) {
    const order = point.order_id ? getOrderById(point.order_id) : null;
    const routeMetrics = order ? (routeMetricsByOrder[Number(order.id)] || null) : null;
    const routeMeta = getRouteMetaForPoint(point);
    const missionColor = getStopColor(point, routeMeta);
    const openOrderUrl = order
        ? (INTERNAL_DRIVER_MODE ? null : order.show_url)
        : null;
    const verifyActionUrl = INTERNAL_DRIVER_MODE ? order?.driver_deliver_url : order?.verify_url;
    const canVerifyCode = Boolean(order?.can_verify_code) && Boolean(verifyActionUrl);
    const verifyDisabled = canVerifyCode ? '' : 'disabled';
    const orderId = Number(order?.id || 0);
    const iwInputId = `im-iw-verify-code-${orderId}`;
    const iwButtonId = `im-iw-verify-btn-${orderId}`;
    const codeDraft = String(verifyCodeDrafts[orderId] || '');
    const html = `
        <div class="im-iw" style="border-top:4px solid ${escapeHtml(missionColor)};padding-top:6px;">
            <h4 style="margin-top:2px;color:${escapeHtml(missionColor)};">${escapeHtml(point.title || 'Point')}</h4>
            <div class="im-iw-row"><b>Type:</b> ${escapeHtml(pointTypeLabel(point))}</div>
            <div class="im-iw-row"><b>Couleur mission:</b> <span style="display:inline-block;width:11px;height:11px;border-radius:999px;background:${escapeHtml(missionColor)};vertical-align:middle;border:1px solid #d1d5db;"></span></div>
            <div class="im-iw-row"><b>Livreur:</b> ${escapeHtml(point.driver_name || '-')}</div>
            ${point.order_number ? `<div class="im-iw-row"><b>Commande:</b> ${escapeHtml(point.order_number)}</div>` : ''}
            ${routeMeta ? `<div class="im-iw-row"><b>Ordre passage:</b> #${routeMeta.step}</div>` : ''}
            ${order ? `<div class="im-iw-row"><b>Client:</b> ${escapeHtml(order.client_name || '-')}</div>` : ''}
            ${order && order.client_phone ? `<div class="im-iw-row"><b>Tel:</b> ${escapeHtml(order.client_phone)}</div>` : ''}
            <div class="im-iw-row"><b>Adresse:</b> ${escapeHtml(point.address || '-')}</div>
            ${order ? `<div class="im-iw-row"><b>Gain livraison:</b> ${escapeHtml(formatEuro(order.delivery_fee || 0))}</div>` : ''}
            ${order ? `<div class="im-iw-row"><b>Distance livraison:</b> ${escapeHtml(formatKm(resolveOrderDistanceKm(order, routeMetrics)))}</div>` : ''}
            ${order ? `<div class="im-iw-row"><b>Statut:</b> ${escapeHtml(deliveryStatusLabel(order.delivery_status))}</div>` : ''}
            ${order ? `<div class="im-iw-row"><b>Priorite:</b> ${escapeHtml(order.priority_label || 'Basse')}</div>` : ''}
            ${routeMetrics ? `<div class="im-iw-row"><b>ETA route:</b> ${escapeHtml(formatEta(routeMetrics.etaMinutes || 0))}</div>` : ''}
            ${order ? `
                <div class="im-order-verify" style="margin-top:8px;">
                    <input id="${iwInputId}" class="im-code-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" placeholder="Code client (4 chiffres)" value="${escapeHtml(codeDraft)}" ${verifyDisabled}>
                    <button id="${iwButtonId}" class="im-btn success" type="button" ${verifyDisabled}>Valider livraison</button>
                </div>
            ` : ''}
            ${openOrderUrl ? `<a href="${openOrderUrl}">Ouvrir la commande</a>` : ''}
        </div>
    `;
    infoWindow.setContent(html);
    infoWindow.open({ map, anchor: marker });

    if (orderId > 0 && canVerifyCode && window.google?.maps?.event) {
        google.maps.event.addListenerOnce(infoWindow, 'domready', () => {
            const input = document.getElementById(iwInputId);
            const button = document.getElementById(iwButtonId);
            if (!input || !button) return;

            input.addEventListener('input', () => {
                input.value = String(input.value || '').replace(/\D+/g, '').slice(0, 4);
                verifyCodeDrafts[orderId] = String(input.value || '');
            });

            input.addEventListener('keydown', async event => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                await verifyOrderCode(orderId, String(input.value || ''), button);
            });

            button.addEventListener('click', async () => {
                await verifyOrderCode(orderId, String(input.value || ''), button);
            });
        });
    }
}

function clearMapObjects() {
    markerRefs.forEach(entry => entry.marker.setMap(null));
    markerRefs = [];

    directionsRefs.forEach(renderer => renderer.setMap(null));
    directionsRefs = [];

    polylineRefs.forEach(line => line.setMap(null));
    polylineRefs = [];
    clearGpsNavigationOverlay();

    routeStepByPointKey = {};
    routeOriginPoints = [];
    if (mapStepsEl) {
        mapStepsEl.innerHTML = '<strong>Ordre de tournée</strong><div class="im-step-small">Calcul en cours...</div>';
    }
}

function renderFallbackDriverPaths(visiblePoints) {
    const grouped = {};
    visiblePoints
        .filter(point => point.type !== 'driver')
        .forEach(point => {
            const key = String(point.driver_id || '');
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(point);
        });

    Object.keys(grouped).forEach(driverKey => {
        const route = grouped[driverKey]
            .sort((a, b) => Number(a.sequence || 9999) - Number(b.sequence || 9999))
            .map(p => ({ lat: Number(p.lat), lng: Number(p.lng) }));

        if (route.length < 2) return;

        const color = grouped[driverKey][0].color || '#2563eb';
        const line = new google.maps.Polyline({
            path: route,
            geodesic: true,
            strokeColor: color,
            strokeOpacity: 0.75,
            strokeWeight: 4,
            map,
        });
        polylineRefs.push(line);
    });
}

function buildDriverOptionsHtml(order) {
    const selectedId = Number(order.driver_id || 0);
    const options = ['<option value="">Non assigne</option>'];
    let selectedExists = selectedId === 0;

    getDrivers().forEach(driver => {
        const isSelected = Number(driver.id) === selectedId;
        if (isSelected) selectedExists = true;
        options.push(
            `<option value="${Number(driver.id)}" ${isSelected ? 'selected' : ''}>${escapeHtml(driver.name)}</option>`
        );
    });

    if (selectedId > 0 && !selectedExists) {
        options.push(`<option value="${selectedId}" selected>${escapeHtml(order.driver_name || 'Livreur')}</option>`);
    }

    return options.join('');
}

function renderOrderList(orders) {
    orders = Array.isArray(orders) ? orders : [];
    captureVerifyCodeDrafts();

    listCountEl.textContent = `${orders.length} course(s)`;
    orderListEl.innerHTML = '';
    renderOrderSummary(orders);

    if (!orders.length) {
        orderListEl.innerHTML = '<div style="font-size:.78rem;color:#6b7280;">Aucune course pour ce filtre.</div>';
        return;
    }

    orders.forEach(order => {
        const routeMetrics = routeMetricsByOrder[Number(order.id)] || {};
        const missionColor = routeMetrics.color || order.driver_color || getOrderColor(order.id);
        const etaText = routeMetrics.etaMinutes ? formatEta(routeMetrics.etaMinutes) : '-';
        const routeText = routeMetrics.routeIndex ? `Etape #${routeMetrics.routeIndex}` : 'Ordre -';
        const dueInfo = order.priority_due_at_human
            ? `Cible ${order.priority_due_at_human}`
            : `Maj ${order.updated_at || '-'}`;
        const waitInfo = Number(order.priority_wait_minutes || 0);
        const deliveryDistanceKm = resolveOrderDistanceKm(order, routeMetrics);
        const deliveryDistanceText = formatKm(deliveryDistanceKm);
        const deliveryFeeText = formatEuro(order.delivery_fee || 0);
        const routeDistanceText = formatKm(
            routeMetrics.routeTotalWithReturnKm
            || routeMetrics.routeDistanceKm
            || routeMetrics.distanceKm
            || 0
        );
        const returnDistanceText = Number(routeMetrics.returnDistanceKm || 0) > 0
            ? ` · retour ${formatKm(routeMetrics.returnDistanceKm)}`
            : '';
        const canAssign = Boolean(order.can_assign_driver);
        const assignDisabled = canAssign ? '' : 'disabled';
        const verifyActionUrl = INTERNAL_DRIVER_MODE ? order.driver_deliver_url : order.verify_url;
        const canVerifyCode = Boolean(order.can_verify_code) && Boolean(verifyActionUrl);
        const verifyDisabled = canVerifyCode ? '' : 'disabled';
        const codeDraft = String(verifyCodeDrafts[Number(order.id)] || '');
        const canManageOrder = !INTERNAL_DRIVER_MODE;
        const openOrderUrl = INTERNAL_DRIVER_MODE ? null : order.show_url;
        const canOpenOrder = Boolean(openOrderUrl);

        const card = document.createElement('div');
        card.className = 'im-order-card';
        card.style.borderLeftColor = missionColor;
        card.innerHTML = `
            <div class="im-order-top">
                <strong>${escapeHtml(order.order_number)}</strong>
                <span>${escapeHtml(order.driver_name || '-')}</span>
            </div>
            <div class="im-order-priority">
                <span class="im-pill ${priorityClass(order.priority_rank)}">Priorite ${escapeHtml(order.priority_label || 'Basse')}</span>
                <span class="im-pill eta">ETA ${escapeHtml(etaText)}</span>
                <span class="im-pill route" style="border-color:${escapeHtml(missionColor)};color:${escapeHtml(missionColor)}">${escapeHtml(routeText)}</span>
            </div>
            <div class="im-order-meta">${escapeHtml(order.client_name || '-')}</div>
            <div class="im-order-meta">Livraison: ${escapeHtml(deliveryFeeText)} · Distance: ${escapeHtml(deliveryDistanceText)}</div>
            <div class="im-order-meta">Statut: ${escapeHtml(deliveryStatusLabel(order.delivery_status))} · ${order.items_count || 0} article(s)</div>
            <div class="im-order-note">${escapeHtml(dueInfo)} · attente ${waitInfo} min · tournee A/R ${escapeHtml(routeDistanceText)}${escapeHtml(returnDistanceText)}</div>
            <div class="im-order-addr">${escapeHtml(order.dropoff_address || '-')}</div>
            ${canManageOrder ? `
                <div class="im-order-assign">
                    <select class="im-assign-select" data-assign-select="${order.id}" ${assignDisabled}>
                        ${buildDriverOptionsHtml(order)}
                    </select>
                    <button class="im-btn primary" type="button" data-assign-btn="${order.id}" ${assignDisabled}>Assigner</button>
                </div>
                ${!canAssign && order.assign_locked_reason ? `<div class="im-order-lock">${escapeHtml(order.assign_locked_reason)}</div>` : ''}
            ` : ''}
            <div class="im-order-verify">
                <input class="im-code-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" placeholder="Code client (4 chiffres)" data-verify-code="${order.id}" value="${escapeHtml(codeDraft)}" ${verifyDisabled}>
                <button class="im-btn success" type="button" data-verify-btn="${order.id}" ${verifyDisabled}>Valider livraison</button>
            </div>
            ${!canVerifyCode ? `<div class="im-order-note">Validation par code indisponible pour cet etat.</div>` : ''}
            <div class="im-order-actions">
                <button class="im-btn" type="button" data-order-id="${order.id}">Voir sur carte</button>
                ${canOpenOrder ? `<a class="im-btn" href="${openOrderUrl}">Ouvrir</a>` : ''}
            </div>
        `;
        orderListEl.appendChild(card);
    });

    orderListEl.querySelectorAll('button[data-order-id]').forEach(button => {
        button.addEventListener('click', () => {
            const orderId = Number(button.getAttribute('data-order-id'));
            focusOrder(orderId);
        });
    });

    orderListEl.querySelectorAll('button[data-assign-btn]').forEach(button => {
        button.addEventListener('click', async () => {
            const orderId = Number(button.getAttribute('data-assign-btn'));
            const select = orderListEl.querySelector(`select[data-assign-select="${orderId}"]`);
            if (!select) return;
            await assignOrderDriver(orderId, String(select.value || ''), button);
        });
    });

    orderListEl.querySelectorAll('input[data-verify-code]').forEach(input => {
        input.addEventListener('input', () => {
            input.value = String(input.value || '').replace(/\D+/g, '').slice(0, 4);
            const orderId = Number(input.getAttribute('data-verify-code'));
            if (orderId) {
                verifyCodeDrafts[orderId] = String(input.value || '');
            }
        });
        input.addEventListener('keydown', async event => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const orderId = Number(input.getAttribute('data-verify-code'));
            const button = orderListEl.querySelector(`button[data-verify-btn="${orderId}"]`);
            await verifyOrderCode(orderId, String(input.value || ''), button);
        });
    });

    orderListEl.querySelectorAll('button[data-verify-btn]').forEach(button => {
        button.addEventListener('click', async () => {
            const orderId = Number(button.getAttribute('data-verify-btn'));
            const input = orderListEl.querySelector(`input[data-verify-code="${orderId}"]`);
            await verifyOrderCode(orderId, String(input?.value || ''), button);
        });
    });
}

function focusOrder(orderId) {
    const markerEntry = markerRefs.find(entry => Number(entry.point.order_id) === Number(orderId) && entry.point.type !== 'driver');
    if (!markerEntry) {
        alert('Cette commande n\'a pas de coordonnees GPS disponibles pour la carte.');
        return;
    }
    map.panTo(markerEntry.marker.getPosition());
    if (map.getZoom() < 14) map.setZoom(14);
    openPointInfo(markerEntry.point, markerEntry.marker);
}

function fitMapToCurrentMarkers() {
    if (!map || !Array.isArray(markerRefs) || markerRefs.length === 0) return;

    const bounds = new google.maps.LatLngBounds();
    markerRefs.forEach(entry => {
        const pos = entry?.marker?.getPosition?.();
        if (pos) bounds.extend(pos);
    });

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds);
        if (map.getZoom() > 16) {
            map.setZoom(16);
        }
    }
}

function setMapFullscreen(enabled) {
    mapFullscreenEnabled = Boolean(enabled);
    if (mapWrapEl) {
        mapWrapEl.classList.toggle('is-fullscreen', mapFullscreenEnabled);
    }
    if (mapPageEl) {
        mapPageEl.classList.toggle('map-fullscreen', mapFullscreenEnabled);
    }
    document.body.classList.toggle('im-fullscreen-lock', mapFullscreenEnabled);
    if (toggleFullMapBtn) {
        toggleFullMapBtn.textContent = mapFullscreenEnabled ? 'Fermer plein ecran' : 'Plein ecran';
    }

    setTimeout(() => {
        if (map && window.google?.maps) {
            google.maps.event.trigger(map, 'resize');
            fitMapToCurrentMarkers();
        }
    }, 120);
}

function mapDataRefreshUrl() {
    const sep = MAP_DATA_URL.includes('?') ? '&' : '?';
    return `${MAP_DATA_URL}${sep}_ts=${Date.now()}`;
}

function haversineDistanceKm(a, b) {
    if (!a || !b) return 0;
    const toRad = d => (d * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(Number(b.lat) - Number(a.lat));
    const dLng = toRad(Number(b.lng) - Number(a.lng));
    const lat1 = toRad(Number(a.lat));
    const lat2 = toRad(Number(b.lat));
    const h = Math.sin(dLat / 2) ** 2
        + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(Math.max(0, Math.min(1, h))));
}

function getMapOrigin() {
    const origin = mapData.origin || {};
    if (origin.lat && origin.lng) {
        return { lat: Number(origin.lat), lng: Number(origin.lng) };
    }
    const center = mapData.center || {};
    if (center.lat && center.lng) {
        return { lat: Number(center.lat), lng: Number(center.lng) };
    }
    return null;
}

function buildTaskFromOrder(order) {
    const status = String(order.delivery_status || '');
    const orderColor = order?.driver_color || getOrderColor(order.id);
    const pickup = (order.pickup_lat && order.pickup_lng) ? {
        lat: Number(order.pickup_lat),
        lng: Number(order.pickup_lng),
    } : null;
    const dropoff = (order.dropoff_lat && order.dropoff_lng) ? {
        lat: Number(order.dropoff_lat),
        lng: Number(order.dropoff_lng),
    } : null;

    const needsPickup = Boolean(order.show_pickup_point)
        && !!pickup
        && ['pending', 'assigned', 'self_delivery'].includes(status);

    const firstStop = needsPickup ? pickup : (dropoff || pickup);
    const lastStop = dropoff || pickup;
    if (!firstStop || !lastStop) return null;

    return {
        orderId: Number(order.id),
        orderNumber: order.order_number || `#${order.id}`,
        color: orderColor,
        priorityScore: Number(order.priority_score || 0),
        priorityRank: Number(order.priority_rank || 4),
        waitMinutes: Number(order.priority_wait_minutes || 0),
        overdueMinutes: Number(order.priority_overdue_minutes || 0),
        dueAtTs: Date.parse(order.priority_due_at || ''),
        needsPickup,
        pickup,
        dropoff,
        firstStop,
        lastStop,
    };
}

function taskRouteCost(task, cursor) {
    const distanceCost = cursor ? haversineDistanceKm(cursor, task.firstStop) : 0;
    const priorityBoost = Number(task.priorityScore || 0) * 0.02;
    const waitBoost = Math.min(2, Number(task.waitMinutes || 0) * 0.01);
    const overdueBoost = Math.min(4, Number(task.overdueMinutes || 0) * 0.015);

    let dueBoost = 0;
    if (Number.isFinite(task.dueAtTs) && task.dueAtTs > 0) {
        const minsToDue = Math.round((task.dueAtTs - Date.now()) / 60000);
        if (minsToDue <= 60) {
            dueBoost += Math.max(0, (60 - minsToDue) / 30);
        }
        if (minsToDue < 0) {
            dueBoost += Math.min(2, Math.abs(minsToDue) / 45);
        }
    }

    return distanceCost - priorityBoost - waitBoost - overdueBoost - dueBoost;
}

function buildOptimizedStops(orders, origin) {
    const tasks = orders
        .map(buildTaskFromOrder)
        .filter(Boolean);

    if (!tasks.length) return [];

    const pending = [...tasks];
    const sortedTasks = [];
    let cursor = origin;

    while (pending.length) {
        pending.sort((a, b) => taskRouteCost(a, cursor) - taskRouteCost(b, cursor));
        const nextTask = pending.shift();
        sortedTasks.push(nextTask);
        cursor = nextTask.lastStop || cursor;
    }

    const stops = [];
    const pickupTasks = sortedTasks.filter(task => task.needsPickup && task.pickup);
    const canBatchPickups = pickupTasks.length > 1 && pickupTasks.every(task =>
        haversineDistanceKm(task.pickup, pickupTasks[0].pickup) <= 0.6
    );

    if (canBatchPickups) {
        pickupTasks.forEach(task => {
            stops.push({
                lat: Number(task.pickup.lat),
                lng: Number(task.pickup.lng),
                order_id: task.orderId,
                kind: 'pickup',
                color: task.color,
            });
        });

        const remainingDropoffs = [...sortedTasks];
        let dropCursor = pickupTasks[0].pickup || origin;

        while (remainingDropoffs.length) {
            remainingDropoffs.sort((a, b) => {
                const distA = haversineDistanceKm(dropCursor, a.dropoff || a.lastStop);
                const distB = haversineDistanceKm(dropCursor, b.dropoff || b.lastStop);
                const prioA = Number(a.priorityScore || 0) * 0.02;
                const prioB = Number(b.priorityScore || 0) * 0.02;
                return (distA - prioA) - (distB - prioB);
            });

            const task = remainingDropoffs.shift();
            if (!task) break;

            if (task.dropoff) {
                stops.push({
                    lat: Number(task.dropoff.lat),
                    lng: Number(task.dropoff.lng),
                    order_id: task.orderId,
                    kind: 'dropoff',
                    color: task.color,
                });
                dropCursor = task.dropoff;
            } else if (task.pickup) {
                stops.push({
                    lat: Number(task.pickup.lat),
                    lng: Number(task.pickup.lng),
                    order_id: task.orderId,
                    kind: 'pickup',
                    color: task.color,
                });
                dropCursor = task.pickup;
            }
        }
        return stops;
    }

    sortedTasks.forEach(task => {
        if (task.needsPickup && task.pickup) {
            stops.push({
                lat: Number(task.pickup.lat),
                lng: Number(task.pickup.lng),
                order_id: task.orderId,
                kind: 'pickup',
                color: task.color,
            });
        }
        if (task.dropoff) {
            stops.push({
                lat: Number(task.dropoff.lat),
                lng: Number(task.dropoff.lng),
                order_id: task.orderId,
                kind: 'dropoff',
                color: task.color,
            });
        } else if (task.pickup) {
            stops.push({
                lat: Number(task.pickup.lat),
                lng: Number(task.pickup.lng),
                order_id: task.orderId,
                kind: 'pickup',
                color: task.color,
            });
        }
    });

    return stops;
}

function internalStopCost(task, cursor) {
    const distanceCost = cursor ? haversineDistanceKm(cursor, task.target) : 0;
    const priorityBoost = Number(task.priorityScore || 0) * 0.018;

    let dueBoost = 0;
    if (Number.isFinite(task.dueAtTs) && task.dueAtTs > 0) {
        const minsToDue = Math.round((task.dueAtTs - Date.now()) / 60000);
        if (minsToDue <= 90) {
            dueBoost += Math.max(0, (90 - minsToDue) / 45);
        }
        if (minsToDue < 0) {
            dueBoost += Math.min(2.2, Math.abs(minsToDue) / 35);
        }
    }

    return distanceCost - priorityBoost - dueBoost;
}

function buildInternalDriverStops(orders, origin) {
    const tasks = (Array.isArray(orders) ? orders : [])
        .map(buildTaskFromOrder)
        .filter(Boolean)
        .map(task => {
            const target = task.dropoff || task.lastStop || task.pickup;
            if (!target) return null;

            return {
                orderId: Number(task.orderId),
                color: task.color,
                kind: task.dropoff ? 'dropoff' : 'pickup',
                target: {
                    lat: Number(target.lat),
                    lng: Number(target.lng),
                },
                priorityScore: Number(task.priorityScore || 0),
                dueAtTs: Number(task.dueAtTs),
            };
        })
        .filter(Boolean);

    if (!tasks.length) return [];

    const pending = [...tasks];
    const ordered = [];
    let cursor = origin || tasks[0].target;

    while (pending.length) {
        pending.sort((a, b) => internalStopCost(a, cursor) - internalStopCost(b, cursor));
        const next = pending.shift();
        ordered.push({
            lat: Number(next.target.lat),
            lng: Number(next.target.lng),
            order_id: Number(next.orderId),
            kind: next.kind,
            color: next.color,
        });
        cursor = next.target;
    }

    return ordered;
}

function directionsRoute(request) {
    return new Promise((resolve, reject) => {
        if (!directionsService || !directionsAvailable) {
            reject('NO_DIRECTIONS');
            return;
        }
        directionsService.route(request, (result, status) => {
            if (status === 'OK' && result) {
                resolve(result);
                return;
            }
            const statusCode = String(status || 'UNKNOWN_ERROR');
            if (statusCode === 'REQUEST_DENIED') {
                directionsAvailable = false;
                directionsDisabledReason = statusCode;
            }
            reject(statusCode);
        });
    });
}

async function osrmRoute(origin, stops) {
    const points = [origin, ...stops].filter(p => p && p.lat && p.lng);
    if (points.length < 2) {
        throw new Error('OSRM_NOT_ENOUGH_POINTS');
    }

    const coordinates = points
        .map(p => `${Number(p.lng)},${Number(p.lat)}`)
        .join(';');

    const url = `${OSRM_BASE_URL}/route/v1/driving/${coordinates}?overview=full&geometries=geojson&steps=true`;
    const response = await fetch(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`OSRM_HTTP_${response.status}`);
    }

    const data = await response.json();
    if (!data || data.code !== 'Ok' || !Array.isArray(data.routes) || !data.routes.length) {
        throw new Error(`OSRM_${data?.code || 'NO_ROUTE'}`);
    }

    const route = data.routes[0] || {};
    const geometry = Array.isArray(route.geometry?.coordinates)
        ? route.geometry.coordinates.map(coord => ({
            lat: Number(coord[1]),
            lng: Number(coord[0]),
        }))
        : [];

    const legs = Array.isArray(route.legs) ? route.legs : [];
    return { geometry, legs };
}

function applyFallbackMetrics(origin, stops, metrics, state) {
    let cursor = origin;

    stops.forEach(stop => {
        state.stepNumber = Number(state.stepNumber || 0) + 1;
        routeStepByPointKey[routePointKey(stop.kind, stop.order_id)] = {
            step: state.stepNumber,
            orderId: Number(stop.order_id),
            kind: stop.kind,
            color: stop.color || getOrderColor(stop.order_id),
        };

        const distKm = haversineDistanceKm(cursor, stop);
        const etaStep = Math.max(180, Math.round((distKm / 28) * 3600));
        state.etaSeconds += etaStep;
        state.distanceMeters += distKm * 1000;
        const legDistanceKm = Number(distKm.toFixed(1));
        const routeDistanceKm = Number((state.distanceMeters / 1000).toFixed(1));

        if (stop.kind === 'dropoff') {
            state.routeIndex += 1;
            metrics[Number(stop.order_id)] = {
                etaMinutes: Math.max(1, Math.round(state.etaSeconds / 60)),
                routeIndex: state.routeIndex,
                legDistanceKm,
                routeDistanceKm,
                distanceKm: routeDistanceKm,
                driverId: Number(state.driverId || 0),
                color: stop.color || getOrderColor(stop.order_id),
            };
        }

        cursor = { lat: Number(stop.lat), lng: Number(stop.lng) };
    });

    return cursor;
}

function drawStyledRoutePath(path, color, zIndex = 20, lateralOffsetMeters = 0) {
    if (!Array.isArray(path) || path.length < 2) return;
    const drawPath = buildParallelOffsetPath(path, lateralOffsetMeters);
    const routeStrokeWeight = COMPACT_MOBILE_MODE ? 3.5 : 4;
    const underlayStrokeWeight = COMPACT_MOBILE_MODE ? 6 : 7;

    const underlay = new google.maps.Polyline({
        path: drawPath,
        geodesic: false,
        strokeColor: '#ffffff',
        strokeOpacity: 0.92,
        strokeWeight: underlayStrokeWeight,
        zIndex,
        map,
    });
    polylineRefs.push(underlay);

    const routeLineOptions = {
        path: drawPath,
        geodesic: false,
        strokeColor: color,
        strokeOpacity: 0.9,
        strokeWeight: routeStrokeWeight,
        zIndex: zIndex + 1,
        map,
    };

    if (SHOW_ROUTE_ARROWS) {
        routeLineOptions.icons = [{
            icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                scale: 2.5,
                strokeColor: color,
                strokeWeight: 1.4,
            },
            offset: '100%',
            repeat: '90px',
        }];
    }

    const routeLine = new google.maps.Polyline(routeLineOptions);
    polylineRefs.push(routeLine);
}

function renderRouteSteps(points) {
    if (!mapStepsEl) return;

    const steps = (Array.isArray(points) ? points : [])
        .map(point => {
            const meta = getRouteMetaForPoint(point);
            if (!meta || !meta.step || !point.order_id) return null;
            const order = getOrderById(point.order_id);
            const typeText = point.type === 'pickup' ? 'Pickup' : 'Livraison';
            return {
                step: Number(meta.step),
                orderNumber: order?.order_number || point.order_number || `#${point.order_id}`,
                address: point.address || '-',
                type: typeText,
                color: meta.color || getOrderColor(point.order_id),
            };
        })
        .filter(Boolean)
        .sort((a, b) => a.step - b.step);

    if (!steps.length) {
        mapStepsEl.innerHTML = '<strong>Ordre de tournée</strong><div class="im-step-small">Aucun ordre disponible.</div>';
        return;
    }

    const rows = steps.map(step => `
        <div class="im-step-row">
            <span class="im-step-num" style="background:${escapeHtml(step.color)}">${step.step}</span>
            <div>
                <span class="im-step-label">${escapeHtml(step.type)} ${escapeHtml(step.orderNumber)}</span>
                <span class="im-step-small">${escapeHtml(step.address)}</span>
            </div>
        </div>
    `).join('');

    mapStepsEl.innerHTML = `<strong>Ordre de tournée</strong>${rows}`;
}

async function renderRoadRoutes(visibleOrders, visiblePoints, token) {
    routeMetricsByOrder = {};
    routeStepByPointKey = {};
    routeOriginPoints = [];

    if (!visibleOrders.length) {
        return;
    }

    const groupedOrders = {};
    visibleOrders.forEach(order => {
        const key = String(Number(order.driver_id || 0));
        if (!groupedOrders[key]) groupedOrders[key] = [];
        groupedOrders[key].push(order);
    });

    const metrics = {};
    const separateOverlaps = !INTERNAL_DRIVER_MODE && Number(visibleOrders.length || 0) > 1;

    for (const [driverKey, driverOrders] of Object.entries(groupedOrders)) {
        if (token !== renderToken) return;

        const driverId = Number(driverKey);
        const sortedDriverOrders = [...driverOrders].sort((a, b) => {
            const seqA = Number(a.sequence || 9999);
            const seqB = Number(b.sequence || 9999);
            if (seqA !== seqB) return seqA - seqB;

            const rankA = Number(a.priority_rank || 4);
            const rankB = Number(b.priority_rank || 4);
            if (rankA !== rankB) return rankA - rankB;

            const scoreDiff = Number(b.priority_score || 0) - Number(a.priority_score || 0);
            if (scoreDiff !== 0) return scoreDiff;

            return String(a.order_number || '').localeCompare(String(b.order_number || ''));
        });

        if (driverId <= 0) {
            const unassignedState = {
                etaSeconds: 0,
                routeIndex: 0,
                distanceMeters: 0,
                stepNumber: 0,
                driverId: 0,
            };

            sortedDriverOrders.forEach((order, orderIndex) => {
                const missionColor = getOrderColor(order.id);
                const status = String(order.delivery_status || '');
                const pickup = (order.pickup_lat && order.pickup_lng)
                    ? { lat: Number(order.pickup_lat), lng: Number(order.pickup_lng) }
                    : null;
                const dropoff = (order.dropoff_lat && order.dropoff_lng)
                    ? { lat: Number(order.dropoff_lat), lng: Number(order.dropoff_lng) }
                    : null;

                const needsPickup = Boolean(order.show_pickup_point)
                    && !!pickup
                    && ['pending', 'assigned', 'self_delivery'].includes(status);

                const stops = [];
                if (needsPickup && pickup) {
                    stops.push({
                        lat: Number(pickup.lat),
                        lng: Number(pickup.lng),
                        kind: 'pickup',
                        order_id: Number(order.id),
                        color: missionColor,
                    });
                }
                if (dropoff) {
                    stops.push({
                        lat: Number(dropoff.lat),
                        lng: Number(dropoff.lng),
                        kind: 'dropoff',
                        order_id: Number(order.id),
                        color: missionColor,
                    });
                } else if (!needsPickup && pickup) {
                    stops.push({
                        lat: Number(pickup.lat),
                        lng: Number(pickup.lng),
                        kind: 'pickup',
                        order_id: Number(order.id),
                        color: missionColor,
                    });
                }

                if (!stops.length) {
                    return;
                }

                const firstStop = stops[0];
                unassignedState.stepNumber += 1;
                routeStepByPointKey[routePointKey(firstStop.kind, firstStop.order_id)] = {
                    step: unassignedState.stepNumber,
                    driverId: 0,
                    orderId: Number(order.id),
                    kind: firstStop.kind,
                    color: missionColor,
                };

                if (stops.length > 1) {
                    const path = stops.map(stop => ({
                        lat: Number(stop.lat),
                        lng: Number(stop.lng),
                    }));
                    const lateralOffsetMeters = routeOffsetMetersForOrder(0, order.id, separateOverlaps);
                    drawStyledRoutePath(path, missionColor, 16 + unassignedState.stepNumber + orderIndex, lateralOffsetMeters);

                    applyFallbackMetrics(
                        { lat: Number(firstStop.lat), lng: Number(firstStop.lng) },
                        stops.slice(1),
                        metrics,
                        unassignedState
                    );
                    return;
                }

                if (firstStop.kind === 'dropoff') {
                    unassignedState.routeIndex += 1;
                    const staticKm = Number(order.delivery_distance_km || 0);
                    const legDistanceKm = Number((Math.max(0, staticKm)).toFixed(1));
                    const routeDistanceKm = Number((Math.max(legDistanceKm, staticKm)).toFixed(1));
                    metrics[Number(order.id)] = {
                        etaMinutes: Math.max(1, Math.round((routeDistanceKm / 28) * 60) || 1),
                        routeIndex: unassignedState.routeIndex,
                        legDistanceKm,
                        routeDistanceKm,
                        distanceKm: routeDistanceKm,
                        driverId: 0,
                        color: missionColor,
                    };
                }
            });
            continue;
        }

        const driver = getDriverById(driverId);
        const color = (driver && driver.color)
            ? driver.color
            : (sortedDriverOrders[0]?.driver_color || '#ef4444');

        let origin = null;
        if (driver && driver.current_lat && driver.current_lng) {
            origin = {
                lat: Number(driver.current_lat),
                lng: Number(driver.current_lng),
            };
        }
        if (!origin) {
            const firstPickup = sortedDriverOrders.find(order => order.pickup_lat && order.pickup_lng);
            if (firstPickup) {
                origin = {
                    lat: Number(firstPickup.pickup_lat),
                    lng: Number(firstPickup.pickup_lng),
                };
            }
        }
        if (!origin) {
            origin = getMapOrigin();
        }
        if (!origin) {
            continue;
        }

        routeOriginPoints.push({
            id: `origin-${driverId}`,
            type: 'origin',
            driver_id: driverId,
            driver_name: driver?.name || sortedDriverOrders[0]?.driver_name || 'Tournee',
            title: driverId > 0 ? `Depart ${driver?.name || 'livreur'}` : 'Depart tournee non assignee',
            address: driver?.address || sortedDriverOrders[0]?.pickup_address || 'Point de depart',
            lat: Number(origin.lat),
            lng: Number(origin.lng),
            color: '#16a34a',
        });

        const optimizedStops = INTERNAL_DRIVER_MODE
            ? buildInternalDriverStops(sortedDriverOrders, origin)
            : buildOptimizedStops(sortedDriverOrders, origin);
        if (!optimizedStops.length) {
            continue;
        }

        let segmentOrigin = origin;
        const state = {
            etaSeconds: 0,
            routeIndex: 0,
            distanceMeters: 0,
            stepNumber: 0,
            driverId,
        };

        for (let offset = 0; offset < optimizedStops.length; offset += 24) {
            if (token !== renderToken) return;

            const segmentStops = optimizedStops.slice(offset, offset + 24);
            const destination = segmentStops[segmentStops.length - 1];
            const waypoints = segmentStops.slice(0, -1).map(stop => ({
                location: { lat: Number(stop.lat), lng: Number(stop.lng) },
                stopover: true,
            }));

            const applyLegMetrics = (legs) => {
                legs.forEach((leg, index) => {
                    const stop = segmentStops[index];
                    if (!stop) return;

                    state.stepNumber += 1;
                    routeStepByPointKey[routePointKey(stop.kind, stop.order_id)] = {
                        step: state.stepNumber,
                        driverId,
                        orderId: Number(stop.order_id),
                        kind: stop.kind,
                        color: stop.color || getOrderColor(stop.order_id),
                    };

                    state.etaSeconds += Number(leg?.duration_in_traffic?.value || leg?.duration?.value || leg?.duration || 0);
                    const legDistanceMeters = Number(leg?.distance?.value || leg?.distance || 0);
                    state.distanceMeters += legDistanceMeters;
                    const legDistanceKm = Number((legDistanceMeters / 1000).toFixed(1));
                    const routeDistanceKm = Number((state.distanceMeters / 1000).toFixed(1));

                    if (stop.kind === 'dropoff') {
                        state.routeIndex += 1;
                        metrics[Number(stop.order_id)] = {
                            etaMinutes: Math.max(1, Math.round(state.etaSeconds / 60)),
                            routeIndex: state.routeIndex,
                            legDistanceKm,
                            routeDistanceKm,
                            distanceKm: routeDistanceKm,
                            driverId,
                            color: stop.color || getOrderColor(stop.order_id),
                        };
                    }
                });
            };

            let routeDrawn = false;

            // OSRM d'abord: plus stable pour la carte interne (et evite les erreurs Directions API).
            try {
                const osrm = await osrmRoute(segmentOrigin, segmentStops);
                if (token !== renderToken) return;

                if (Array.isArray(osrm.geometry) && osrm.geometry.length > 1) {
                    const firstStop = segmentStops[0];
                    if (INTERNAL_DRIVER_MODE) {
                        let drewLegs = false;
                        if (Array.isArray(osrm.legs) && osrm.legs.length) {
                            osrm.legs.forEach((leg, legIndex) => {
                                const stop = segmentStops[legIndex];
                                const legColor = stop?.color || getOrderColor(stop?.order_id) || color;
                                const coords = (Array.isArray(leg?.steps) ? leg.steps : []).flatMap(step => {
                                    const c = step?.geometry?.coordinates;
                                    if (!Array.isArray(c) || !c.length) return [];
                                    return c.map(pair => ({ lat: Number(pair[1]), lng: Number(pair[0]) }));
                                });
                                if (!coords.length) return;
                                drawStyledRoutePath(coords, legColor, 20 + state.stepNumber + legIndex, 0);
                                drewLegs = true;
                            });
                        }
                        if (!drewLegs) {
                            drawStyledRoutePath(
                                osrm.geometry,
                                firstStop?.color || getOrderColor(firstStop?.order_id) || color,
                                20 + state.stepNumber,
                                0
                            );
                        }
                    } else {
                        let drewLegs = false;
                        if (Array.isArray(osrm.legs) && osrm.legs.length) {
                            osrm.legs.forEach((leg, legIndex) => {
                                const stop = segmentStops[legIndex];
                                const legColor = stop?.color || getOrderColor(stop?.order_id) || color;
                                const coords = (Array.isArray(leg?.steps) ? leg.steps : []).flatMap(step => {
                                    const c = step?.geometry?.coordinates;
                                    if (!Array.isArray(c) || !c.length) return [];
                                    return c.map(pair => ({ lat: Number(pair[1]), lng: Number(pair[0]) }));
                                });
                                if (!coords.length) return;
                                const lateralOffsetMeters = routeOffsetMetersForOrder(driverId, stop?.order_id, separateOverlaps);
                                drawStyledRoutePath(coords, legColor, 20 + state.stepNumber + legIndex, lateralOffsetMeters);
                                drewLegs = true;
                            });
                        }
                        if (!drewLegs) {
                            const lateralOffsetMeters = routeOffsetMetersForOrder(driverId, firstStop?.order_id, separateOverlaps);
                            drawStyledRoutePath(
                                osrm.geometry,
                                firstStop?.color || getOrderColor(firstStop?.order_id) || color,
                                20 + state.stepNumber,
                                lateralOffsetMeters
                            );
                        }
                    }
                }

                if (Array.isArray(osrm.legs) && osrm.legs.length) {
                    applyLegMetrics(osrm.legs);
                } else {
                    applyFallbackMetrics(segmentOrigin, segmentStops, metrics, state);
                }
                routeDrawn = true;
            } catch (osrmError) {
                if (!osrmFallbackNotified) {
                    console.warn('OSRM indisponible, tentative Google Directions.', osrmError);
                    osrmFallbackNotified = true;
                }
            }

            if (!routeDrawn) {
                try {
                    if (directionsService && directionsAvailable) {
                        const result = await directionsRoute({
                            origin: { lat: Number(segmentOrigin.lat), lng: Number(segmentOrigin.lng) },
                            destination: { lat: Number(destination.lat), lng: Number(destination.lng) },
                            waypoints,
                            optimizeWaypoints: false,
                            travelMode: google.maps.TravelMode.DRIVING,
                            drivingOptions: {
                                departureTime: new Date(),
                                trafficModel: 'bestguess',
                            },
                        });

                        if (token !== renderToken) return;

                        const legs = result.routes?.[0]?.legs || [];
                        if (INTERNAL_DRIVER_MODE) {
                            legs.forEach((leg, legIndex) => {
                                const coords = (leg?.steps || []).flatMap(step => {
                                    if (!Array.isArray(step?.path) || !step.path.length) return [];
                                    return step.path.map(p => ({ lat: Number(p.lat()), lng: Number(p.lng()) }));
                                });

                                if (!coords.length) return;
                                const stop = segmentStops[legIndex];
                                const legColor = stop?.color || getOrderColor(stop?.order_id) || color;
                                drawStyledRoutePath(
                                    coords,
                                    legColor,
                                    20 + state.stepNumber + legIndex,
                                    0
                                );
                            });
                        } else {
                            legs.forEach((leg, legIndex) => {
                                const coords = (leg?.steps || []).flatMap(step => {
                                    if (!Array.isArray(step?.path) || !step.path.length) return [];
                                    return step.path.map(p => ({ lat: Number(p.lat()), lng: Number(p.lng()) }));
                                });

                                if (!coords.length) return;
                                const stop = segmentStops[legIndex];
                                const legColor = stop?.color || getOrderColor(stop?.order_id) || color;
                                const lateralOffsetMeters = routeOffsetMetersForOrder(driverId, stop?.order_id, separateOverlaps);
                                drawStyledRoutePath(
                                    coords,
                                    legColor,
                                    20 + state.stepNumber + legIndex,
                                    lateralOffsetMeters
                                );
                            });
                        }

                        applyLegMetrics(legs);
                        routeDrawn = true;
                    }
                } catch (error) {
                    if (!directionsFallbackNotified) {
                        const reason = directionsDisabledReason
                            ? ` (${directionsDisabledReason})`
                            : '';
                        console.warn(`Google Directions indisponible${reason}. Fallback estimation seule.`, error);
                        directionsFallbackNotified = true;
                    }
                }
            }

            if (!routeDrawn) {
                applyFallbackMetrics(segmentOrigin, segmentStops, metrics, state);
            }

            segmentOrigin = {
                lat: Number(destination.lat),
                lng: Number(destination.lng),
            };
        }

        if (segmentOrigin && origin) {
            const returnDistanceKm = Number(haversineDistanceKm(segmentOrigin, origin) || 0);
            const totalWithReturnKm = Number(((Number(state.distanceMeters || 0) / 1000) + returnDistanceKm).toFixed(1));
            const returnKmRounded = Number(returnDistanceKm.toFixed(1));

            Object.values(metrics).forEach(metric => {
                if (Number(metric?.driverId || 0) !== driverId) return;
                metric.routeTotalWithReturnKm = totalWithReturnKm;
                metric.returnDistanceKm = returnKmRounded;
            });
        }
    }

    if (token !== renderToken) return;
    routeMetricsByOrder = metrics;
}

async function assignOrderDriver(orderId, driverValue, triggerBtn) {
    const order = getOrderById(orderId);
    if (!order || !order.assign_url) return;

    const button = triggerBtn;
    const initialText = button.textContent;
    button.disabled = true;
    button.textContent = '...';

    try {
        const body = new URLSearchParams();
        if (driverValue) {
            body.set('driver_id', driverValue);
        }

        const response = await fetch(order.assign_url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: body.toString(),
            credentials: 'same-origin',
        });

        let json = null;
        try {
            json = await response.json();
        } catch (_e) {
            json = null;
        }

        if (!response.ok || !json || !json.success) {
            throw new Error((json && json.message) ? json.message : 'Affectation impossible.');
        }

        await refreshData();
    } catch (e) {
        alert(e.message || 'Affectation impossible.');
    } finally {
        button.disabled = false;
        button.textContent = initialText;
    }
}

async function verifyOrderCode(orderId, codeValue, triggerBtn) {
    const order = getOrderById(orderId);
    const useDriverDeliverRoute = INTERNAL_DRIVER_MODE && Boolean(order?.driver_deliver_url);
    const verifyUrl = useDriverDeliverRoute ? order?.driver_deliver_url : order?.verify_url;
    if (!order || !verifyUrl) return;

    const code = String(codeValue || '').trim().replace(/\D+/g, '');
    if (!/^\d{4}$/.test(code)) {
        alert('Code client invalide. Entrez 4 chiffres.');
        return;
    }

    const button = triggerBtn;
    const initialText = button ? button.textContent : 'Valider livraison';
    if (button) {
        button.disabled = true;
        button.textContent = 'Validation...';
    }

    try {
        const body = new URLSearchParams();
        if (useDriverDeliverRoute) {
            body.set('delivery_code', code);
        } else {
            body.set('code', code);
        }

        const response = await fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: body.toString(),
            credentials: 'same-origin',
        });

        let json = null;
        try {
            json = await response.json();
        } catch (_e) {
            json = null;
        }

        if (!response.ok || !json || !json.success) {
            throw new Error((json && json.message) ? json.message : 'Validation du code impossible.');
        }

        delete verifyCodeDrafts[Number(orderId)];
        await refreshData();
    } catch (e) {
        alert(e.message || 'Validation du code impossible.');
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = initialText;
        }
    }
}

async function renderMapData(focusFirstMatch = false) {
    if (!map) return;

    const token = ++renderToken;
    clearMapObjects();
    routeMetricsByOrder = {};

    const visibleOrders = getVisibleOrders();
    setActiveOrderColors(visibleOrders);
    const visibleOrderIds = new Set(visibleOrders.map(order => Number(order.id)));
    let visiblePoints = getPoints()
        .filter(point => isPointVisible(point, visibleOrderIds, visibleOrders))
        .sort((a, b) => Number(a.sequence || 9999) - Number(b.sequence || 9999));

    if (INTERNAL_DRIVER_MODE) {
        const dropoffOrderIds = new Set(
            visiblePoints
                .filter(point => point.type === 'dropoff' && point.order_id)
                .map(point => Number(point.order_id))
        );
        visiblePoints = visiblePoints.filter(point => {
            if (point.type !== 'pickup' || !point.order_id) {
                return true;
            }
            return !dropoffOrderIds.has(Number(point.order_id));
        });
    }

    const hasNoPoints = visiblePoints.length === 0;
    mapEmptyEl.classList.toggle('show', hasNoPoints);
    if (hasNoPoints && visibleOrders.length > 0) {
        mapEmptyEl.textContent = 'Courses trouvees, mais sans coordonnees GPS utilisables.';
    } else if (hasNoPoints) {
        mapEmptyEl.textContent = 'Aucun point de livraison a afficher';
    }

    await renderRoadRoutes(visibleOrders, visiblePoints, token);
    if (token !== renderToken) return;

    const pointsToRender = [...routeOriginPoints, ...visiblePoints];
    const markerEntries = pointsToRender
        .filter(point => Number.isFinite(Number(point?.lat)) && Number.isFinite(Number(point?.lng)))
        .map((point, index) => ({ id: pointDisplayId(point, index), point }));
    const markerDisplayPositions = buildMarkerDisplayPositions(markerEntries);

    markerEntries.forEach(entry => {
        const point = entry.point;
        const displayPosition = markerDisplayPositions.get(entry.id) || {
            lat: Number(point.lat),
            lng: Number(point.lng),
        };

        const routeMeta = getRouteMetaForPoint(point);
        const labelText = markerLabel(point, routeMeta);
        const labelSize = point.type === 'origin'
            ? '10px'
            : (String(labelText || '').length >= 3 ? '9px' : '11px');
        const zIndex = markerBaseZIndex(point) + Number(routeMeta?.step || 0);
        const marker = new google.maps.Marker({
            position: displayPosition,
            map,
            title: point.title || '',
            icon: markerIcon(point, routeMeta),
            label: {
                text: labelText,
                color: '#ffffff',
                fontWeight: '700',
                fontSize: labelSize,
            },
            zIndex,
        });

        marker.addListener('click', () => openPointInfo(point, marker));
        markerRefs.push({ point, marker });
    });

    renderRouteSteps(visiblePoints);
    renderOrderList(visibleOrders);
    updateHud(mapData, visibleOrders);
    if (gpsNavigationMode) {
        setRouteOverlayVisibility(false);
        const pos = getCurrentGpsPositionFallback();
        if (pos) {
            updateGpsLiveMarker(pos, 0);
            void updateGpsNavigation(pos, { force: true });
        }
    } else {
        setRouteOverlayVisibility(true);
    }

    if (pendingInitialFocusOrderId > 0 && markerRefs.length > 0) {
        const entry = markerRefs.find(e => Number(e?.point?.order_id || 0) === Number(pendingInitialFocusOrderId) && e?.point?.type !== 'driver');
        if (entry) {
            map.panTo(entry.marker.getPosition());
            if (map.getZoom() < 14) map.setZoom(14);
            openPointInfo(entry.point, entry.marker);
            pendingInitialFocusOrderId = 0;
        }
    }

    if (focusFirstMatch && markerRefs.length > 0) {
        const entry = markerRefs.find(e => e.point && e.point.order_id) || markerRefs[0];
        map.panTo(entry.marker.getPosition());
        if (map.getZoom() < 13) map.setZoom(13);
        openPointInfo(entry.point, entry.marker);
    }
}

function applyFilters(focusFirstMatch = false) {
    const rawDriverValue = driverFilterEl.value;
    if (INTERNAL_DRIVER_MODE) {
        const forcedId = Number(INITIAL_SELECTED_DRIVER_ID || mapData?.filters?.selected_driver_id || 0);
        selectedDriverId = forcedId > 0 ? forcedId : null;
    } else {
        selectedDriverId = rawDriverValue ? Number(rawDriverValue) : null;
    }
    searchTerm = (orderSearchInput.value || '').trim();

    renderDriverLegend();
    void renderMapData(focusFirstMatch);
}

async function refreshData(options = {}) {
    const force = Boolean(options?.force);
    if (refreshInFlight && !force) return;
    refreshInFlight = true;
    try {
        const searchSnapshot = String(orderSearchInput?.value || '');
        const selectedSnapshot = selectedDriverId ? Number(selectedDriverId) : null;

        const response = await fetch(mapDataRefreshUrl(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        const json = await response.json();
        if (!json || !json.success || !json.data) return;

        const dataFingerprint = buildDataFingerprint(json.data);
        const mapFingerprint = buildMapFingerprint(json.data);
        const sameData = !force && lastDataFingerprint && dataFingerprint === lastDataFingerprint;
        const sameMap = !force && lastMapFingerprint && mapFingerprint === lastMapFingerprint;

        if (sameData) {
            mapData = json.data;
            syncLiveDriverPositions(json.data);
            updateHud(mapData, getVisibleOrders());
            return;
        }

        notifyRealtimeChanges(json.data, !force);
        lastDataFingerprint = dataFingerprint;
        mapData = json.data;
        if (!INTERNAL_DRIVER_MODE && selectedSnapshot) {
            selectedDriverId = selectedSnapshot;
        }
        setDriverFilterOptions();
        if (orderSearchInput && orderSearchInput.value !== searchSnapshot) {
            orderSearchInput.value = searchSnapshot;
        }
        searchTerm = searchSnapshot.trim();
        renderDriverLegend();
        if (sameMap) {
            syncLiveDriverPositions(json.data);
            const visibleOrders = getVisibleOrders();
            renderOrderList(visibleOrders);
            updateHud(mapData, visibleOrders);
            return;
        }
        await renderMapData(false);
        lastMapFingerprint = mapFingerprint;
    } catch (e) {
        console.error('Refresh internal map failed', e);
    } finally {
        refreshInFlight = false;
    }
}

function startLiveRefreshLoop() {
    if (refreshIntervalId) {
        clearInterval(refreshIntervalId);
        refreshIntervalId = null;
    }

    refreshIntervalId = setInterval(() => {
        if (document.hidden || isUserEditingMapForm()) return;
        void refreshData();
    }, LIVE_REFRESH_MS);
}

function startInternalGpsLivePush() {
    if (!INTERNAL_DRIVER_MODE) return;
    if (!('geolocation' in navigator)) return;
    if (internalGpsWatchId !== null) return;

    internalGpsWatchId = navigator.geolocation.watchPosition(async position => {
        const lat = Number(position?.coords?.latitude);
        const lng = Number(position?.coords?.longitude);
        const heading = Number(position?.coords?.heading || 0);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const nowTs = Date.now();
        const movedKm = internalGpsLastPosition
            ? haversineDistanceKm(internalGpsLastPosition, { lat, lng })
            : Number.POSITIVE_INFINITY;

        updateGpsLiveMarker({ lat, lng }, heading);
        if (gpsNavigationMode && map) {
            if (gpsFollowEnabled) {
                centerMapOnGpsPosition({ lat, lng }, 16);
            }
            void updateGpsNavigation({ lat, lng }, { force: movedKm >= 0.05 });
        }

        if (
            (nowTs - internalGpsLastPushAt) < INTERNAL_GPS_MIN_PUSH_MS
            && movedKm < 0.01
        ) {
            return;
        }

        internalGpsLastPushAt = nowTs;
        internalGpsLastPosition = { lat, lng };

        try {
            await fetch(DRIVER_UPDATE_LOCATION_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    lat,
                    lng,
                    speed: Number(position?.coords?.speed || 0),
                    heading: Number(position?.coords?.heading || 0),
                }),
                credentials: 'same-origin',
                cache: 'no-store',
            });

        } catch (e) {
            console.debug('Internal GPS push failed', e);
        }
    }, _error => {
        // Silence on client side; map keeps polling backend snapshot.
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
    });
}

window.initInternalMap = function initInternalMap() {
    if (!GOOGLE_MAPS_KEY_PRESENT) {
        mapEmptyEl.classList.add('show');
        mapEmptyEl.textContent = 'Google Maps non configure (cle API manquante).';
        return;
    }

    const center = mapData.center || { lat: 46.603354, lng: 1.888334 };
    map = new google.maps.Map(document.getElementById('internalMapCanvas'), {
        center: { lat: Number(center.lat), lng: Number(center.lng) },
        zoom: 11,
        gestureHandling: 'greedy',
        mapTypeControl: true,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.LEFT_CENTER,
        },
        streetViewControl: false,
        fullscreenControl: false,
    });
    map.addListener('dragstart', () => {
        if (!gpsNavigationMode || !gpsFollowEnabled) return;
        setGpsFollowEnabled(false);
    });
    map.addListener('zoom_changed', () => {
        if (!gpsNavigationMode || !gpsFollowEnabled) return;
        if (Date.now() < gpsCameraLockUntil) return;
        setGpsFollowEnabled(false);
    });

    directionsService = new google.maps.DirectionsService();
    infoWindow = new google.maps.InfoWindow();
    updateHud(mapData);
    realtimeSnapshot = buildRealtimeSnapshot(mapData);
    realtimeReady = true;

    setDriverFilterOptions();
    if (selectedDriverId) {
        driverFilterEl.value = String(selectedDriverId);
    }

    renderDriverLegend();
    void renderMapData(false);
    lastDataFingerprint = buildDataFingerprint(mapData);
    lastMapFingerprint = buildMapFingerprint(mapData);
    startLiveRefreshLoop();
    startInternalGpsLivePush();
    void refreshData();

    if (INTERNAL_DRIVER_MODE && COMPACT_MOBILE_MODE) {
        setMapFullscreen(true);
    }
};

driverFilterEl.addEventListener('change', () => applyFilters(false));
orderSearchInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        applyFilters(true);
    }
});
document.getElementById('searchBtn').addEventListener('click', () => applyFilters(true));
document.getElementById('resetBtn').addEventListener('click', () => {
    selectedDriverId = null;
    searchTerm = '';
    driverFilterEl.value = '';
    orderSearchInput.value = '';
    applyFilters(false);
});
document.getElementById('refreshMapBtn').addEventListener('click', () => {
    void refreshData({ force: true });
});
if (gpsModeBtn) {
    gpsModeBtn.addEventListener('click', () => {
        setGpsModeEnabled(!gpsNavigationMode);
    });
}
if (gpsStopBtn) {
    gpsStopBtn.addEventListener('click', () => {
        setGpsModeEnabled(false);
    });
}
if (gpsRecenterBtn) {
    gpsRecenterBtn.addEventListener('click', () => {
        if (!gpsNavigationMode) return;
        setGpsFollowEnabled(true);
        const pos = getCurrentGpsPositionFallback();
        if (!pos) return;
        centerMapOnGpsPosition(pos, 16);
        updateGpsLiveMarker(pos, 0);
        void updateGpsNavigation(pos, { force: true });
    });
}
if (toggleFullMapBtn) {
    toggleFullMapBtn.addEventListener('click', () => {
        setMapFullscreen(!mapFullscreenEnabled);
    });
}
if (fullMapExitBtn) {
    fullMapExitBtn.addEventListener('click', () => {
        setMapFullscreen(false);
    });
}
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && mapFullscreenEnabled) {
        setMapFullscreen(false);
    }
});
document.addEventListener('visibilitychange', () => {
    if (document.hidden) return;
    void refreshData({ force: true });
});
</script>

@if(!empty($googleMapsKey))
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initInternalMap"></script>
@endif
@endsection
