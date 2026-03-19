@extends('layouts.app')

@section('title', 'Avis clients - ' . $user->name)

@push('styles')
<style>
    .client-profile-header {
        background: linear-gradient(135deg, #1e293b, #334155);
        color: white;
        padding: 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
    }
    
    .client-profile-info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    
    .client-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
    }
    
    .client-details h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .client-rating-display {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .rating-stars {
        color: #fbbf24;
        font-size: 1.25rem;
    }
    
    .rating-value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .rating-count {
        color: #94a3b8;
        font-size: 0.875rem;
    }
    
    .stats-row {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #10b981;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .reviews-section {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
    }
    
    .reviews-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .review-item {
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s;
    }
    
    .review-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    
    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .reviewer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .reviewer-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9375rem;
    }
    
    .review-date {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .review-rating {
        color: #fbbf24;
    }
    
    .review-comment {
        color: #475569;
        font-size: 0.9375rem;
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }
    
    .review-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .quality-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.625rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-excellent {
        background: #dcfce7;
        color: #166534;
    }
    
    .badge-good {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-average {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-poor {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .would-work-badge {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #86efac;
    }
    
    .would-not-work-badge {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .no-reviews {
        text-align: center;
        padding: 3rem;
        color: #94a3b8;
    }
    
    .no-reviews i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header avec info client -->
    <div class="client-profile-header">
        <div class="client-profile-info">
            @if($user->profile_photo_url)
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="client-avatar-large">
            @else
                <div class="client-avatar-large" style="background: #475569; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
            @endif
            
            <div class="client-details">
                <h1>{{ $user->name }}</h1>
                
                @if($stats['average_rating'])
                    <div class="client-rating-display">
                        <span class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= round($stats['average_rating']) ? '' : '-half-alt' }}"></i>
                            @endfor
                        </span>
                        <span class="rating-value">{{ number_format($stats['average_rating'], 1) }}</span>
                        <span class="rating-count">({{ $stats['total_reviews'] }} avis)</span>
                    </div>
                @else
                    <div class="rating-count">Aucun avis pour le moment</div>
                @endif
                
                @if($stats['total_reviews'] > 0)
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-value">{{ $stats['total_reviews'] }}</div>
                            <div class="stat-label">Avis reçus</div>
                        </div>
                        @if($stats['would_work_again_percentage'] !== null)
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['would_work_again_percentage'] }}%</div>
                                <div class="stat-label">Recommandé</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Liste des avis -->
    <div class="reviews-section">
        <h2 class="reviews-title">
            <i class="fas fa-comments mr-2"></i>
            Avis des prestataires
        </h2>
        
        @forelse($reviews as $review)
            @php
                $prestataireUser = $review->prestataireUser ?? ($review->prestataire && $review->prestataire->user ? $review->prestataire->user : null);
            @endphp
            <div class="review-item">
                <div class="review-header">
                    <div class="reviewer-info">
                        @if($prestataireUser && $prestataireUser->profile_photo_url)
                            <img src="{{ $prestataireUser->profile_photo_url }}" alt="{{ $prestataireUser->name ?? 'Prestataire' }}" class="reviewer-avatar">
                        @else
                            <div class="reviewer-avatar" style="background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        @endif
                        <div>
                            <div class="reviewer-name">{{ $prestataireUser->name ?? 'Prestataire' }}</div>
                            <div class="review-date">{{ $review->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    
                    <div class="review-rating">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= $review->rating ? '' : ' text-gray-300' }}"></i>
                        @endfor
                    </div>
                </div>
                
                @if($review->comment)
                    <p class="review-comment">{{ $review->comment }}</p>
                @endif
                
                <div class="review-badges">
                    @if($review->punctuality)
                        <span class="quality-badge badge-{{ $review->punctuality }}">
                            <i class="fas fa-clock"></i>
                            Ponctualité: {{ $review->punctuality_label }}
                        </span>
                    @endif
                    
                    @if($review->communication)
                        <span class="quality-badge badge-{{ $review->communication }}">
                            <i class="fas fa-comment"></i>
                            Communication: {{ $review->communication_label }}
                        </span>
                    @endif
                    
                    @if($review->respect)
                        <span class="quality-badge badge-{{ $review->respect }}">
                            <i class="fas fa-handshake"></i>
                            Respect: {{ $review->respect_label }}
                        </span>
                    @endif
                    
                    @if($review->would_work_again)
                        <span class="quality-badge would-work-badge">
                            <i class="fas fa-thumbs-up"></i>
                            Recommandé
                        </span>
                    @else
                        <span class="quality-badge would-not-work-badge">
                            <i class="fas fa-thumbs-down"></i>
                            Non recommandé
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="no-reviews">
                <i class="fas fa-star"></i>
                <p>Ce client n'a pas encore reçu d'avis.</p>
            </div>
        @endforelse
        
        {{ $reviews->links() }}
    </div>
</div>
@endsection
