@extends('layouts.app')

@section('title', 'Noter le client - ' . $client->name)

@push('styles')
<style>
    .review-container {
        max-width: 700px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    .review-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .review-header {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: white;
        padding: 1.5rem 2rem;
        text-align: center;
    }
    
    .review-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .client-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .client-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid white;
        object-fit: cover;
    }
    
    .client-name {
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .review-body {
        padding: 2rem;
    }
    
    .booking-info {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .form-section {
        margin-bottom: 1.5rem;
    }
    
    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }
    
    /* Star Rating */
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.25rem;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        cursor: pointer;
        font-size: 2rem;
        color: #e2e8f0;
        transition: color 0.2s;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #fbbf24;
    }
    
    /* Quality Rating Pills */
    .quality-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .quality-option {
        position: relative;
    }
    
    .quality-option input {
        display: none;
    }
    
    .quality-option label {
        display: inline-block;
        padding: 0.5rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 2rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .quality-option input:checked + label {
        border-color: #2563eb;
        background: #eff6ff;
        color: #2563eb;
    }
    
    .quality-option label:hover {
        border-color: #94a3b8;
    }
    
    /* Toggle Switch */
    .toggle-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
    }
    
    .toggle-switch input {
        display: none;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        border-radius: 26px;
        transition: 0.3s;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    
    .toggle-switch input:checked + .toggle-slider {
        background-color: #10b981;
    }
    
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    
    .toggle-label {
        font-size: 0.9375rem;
        color: #374151;
    }
    
    /* Comment Textarea */
    .comment-textarea {
        width: 100%;
        padding: 0.875rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        font-size: 0.9375rem;
        resize: vertical;
        min-height: 100px;
        transition: border-color 0.2s;
    }
    
    .comment-textarea:focus {
        outline: none;
        border-color: #2563eb;
    }
    
    .char-count {
        font-size: 0.75rem;
        color: #94a3b8;
        text-align: right;
        margin-top: 0.25rem;
    }
    
    /* Submit Button */
    .submit-btn {
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(90deg, #2563eb, #7c3aed);
        color: white;
        border: none;
        border-radius: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }
    
    .cancel-link {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: #64748b;
        font-size: 0.875rem;
        text-decoration: none;
    }
    
    .cancel-link:hover {
        color: #374151;
    }
</style>
@endpush

@section('content')
<div class="review-container">
    <div class="review-card">
        <div class="review-header">
            <h1>Évaluer le client</h1>
            <div class="client-info">
                @php
                    $clientName = is_object($client) ? ($client->name ?? 'Client') : 'Client';
                    $clientPhoto = is_object($client) ? ($client->profile_photo_url ?? null) : null;
                @endphp
                @if($clientPhoto)
                    <img src="{{ $clientPhoto }}" alt="{{ $clientName }}" class="client-avatar">
                @else
                    <div class="client-avatar" style="background: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user text-gray-400 text-xl"></i>
                    </div>
                @endif
                <span class="client-name">{{ $clientName }}</span>
            </div>
        </div>
        
        <div class="review-body">
            <div class="booking-info">
                <i class="fas fa-calendar-check mr-2"></i>
                Prestation du {{ $booking->start_datetime ? $booking->start_datetime->format('d/m/Y') : 'N/A' }} - {{ $booking->service->name ?? 'Service' }}
            </div>
            
            <form action="{{ route('client-reviews.store', $booking) }}" method="POST">
                @csrf
                
                <!-- Note globale -->
                <div class="form-section">
                    <div class="form-section-title">Note globale *</div>
                    <div class="star-rating">
                        @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                            <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Ponctualité -->
                <div class="form-section">
                    <div class="form-section-title">Ponctualité</div>
                    <div class="quality-options">
                        @foreach(['excellent' => 'Excellent', 'good' => 'Bon', 'average' => 'Moyen', 'poor' => 'Mauvais'] as $value => $label)
                            <div class="quality-option">
                                <input type="radio" name="punctuality" id="punctuality_{{ $value }}" value="{{ $value }}" {{ old('punctuality') == $value ? 'checked' : '' }}>
                                <label for="punctuality_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Communication -->
                <div class="form-section">
                    <div class="form-section-title">Communication</div>
                    <div class="quality-options">
                        @foreach(['excellent' => 'Excellent', 'good' => 'Bon', 'average' => 'Moyen', 'poor' => 'Mauvais'] as $value => $label)
                            <div class="quality-option">
                                <input type="radio" name="communication" id="communication_{{ $value }}" value="{{ $value }}" {{ old('communication') == $value ? 'checked' : '' }}>
                                <label for="communication_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Respect -->
                <div class="form-section">
                    <div class="form-section-title">Respect</div>
                    <div class="quality-options">
                        @foreach(['excellent' => 'Excellent', 'good' => 'Bon', 'average' => 'Moyen', 'poor' => 'Mauvais'] as $value => $label)
                            <div class="quality-option">
                                <input type="radio" name="respect" id="respect_{{ $value }}" value="{{ $value }}" {{ old('respect') == $value ? 'checked' : '' }}>
                                <label for="respect_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Travaillerait à nouveau -->
                <div class="form-section">
                    <div class="form-section-title">Travailleriez-vous à nouveau avec ce client ?</div>
                    <div class="toggle-container">
                        <label class="toggle-switch">
                            <input type="checkbox" name="would_work_again" value="1" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label">Oui, je recommande ce client</span>
                    </div>
                </div>
                
                <!-- Commentaire -->
                <div class="form-section">
                    <div class="form-section-title">Commentaire (optionnel)</div>
                    <textarea name="comment" class="comment-textarea" placeholder="Partagez votre expérience avec ce client..." maxlength="1000">{{ old('comment') }}</textarea>
                    <div class="char-count"><span id="charCount">0</span>/1000 caractères</div>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane mr-2"></i>Envoyer mon évaluation
                </button>
                
                <a href="{{ route('prestataire.bookings.index') }}" class="cancel-link">
                    Annuler et revenir plus tard
                </a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.comment-textarea');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});
</script>
@endsection
