@extends('layouts.app')

@section('title', 'Mes propositions - TapRestation')

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
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            opacity: 0.9;
            transition: opacity 0.2s;
        }
        
        .back-link:hover {
            opacity: 1;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* Container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(5, 1fr);
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
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
        
        .stat-icon.total {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
        }
        
        .stat-icon.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-icon.viewed {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .stat-icon.shortlisted {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
        
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            -webkit-overflow-scrolling: touch;
        }
        
        .filter-tab {
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 20px;
            text-decoration: none;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .filter-tab:hover {
            color: #7c3aed;
            border-color: #7c3aed;
        }
        
        .filter-tab.active {
            background: #7c3aed;
            color: white;
        }
        
        /* Response Cards */
        .responses-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .response-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .response-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .status-bar {
            height: 4px;
        }
        
        .status-bar.pending { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .status-bar.viewed { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .status-bar.shortlisted { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        .status-bar.accepted { background: linear-gradient(90deg, #10b981, #34d399); }
        .status-bar.rejected { background: linear-gradient(90deg, #ef4444, #f87171); }
        .status-bar.withdrawn { background: linear-gradient(90deg, #6b7280, #9ca3af); }
        
        .response-content {
            padding: 1.25rem;
        }
        
        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .tender-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .tender-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .tender-title a:hover {
            color: #7c3aed;
        }
        
        .tender-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .tender-meta span {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .tender-meta i {
            color: #7c3aed;
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        
        .status-badge.pending {
            background: #fef3c7;
            color: #b45309;
        }
        
        .status-badge.viewed {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .status-badge.shortlisted {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        .status-badge.accepted {
            background: #d1fae5;
            color: #059669;
        }
        
        .status-badge.rejected {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .status-badge.withdrawn {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .response-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            margin: 1rem 0;
        }
        
        @media (min-width: 640px) {
            .response-details {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .detail-value.price {
            color: #7c3aed;
        }
        
        .response-dates {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        .response-dates span {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .response-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
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
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #7c3aed;
            color: #7c3aed;
        }
        
        .btn-outline:hover {
            background: #7c3aed;
            color: white;
        }
        
        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
        }
        
        /* Client Message */
        .client-message {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 1rem;
            margin: 0 1.25rem 1.25rem;
            border-radius: 0 8px 8px 0;
        }
        
        .client-message .message-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .client-message p {
            color: #166534;
            font-size: 0.9rem;
        }
        
        /* Success Banner (for accepted) */
        .success-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .success-banner i {
            font-size: 1.5rem;
        }
        
        .contact-options {
            display: flex;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: #f0fdf4;
        }
        
        .contact-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .contact-btn.phone {
            background: #10b981;
            color: white;
        }
        
        .contact-btn.phone:hover {
            background: #059669;
        }
        
        .contact-btn.message {
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
        }
        
        .contact-btn.message:hover {
            background: #10b981;
            color: white;
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
        
        /* Pagination */
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .pagination-wrapper nav {
            display: flex;
            gap: 0.25rem;
        }
        
        .pagination-wrapper a,
        .pagination-wrapper span {
            padding: 0.5rem 0.75rem;
            background: white;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .pagination-wrapper a:hover {
            background: #7c3aed;
            color: white;
        }
        
        .pagination-wrapper .active span {
            background: #7c3aed;
            color: white;
        }
        
        /* Alert */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* ============================================
           VERY SMALL SCREENS (< 400px)
           ============================================ */
        @media (max-width: 400px) {
            .header {
                padding: 0.625rem 0.5rem;
            }
            
            .header-title {
                font-size: 1rem;
            }
            
            .back-link {
                font-size: 0.8125rem;
            }
            
            .container {
                padding: 0.75rem 0.5rem;
            }
            
            .page-header h1 {
                font-size: 1.25rem;
            }
            
            .page-subtitle {
                font-size: 0.8125rem;
            }
            
            .stats-grid {
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .stat-card {
                padding: 0.625rem;
                border-radius: 8px;
            }
            
            .stat-icon {
                width: 32px;
                height: 32px;
                font-size: 0.875rem;
            }
            
            .stat-value {
                font-size: 1rem;
            }
            
            .stat-label {
                font-size: 0.625rem;
            }
            
            .tabs-container {
                gap: 0.25rem;
                padding: 0.375rem;
            }
            
            .tab-btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.6875rem;
            }
            
            .response-card {
                border-radius: 10px;
            }
            
            .response-header {
                padding: 0.625rem;
            }
            
            .response-title {
                font-size: 0.875rem;
            }
            
            .response-meta {
                font-size: 0.625rem;
            }
            
            .response-body {
                padding: 0.625rem;
            }
            
            .price-display {
                font-size: 1.125rem;
            }
            
            .price-label {
                font-size: 0.625rem;
            }
            
            .response-footer {
                padding: 0.5rem 0.625rem;
            }
            
            .action-btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.6875rem;
            }
            
            .status-badge {
                padding: 0.25rem 0.5rem;
                font-size: 0.625rem;
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
            
            .alert {
                padding: 0.625rem;
                font-size: 0.8125rem;
                border-radius: 8px;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="{{ route('prestataire.tenders.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Appels d'offres</span>
            </a>
            <span class="header-title">Mes propositions</span>
            <div style="width: 100px;"></div>
        </div>
    </header>
    
    <div class="container">
        <!-- Message de succès -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-paper-plane" style="color: #7c3aed;"></i> Mes propositions</h1>
            <p class="page-subtitle">Suivez l'évolution de toutes vos propositions envoyées</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $responses->total() }}</span>
                    <span class="stat-label">Envoyées</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['pending'] ?? 0 }}</span>
                    <span class="stat-label">En attente</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon shortlisted">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['shortlisted'] ?? 0 }}</span>
                    <span class="stat-label">Présélectionnées</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon accepted">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['accepted'] ?? 0 }}</span>
                    <span class="stat-label">Acceptées</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $stats['rejected'] ?? 0 }}</span>
                    <span class="stat-label">Refusées</span>
                </div>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="{{ route('prestataire.tenders.my-responses') }}" 
               class="filter-tab {{ !request('status') ? 'active' : '' }}">
                Toutes
            </a>
            <a href="{{ route('prestataire.tenders.my-responses', ['status' => 'pending']) }}" 
               class="filter-tab {{ request('status') === 'pending' ? 'active' : '' }}">
                <i class="fas fa-clock"></i> En attente
            </a>
            <a href="{{ route('prestataire.tenders.my-responses', ['status' => 'shortlisted']) }}" 
               class="filter-tab {{ request('status') === 'shortlisted' ? 'active' : '' }}">
                <i class="fas fa-star"></i> Présélectionnées
            </a>
            <a href="{{ route('prestataire.tenders.my-responses', ['status' => 'accepted']) }}" 
               class="filter-tab {{ request('status') === 'accepted' ? 'active' : '' }}">
                <i class="fas fa-check"></i> Acceptées
            </a>
            <a href="{{ route('prestataire.tenders.my-responses', ['status' => 'rejected']) }}" 
               class="filter-tab {{ request('status') === 'rejected' ? 'active' : '' }}">
                <i class="fas fa-times"></i> Refusées
            </a>
        </div>
        
        <!-- Responses List -->
        @if($responses->count() > 0)
            <div class="responses-list">
                @foreach($responses as $response)
                    @php
                        $tender = $response->tenderRequest;
                    @endphp
                    <div class="response-card">
                        <div class="status-bar {{ $response->status }}"></div>
                        
                        <div class="response-content">
                            <div class="response-header">
                                <div>
                                    <h3 class="tender-title">
                                        @if($tender)
                                            <a href="{{ route('prestataire.tenders.show', $tender) }}">
                                                {{ $tender->title }}
                                            </a>
                                        @else
                                            <span style="color: #94a3b8;">Appel d'offre supprimé</span>
                                        @endif
                                    </h3>
                                    @if($tender)
                                        <div class="tender-meta">
                                            <span><i class="fas fa-map-marker-alt"></i> {{ $tender->city }}</span>
                                            <span><i class="fas fa-calendar"></i> {{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Flexible' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="status-badge {{ $response->status }}">
                                    @switch($response->status)
                                        @case('pending')
                                            <i class="fas fa-clock"></i> En attente
                                            @break
                                        @case('viewed')
                                            <i class="fas fa-eye"></i> Consultée
                                            @break
                                        @case('shortlisted')
                                            <i class="fas fa-star"></i> Présélectionnée
                                            @break
                                        @case('accepted')
                                            <i class="fas fa-check-circle"></i> Acceptée
                                            @break
                                        @case('rejected')
                                            <i class="fas fa-times-circle"></i> Refusée
                                            @break
                                        @case('withdrawn')
                                            <i class="fas fa-undo"></i> Retirée
                                            @break
                                        @default
                                            {{ $response->status }}
                                    @endswitch
                                </span>
                            </div>
                            
                            <div class="response-details">
                                <div class="detail-item">
                                    <div class="detail-label">Prix proposé</div>
                                    <div class="detail-value price">
                                        @if($response->price_type === 'negotiable')
                                            À négocier
                                        @else
                                            {{ number_format($response->proposed_price, 0, ',', ' ') }} €
                                        @endif
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Type</div>
                                    <div class="detail-value">
                                        @switch($response->price_type)
                                            @case('hourly') Par heure @break
                                            @case('daily') Par jour @break
                                            @case('negotiable') Négociable @break
                                            @default Prix fixe @break
                                        @endswitch
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Score</div>
                                    <div class="detail-value">{{ $response->match_score ?? 0 }}%</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Disponibilité</div>
                                    <div class="detail-value">
                                        @if($response->availability_start)
                                            {{ \Carbon\Carbon::parse($response->availability_start)->format('d/m') }}
                                        @else
                                            Flexible
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="response-dates">
                                <span>
                                    <i class="fas fa-paper-plane"></i>
                                    Envoyée {{ $response->created_at->diffForHumans() }}
                                </span>
                                @if($response->viewed_at)
                                    <span>
                                        <i class="fas fa-eye"></i>
                                        Vue {{ \Carbon\Carbon::parse($response->viewed_at)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="response-actions">
                                @if($tender)
                                    <a href="{{ route('prestataire.tenders.show', $tender) }}" class="btn btn-outline btn-sm">
                                        <i class="fas fa-eye"></i> Voir l'appel
                                    </a>
                                    @if(in_array($response->status, ['pending', 'viewed']))
                                        <a href="{{ route('prestataire.tenders.edit-response', [$tender, $response]) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <form action="{{ route('prestataire.tenders.withdraw-response', $response) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cette proposition ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #dc2626; border: none;">
                                                <i class="fas fa-trash-alt"></i> Retirer
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        <!-- Client Message -->
                        @if($response->client_message)
                            <div class="client-message">
                                <div class="message-label">
                                    <i class="fas fa-comment"></i> Message du client
                                </div>
                                <p>{{ $response->client_message }}</p>
                            </div>
                        @endif
                        
                        <!-- Accepted Actions -->
                        @if($response->status === 'accepted' && $tender)
                            <div class="success-banner">
                                <i class="fas fa-trophy"></i>
                                <span>Félicitations ! Votre proposition a été retenue.</span>
                            </div>
                            <div class="contact-options">
                                @if($tender->contact_phone)
                                    <a href="tel:{{ $tender->contact_phone }}" class="contact-btn phone">
                                        <i class="fas fa-phone"></i> Appeler
                                    </a>
                                @endif
                                <a href="{{ route('messaging.conversation', $tender->client_id) }}" class="contact-btn message">
                                    <i class="fas fa-comment"></i> Envoyer un message
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($responses->hasPages())
                <div class="pagination-wrapper">
                    {{ $responses->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>Aucune proposition</h3>
                @if(request('status'))
                    <p>Vous n'avez pas de proposition avec ce statut.</p>
                    <a href="{{ route('prestataire.tenders.my-responses') }}" class="btn btn-secondary">
                        Voir toutes les propositions
                    </a>
                @else
                    <p>Vous n'avez pas encore envoyé de proposition.</p>
                    <a href="{{ route('prestataire.tenders.index') }}" class="btn btn-primary">
                        <i class="fas fa-search"></i> Parcourir les appels d'offres
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection
