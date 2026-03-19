@extends('layouts.app')

@section('title', 'Mon Agenda - Prestataire')

@push('styles')
<style>
    /* ===== MOBILE APP OPTIMIZATIONS ===== */
    .agenda-page {
        background:
            radial-gradient(circle at top left, rgba(194, 172, 141, 0.10), transparent 26%),
            linear-gradient(180deg, #f5f1eb 0%, #faf7f2 44%, #ffffff 100%);
    }
    .agenda-page-header {
        border: none !important;
        background: rgba(255, 252, 247, 0.96);
        border-radius: 1.6rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }
    .agenda-page-kicker {
        border: none !important;
        background: #efe7dc;
        color: #74614d;
    }
    .agenda-main-panel {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        border-radius: 0 !important;
        overflow: visible !important;
        margin-bottom: 1.5rem;
    }
    .agenda-demands-panel,
    .agenda-stat-card {
        border: none !important;
    }
    .agenda-demands-panel {
        background: rgba(255, 252, 247, 0.96);
        border-radius: 1.45rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }
    .agenda-toolbar,
    .agenda-legend,
    .agenda-demands-header,
    .agenda-status-row {
        border-width: 0 !important;
    }
    .agenda-toolbar,
    .agenda-legend {
        background: rgba(255, 252, 247, 0.96) !important;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04) !important;
        border-radius: 1.35rem;
    }
    .agenda-legend {
        background: transparent !important;
        box-shadow: none !important;
        padding-top: 0.25rem !important;
        padding-bottom: 0 !important;
    }
    .agenda-panel-shell {
        max-width: 1140px;
        margin-inline: auto;
    }
    .agenda-period-nav {
        max-width: 1140px;
        margin-inline: auto;
        background: rgba(255, 252, 247, 0.98);
        color: #1f2937;
        border-radius: 1.35rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }
    .agenda-period-nav h2 {
        color: #111827;
        letter-spacing: -0.01em;
    }
    .agenda-period-nav a {
        color: inherit;
    }
    .agenda-period-nav a.p-2 {
        background: #f2eadf;
        color: #7b6751;
    }
    .agenda-period-nav a.p-2:hover,
    .agenda-period-nav a.p-2:active {
        background: #eadfd0 !important;
    }
    .agenda-period-nav a.inline-flex {
        background: #f4ede4 !important;
        color: #77634d !important;
    }
    .agenda-calendar-wrap {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        -webkit-overflow-scrolling: touch;
        scroll-padding-inline: 0;
        background: transparent;
        overscroll-behavior-x: contain;
    }
    .agenda-calendar-wrap::-webkit-scrollbar {
        height: 10px;
    }
    .agenda-calendar-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .agenda-calendar-wrap::-webkit-scrollbar-track {
        background: transparent;
    }
    .agenda-legend {
        background: transparent;
    }
    .agenda-legend-inner {
        max-width: 1140px;
        margin-inline: auto;
        padding: 0.25rem 0 0;
    }
    
    /* Mobile Tab Navigation */
    .mobile-tabs {
        display: flex;
        gap: 0.25rem;
        background: rgba(255, 252, 247, 0.96);
        padding: 0.28rem;
        border-radius: 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        border: none;
    }
    .mobile-tab {
        flex: 1;
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        text-align: center;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: none;
        cursor: pointer;
    }
    .mobile-tab.active {
        background: #111827;
        color: white;
        box-shadow: none;
    }
    .mobile-tab:not(.active) {
        background: transparent;
        color: #7b6d5f;
    }
    
    /* Mobile Panel System */
    .mobile-panel {
        display: none;
    }
    .mobile-panel.active {
        display: block;
    }
    @media (min-width: 768px) {
        .mobile-panel {
            display: block !important;
        }
    }
    
    /* Mobile FAB */
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
        border: none;
        cursor: pointer;
    }
    .mobile-fab:active {
        transform: scale(0.95);
        box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
    }
    @media (min-width: 768px) {
        .mobile-fab { display: none; }
    }
    
    /* Safe area for iOS */
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .mobile-safe-bottom {
            padding-bottom: calc(6rem + env(safe-area-inset-bottom)) !important;
        }
    }

    /* Garantit la présence des barres globales sur cette page */
    #site-navbar {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    @media (max-width: 640px) {
        #mobile-bottom-nav {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    }

    .agenda-rotate-tip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: 0.35rem;
        padding: 0.18rem 0.45rem;
        border-radius: 9999px;
        border: none;
        background: #f2eadf;
        color: #74614d;
        font-size: 0.66rem;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }
    .agenda-rotate-phone {
        display: inline-flex;
        transform-origin: center;
        animation: agendaPhoneTilt 1.8s ease-in-out infinite;
    }
    @keyframes agendaPhoneTilt {
        0%, 100% { transform: rotate(0deg); }
        35% { transform: rotate(-78deg); }
        70% { transform: rotate(0deg); }
    }

    /* Landscape phone mode: keep top/bottom bars usable */
    @media (orientation: landscape) and (max-height: 520px) and (pointer: coarse) {
        body {
            padding-top: calc(56px + env(safe-area-inset-top, 0px)) !important;
        }
        #site-navbar-main-row {
            height: 56px !important;
        }
        #site-navbar-brand-text {
            display: none !important;
        }
        #site-navbar-desktop-links,
        #site-navbar-desktop-actions {
            display: none !important;
        }
        #site-navbar-mobile-actions {
            display: flex !important;
            margin-right: 0 !important;
        }
        #mobile-bottom-nav {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        #mobile-bottom-nav > div {
            height: 56px !important;
        }
        #mobile-bottom-nav a span.text-xs {
            font-size: 0.68rem !important;
            margin-top: 0 !important;
        }
        main {
            padding-bottom: calc(90px + env(safe-area-inset-bottom, 0px)) !important;
            scroll-padding-bottom: calc(90px + env(safe-area-inset-bottom, 0px));
        }
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.32rem;
        background: transparent;
        border-radius: 0;
        overflow: visible;
        width: 100%;
        border: none;
        box-shadow: none;
        min-width: 0;
    }
    @media (min-width: 768px) {
        .calendar-grid {
            max-width: 1120px;
            margin-inline: auto;
        }
    }
    @media (max-width: 767px) {
        .calendar-grid {
            grid-template-columns: repeat(7, minmax(74px, 1fr));
            min-width: 520px;
        }
    }
    
    /* Mode portrait mobile - Affichage optimisé */
    @media (max-width: 480px) and (orientation: portrait) {
        .calendar-grid {
            grid-template-columns: repeat(7, minmax(70px, 1fr));
            font-size: 0.72rem;
            min-width: 490px;
        }
        .agenda-rotate-tip {
            font-size: 0.6rem;
            padding: 0.15rem 0.38rem;
            gap: 0.25rem;
        }
        .calendar-header {
            padding: 8px 3px !important;
            font-size: 0.68rem !important;
        }
        .calendar-day {
            min-height: 86px !important;
            padding: 4px !important;
        }
        .day-number {
            font-size: 0.85rem !important;
            margin-bottom: 3px !important;
        }
        .event-item {
            font-size: 0.68rem !important;
            padding: 3px 4px !important;
            min-height: 20px !important;
            margin-bottom: 2px !important;
            border-left-width: 2px !important;
        }
        .event-item::before {
            font-size: 0.62rem !important;
            margin-right: 1px !important;
        }
        /* Limiter à 2 événements en portrait */
        .calendar-day .event-item:nth-child(n+4) {
            display: none;
        }
    }
    
    .calendar-header {
        background: transparent;
        color: #8b7760;
        padding: 4px 6px 8px;
        text-align: center;
        font-weight: 700;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-shadow: none;
    }
    @media (min-width: 640px) {
        .calendar-header {
            padding: 6px 10px 10px;
            font-size: 0.7rem;
        }
    }
    .calendar-day {
        background: #fffdfa;
        min-height: 104px;
        padding: 7px;
        position: relative;
        cursor: pointer;
        transition: all 0.22s ease;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        overflow: hidden;
        border-radius: 0.95rem;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
    }
    @media (min-width: 640px) {
        .calendar-day {
            min-height: 116px;
            padding: 8px;
        }
    }
    .calendar-day:hover, .calendar-day:active {
        background: #fff9f2;
        transform: translateY(-1px);
    }
    .calendar-day.other-month {
        background: #f8f4ee;
        color: #b2a697;
    }
    .calendar-day.today {
        background: #fff8ee;
        box-shadow: 0 8px 18px rgba(194, 172, 141, 0.16);
    }
    .calendar-day.has-events {
        background: #fffdfa;
    }
    .day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.9rem;
        height: 1.9rem;
        padding: 0 0.45rem;
        margin-bottom: 0.45rem;
        border-radius: 9999px;
        background: #f3ece3;
        border: none;
        box-shadow: none;
        font-weight: 800;
        font-size: 0.88rem;
        line-height: 1;
        color: #0f172a;
    }
    @media (min-width: 640px) {
        .day-number {
            min-width: 2rem;
            height: 2rem;
            font-size: 0.94rem;
            margin-bottom: 0.55rem;
        }
    }
    .calendar-day.today .day-number {
        background: #111827;
        color: #fff;
        box-shadow: none;
    }
    .calendar-day.other-month .day-number {
        background: #f2ece4;
        color: #b2a697;
    }
    .event-item {
        font-size: 0.72rem;
        padding: 4px 6px;
        border-radius: 9px;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        transition: transform 0.14s ease, box-shadow 0.18s ease, filter 0.18s ease;
        min-height: 26px;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        line-height: 1.1;
        font-weight: 600;
        border: none;
        box-shadow: none;
    }
    @media (min-width: 640px) {
        .event-item {
            font-size: 0.74rem;
            padding: 5px 7px;
            min-height: 28px;
        }
    }
    .event-item:hover, .event-item:active {
        transform: translateY(-1px) scale(1.01);
        box-shadow: none;
    }
    .event-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
        flex: 1;
    }
    .event-time {
        flex-shrink: 0;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        border-radius: 9999px;
        padding: 0.12rem 0.34rem;
        background: rgba(255, 255, 255, 0.82);
        color: inherit;
        box-shadow: none;
    }
    .event-service { background: #eaf2ff; color: #30588d; }
    .event-equipment { background: #eaf8ef; color: #1f6a52; }
    .event-food { background: #fff2e5; color: #b55c1d; }
    .event-manual { background: #f8ebf3; color: #9c416e; }

    /* Keep food events orange even when accepted status adds green background */
    .event-food.event-status-accepted { background: #ffedd5 !important; color: #9a3412 !important; }
    
    /* Statuts des événements */
    .event-status-pending { position: relative; }
    .event-status-pending::before { content: '⏳'; margin-right: 2px; font-size: 0.66rem; }
    .event-status-confirmed { }
    .event-status-confirmed::before { content: '✓'; margin-right: 2px; font-size: 0.66rem; color: #2563eb; }
    .event-status-accepted { }
    .event-status-accepted::before { content: '✅'; margin-right: 2px; font-size: 0.66rem; }
    .event-status-completed { opacity: 0.78; filter: saturate(0.72); }
    .event-status-completed::before { content: '✔'; margin-right: 2px; font-size: 0.66rem; color: #6b7280; }
    .event-status-cancelled { opacity: 0.52; text-decoration: line-through; filter: grayscale(0.18); }
    
    /* Animation conflit - accent visuel plus doux */
    @keyframes conflict-blink {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.12); }
        50% { box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.12); }
    }
    .event-conflict {
        animation: conflict-blink 1s ease-in-out infinite;
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%) !important;
        color: #b91c1c !important;
        font-weight: 600;
    }
    .event-conflict::after {
        content: '⚠️';
        margin-left: 2px;
        font-size: 0.5rem;
    }
    @media (max-width: 480px) {
        .event-conflict::after {
            content: '!';
            font-size: 0.45rem;
            color: #dc2626;
            font-weight: bold;
        }
    }
    
    .week-grid {
        display: grid;
        grid-template-columns: 46px repeat(7, 1fr);
        gap: 0.32rem;
        background: transparent;
        border-radius: 0;
        overflow: visible;
        border: none;
        box-shadow: none;
        min-width: 920px;
    }
    @media (min-width: 768px) {
        .week-grid {
            max-width: 1120px;
            margin-inline: auto;
        }
    }
    @media (max-width: 767px) {
        .week-grid {
            grid-template-columns: 40px repeat(7, minmax(74px, 1fr));
            min-width: 558px;
        }
    }
    @media (min-width: 640px) {
        .week-grid {
            grid-template-columns: 60px repeat(7, 1fr);
        }
    }
    /* Vue semaine en portrait mobile */
    @media (max-width: 480px) and (orientation: portrait) {
        .week-grid {
            grid-template-columns: 36px repeat(7, minmax(68px, 1fr));
            min-width: 512px;
        }
        .week-header {
            padding: 6px 2px !important;
            font-size: 0.60rem !important;
        }
        .week-header .text-lg {
            font-size: 0.82rem !important;
        }
        .week-time {
            font-size: 0.60rem !important;
            padding: 3px !important;
            min-height: 44px !important;
        }
        .week-cell {
            min-height: 44px !important;
            padding: 2px !important;
        }
    }
    .week-header {
        background: transparent;
        color: #8b7760;
        padding: 4px 6px 8px;
        text-align: center;
        font-weight: 700;
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-shadow: none;
        border-radius: 0;
    }
    @media (min-width: 640px) {
        .week-header {
            padding: 6px 6px 10px;
            font-size: 0.7rem;
        }
    }
    .week-time {
        position: sticky;
        left: 0;
        z-index: 3;
        background: #f7f1e8;
        padding: 4px;
        font-size: 0.72rem;
        color: #74614d;
        text-align: center;
        min-height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        border-right: none;
        border-radius: 0.85rem;
        box-shadow: none;
    }
    @media (min-width: 640px) {
        .week-time {
            font-size: 0.74rem;
            min-height: 56px;
        }
    }
    .week-cell {
        background: #fffdfa;
        min-height: 52px;
        padding: 4px;
        position: relative;
        cursor: pointer;
        touch-action: manipulation;
        transition: background 0.18s ease, transform 0.18s ease;
        border-radius: 0.85rem;
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.04);
    }
    @media (min-width: 640px) {
        .week-cell {
            min-height: 56px;
        }
    }
    .week-cell:hover, .week-cell:active {
        background: #fff9f2;
        transform: translateY(-1px);
    }
    .week-cell.has-events {
        background: #fffdfa;
    }
    
    .filter-btn.active {
        background: #111827 !important;
        color: #ffffff !important;
        box-shadow: none;
    }
    
    .view-btn.active {
        background: #111827;
        color: white;
        box-shadow: none;
    }
    .agenda-toolbar .bg-gray-100.rounded-lg.p-1 {
        background: #f4ede4 !important;
        border-radius: 9999px !important;
        padding: 0.2rem !important;
    }
    
    /* Modal - Mobile Optimized */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.38);
        backdrop-filter: blur(6px);
        z-index: 80;
        display: flex;
        align-items: center;
        justify-content: center;
        padding:
            max(0.75rem, env(safe-area-inset-top, 0px))
            0.5rem
            max(0.75rem, env(safe-area-inset-bottom, 0px));
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    @media (min-width: 640px) {
        .modal-backdrop {
            align-items: center;
            padding: 1rem;
        }
    }
    .modal-content {
        background: #fffdf9;
        border-radius: 1.2rem;
        max-width: 100%;
        width: 100%;
        max-height: min(88svh, calc(100dvh - 0.5rem));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.12);
        min-height: 0;
    }
    @media (min-width: 640px) {
        .modal-content {
            border-radius: 1.35rem;
            max-width: 560px;
            max-height: min(86vh, 760px);
        }
    }
    
    /* Mobile swipe indicator */
    .modal-swipe-indicator {
        display: none;
    }
    .agenda-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 0.95rem;
        border-bottom: 1px solid #f0e6da;
        flex-shrink: 0;
    }
    .agenda-modal-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.98rem;
        font-weight: 700;
        color: #1f2937;
    }
    .agenda-modal-icon {
        color: #8a7356;
    }
    .agenda-modal-body {
        padding: 0.9rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        flex: 1 1 auto;
        min-height: 0;
    }
    .agenda-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 9999px;
        background: #f3ece3;
        color: #6d5d49;
        border: none;
    }
    .agenda-modal-close:hover,
    .agenda-modal-close:active {
        background: #eadfd0;
    }
    .agenda-form-label {
        display: block;
        font-size: 0.92rem;
        font-weight: 600;
        color: #6f6253;
        margin-bottom: 0.35rem;
    }
    .agenda-form-input {
        width: 100%;
        min-height: 42px;
        padding: 0.64rem 0.85rem;
        border: none;
        border-radius: 0.95rem;
        background: #fcf8f2;
        box-shadow: inset 0 0 0 1px #eadfd0;
        color: #1f2937;
    }
    .agenda-form-input:focus {
        outline: none;
        box-shadow: inset 0 0 0 2px #c9ae87;
        background: #fffdf9;
    }
    .agenda-mode-btn {
        flex: 1;
        min-height: 42px;
        padding: 0.64rem 0.8rem;
        border: none;
        border-radius: 9999px;
        font-size: 0.84rem;
        font-weight: 600;
        transition: background 0.18s ease, color 0.18s ease;
    }
    .agenda-mode-btn.is-active {
        background: #111827;
        color: #ffffff;
    }
    .agenda-mode-btn.is-inactive {
        background: #f3ece3;
        color: #6f6253;
    }
    .agenda-modal-secondary,
    .agenda-modal-primary,
    .agenda-modal-danger {
        min-height: 42px;
        padding: 0.68rem 0.9rem;
        border: none;
        border-radius: 9999px;
        font-weight: 600;
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }
    .agenda-modal-secondary {
        background: #f3ece3;
        color: #6d5d49;
    }
    .agenda-modal-primary {
        background: #111827;
        color: #ffffff;
    }
    .agenda-modal-danger {
        background: #b91c1c;
        color: #ffffff;
    }
    .agenda-modal-secondary:hover,
    .agenda-modal-primary:hover,
    .agenda-modal-danger:hover {
        transform: translateY(-1px);
    }
    .agenda-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.8rem;
        border-radius: 9999px;
        background: #ecfdf3;
        color: #17724f;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .agenda-note-box {
        background: #f8f3ec;
        border-radius: 1rem;
        padding: 0.9rem 1rem;
        color: #6f6253;
    }
    .agenda-day-list-item {
        display: block;
        padding: 0.8rem 0.9rem;
        border-radius: 1rem;
        background: #fcf8f2;
        color: inherit;
        transition: background 0.18s ease, transform 0.18s ease;
    }
    .agenda-day-list-item:hover,
    .agenda-day-list-item:active {
        background: #f6eee3;
        transform: translateY(-1px);
    }
    .agenda-empty-state {
        background: #f8f3ec;
        border-radius: 1rem;
    }
    #addEventModal .modal-content {
        max-width: min(100%, 31rem);
    }
    #addEventModal .agenda-modal-header {
        padding: 0.72rem 0.8rem;
    }
    #addEventModal .agenda-modal-title {
        font-size: 0.94rem;
    }
    #addEventModal .agenda-modal-body {
        padding: 0.78rem;
    }
    #addEventModal .agenda-modal-body.space-y-4 > * + * {
        margin-top: 0.72rem;
    }
    #addEventModal .agenda-form-label {
        font-size: 0.82rem;
        margin-bottom: 0.22rem;
    }
    #addEventModal .agenda-form-input {
        min-height: 38px;
        padding: 0.58rem 0.74rem;
        border-radius: 0.82rem;
        font-size: 0.9rem;
    }
    #addEventModal textarea.agenda-form-input {
        min-height: 74px;
    }
    #addEventModal .agenda-mode-btn,
    #addEventModal .agenda-modal-secondary,
    #addEventModal .agenda-modal-primary {
        min-height: 38px;
        padding: 0.62rem 0.8rem;
        font-size: 0.8rem;
    }
    #addEventModal .agenda-modal-close {
        width: 38px;
        height: 38px;
    }
    #addEventModal .agenda-inline-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        gap: 0.65rem;
    }
    #addEventModal .agenda-inline-grid--period {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    #addEventModal .agenda-color-grid {
        gap: 0.45rem;
    }
    #addEventModal .agenda-color-swatch {
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 0.78rem;
    }
    @media (max-width: 380px) {
        #addEventModal .agenda-inline-grid,
        #addEventModal .agenda-inline-grid--period {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .modal-content {
            width: min(100%, 32rem);
            margin: auto;
            border-radius: 1.15rem;
            max-height: min(82svh, calc(100dvh - 1rem));
        }
        .agenda-modal-header {
            padding: 0.75rem 0.8rem;
        }
        .agenda-modal-body {
            padding: 0.8rem;
        }
        .agenda-modal-title {
            font-size: 0.92rem;
        }
        .agenda-modal-close {
            width: 40px;
            height: 40px;
        }
        .agenda-form-input {
            min-height: 40px;
            padding: 0.6rem 0.8rem;
            font-size: 0.92rem;
        }
        .agenda-mode-btn,
        .agenda-modal-secondary,
        .agenda-modal-primary,
        .agenda-modal-danger {
            min-height: 40px;
            font-size: 0.8rem;
        }
        #duration_fields .grid,
        #period_fields .grid {
            gap: 0.65rem !important;
        }
        .agenda-modal-body .space-y-4 > * + * {
            margin-top: 0.8rem;
        }
        #addEventModal .agenda-inline-grid,
        #addEventModal .agenda-inline-grid--period {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        #dayDetailsList {
            max-height: 50svh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* Demand card mobile */
    .demand-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        gap: 0.75rem;
        transition: background 0.2s;
        touch-action: manipulation;
    }
    .demand-card:active {
        background: #f3f4f6;
    }

    /* ===== MOBILE UX REFINEMENTS ===== */
    .agenda-stats-row {
        scrollbar-width: none;
    }
    .agenda-stats-row::-webkit-scrollbar {
        display: none;
    }
    .agenda-chip-row {
        scrollbar-width: none;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }
    .agenda-chip-row::-webkit-scrollbar {
        display: none;
    }
    .agenda-chip-row > * {
        scroll-snap-align: start;
    }
    .day-more-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.6rem;
        height: 1.2rem;
        margin: 0.25rem auto 0;
        padding: 0 0.4rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1d4ed8;
        font-size: 0.66rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: none;
    }
    .week-header.weekend {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }
    .calendar-day[data-weekend="1"] {
        background: #f8fafc;
    }
    .calendar-day.today[data-weekend="1"] {
        background: #fef3c7;
    }

    @media (max-width: 640px) {
        .agenda-page > div {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .agenda-page-header,
        .sm\:hidden.mb-4,
        .agenda-stats-row,
        .agenda-demands-panel {
            margin-left: 0.7rem;
            margin-right: 0.7rem;
        }
        .mobile-tab {
            min-height: 48px;
        }
        .agenda-stats-row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            overflow: visible !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .agenda-stats-row .agenda-stat-card {
            min-height: 64px;
            justify-content: center;
        }
        .agenda-toolbar .filter-btn {
            min-height: 44px;
            padding: 0.6rem 0.9rem;
            border-radius: 0.75rem;
            font-size: 0.84rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .agenda-toolbar .view-btn {
            min-height: 44px;
            padding: 0.6rem 0.9rem;
            font-size: 0.84rem;
            border-radius: 9999px;
        }
        .agenda-calendar-wrap {
            padding: 0.15rem 0.35rem 0.35rem !important;
            margin-inline: 0;
        }
        .agenda-panel-shell,
        .agenda-period-nav,
        .agenda-legend-inner {
            max-width: none;
        }
        .agenda-toolbar,
        .agenda-period-nav,
        .agenda-legend {
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .agenda-period-nav {
            margin-left: 0;
            margin-right: 0;
        }
        .agenda-main-panel,
        .agenda-demands-panel {
            border-radius: 0;
        }
        .calendar-day {
            min-height: 78px;
        }
        .event-time {
            display: none;
        }
        .agenda-period-nav h2 {
            letter-spacing: 0.01em;
        }
        .agenda-legend-inner {
            padding-left: 0.7rem;
            padding-right: 0.7rem;
        }
    }
    @media (display-mode: standalone) and (max-width: 767px), (display-mode: fullscreen) and (max-width: 767px) {
        .agenda-page {
            background: linear-gradient(180deg, #f5f1eb 0%, #faf7f2 100%);
        }
        .agenda-page > div {
            padding-top: calc(0.75rem + env(safe-area-inset-top, 0px));
            padding-left: 0;
            padding-right: 0;
        }
        .agenda-page-header,
        .agenda-main-panel,
        .agenda-demands-panel,
        .agenda-stat-card,
        .mobile-tabs {
            box-shadow: none !important;
        }
        .agenda-calendar-wrap {
            padding-inline: 0.35rem;
            margin-inline: 0;
        }
        .agenda-period-nav {
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }
        .agenda-rotate-tip {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="agenda-page min-h-screen pb-24 mobile-safe-bottom">
    <div class="max-w-[1180px] mx-auto px-2.5 sm:px-4 py-3 sm:py-5">
        
        {{-- Header --}}
        <div class="agenda-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-5 px-4 py-4 sm:px-5 sm:py-5">
            <div class="flex items-center justify-between sm:block">
                <div>
                    <span class="agenda-page-kicker inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-blue-700 mb-2">
                        Vue planning
                    </span>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Mon Agenda
                        <span class="agenda-rotate-tip" title="Affichage plus confortable en paysage">
                            <span class="agenda-rotate-phone">📱</span>
                            <span>Paysage +</span>
                        </span>
                    </h1>
                    <p class="text-gray-600 text-xs sm:text-sm mt-0.5 sm:mt-1 hidden sm:block">Une vue plus claire de vos réservations, locations et créneaux manuels.</p>
                </div>
            </div>
            <button type="button" data-open-add-event class="hidden sm:flex px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-semibold rounded-2xl hover:shadow-lg transition items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter événement
            </button>
        </div>
        
        <!-- Mobile Tab Navigation -->
        <div class="sm:hidden mb-4">
            <div class="mobile-tabs">
                <button type="button" class="mobile-tab active" data-mobile-tab="calendar" id="tab-calendar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Calendrier
                </button>
                <button type="button" class="mobile-tab" data-mobile-tab="demands" id="tab-demands">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Demandes
                    @if(isset($stats['pending']) && $stats['pending'] > 0)
                        <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $stats['pending'] }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Stats rapides - toutes sur une ligne --}}
        <div class="agenda-stats-row flex gap-2 mb-4 overflow-x-auto pb-1 -mx-3 px-3 sm:mx-0 sm:px-0">
            <div class="agenda-stat-card bg-white rounded-lg px-3 py-2 flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                <span class="text-lg font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</span>
                <span class="text-xs text-gray-500">Total</span>
            </div>
            <div class="agenda-stat-card bg-amber-50 rounded-lg px-3 py-2 flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                <span class="text-lg font-bold text-amber-600">{{ $stats['pending'] ?? 0 }}</span>
                <span class="text-xs text-amber-600">En attente</span>
            </div>
            <div class="agenda-stat-card bg-blue-50 rounded-lg px-3 py-2 flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                <span class="text-lg font-bold text-blue-600">{{ $stats['confirmed'] ?? 0 }}</span>
                <span class="text-xs text-blue-600">Confirmées</span>
            </div>
            <div class="agenda-stat-card bg-green-50 rounded-lg px-3 py-2 flex items-center gap-2 whitespace-nowrap">
                <span class="text-lg font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</span>
                <span class="text-xs text-green-600">Complétées</span>
            </div>
        </div>

        {{-- Filtres et Vue --}}
        @php
            $currentView = request('view', 'month');
            $currentFilter = request('filter', 'all');
            $currentStatus = request('status', 'all');
            $currentDate = request('date') ? \Carbon\Carbon::parse(request('date')) : now();
            $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d');
            $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d');
            $prevWeek = $currentDate->copy()->subWeek()->format('Y-m-d');
            $nextWeek = $currentDate->copy()->addWeek()->format('Y-m-d');
            $today = now()->format('Y-m-d');
            
            // Appliquer filtre sur les événements
            $allEvents = collect($calendarEvents ?? []);
            if($currentFilter !== 'all') {
                $allEvents = $allEvents->filter(fn($e) => ($e['type'] ?? 'service') === $currentFilter);
            }
            if($currentStatus !== 'all') {
                $allEvents = $allEvents->filter(fn($e) => ($e['status'] ?? 'pending') === $currentStatus);
            }
            
            // Détecter les conflits horaires (même heure, durée 1h par défaut)
            $eventsWithConflicts = $allEvents->map(function($event) use ($allEvents) {
                $event['has_conflict'] = false;
                if(!isset($event['start'])) return $event;
                
                $start = \Carbon\Carbon::parse($event['start']);
                $duration = $event['duration'] ?? 60; // durée en minutes, défaut 1h
                $end = $start->copy()->addMinutes($duration);
                $status = $event['status'] ?? 'pending';
                
                // Chercher conflits avec événements acceptés/confirmés
                $conflicts = $allEvents->filter(function($other) use ($event, $start, $end) {
                    if(($other['id'] ?? null) === ($event['id'] ?? null)) return false;
                    if(!isset($other['start'])) return false;
                    
                    $otherStatus = $other['status'] ?? 'pending';
                    // Un conflit existe si l'autre est accepté/confirmé
                    if(!in_array($otherStatus, ['accepted', 'confirmed'])) return false;
                    
                    $otherStart = \Carbon\Carbon::parse($other['start']);
                    $otherDuration = $other['duration'] ?? 60;
                    $otherEnd = $otherStart->copy()->addMinutes($otherDuration);
                    
                    // Chevauchement de créneaux
                    return $start < $otherEnd && $end > $otherStart;
                });
                
                // Marquer comme conflit si en attente et chevauche un accepté
                if($status === 'pending' && $conflicts->count() > 0) {
                    $event['has_conflict'] = true;
                }
                
                return $event;
            });
            $allEvents = $eventsWithConflicts;
        @endphp
        
        <!-- Calendar Panel -->
        <div class="mobile-panel active" id="panel-calendar">
        <div class="agenda-main-panel">
            {{-- Toolbar --}}
            <div class="agenda-toolbar agenda-panel-shell p-3 sm:p-4">
                @php
                    $mobileFilterLabels = [
                        'all' => 'Tous types',
                        'service' => 'Services',
                        'equipment' => 'Équipement',
                        'food' => 'Food',
                        'manual' => 'Manuel',
                    ];
                    $mobileStatusLabels = [
                        'all' => 'Tous statuts',
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'accepted' => 'Accepté',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ];
                @endphp

                <div class="sm:hidden flex items-center justify-between gap-2 mb-2">
                    <div class="text-[11px] text-slate-500 leading-tight">
                        {{ $currentView === 'week' ? 'Vue semaine' : 'Vue mois' }} • {{ $mobileFilterLabels[$currentFilter] ?? 'Filtre' }} • {{ $mobileStatusLabels[$currentStatus] ?? 'Statut' }}
                    </div>
                    @if($currentFilter !== 'all' || $currentStatus !== 'all')
                        <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'all', 'status' => 'all']) }}"
                           class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                            Réinitialiser
                        </a>
                    @endif
                </div>

                {{-- Filtres Type - Scrollable sur mobile --}}
                <div class="agenda-chip-row flex gap-2 overflow-x-auto pb-2 -mx-3 px-3 sm:mx-0 sm:px-0">
                    <span class="text-xs text-gray-400 font-medium hidden sm:inline-flex items-center flex-shrink-0">Type:</span>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'all', 'status' => $currentStatus]) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentFilter === 'all' ? 'bg-gray-900 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Tous
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'service', 'status' => $currentStatus]) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentFilter === 'service' ? 'bg-blue-600 text-white active' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                        <span class="hidden sm:inline">🎯 </span>Services
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'equipment', 'status' => $currentStatus]) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentFilter === 'equipment' ? 'bg-green-600 text-white active' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                        <span class="hidden sm:inline">🔧 </span>Équip.
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'food', 'status' => $currentStatus]) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentFilter === 'food' ? 'bg-orange-600 text-white active' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' }}">
                        <span class="hidden sm:inline">🍽️ </span>Food
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => 'manual', 'status' => $currentStatus]) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentFilter === 'manual' ? 'bg-pink-600 text-white active' : 'bg-pink-50 text-pink-600 hover:bg-pink-100' }}">
                        <span class="hidden sm:inline">✏️ </span>Manuel
                    </a>
                </div>
                
                {{-- Filtres Statut --}}
                <div class="agenda-chip-row agenda-status-row flex gap-2 overflow-x-auto pb-2 -mx-3 px-3 sm:mx-0 sm:px-0 mt-2 pt-2">
                    <span class="text-xs text-gray-400 font-medium hidden sm:inline-flex items-center flex-shrink-0">Statut:</span>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => $currentFilter, 'status' => 'all']) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentStatus === 'all' ? 'bg-gray-900 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Tous
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => $currentFilter, 'status' => 'pending']) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentStatus === 'pending' ? 'bg-amber-500 text-white active' : 'bg-amber-50 text-amber-600 hover:bg-amber-100' }}">
                        ⏳ Attente
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => $currentFilter, 'status' => 'confirmed']) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentStatus === 'confirmed' ? 'bg-blue-600 text-white active' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                        ✓ Confirmé
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => $currentFilter, 'status' => 'accepted']) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentStatus === 'accepted' ? 'bg-green-600 text-white active' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                        ✅ Accepté
                    </a>
                    <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => $currentView, 'filter' => $currentFilter, 'status' => 'completed']) }}" 
                       class="filter-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold rounded-lg transition whitespace-nowrap flex-shrink-0 {{ $currentStatus === 'completed' ? 'bg-gray-600 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        ✔ Terminé
                    </a>
                </div>

                <p class="sm:hidden text-[11px] text-slate-500 mt-1">Glissez horizontalement pour afficher tous les filtres.</p>
                
                {{-- Vues - Compact sur mobile --}}
                <div class="flex items-center justify-between mt-2">
                    <div class="text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span> = Conflit</span>
                    </div>
                    <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                        <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => 'month', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" 
                           class="view-btn px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $currentView === 'month' ? 'active' : 'text-gray-600 hover:bg-gray-200' }}">
                            <span class="hidden sm:inline">📅 </span>Mois
                        </a>
                        <a href="{{ route('prestataire.agenda.index', ['date' => request('date'), 'view' => 'week', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" 
                           class="view-btn px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $currentView === 'week' ? 'active' : 'text-gray-600 hover:bg-gray-200' }}">
                            <span class="hidden sm:inline">📆 </span>Semaine
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Navigation --}}
            <div class="agenda-period-nav flex items-center justify-between p-3 sm:p-4">
                @if($currentView === 'week')
                    <a href="{{ route('prestataire.agenda.index', ['date' => $prevWeek, 'view' => 'week', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="p-2 hover:bg-white/20 active:bg-white/30 rounded-lg transition min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="text-center flex-1 px-2">
                        <h2 class="text-sm sm:text-lg font-bold">{{ $currentDate->copy()->startOfWeek()->format('d') }} - {{ $currentDate->copy()->endOfWeek()->format('d M') }}</h2>
                        <a href="{{ route('prestataire.agenda.index', ['date' => $today, 'view' => 'week', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="inline-flex mt-1 px-2.5 py-1 text-[11px] font-semibold rounded-full bg-white/20 hover:bg-white/30 text-white">
                            Aujourd'hui
                        </a>
                    </div>
                    <a href="{{ route('prestataire.agenda.index', ['date' => $nextWeek, 'view' => 'week', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="p-2 hover:bg-white/20 active:bg-white/30 rounded-lg transition min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('prestataire.agenda.index', ['date' => $prevMonth, 'view' => 'month', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="p-2 hover:bg-white/20 active:bg-white/30 rounded-lg transition min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="text-center flex-1 px-2">
                        <h2 class="text-base sm:text-xl font-bold capitalize">{{ $currentDate->translatedFormat('F Y') }}</h2>
                        <a href="{{ route('prestataire.agenda.index', ['date' => $today, 'view' => 'month', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="inline-flex mt-1 px-2.5 py-1 text-[11px] font-semibold rounded-full bg-white/20 hover:bg-white/30 text-white">
                            Aujourd'hui
                        </a>
                    </div>
                    <a href="{{ route('prestataire.agenda.index', ['date' => $nextMonth, 'view' => 'month', 'filter' => $currentFilter, 'status' => $currentStatus]) }}" class="p-2 hover:bg-white/20 active:bg-white/30 rounded-lg transition min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Calendrier --}}
            <div class="agenda-calendar-wrap agenda-panel-shell p-2 sm:p-4 overflow-x-auto">
                @if($currentView === 'week')
                    {{-- Vue Semaine --}}
                    @php
                        $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $endOfWeek = $currentDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        $hours = range(0, 23);

                        // Auto-scroll helper: find earliest event hour in this week
                        $weekEventHours = $allEvents
                            ->filter(function($e) use ($startOfWeek, $endOfWeek) {
                                if (!isset($e['start'])) return false;
                                try {
                                    $s = \Carbon\Carbon::parse($e['start']);
                                    return $s->betweenIncluded($startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay());
                                } catch (\Exception $ex) {
                                    return false;
                                }
                            })
                            ->map(function($e) {
                                try {
                                    return (int) \Carbon\Carbon::parse($e['start'])->hour;
                                } catch (\Exception $ex) {
                                    return null;
                                }
                            })
                            ->filter(fn($h) => $h !== null)
                            ->values();

                        $scrollToHour = $weekEventHours->count() ? (int) $weekEventHours->min() : null;
                    @endphp
                    <div class="week-grid">
                        {{-- Header --}}
                        <div class="week-header"></div>
                        @for($d = 0; $d < 7; $d++)
                            @php $day = $startOfWeek->copy()->addDays($d); @endphp
                            <div class="week-header {{ $day->isToday() ? 'bg-amber-500' : ($day->isWeekend() ? 'weekend' : '') }}">
                                <div>{{ $day->translatedFormat('D') }}</div>
                                <div class="text-lg font-bold">{{ $day->day }}</div>
                            </div>
                        @endfor
                        
                        {{-- Heures --}}
                        @foreach($hours as $hour)
                            @php $hourStr = sprintf('%02d:00', $hour); @endphp
                            <div class="week-time" id="week-hour-{{ $hour }}">{{ $hourStr }}</div>
                            @for($d = 0; $d < 7; $d++)
                                @php 
                                    $day = $startOfWeek->copy()->addDays($d);
                                    $dayStr = $day->format('Y-m-d');
                                    $cellStart = \Carbon\Carbon::parse($dayStr . ' ' . $hourStr);
                                    $cellEnd = $cellStart->copy()->addHour();

                                    // Show events that overlap this hour cell (so a 3h booking appears on 3 rows)
                                    $dayEvents = $allEvents->filter(function($e) use ($dayStr, $cellStart, $cellEnd) {
                                        if(!isset($e['start'])) return false;
                                        $start = \Carbon\Carbon::parse($e['start']);
                                        if ($start->format('Y-m-d') !== $dayStr) return false;

                                        $duration = $e['duration'] ?? 60; // minutes
                                        if (isset($e['end']) && $e['end']) {
                                            try {
                                                $endParsed = \Carbon\Carbon::parse($e['end']);
                                                $duration = max(1, $start->diffInMinutes($endParsed));
                                            } catch (\Exception $ex) {
                                                // ignore parse errors, fallback to duration
                                            }
                                        }
                                        $end = $start->copy()->addMinutes($duration);

                                        // overlap with cell [cellStart, cellEnd)
                                        return $start->lt($cellEnd) && $end->gt($cellStart);
                                    });
                                @endphp
                                <div class="week-cell agenda-cell {{ $dayEvents->count() ? 'has-events' : '' }}" data-date="{{ $dayStr }}" data-time="{{ $hourStr }}">
                                    @foreach($dayEvents->take(2) as $event)
                                        @php
                                            $eventStatus = $event['status'] ?? 'pending';
                                            $hasConflict = $event['has_conflict'] ?? false;
                                            $statusClass = 'event-status-' . $eventStatus;
                                            $conflictClass = $hasConflict ? 'event-conflict' : '';

                                            $isContinuation = false;
                                            try {
                                                $eventStart = isset($event['start']) ? \Carbon\Carbon::parse($event['start']) : null;
                                            $isContinuation = $eventStart && $eventStart->hour !== $hour;
                                            } catch (\Exception $ex) {
                                                $isContinuation = false;
                                            }
                                        @endphp
                                        @if(($event['type'] ?? 'service') === 'manual')
                                            <div class="event-item event-manual {{ $statusClass }} {{ $conflictClass }} cursor-pointer" 
                                                 data-event="{{ e(json_encode($event)) }}"
                                                 data-event-details="1"
                                                 title="{{ $event['title'] ?? '' }}{{ $hasConflict ? ' ⚠️ CONFLIT' : '' }}">
                                                <span class="event-label">{{ $isContinuation ? '↳ ' : '' }}{{ Str::limit($event['title'] ?? '', 15) }}</span>
                                            </div>
                                        @else
                                            <a href="{{ $event['url'] ?? '#' }}" 
                                               class="event-item event-{{ $event['type'] ?? 'service' }} {{ $statusClass }} {{ $conflictClass }}" 
                                               title="{{ $event['title'] ?? '' }}{{ $hasConflict ? ' ⚠️ CONFLIT - Chevauche un RDV accepté!' : '' }}">
                                                <span class="event-label">{{ $isContinuation ? '↳ ' : '' }}{{ Str::limit($event['title'] ?? '', 15) }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endfor
                        @endforeach
                    </div>

                    @if($scrollToHour !== null)
                        <div id="agendaWeekScrollTarget" data-scroll-hour="{{ $scrollToHour }}" hidden></div>
                    @endif
                @else
                    {{-- Vue Mois --}}
                    <div class="calendar-grid">
                        {{-- Jours de la semaine - Version courte pour portrait mobile --}}
                        @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $index => $dayShort)
                            @php
                                $dayFull = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'][$index];
                            @endphp
                            <div class="calendar-header">
                                <span class="hidden sm:inline">{{ $dayFull }}</span>
                                <span class="sm:hidden">{{ $dayShort }}</span>
                            </div>
                        @endforeach
                        
                        {{-- Jours du mois --}}
                        @php
                            $startOfMonth = $currentDate->copy()->startOfMonth();
                            $endOfMonth = $currentDate->copy()->endOfMonth();
                            $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        @endphp
                        
                        @for($date = $startOfCalendar->copy(); $date <= $endOfCalendar; $date->addDay())
                            @php
                                $isToday = $date->isToday();
                                $isCurrentMonth = $date->month === $currentDate->month;
                                $isWeekend = $date->isWeekend();
                                $dateStr = $date->format('Y-m-d');
                                
                                $dayEvents = $allEvents->filter(function($event) use ($dateStr) {
                                    $start = isset($event['start']) ? \Carbon\Carbon::parse($event['start'])->format('Y-m-d') : null;
                                    return $start === $dateStr;
                                })->take(2); // Limité à 2 pour mobile portrait
                                
                                $totalDayEvents = $allEvents->filter(function($event) use ($dateStr) {
                                    $start = isset($event['start']) ? \Carbon\Carbon::parse($event['start'])->format('Y-m-d') : null;
                                    return $start === $dateStr;
                                })->count();
                            @endphp
                            
                               <div class="calendar-day agenda-cell {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $totalDayEvents > 0 ? 'has-events' : '' }}"
                                   data-date="{{ $dateStr }}"
                                   data-weekend="{{ $isWeekend ? '1' : '0' }}">
                                <div class="day-number">{{ $date->day }}</div>
                                @foreach($dayEvents as $event)
                                    @php
                                        $eventStatus = $event['status'] ?? 'pending';
                                        $hasConflict = $event['has_conflict'] ?? false;
                                        $statusClass = 'event-status-' . $eventStatus;
                                        $conflictClass = $hasConflict ? 'event-conflict' : '';
                                        // Titre court pour mobile
                                        $shortTitle = Str::limit($event['title'] ?? '', 12);
                                        $eventTime = isset($event['start']) ? \Carbon\Carbon::parse($event['start'])->format('H:i') : null;
                                    @endphp
                                    @if(($event['type'] ?? 'service') === 'manual')
                                        <div class="event-item event-manual {{ $statusClass }} {{ $conflictClass }} cursor-pointer" 
                                             title="{{ $event['title'] ?? '' }}{{ $hasConflict ? ' ⚠️ CONFLIT' : '' }}"
                                             data-event="{{ e(json_encode($event)) }}"
                                             data-event-details="1">
                                            @if($eventTime)
                                                <span class="event-time">{{ $eventTime }}</span>
                                            @endif
                                            <span class="event-label">{{ $shortTitle }}</span>
                                        </div>
                                    @else
                                        <a href="{{ $event['url'] ?? '#' }}" 
                                           class="event-item event-{{ $event['type'] ?? 'service' }} {{ $statusClass }} {{ $conflictClass }}" 
                                           title="{{ $event['title'] ?? '' }}{{ $hasConflict ? ' ⚠️ CONFLIT - Chevauche un RDV accepté!' : '' }}">
                                            @if($eventTime)
                                                <span class="event-time">{{ $eventTime }}</span>
                                            @endif
                                            <span class="event-label">{{ $shortTitle }}</span>
                                        </a>
                                    @endif
                                @endforeach
                                @if($totalDayEvents > 2)
                                    <div class="day-more-badge">+{{ $totalDayEvents - 2 }}</div>
                                @endif
                            </div>
                        @endfor
                    </div>
                @endif
            </div>
            
            {{-- Légende - Compacte sur mobile --}}
            <div class="agenda-legend p-2 sm:p-4 text-[10px] sm:text-xs">
                <div class="agenda-legend-inner flex flex-wrap items-center justify-center gap-2 sm:gap-4">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-blue-500"></span> Serv.</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-green-500"></span> Éq.</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-orange-500"></span> Food</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-pink-500"></span> Man.</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-red-500 animate-pulse"></span> Conflit</span>
                </div>
            </div>
        </div>
        </div> <!-- Close Calendar Panel -->

        {{-- Liste des demandes récentes --}}
        <div class="mobile-panel sm:block" id="panel-demands">
        <div class="agenda-demands-panel overflow-hidden">
            <div class="agenda-demands-header p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Demandes récentes
                </h3>
                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-1 rounded-full">{{ count($recentDemands ?? []) }}</span>
            </div>
            <div class="space-y-2 p-2 max-h-96 sm:max-h-[500px] overflow-y-auto">
                @forelse($recentDemands ?? [] as $demand)
                    @php
                        $dType = $demand['type'] ?? 'service';
                        $dStatus = $demand['status'] ?? 'pending';
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'confirmed' => 'bg-blue-100 text-blue-700',
                            'accepted' => 'bg-green-100 text-green-700',
                            'completed' => 'bg-gray-100 text-gray-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'pending' => 'En attente',
                            'confirmed' => 'Confirmée',
                            'accepted' => 'Acceptée',
                            'completed' => 'Terminée',
                            'cancelled' => 'Annulée',
                        ];
                        $typeIcons = [
                            'service' => '🎯',
                            'equipment' => '🔧',
                            'food' => '🍽️',
                            'manual' => '✏️',
                        ];
                    @endphp
                    <a href="{{ $demand['url'] ?? '#' }}" class="demand-card hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between w-full gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-base sm:text-lg">{{ $typeIcons[$dType] ?? '📋' }}</span>
                                    <span class="font-semibold text-gray-900 truncate text-sm sm:text-base">{{ $demand['title'] ?? 'Demande' }}</span>
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">
                                    {{ $demand['client_name'] ?? 'Client' }} • {{ isset($demand['start_date']) ? \Carbon\Carbon::parse($demand['start_date'])->format('d/m H:i') : '--' }}
                                </div>
                            </div>
                            <span class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold rounded-full whitespace-nowrap flex-shrink-0 {{ $statusColors[$dStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$dStatus] ?? ucfirst($dStatus) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-4xl mb-2">📭</div>
                        <p>Aucune demande récente</p>
                    </div>
                @endforelse
            </div>
        </div>
        </div> <!-- Close Demands Panel -->

    </div>
</div>

<!-- Mobile FAB Button -->
<button type="button" data-open-add-event class="mobile-fab sm:hidden" aria-label="Ajouter un événement">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
</button>

{{-- Modal Ajouter Événement --}}
<div id="addEventModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" data-modal-content>
        <div class="modal-swipe-indicator"></div>
        <div class="agenda-modal-header">
            <h3 class="agenda-modal-title">
                <svg class="agenda-modal-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un événement
            </h3>
            <button type="button" data-close-modal="addEventModal" class="agenda-modal-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="addEventForm" action="{{ route('prestataire.agenda.events.store') }}" method="POST" class="agenda-modal-body agenda-modal-form space-y-4">
            @csrf
            <div>
                <label for="event_title" class="agenda-form-label">Titre *</label>
                <input type="text" id="event_title" name="title" required
                       class="agenda-form-input"
                       placeholder="Ex: Réunion client, Congé...">
            </div>
            
            {{-- Mode sélection: Durée ou Période --}}
            <div>
                <label class="agenda-form-label">Type de durée</label>
                <div class="flex gap-2 agenda-mode-row">
                    <button type="button" data-duration-mode="duration" id="btn_duration" class="agenda-mode-btn is-active">
                        ⏱️ Durée (heures)
                    </button>
                    <button type="button" data-duration-mode="period" id="btn_period" class="agenda-mode-btn is-inactive">
                        📅 Période (jours)
                    </button>
                </div>
            </div>
            
            <input type="hidden" id="duration_mode" name="duration_mode" value="duration">
            
            {{-- Mode Durée --}}
            <div id="duration_fields">
                <div class="agenda-inline-grid mb-4">
                    <div>
                        <label for="event_date" class="agenda-form-label">Date *</label>
                        <input type="date" id="event_date" name="date"
                               class="agenda-form-input">
                    </div>
                    <div>
                        <label for="event_time" class="agenda-form-label">Heure</label>
                        <input type="time" id="event_time" name="time" value="09:00"
                               class="agenda-form-input">
                    </div>
                </div>
                <div>
                    <label for="event_duration" class="agenda-form-label">Durée</label>
                    <select id="event_duration" name="duration"
                            class="agenda-form-input">
                        <option value="1">1 heure</option>
                        <option value="2" selected>2 heures</option>
                        <option value="3">3 heures</option>
                        <option value="4">4 heures</option>
                        <option value="8">Journée (8h)</option>
                    </select>
                </div>
            </div>
            
            {{-- Mode Période --}}
            <div id="period_fields" style="display: none;">
                <div class="agenda-inline-grid agenda-inline-grid--period">
                    <div>
                        <label for="event_start_date" class="agenda-form-label">Date début *</label>
                        <input type="date" id="event_start_date" name="start_date"
                               class="agenda-form-input">
                    </div>
                    <div>
                        <label for="event_end_date" class="agenda-form-label">Date fin *</label>
                        <input type="date" id="event_end_date" name="end_date"
                               class="agenda-form-input">
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">L'événement sera affiché sur toute la période sélectionnée</p>
            </div>
            
            <div>
                <label class="agenda-form-label">Couleur</label>
                <div class="agenda-color-grid flex gap-2 flex-wrap">
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#ec4899" class="hidden peer" checked>
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-pink-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-pink-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#8b5cf6" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-violet-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-violet-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#3b82f6" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-blue-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-blue-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#10b981" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-green-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-green-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#f59e0b" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-amber-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-amber-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#ef4444" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-red-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-red-500"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#6b7280" class="hidden peer">
                        <span class="agenda-color-swatch block w-8 h-8 rounded-lg bg-gray-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-gray-500"></span>
                    </label>
                </div>
            </div>
            <div>
                <label for="event_notes" class="agenda-form-label">Notes</label>
                <textarea id="event_notes" name="notes" rows="2"
                          class="agenda-form-input"
                          placeholder="Notes optionnelles..."></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" data-close-modal="addEventModal" class="agenda-modal-secondary flex-1">
                    Annuler
                </button>
                <button type="submit" class="agenda-modal-primary flex-1">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

    {{-- Modal Détails Événement Manuel --}}
    <div id="eventDetailsModal" class="modal-backdrop" style="display: none;">
        <div class="modal-content" data-modal-content>
            <div class="modal-swipe-indicator"></div>
            <div class="agenda-modal-header">
                <h3 class="agenda-modal-title">
                    <svg class="agenda-modal-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Événement Manuel
                </h3>
                <button type="button" data-close-modal="eventDetailsModal" class="agenda-modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="agenda-modal-body space-y-4">
                <div>
                    <label class="agenda-form-label">Titre</label>
                    <p id="eventDetailTitle" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="agenda-form-label">Date/Heure</label>
                        <p id="eventDetailDate" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="agenda-form-label">Statut</label>
                        <span class="agenda-status-pill">Confirmé</span>
                    </div>
                </div>
                <div id="eventDetailNotesContainer" style="display: none;">
                    <label class="agenda-form-label">Notes</label>
                    <p id="eventDetailNotes" class="agenda-note-box"></p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" data-close-modal="eventDetailsModal" class="agenda-modal-secondary flex-1">
                        Fermer
                    </button>
                    <form id="deleteEventForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="agenda-modal-danger w-full">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Détails Journée (tap/clic) --}}
    <div id="dayDetailsModal" class="modal-backdrop" style="display: none;">
        <div class="modal-content" data-modal-content>
            <div class="modal-swipe-indicator"></div>
            <div class="agenda-modal-header">
                <h3 class="agenda-modal-title">
                    <svg class="agenda-modal-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="dayDetailTitle">Journée</span>
                </h3>
                <button type="button" data-close-modal="dayDetailsModal" class="agenda-modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="agenda-modal-body">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-sm text-gray-600">Tap: voir la journée • Appui long: ajout manuel</p>
                    <button type="button" id="dayDetailAddBtn" class="agenda-modal-secondary">
                        + Ajouter
                    </button>
                </div>
                <div id="dayDetailsList" class="space-y-2"></div>
                <div id="dayDetailsEmpty" class="agenda-empty-state p-6 text-center text-gray-500" style="display:none;">
                    <div class="text-3xl mb-2">📭</div>
                    <p>Aucun événement ce jour-là</p>
                </div>
            </div>
        </div>
    </div>

<script type="application/json" id="agendaEventsJson">@json(($allEvents ?? collect())->values())</script>

<script>
// Events visible in the current view (after filter/status)
let AGENDA_EVENTS = [];
try {
    const jsonEl = document.getElementById('agendaEventsJson');
    AGENDA_EVENTS = jsonEl ? JSON.parse(jsonEl.textContent || '[]') : [];
} catch (e) {
    AGENDA_EVENTS = [];
}

function openDayDetailsModal(dateStr, preferredTime = null) {
    try {
        const dateObj = new Date(dateStr + 'T00:00:00');
        const title = dateObj.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('dayDetailTitle').textContent = title;
    } catch (e) {
        document.getElementById('dayDetailTitle').textContent = dateStr;
    }

    const list = document.getElementById('dayDetailsList');
    const empty = document.getElementById('dayDetailsEmpty');
    list.innerHTML = '';
    empty.style.display = 'none';

    const dayEvents = (AGENDA_EVENTS || [])
        .filter(e => {
            if (!e || !e.start) return false;
            try {
                const d = new Date(e.start);
                const dStr = d.toISOString().slice(0, 10);
                return dStr === dateStr;
            } catch (ex) {
                return false;
            }
        })
        .sort((a, b) => {
            const sa = a.start ? new Date(a.start).getTime() : 0;
            const sb = b.start ? new Date(b.start).getTime() : 0;
            return sa - sb;
        });

    if (!dayEvents.length) {
        empty.style.display = 'block';
    } else {
        dayEvents.forEach(ev => {
            const start = ev.start ? new Date(ev.start) : null;
            const time = start ? start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '--:--';
            const typeIcon = ({ service: '🎯', equipment: '🔧', food: '🍽️', manual: '✏️' })[ev.type || 'service'] || '📋';
            const status = ev.status || 'pending';
            const statusLabel = ({ pending: 'En attente', confirmed: 'Confirmé', accepted: 'Accepté', completed: 'Terminé', cancelled: 'Annulé' })[status] || status;
            const url = ev.url || '#';

            const el = document.createElement('a');
            el.href = url;
            el.className = 'agenda-day-list-item';
            el.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">${typeIcon} ${ev.title || 'Événement'}</div>
                        <div class="text-xs text-gray-500 mt-0.5">${time}</div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#f3ece3] text-[#6d5d49] whitespace-nowrap">${statusLabel}</span>
                </div>
            `;
            list.appendChild(el);
        });
    }

    // “Ajouter” button uses the same manual modal
    const addBtn = document.getElementById('dayDetailAddBtn');
    addBtn.onclick = function() {
        closeDayDetailsModal();
        openAddEventModal(dateStr, preferredTime);
    };

    document.getElementById('dayDetailsModal').style.display = 'flex';
}

function closeDayDetailsModal() {
    document.getElementById('dayDetailsModal').style.display = 'none';
}

document.getElementById('dayDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeDayDetailsModal();
});

if (!window.__agendaTapGuard) {
    window.__agendaTapGuard = { lastScrollTs: 0, installed: false };
}
if (!window.__agendaTapGuard.installed) {
    window.addEventListener('scroll', function() {
        window.__agendaTapGuard.lastScrollTs = Date.now();
    }, { passive: true });
    window.__agendaTapGuard.installed = true;
}

function attachTapAndLongPress(selector) {
    const longPressMs = 450;
    const moveThresholdPx = 4;
    const scrollThresholdPx = 1;
    const recentScrollMs = 160;
    const minTapMs = 70;
    const maxTapMs = 320;
    document.querySelectorAll(selector).forEach(el => {
        let timer = null;
        let didLongPress = false;
        let didMove = false;
        let lastTouchTs = 0;
        let suppressClickUntil = 0;
        let startX = 0;
        let startY = 0;
        let startScrollY = 0;
        let startScrollX = 0;
        let startTs = 0;
        let touchActive = false;

        const getDate = () => el.getAttribute('data-date');
        const getTime = () => el.getAttribute('data-time');
        const hadRecentScroll = () => (Date.now() - (window.__agendaTapGuard?.lastScrollTs || 0)) < recentScrollMs;

        const shouldIgnore = (e) => {
            return !!(e.target && e.target.closest && e.target.closest('.event-item, a.event-item'));
        };

        const getPoint = (e) => {
            const t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]);
            if (t) return { x: t.clientX, y: t.clientY };
            if (typeof e.clientX === 'number' && typeof e.clientY === 'number') {
                return { x: e.clientX, y: e.clientY };
            }
            return null;
        };

        const clear = () => {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
        };

        const markMoved = (e) => {
            if (!touchActive || didMove) return;
            const point = getPoint(e);
            if (!point) return;

            const movedX = Math.abs(point.x - startX);
            const movedY = Math.abs(point.y - startY);
            const movedScroll = Math.abs((window.scrollY || 0) - startScrollY);
            const currentWrapScrollX = el.closest('.agenda-calendar-wrap')?.scrollLeft || 0;
            const movedScrollX = Math.abs(currentWrapScrollX - startScrollX);
            if (movedX > moveThresholdPx || movedY > moveThresholdPx || movedScroll > scrollThresholdPx || movedScrollX > 2) {
                didMove = true;
                clear();
                suppressClickUntil = Date.now() + 700;
            }
        };

        const start = (e) => {
            if (shouldIgnore(e)) return;
            didLongPress = false;
            didMove = false;
            touchActive = e.type.startsWith('touch');
            startTs = Date.now();

            if (touchActive && hadRecentScroll()) {
                didMove = true;
                suppressClickUntil = Date.now() + 700;
                return;
            }

            if (touchActive) {
                const point = getPoint(e);
                startX = point ? point.x : 0;
                startY = point ? point.y : 0;
                startScrollY = window.scrollY || 0;
                startScrollX = el.closest('.agenda-calendar-wrap')?.scrollLeft || 0;
            }

            timer = setTimeout(() => {
                if (didMove) return;
                didLongPress = true;
                timer = null;
                const date = getDate();
                const time = getTime();
                if (date) openAddEventModal(date, time);
                suppressClickUntil = Date.now() + 700;
            }, longPressMs);
        };

        const end = (e) => {
            markMoved(e);
            clear();
            if (didLongPress) { didLongPress = false; touchActive = false; return; }
            if (didMove) { didMove = false; touchActive = false; return; }
            if (shouldIgnore(e)) { touchActive = false; return; }

            if (touchActive) {
                const pressDuration = Date.now() - startTs;
                if (pressDuration < minTapMs || pressDuration > maxTapMs || hadRecentScroll()) {
                    touchActive = false;
                    suppressClickUntil = Date.now() + 700;
                    return;
                }
            }

            const date = getDate();
            const time = getTime();
            if (date) {
                openDayDetailsModal(date, time);
                suppressClickUntil = Date.now() + 400;
            }
            didLongPress = false;
            touchActive = false;
        };

        // Touch (iOS) + Mouse fallback
        el.addEventListener('touchstart', (e) => { lastTouchTs = Date.now(); start(e); }, { passive: true });
        el.addEventListener('touchmove', (e) => markMoved(e), { passive: true });
        el.addEventListener('touchend', (e) => end(e));
        el.addEventListener('touchcancel', () => {
            clear();
            didLongPress = false;
            didMove = false;
            touchActive = false;
            suppressClickUntil = Date.now() + 700;
        });

        el.addEventListener('mousedown', (e) => start(e));
        el.addEventListener('mouseup', (e) => end(e));
        el.addEventListener('mouseleave', () => {
            clear();
            didLongPress = false;
            didMove = false;
            touchActive = false;
        });

        // Click fallback (some browsers don't reliably fire touchend handlers)
        el.addEventListener('click', (e) => {
            if (Date.now() < suppressClickUntil) return;
            // ignore synthetic click right after touch
            if (Date.now() - lastTouchTs < 600) return;
            if (hadRecentScroll()) return;
            if (shouldIgnore(e)) return;
            const date = getDate();
            const time = getTime();
            if (date) openDayDetailsModal(date, time);
        });
    });
}

// Attach behavior to both month and week cells (after DOM is ready)
document.addEventListener('DOMContentLoaded', function() {
    attachTapAndLongPress('.agenda-cell');
    bindAgendaInteractions();
    initializeMobileAgendaTab();
    autoScrollWeekView();
});

function autoScrollWeekView() {
    const target = document.getElementById('agendaWeekScrollTarget');
    if (!target) return;

    const hour = target.dataset.scrollHour;
    if (!hour) return;

    const anchor = document.getElementById('week-hour-' + hour);
    if (!anchor) return;

    setTimeout(() => {
        anchor.scrollIntoView({ block: 'start', behavior: 'smooth' });
    }, 150);
}

function bindAgendaInteractions() {
    document.querySelectorAll('[data-open-add-event]').forEach((button) => {
        button.addEventListener('click', () => openAddEventModal());
    });

    document.querySelectorAll('[data-mobile-tab]').forEach((button) => {
        button.addEventListener('click', () => switchMobileTab(button.dataset.mobileTab || 'calendar'));
    });

    document.querySelectorAll('.event-item').forEach((item) => {
        item.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.querySelectorAll('[data-event-details]').forEach((item) => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            try {
                showEventDetails(JSON.parse(item.dataset.event || '{}'));
            } catch (error) {
                console.error('Impossible de lire les details de l evenement.', error);
            }
        });
    });

    document.querySelectorAll('[data-modal-content]').forEach((content) => {
        content.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeAgendaModal(button.dataset.closeModal));
    });

    document.querySelectorAll('[data-duration-mode]').forEach((button) => {
        button.addEventListener('click', () => setDurationMode(button.dataset.durationMode));
    });

    const deleteEventForm = document.getElementById('deleteEventForm');
    if (deleteEventForm) {
        deleteEventForm.addEventListener('submit', (event) => {
            if (!window.confirm('Supprimer cet evenement ?')) {
                event.preventDefault();
            }
        });
    }
}

function closeAgendaModal(modalId) {
    if (!modalId) return;

    if (modalId === 'addEventModal') {
        closeAddEventModal();
        return;
    }

    if (modalId === 'eventDetailsModal') {
        closeEventDetailsModal();
        return;
    }

    if (modalId === 'dayDetailsModal') {
        closeDayDetailsModal();
        return;
    }

    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function initializeMobileAgendaTab() {
    if (window.matchMedia('(min-width: 768px)').matches) return;
    try {
        const saved = localStorage.getItem('agenda_mobile_tab');
        if (saved === 'demands' || saved === 'calendar') {
            switchMobileTab(saved, false);
        }
    } catch (e) {
        // ignore storage errors (private mode, etc.)
    }
}

function setDurationMode(mode) {
    document.getElementById('duration_mode').value = mode;
    if (mode === 'duration') {
        document.getElementById('duration_fields').style.display = 'block';
        document.getElementById('period_fields').style.display = 'none';
        document.getElementById('btn_duration').className = 'agenda-mode-btn is-active';
        document.getElementById('btn_period').className = 'agenda-mode-btn is-inactive';
        document.getElementById('event_date').required = true;
        document.getElementById('event_start_date').required = false;
        document.getElementById('event_end_date').required = false;
    } else {
        document.getElementById('duration_fields').style.display = 'none';
        document.getElementById('period_fields').style.display = 'block';
        document.getElementById('btn_duration').className = 'agenda-mode-btn is-inactive';
        document.getElementById('btn_period').className = 'agenda-mode-btn is-active';
        document.getElementById('event_date').required = false;
        document.getElementById('event_start_date').required = true;
        document.getElementById('event_end_date').required = true;
    }
}

function openAddEventModal(date = null, time = null) {
    document.getElementById('addEventModal').style.display = 'flex';
    setDurationMode('duration'); // Reset to duration mode
    const today = new Date().toISOString().split('T')[0];
    if (date) {
        document.getElementById('event_date').value = date;
        document.getElementById('event_start_date').value = date;
        document.getElementById('event_end_date').value = date;
    } else {
        document.getElementById('event_date').value = today;
        document.getElementById('event_start_date').value = today;
        document.getElementById('event_end_date').value = today;
    }
    if (time) {
        document.getElementById('event_time').value = time;
    }
}

function closeAddEventModal() {
    document.getElementById('addEventModal').style.display = 'none';
}

// Fermer modal en cliquant dehors
document.getElementById('addEventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddEventModal();
    }
});

// Modal détails événement
function showEventDetails(event) {
    document.getElementById('eventDetailTitle').textContent = event.title || 'Événement';
    
    // Formater la date
    if (event.start) {
        const date = new Date(event.start);
        document.getElementById('eventDetailDate').textContent = date.toLocaleString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } else {
        document.getElementById('eventDetailDate').textContent = '--';
    }
    
    // Notes
    if (event.notes) {
        document.getElementById('eventDetailNotes').textContent = event.notes;
        document.getElementById('eventDetailNotesContainer').style.display = 'block';
    } else {
        document.getElementById('eventDetailNotesContainer').style.display = 'none';
    }
    
    // Formulaire de suppression
    if (event.id) {
        document.getElementById('deleteEventForm').action = "{{ url('prestataire/agenda/events') }}/" + event.id;
    }
    
    document.getElementById('eventDetailsModal').style.display = 'flex';
}

function closeEventDetailsModal() {
    document.getElementById('eventDetailsModal').style.display = 'none';
}

document.getElementById('eventDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEventDetailsModal();
    }
});

// Mobile Tab Navigation
function switchMobileTab(tabName, persist = true) {
    // Update tabs
    document.querySelectorAll('.mobile-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Update panels
    document.querySelectorAll('.mobile-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.getElementById('panel-' + tabName).classList.add('active');

    if (persist && window.matchMedia('(max-width: 767px)').matches) {
        try {
            localStorage.setItem('agenda_mobile_tab', tabName);
        } catch (e) {
            // ignore storage errors
        }
    }
}
</script>
@endsection
