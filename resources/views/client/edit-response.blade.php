<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Modifier ma proposition - TapRestation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }
        
        .header-content {
            max-width: 800px;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .header-title i {
            font-size: 1rem;
        }
        
        /* Container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }
        
        /* Tender Summary */
        .tender-summary {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .edit-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #fef3c7;
            color: #92400e;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .tender-summary h2 {
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
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
            color: #f59e0b;
        }
        
        .response-status {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.viewed {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .sent-info {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        /* Form */
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-title i {
            color: #f59e0b;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-group label .required {
            color: #ef4444;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.35rem;
        }
        
        .form-error {
            font-size: 0.8rem;
            color: #ef4444;
            margin-top: 0.35rem;
        }
        
        .price-input-group {
            display: flex;
            gap: 0.75rem;
        }
        
        .price-input-group .form-input {
            flex: 1;
        }
        
        .price-input-group .form-select {
            width: 140px;
        }
        
        .dates-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 480px) {
            .dates-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* Buttons */
        .form-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            flex: 1;
            min-width: 140px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
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
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Character counter */
        .char-counter {
            text-align: right;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }
        
        .char-counter.warning {
            color: #f59e0b;
        }
        
        .char-counter.error {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="{{ route('prestataire.tenders.my-responses') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Mes propositions</span>
            </a>
            <span class="header-title">
                <i class="fas fa-edit"></i>
                Modifier
            </span>
            <div style="width: 80px;"></div>
        </div>
    </header>
    
    <div class="container">
        <!-- Errors -->
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Warning -->
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i>
            <span>Vous pouvez modifier votre proposition tant qu'elle n'a pas été acceptée.</span>
        </div>
        
        <!-- Tender Summary -->
        <div class="tender-summary">
            <div class="edit-badge">
                <i class="fas fa-edit"></i> Mode modification
            </div>
            <h2>{{ $tender->title }}</h2>
            <div class="tender-meta">
                <span><i class="fas fa-map-marker-alt"></i> {{ $tender->city }}</span>
                <span><i class="fas fa-calendar"></i> {{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Flexible' }}</span>
                @if($tender->budget_visible && $tender->budget_max)
                    <span><i class="fas fa-euro-sign"></i> {{ number_format($tender->budget_max, 0, ',', ' ') }} €</span>
                @endif
            </div>
            <div class="response-status">
                <span class="status-badge {{ $response->status }}">
                    @switch($response->status)
                        @case('pending') <i class="fas fa-clock"></i> En attente @break
                        @case('viewed') <i class="fas fa-eye"></i> Consultée @break
                        @default {{ $response->status }} @break
                    @endswitch
                </span>
                <span class="sent-info">
                    Envoyée {{ $response->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
        
        <!-- Form -->
        <form action="{{ route('prestataire.tenders.update-response', [$tender, $response]) }}" 
              method="POST" 
              enctype="multipart/form-data"
              id="editForm">
            @csrf
            @method('PUT')
            
            <!-- Prix -->
            <div class="form-card">
                <h3 class="form-title"><i class="fas fa-euro-sign"></i> Votre tarif</h3>
                
                <div class="form-group">
                    <label>Prix proposé <span class="required">*</span></label>
                    <div class="price-input-group">
                        <input type="number" 
                               name="proposed_price" 
                               class="form-input" 
                               value="{{ old('proposed_price', $response->proposed_price) }}"
                               min="0" 
                               step="0.01"
                               placeholder="0.00"
                               required>
                        <select name="price_type" class="form-select" required>
                            <option value="fixed" {{ old('price_type', $response->price_type) == 'fixed' ? 'selected' : '' }}>Prix fixe</option>
                            <option value="hourly" {{ old('price_type', $response->price_type) == 'hourly' ? 'selected' : '' }}>Par heure</option>
                            <option value="daily" {{ old('price_type', $response->price_type) == 'daily' ? 'selected' : '' }}>Par jour</option>
                            <option value="negotiable" {{ old('price_type', $response->price_type) == 'negotiable' ? 'selected' : '' }}>À négocier</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Message -->
            <div class="form-card">
                <h3 class="form-title"><i class="fas fa-comment-alt"></i> Votre message</h3>
                
                <div class="form-group">
                    <label>Message au client <span class="required">*</span></label>
                    <textarea name="message" 
                              id="messageField"
                              class="form-textarea" 
                              placeholder="Décrivez votre approche..."
                              minlength="50"
                              maxlength="2000"
                              required>{{ old('message', $response->cover_letter) }}</textarea>
                    <div class="char-counter" id="charCounter">0 / 2000 caractères</div>
                    <p class="form-hint">Minimum 50 caractères. Expliquez votre approche et vos qualifications.</p>
                </div>
            </div>
            
            <!-- Disponibilité -->
            <div class="form-card">
                <h3 class="form-title"><i class="fas fa-calendar-check"></i> Disponibilité</h3>
                
                <div class="dates-row">
                    <div class="form-group">
                        <label>Disponible à partir du</label>
                        @php
                            $availStart = $response->availability_start ?? $response->proposed_start_date ?? null;
                            $availEnd = $response->availability_end ?? $response->proposed_end_date ?? null;
                        @endphp
                        <input type="date" 
                               name="availability_start" 
                               class="form-input"
                               value="{{ old('availability_start', $availStart ? (is_string($availStart) ? $availStart : $availStart->format('Y-m-d')) : '') }}">
                    </div>
                    <div class="form-group">
                        <label>Jusqu'au (optionnel)</label>
                        <input type="date" 
                               name="availability_end" 
                               class="form-input"
                               value="{{ old('availability_end', $availEnd ? (is_string($availEnd) ? $availEnd : $availEnd->format('Y-m-d')) : '') }}">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Durée estimée</label>
                    <input type="text" 
                           name="estimated_duration" 
                           class="form-input"
                           value="{{ old('estimated_duration', $response->estimated_duration ?? $response->estimated_duration_hours ?? '') }}"
                           placeholder="Ex: 2 jours, 1 semaine...">
                </div>
            </div>
            
            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('prestataire.tenders.my-responses') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
        
        <!-- Retirer la proposition -->
        <div class="form-card" style="margin-top: 1.5rem; border: 2px solid #fecaca;">
            <h3 class="form-title" style="color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> Zone de danger</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
                Retirer votre proposition la supprimera définitivement. Cette action est irréversible.
            </p>
            <form action="{{ route('prestataire.tenders.withdraw-response', $response) }}" 
                  method="POST" 
                  id="withdrawForm"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cette proposition ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background: #fee2e2; color: #dc2626; border: 2px solid #fecaca;">
                    <i class="fas fa-trash-alt"></i> Retirer ma proposition
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Character counter
        const messageField = document.getElementById('messageField');
        const charCounter = document.getElementById('charCounter');
        
        function updateCharCount() {
            const length = messageField.value.length;
            charCounter.textContent = length + ' / 2000 caractères';
            
            charCounter.classList.remove('warning', 'error');
            if (length < 50) {
                charCounter.classList.add('error');
            } else if (length > 1800) {
                charCounter.classList.add('warning');
            }
        }
        
        messageField.addEventListener('input', updateCharCount);
        updateCharCount();
        
        // Form submission
        const form = document.getElementById('editForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            if (messageField.value.length < 50) {
                e.preventDefault();
                alert('Le message doit contenir au moins 50 caractères.');
                messageField.focus();
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Enregistrement...';
        });
    </script>
</body>
</html>
