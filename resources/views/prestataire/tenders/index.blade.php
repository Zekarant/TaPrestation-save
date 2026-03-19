@extends('layouts.app')

@section('title', "Appels d'Offres - TapRestation")

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #1e293b;
            line-height: 1.6;
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
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: var(--site-nav-h, 70px);
            z-index: 100;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .header-title h1 {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .header-title p {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .header-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .header-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .header-btn.primary {
            background: white;
            color: #7c3aed;
        }
        
        .header-btn.primary:hover {
            background: #f8fafc;
            transform: translateY(-1px);
        }
        
        .header-btn.secondary {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .header-btn.secondary:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .badge {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            margin-left: 0.25rem;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }
        
        /* Warning Banner */
        .warning-banner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .warning-icon {
            width: 50px;
            height: 50px;
            background: #f59e0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .warning-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .warning-content h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 0.25rem;
        }
        
        .warning-content p {
            color: #a16207;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-icon.available {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
        }
        
        .stat-icon.responded {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .stat-icon.shortlisted {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-icon.accepted {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: block;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        /* Invitations Banner */
        .invitations-banner {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .invitations-icon {
            width: 50px;
            height: 50px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .invitations-icon i {
            font-size: 1.25rem;
            color: white;
        }
        
        .invitations-content {
            flex: 1;
            min-width: 200px;
        }
        
        .invitations-content h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        
        .invitations-content p {
            color: #3b82f6;
            font-size: 0.875rem;
        }
        
        .invitations-list {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .invitation-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #1d4ed8;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .invitation-chip:hover {
            background: #3b82f6;
            color: white;
        }
        
        .invitation-chip .score {
            background: #10b981;
            color: white;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        /* Filters */
        .filters-section {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        @media (min-width: 768px) {
            .filters-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .filter-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.35rem;
        }
        
        .filter-group label i {
            color: #7c3aed;
            margin-right: 0.25rem;
        }
        
        .filter-input,
        .filter-select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        
        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #7c3aed;
        }
        
        .filters-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        
        .results-count {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .results-count strong {
            color: #7c3aed;
        }
        
        .sort-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sort-group label {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .sort-select {
            padding: 0.4rem 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        /* Tenders Grid */
        .tenders-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 640px) {
            .tenders-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .tenders-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .tender-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            display: flex;
            flex-direction: column;
            padding-bottom: 40px;
        }
        
        .tender-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .match-score {
            width: 50px;
            height: 50px;
            position: relative;
            flex-shrink: 0;
        }
        
        .match-score svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        
        .match-score .bg {
            fill: none;
            stroke: #e2e8f0;
            stroke-width: 3;
        }
        
        .match-score .progress {
            fill: none;
            stroke: #10b981;
            stroke-width: 3;
            stroke-linecap: round;
            transition: stroke-dasharray 0.5s;
        }
        
        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 700;
            color: #10b981;
        }
        
        .card-badges {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            align-items: flex-end;
            flex-shrink: 0;
        }
        
        .urgency-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .urgency-badge.urgent {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .urgency-badge.high {
            background: #fef3c7;
            color: #d97706;
        }
        
        .slots-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 600;
            background: #fef3c7;
            color: #92400e;
            white-space: nowrap;
        }
        
        .card-body {
            padding: 0 1rem 1rem;
            flex: 1;
        }
        
        .tender-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .tender-title a {
            color: #1e293b;
            text-decoration: none;
        }
        
        .tender-title a:hover {
            color: #7c3aed;
        }
        
        .tender-description {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        
        .tender-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }
        
        .tender-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }
        
        .tender-meta i {
            color: #7c3aed;
        }
        
        .tender-meta .budget {
            color: #10b981;
            font-weight: 600;
        }
        
        .tender-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        
        .category-tag {
            background: #ede9fe;
            color: #7c3aed;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .category-more {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
        }
        
        .card-footer {
            padding: 0.75rem 1rem;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f9;
            gap: 0.5rem;
        }
        
        .footer-info {
            display: flex;
            gap: 0.75rem;
            font-size: 0.7rem;
            color: #94a3b8;
            flex-wrap: wrap;
        }
        
        .footer-info span {
            display: flex;
            align-items: center;
            gap: 0.2rem;
            white-space: nowrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .expiring-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #ef4444, #f87171);
            color: white;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .expiring-badge i {
            margin-right: 0.25rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .empty-icon i {
            font-size: 2rem;
            color: #94a3b8;
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        
        .empty-tips {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .empty-tips h4 {
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .empty-tips ul {
            list-style: none;
            padding: 0;
        }
        
        .empty-tips li {
            font-size: 0.85rem;
            color: #64748b;
            padding: 0.25rem 0;
            padding-left: 1.25rem;
            position: relative;
        }
        
        .empty-tips li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
        }
        
        /* Pagination */
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .pagination-wrapper nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .pagination-wrapper nav > div {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .pagination-wrapper nav > div:first-child {
            display: none;
        }
        
        .pagination-wrapper a,
        .pagination-wrapper span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0.5rem 0.75rem;
            background: white;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }
        
        .pagination-wrapper a:hover {
            background: #7c3aed;
            color: white;
            border-color: #7c3aed;
        }
        
        .pagination-wrapper span[aria-current="page"] span,
        .pagination-wrapper .active span {
            background: #7c3aed;
            color: white;
            border-color: #7c3aed;
        }
        
        .pagination-wrapper svg {
            width: 16px;
            height: 16px;
        }
        
        .pagination-wrapper p {
            display: none;
        }
        
        /* SVG Gradient Definition */
        .svg-defs {
            position: absolute;
            width: 0;
            height: 0;
        }
        
        /* ============================================
           VERY SMALL SCREENS (< 400px)
           Galaxy S9+, iPhone SE, etc.
           ============================================ */
        @media (max-width: 400px) {
            .header {
                padding: 0.625rem 0.5rem;
            }
            
            .header-icon {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
            
            .header-title h1 {
                font-size: 1rem;
            }
            
            .header-title p {
                font-size: 0.75rem;
            }
            
            .header-btn {
                padding: 0.4rem 0.625rem;
                font-size: 0.75rem;
                gap: 0.25rem;
            }
            
            .header-btn i {
                font-size: 0.75rem;
            }
            
            .header-btn span {
                display: none;
            }
            
            .container {
                padding: 0.5rem;
            }
            
            .stats-grid {
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .stat-card {
                padding: 0.625rem;
                gap: 0.5rem;
                border-radius: 8px;
            }
            
            .stat-icon {
                width: 32px;
                height: 32px;
                font-size: 0.875rem;
                border-radius: 8px;
            }
            
            .stat-content .stat-value {
                font-size: 1rem;
            }
            
            .stat-content .stat-label {
                font-size: 0.625rem;
            }
            
            .tabs-container {
                gap: 0.25rem;
                padding: 0.375rem;
                margin-bottom: 1rem;
            }
            
            .tab-btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.6875rem;
                gap: 0.25rem;
            }
            
            .tab-btn i {
                font-size: 0.625rem;
            }
            
            .filters-section {
                padding: 0.625rem;
                margin-bottom: 0.75rem;
                border-radius: 8px;
            }
            
            .filters-grid {
                gap: 0.5rem;
            }
            
            .filter-group label {
                font-size: 0.6875rem;
            }
            
            .filter-group input,
            .filter-group select {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .tender-card {
                border-radius: 10px;
                padding-bottom: 32px;
            }
            
            .card-header {
                padding: 0.625rem;
                gap: 0.375rem;
            }
            
            .match-score {
                width: 36px;
                height: 36px;
            }
            
            .match-score .score-text {
                font-size: 0.625rem;
            }
            
            .card-title {
                font-size: 0.875rem;
            }
            
            .card-category {
                font-size: 0.625rem;
                padding: 0.125rem 0.375rem;
            }
            
            .card-body {
                padding: 0 0.625rem 0.625rem;
            }
            
            .card-info {
                font-size: 0.6875rem;
                gap: 0.25rem;
                margin-bottom: 0.375rem;
            }
            
            .card-info i {
                width: 12px;
                font-size: 0.625rem;
            }
            
            .budget-range {
                padding: 0.375rem;
                border-radius: 6px;
                margin-top: 0.5rem;
            }
            
            .budget-label {
                font-size: 0.625rem;
            }
            
            .budget-value {
                font-size: 0.8125rem;
            }
            
            .card-footer {
                padding: 0.5rem 0.625rem;
            }
            
            .responses-count,
            .time-left {
                font-size: 0.625rem;
                gap: 0.25rem;
            }
            
            .view-btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.6875rem;
            }
            
            .empty-state-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
            
            .empty-state h3 {
                font-size: 1rem;
            }
            
            .empty-state p {
                font-size: 0.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <!-- SVG Gradients -->
    <svg class="svg-defs">
        <defs>
            <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#10b981"/>
                <stop offset="100%" stop-color="#3b82f6"/>
            </linearGradient>
        </defs>
    </svg>
    
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-search-dollar"></i>
                </div>
                <div class="header-title">
                    <h1>Appels d'Offres</h1>
                    <p>Trouvez des missions pour vous</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('prestataire.tenders.my-responses') }}" class="header-btn primary">
                    <i class="fas fa-paper-plane"></i>
                    <span>Mes propositions</span>
                </a>
                <a href="{{ route('prestataire.tenders.invitations') }}" class="header-btn secondary">
                    <i class="fas fa-bell"></i>
                    <span>Invitations</span>
                    @if(isset($invitations) && $invitations->count() > 0)
                        <span class="badge">{{ $invitations->count() }}</span>
                    @endif
                </a>
                @if(auth()->user()->client)
                <a href="{{ route('client.tenders.index') }}" class="header-btn secondary" style="background: #10b981; color: white;">
                    <i class="fas fa-folder-open"></i>
                    <span>Mes demandes</span>
                </a>
                @endif
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Warning if no categories -->
        @if(isset($noCategoriesWarning) && $noCategoriesWarning)
            <div class="warning-banner">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="warning-content">
                    <h3>Complétez votre profil</h3>
                    <p>Ajoutez vos spécialités pour voir les appels d'offres correspondants.</p>
                    <a href="{{ route('prestataire.profile.edit') }}" class="btn btn-warning">
                        <i class="fas fa-user-edit"></i> Modifier mon profil
                    </a>
                </div>
            </div>
        @endif
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon available">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['available'] ?? 0 }}</span>
                    <span class="stat-label">Disponibles</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon responded">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['responded'] ?? 0 }}</span>
                    <span class="stat-label">Propositions</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon shortlisted">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['shortlisted'] ?? 0 }}</span>
                    <span class="stat-label">Présélections</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon accepted">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['accepted'] ?? 0 }}</span>
                    <span class="stat-label">Obtenues</span>
                </div>
            </div>
        </div>
        
        <!-- Invitations Banner -->
        @if(isset($invitations) && $invitations->count() > 0)
            <div class="invitations-banner">
                <div class="invitations-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="invitations-content">
                    <h3>{{ $invitations->count() }} nouvelle(s) invitation(s)</h3>
                    <p>Des clients vous ont sélectionné</p>
                </div>
                <div class="invitations-list">
                    @foreach($invitations->take(3) as $invitation)
                        @if($invitation->tenderRequest)
                            <a href="{{ route('prestataire.tenders.show', $invitation->tenderRequest) }}" class="invitation-chip">
                                <span class="score">{{ $invitation->match_score ?? 0 }}%</span>
                                {{ Str::limit($invitation->tenderRequest->title, 25) }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="{{ route('prestataire.tenders.index') }}">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-map-marker-alt"></i> Ville</label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Rechercher..." class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-tag"></i> Catégorie</label>
                        <select name="category" class="filter-select">
                            <option value="">Toutes</option>
                            @if(isset($categories))
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-euro-sign"></i> Budget min</label>
                        <input type="number" name="budget_min" value="{{ request('budget_min') }}" placeholder="0 €" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-fire"></i> Urgence</label>
                        <select name="urgency" class="filter-select">
                            <option value="">Toutes</option>
                            <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('urgency') == 'high' ? 'selected' : '' }}>Prioritaire</option>
                            <option value="normal" {{ request('urgency') == 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                </div>
                
                <div class="filters-footer">
                    <span class="results-count">
                        <strong>{{ method_exists($tenders, 'total') ? $tenders->total() : $tenders->count() }}</strong> appel(s) d'offre
                    </span>
                    <div class="sort-group">
                        <label>Trier :</label>
                        <select name="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="match" {{ request('sort') == 'match' ? 'selected' : '' }}>Meilleur match</option>
                            <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récent</option>
                            <option value="deadline" {{ request('sort') == 'deadline' ? 'selected' : '' }}>Deadline</option>
                            <option value="budget" {{ request('sort') == 'budget' ? 'selected' : '' }}>Budget</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="margin-left: 0.5rem;">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Tenders Grid -->
        @if($tenders->count() > 0)
            <div class="tenders-grid">
                @foreach($tenders as $tender)
                    @php
                        $matchScore = $tender->match_score ?? 0;
                        $radius = 18;
                        $circumference = 2 * 3.14159 * $radius;
                        $dashArray = ($matchScore / 100) * $circumference;
                        $dashOffset = $circumference - $dashArray;
                    @endphp
                    <div class="tender-card">
                        <div class="card-header">
                            <div class="match-score">
                                <svg viewBox="0 0 44 44">
                                    <circle class="bg" cx="22" cy="22" r="{{ $radius }}"/>
                                    <circle class="progress" cx="22" cy="22" r="{{ $radius }}"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $dashOffset }}"/>
                                </svg>
                                <span class="score-text">{{ $matchScore }}%</span>
                            </div>
                            <div class="card-badges">
                                @if($tender->urgency === 'urgent')
                                    <span class="urgency-badge urgent">
                                        <i class="fas fa-exclamation-triangle"></i> Urgent
                                    </span>
                                @elseif($tender->urgency === 'high')
                                    <span class="urgency-badge high">
                                        <i class="fas fa-bolt"></i> Prioritaire
                                    </span>
                                @endif
                                
                                @if(isset($tender->responses_count) && $tender->max_responses && $tender->responses_count >= $tender->max_responses * 0.8)
                                    <span class="slots-badge">
                                        <i class="fas fa-users"></i> Peu de places
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h3 class="tender-title">
                                <a href="{{ route('prestataire.tenders.show', $tender) }}">
                                    {{ $tender->title }}
                                </a>
                            </h3>
                            
                            <p class="tender-description">
                                {{ Str::limit($tender->description, 100) }}
                            </p>
                            
                            <div class="tender-meta">
                                <span><i class="fas fa-map-marker-alt"></i> {{ $tender->city }}</span>
                                <span><i class="fas fa-calendar"></i> {{ $tender->start_date ? $tender->start_date->format('d/m') : 'Flexible' }}</span>
                                @if($tender->budget_visible && $tender->budget_max)
                                    <span class="budget"><i class="fas fa-euro-sign"></i> {{ number_format($tender->budget_max, 0, ',', ' ') }} €</span>
                                @endif
                            </div>
                            
                            @if($tender->categories && $tender->categories->count() > 0)
                                <div class="tender-categories">
                                    @foreach($tender->categories->take(2) as $category)
                                        <span class="category-tag">{{ $category->name }}</span>
                                    @endforeach
                                    @if($tender->categories->count() > 2)
                                        <span class="category-more">+{{ $tender->categories->count() - 2 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-footer">
                            <div class="footer-info">
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $tender->created_at ? $tender->created_at->diffForHumans() : '' }}
                                </span>
                                <span>
                                    <i class="fas fa-comments"></i>
                                    {{ $tender->responses_count ?? 0 }}/{{ $tender->max_responses ?? 10 }}
                                </span>
                            </div>
                            
                            <a href="{{ route('prestataire.tenders.show', $tender) }}" class="btn btn-primary">
                                Voir <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        @if($tender->expires_at && $tender->expires_at->diffInDays(now()) < 3)
                            <div class="expiring-badge">
                                <i class="fas fa-hourglass-half"></i>
                                Expire {{ $tender->expires_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if(method_exists($tenders, 'links') && $tenders->hasPages())
                <div class="pagination-wrapper">
                    {{ $tenders->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Aucun appel d'offre trouvé</h3>
                <p>Il n'y a pas d'appel d'offre correspondant à vos critères.</p>
                <div class="empty-tips">
                    <h4>Conseils :</h4>
                    <ul>
                        <li>Élargissez vos critères de recherche</li>
                        <li>Ajoutez plus de catégories à votre profil</li>
                        <li>Revenez plus tard, de nouveaux appels arrivent</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endsection
