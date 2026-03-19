@extends('layouts.app')

@section('title', 'Mes Devis')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages-ergonomics.css') }}">
<link rel="stylesheet" href="{{ asset('css/tender-respond.css') }}">
<style>
    .quote-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .quote-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .quote-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-card.active {
        border-color: var(--primary-blue, #3b82f6);
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, white 100%);
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    
    .quote-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .quote-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .quote-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .quote-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .quote-reference {
        font-size: 0.8rem;
        color: #6b7280;
        font-family: monospace;
    }
    
    .quote-status {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .quote-status.draft { background: #f3f4f6; color: #6b7280; }
    .quote-status.sent { background: #dbeafe; color: #1d4ed8; }
    .quote-status.viewed { background: #fef3c7; color: #92400e; }
    .quote-status.accepted { background: #d1fae5; color: #065f46; }
    .quote-status.rejected { background: #fee2e2; color: #991b1b; }
    .quote-status.expired { background: #fed7aa; color: #9a3412; }
    .quote-status.cancelled { background: #e5e7eb; color: #4b5563; }
    
    .quote-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    @media (max-width: 640px) {
        .quote-info {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    .quote-info-item {
        text-align: center;
    }
    
    .quote-info-label {
        font-size: 0.7rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .quote-info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .quote-client {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .client-info h4 {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
    }
    
    .client-info p {
        color: #6b7280;
        font-size: 0.8rem;
    }
    
    .quote-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .btn-view {
        background: #eff6ff;
        color: #2563eb;
    }
    
    .btn-view:hover {
        background: #dbeafe;
    }
    
    .btn-edit {
        background: #f0fdf4;
        color: #16a34a;
    }
    
    .btn-edit:hover {
        background: #dcfce7;
    }
    
    .btn-send {
        background: #3b82f6;
        color: white;
    }
    
    .btn-send:hover {
        background: #2563eb;
    }
    
    .btn-pdf {
        background: #fef2f2;
        color: #dc2626;
    }
    
    .btn-pdf:hover {
        background: #fee2e2;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 16px;
        border: 2px dashed #e5e7eb;
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .empty-icon svg {
        width: 40px;
        height: 40px;
        color: #3b82f6;
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        background: #f3f4f6;
        color: #6b7280;
        transition: all 0.2s ease;
    }
    
    .filter-tab:hover {
        background: #e5e7eb;
    }
    
    .filter-tab.active {
        background: #3b82f6;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        
        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📄 Mes Devis</h1>
                <p class="text-gray-600 mt-1">Créez et envoyez des devis à vos clients</p>
            </div>
            <a href="{{ route('prestataire.quotes.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau devis
            </a>
        </div>
        
        {{-- Statistiques --}}
        <div class="quote-stats">
            <a href="{{ route('prestataire.quotes.index') }}" 
               class="stat-card {{ !$currentStatus ? 'active' : '' }}">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'draft']) }}" 
               class="stat-card {{ $currentStatus === 'draft' ? 'active' : '' }}">
                <div class="stat-value">{{ $stats['draft'] }}</div>
                <div class="stat-label">Brouillons</div>
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'sent']) }}" 
               class="stat-card {{ $currentStatus === 'sent' ? 'active' : '' }}">
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">En attente</div>
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'accepted']) }}" 
               class="stat-card {{ $currentStatus === 'accepted' ? 'active' : '' }}">
                <div class="stat-value text-green-600">{{ $stats['accepted'] }}</div>
                <div class="stat-label">Acceptés</div>
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'rejected']) }}" 
               class="stat-card {{ $currentStatus === 'rejected' ? 'active' : '' }}">
                <div class="stat-value text-red-600">{{ $stats['rejected'] }}</div>
                <div class="stat-label">Refusés</div>
            </a>
        </div>
        
        {{-- Filtres --}}
        <div class="filter-tabs">
            <a href="{{ route('prestataire.quotes.index') }}" 
               class="filter-tab {{ !$currentStatus ? 'active' : '' }}">
                Tous
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'draft']) }}" 
               class="filter-tab {{ $currentStatus === 'draft' ? 'active' : '' }}">
                Brouillons
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'sent']) }}" 
               class="filter-tab {{ $currentStatus === 'sent' ? 'active' : '' }}">
                Envoyés
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'viewed']) }}" 
               class="filter-tab {{ $currentStatus === 'viewed' ? 'active' : '' }}">
                Consultés
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'accepted']) }}" 
               class="filter-tab {{ $currentStatus === 'accepted' ? 'active' : '' }}">
                Acceptés
            </a>
            <a href="{{ route('prestataire.quotes.index', ['status' => 'rejected']) }}" 
               class="filter-tab {{ $currentStatus === 'rejected' ? 'active' : '' }}">
                Refusés
            </a>
        </div>
        
        {{-- Liste des devis --}}
        @if($quotes->count() > 0)
            @foreach($quotes as $quote)
                <div class="quote-card">
                    <div class="quote-header">
                        <div>
                            <h3 class="quote-title">{{ $quote->title }}</h3>
                            <span class="quote-reference">{{ $quote->reference_number }}</span>
                        </div>
                        <span class="quote-status {{ $quote->status }}">
                            {{ $quote->status_label }}
                        </span>
                    </div>
                    
                    <div class="quote-client">
                        <div class="client-avatar">
                            {{ substr($quote->client->user->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="client-info">
                            <h4>{{ $quote->client->user->name ?? 'Client' }}</h4>
                            <p>{{ $quote->client->user->email ?? '' }}</p>
                        </div>
                    </div>
                    
                    <div class="quote-info">
                        <div class="quote-info-item">
                            <div class="quote-info-label">Montant</div>
                            <div class="quote-info-value text-blue-600">{{ $quote->formatted_total }}</div>
                        </div>
                        <div class="quote-info-item">
                            <div class="quote-info-label">Créé le</div>
                            <div class="quote-info-value">{{ $quote->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div class="quote-info-item">
                            <div class="quote-info-label">Valide jusqu'au</div>
                            <div class="quote-info-value {{ $quote->is_expired ? 'text-red-600' : '' }}">
                                {{ $quote->valid_until ? $quote->valid_until->format('d/m/Y') : 'Non défini' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="quote-actions">
                        <a href="{{ route('prestataire.quotes.show', $quote) }}" class="btn-action btn-view">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Voir
                        </a>
                        
                        @if($quote->can_be_edited)
                            <a href="{{ route('prestataire.quotes.edit', $quote) }}" class="btn-action btn-edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>
                        @endif
                        
                        @if($quote->can_be_sent)
                            <form action="{{ route('prestataire.quotes.send', $quote) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn-action btn-send">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Envoyer
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('prestataire.quotes.pdf', $quote) }}" class="btn-action btn-pdf" target="_blank">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>
            @endforeach
            
            {{-- Pagination --}}
            <div class="mt-6">
                {{ $quotes->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun devis</h3>
                <p class="text-gray-600 mb-4">Commencez par créer votre premier devis pour un client.</p>
                <a href="{{ route('prestataire.quotes.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer un devis
                </a>
            </div>
        @endif
        
    </div>
</div>
@endsection
