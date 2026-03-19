@extends('layouts.prestataire')

@section('title', 'Proposer mes services - ' . $tender->title)

@section('content')
<style>
    * { box-sizing: border-box; }
    .respond-page { max-width: 700px; margin: 0 auto; padding: 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: #3b82f6; text-decoration: none; font-size: 14px; margin-bottom: 16px; }
    
    /* Header */
    .respond-header { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); border-radius: 16px; padding: 20px; color: white; margin-bottom: 20px; }
    .respond-header h1 { margin: 0 0 8px; font-size: 20px; }
    .respond-header .tender-title { font-size: 16px; opacity: 0.9; margin: 0 0 12px; }
    .respond-header .meta { display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; opacity: 0.9; }
    .respond-header .meta span { display: flex; align-items: center; gap: 4px; }
    
    /* Form */
    .form-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .form-card h2 { font-size: 16px; margin: 0 0 16px; color: #1f2937; display: flex; align-items: center; gap: 8px; }
    .form-card h2 i { color: #3b82f6; }
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .form-group .hint { font-size: 12px; color: #6b7280; margin-top: 4px; }
    
    .form-control { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: border-color 0.2s; }
    .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    textarea.form-control { resize: vertical; min-height: 120px; }
    
    /* Price Type */
    .price-types { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 16px; }
    .price-type-btn { padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; background: white; cursor: pointer; text-align: center; transition: all 0.2s; }
    .price-type-btn:hover { border-color: #3b82f6; }
    .price-type-btn.active { border-color: #3b82f6; background: #eff6ff; }
    .price-type-btn input { display: none; }
    .price-type-btn .label { font-weight: 500; color: #1f2937; }
    .price-type-btn .desc { font-size: 12px; color: #6b7280; margin-top: 2px; }
    
    /* Price Input */
    .price-input-wrapper { position: relative; }
    .price-input-wrapper input { padding-right: 50px; }
    .price-input-wrapper .suffix { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 500; }
    
    /* Dates */
    .date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    
    /* Submit */
    .submit-section { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .btn-submit { width: 100%; padding: 16px; background: #10b981; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
    .btn-submit:hover { background: #059669; }
    .btn-submit:disabled { background: #9ca3af; cursor: not-allowed; }
    
    /* Error messages */
    .error-message { color: #dc2626; font-size: 13px; margin-top: 4px; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
    
    @media (max-width: 480px) {
        .price-types { grid-template-columns: 1fr; }
        .date-row { grid-template-columns: 1fr; }
    }
</style>

<div class="respond-page">
    
    <a href="{{ route('prestataire.tenders.show', $tender) }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Retour à la demande
    </a>

    {{-- Header --}}
    <div class="respond-header">
        <h1><i class="fas fa-paper-plane"></i> Proposer mes services</h1>
        <p class="tender-title">{{ $tender->title }}</p>
        <div class="meta">
            <span><i class="fas fa-map-marker-alt"></i> {{ $tender->city }}</span>
            <span><i class="fas fa-calendar"></i> {{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Flexible' }}</span>
            @if($tender->budget_visible && $tender->budget_min)
                <span><i class="fas fa-euro-sign"></i> {{ number_format($tender->budget_min, 0, ',', ' ') }} - {{ number_format($tender->budget_max, 0, ',', ' ') }} €</span>
            @endif
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('prestataire.tenders.store-response', $tender) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Prix --}}
        <div class="form-card">
            <h2><i class="fas fa-euro-sign"></i> Votre tarif</h2>
            
            <div class="form-group">
                <label>Type de tarification</label>
                <div class="price-types">
                    <label class="price-type-btn" id="type-fixed">
                        <input type="radio" name="price_type" value="fixed" {{ old('price_type', 'fixed') == 'fixed' ? 'checked' : '' }} onchange="updatePriceType('fixed')">
                        <div class="label">Prix fixe</div>
                        <div class="desc">Montant global</div>
                    </label>
                    <label class="price-type-btn" id="type-hourly">
                        <input type="radio" name="price_type" value="hourly" {{ old('price_type') == 'hourly' ? 'checked' : '' }} onchange="updatePriceType('hourly')">
                        <div class="label">À l'heure</div>
                        <div class="desc">Tarif horaire</div>
                    </label>
                    <label class="price-type-btn" id="type-daily">
                        <input type="radio" name="price_type" value="daily" {{ old('price_type') == 'daily' ? 'checked' : '' }} onchange="updatePriceType('daily')">
                        <div class="label">À la journée</div>
                        <div class="desc">Tarif journalier</div>
                    </label>
                    <label class="price-type-btn" id="type-negotiable">
                        <input type="radio" name="price_type" value="negotiable" {{ old('price_type') == 'negotiable' ? 'checked' : '' }} onchange="updatePriceType('negotiable')">
                        <div class="label">À négocier</div>
                        <div class="desc">Sur devis</div>
                    </label>
                </div>
            </div>

            <div class="form-group" id="price-group">
                <label>Montant proposé</label>
                <div class="price-input-wrapper">
                    <input type="number" name="proposed_price" class="form-control" 
                           value="{{ old('proposed_price') }}" 
                           min="0" step="0.01" placeholder="0.00" required>
                    <span class="suffix" id="price-suffix">€</span>
                </div>
                @error('proposed_price')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Durée estimée (optionnel)</label>
                <input type="text" name="estimated_duration" class="form-control" 
                       value="{{ old('estimated_duration') }}" 
                       placeholder="ex: 2 jours, 5 heures...">
            </div>
        </div>

        {{-- Disponibilité --}}
        <div class="form-card">
            <h2><i class="fas fa-calendar-check"></i> Disponibilité</h2>
            
            <div class="date-row">
                <div class="form-group">
                    <label>Disponible à partir du *</label>
                    <input type="date" name="availability_start" class="form-control" 
                           value="{{ old('availability_start', date('Y-m-d')) }}" 
                           min="{{ date('Y-m-d') }}" required>
                    @error('availability_start')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Jusqu'au (optionnel)</label>
                    <input type="date" name="availability_end" class="form-control" 
                           value="{{ old('availability_end') }}">
                </div>
            </div>
        </div>

        {{-- Message --}}
        <div class="form-card">
            <h2><i class="fas fa-comment-alt"></i> Votre message</h2>
            
            <div class="form-group">
                <label>Présentez votre offre au client *</label>
                <textarea name="message" class="form-control" rows="6" required 
                          placeholder="Présentez brièvement votre expérience, expliquez pourquoi vous êtes le bon choix, mentionnez ce qui est inclus dans votre prix...">{{ old('message') }}</textarea>
                <div class="hint">Minimum 50 caractères, maximum 2000 caractères</div>
                @error('message')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Pièces jointes --}}
        <div class="form-card">
            <h2><i class="fas fa-paperclip"></i> Pièces jointes (optionnel)</h2>
            
            <div class="form-group">
                <label>Ajouter des documents</label>
                <input type="file" name="attachments[]" class="form-control" multiple 
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <div class="hint">PDF, Word ou images. Maximum 5 fichiers, 10 Mo chacun.</div>
                @error('attachments.*')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="submit-section">
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i>
                Envoyer ma proposition
            </button>
            <p style="text-align: center; font-size: 13px; color: #6b7280; margin: 12px 0 0;">
                En soumettant cette proposition, vous vous engagez à réaliser la prestation si le client accepte.
            </p>
        </div>

    </form>
</div>

<script>
    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        var checked = document.querySelector('input[name="price_type"]:checked');
        if (checked) {
            updatePriceType(checked.value);
        }
    });

    function updatePriceType(type) {
        // Reset all buttons
        document.querySelectorAll('.price-type-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Activate selected
        var btn = document.getElementById('type-' + type);
        if (btn) {
            btn.classList.add('active');
        }
        
        // Update suffix
        var suffix = document.getElementById('price-suffix');
        var priceGroup = document.getElementById('price-group');
        
        if (type === 'hourly') {
            suffix.textContent = '€/h';
        } else if (type === 'daily') {
            suffix.textContent = '€/jour';
        } else {
            suffix.textContent = '€';
        }
        
        // Hide price input for negotiable
        if (type === 'negotiable') {
            priceGroup.style.display = 'none';
            document.querySelector('input[name="proposed_price"]').removeAttribute('required');
        } else {
            priceGroup.style.display = 'block';
            document.querySelector('input[name="proposed_price"]').setAttribute('required', 'required');
        }
    }
</script>

@endsection
