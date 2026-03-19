@extends('layouts.client')

@section('title', 'Nouvelle demande de devis')

@push('client-styles')
<style>
/* ===============================================
   FORMULAIRE DEMANDE DE DEVIS - Multi-étapes
   =============================================== */
.tender-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem 1rem;
}

.tender-container {
    max-width: 800px;
    margin: 0 auto;
}

/* Header */
.tender-header {
    text-align: center;
    margin-bottom: 1.5rem;
    color: white;
}

.tender-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.tender-header p {
    opacity: 0.9;
    font-size: 1rem;
}

/* Barre de progression */
.progress-bar-container {
    background: rgba(255,255,255,0.2);
    border-radius: 50px;
    padding: 0.5rem 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    flex: 1;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.progress-step::after {
    content: '';
    position: absolute;
    top: 15px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: rgba(255,255,255,0.3);
    z-index: 0;
}

.progress-step:last-child::after {
    display: none;
}

.progress-step.completed::after {
    background: white;
}

.step-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 0.9rem;
    z-index: 1;
    transition: all 0.3s;
}

.progress-step.active .step-circle {
    background: white;
    color: #667eea;
    transform: scale(1.1);
}

.progress-step.completed .step-circle {
    background: #10b981;
}

.step-label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    margin-top: 0.5rem;
    text-align: center;
}

.progress-step.active .step-label {
    color: white;
    font-weight: 600;
}

/* Carte principale */
.tender-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Titre étape */
.step-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.step-title-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.step-title-text h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.step-title-text p {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0.25rem 0 0;
}

/* Formulaire */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.2s;
    background: #f9fafb;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-hint {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.char-counter {
    margin-left: auto;
    font-weight: 500;
}

.char-counter.valid { color: #10b981; }
.char-counter.invalid { color: #ef4444; }

/* Autocomplete ville */
.city-autocomplete {
    position: relative;
}

.city-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #667eea;
    border-top: none;
    border-radius: 0 0 12px 12px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.city-suggestion {
    padding: 0.875rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.city-suggestion:hover {
    background: #f0f4ff;
}

.city-suggestion:last-child {
    border-bottom: none;
}

.city-suggestion i {
    color: #667eea;
    font-size: 0.9rem;
}

.city-suggestion-text {
    flex: 1;
}

.city-suggestion-name {
    font-weight: 600;
    color: #1e293b;
}

.city-suggestion-details {
    font-size: 0.8rem;
    color: #6b7280;
}

/* Urgence options */
.urgency-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

@media (max-width: 500px) {
    .urgency-grid {
        grid-template-columns: 1fr;
    }
}

.urgency-card {
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9fafb;
}

.urgency-card:hover {
    border-color: #667eea;
}

.urgency-card.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.urgency-card input { display: none; }

.urgency-card i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.urgency-card.low i { color: #10b981; }
.urgency-card.normal i { color: #3b82f6; }
.urgency-card.urgent i { color: #ef4444; }

.urgency-card span {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
}

/* Budget */
.budget-type-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.budget-type-card {
    padding: 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9fafb;
}

.budget-type-card:hover {
    border-color: #667eea;
}

.budget-type-card.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.budget-type-card input { display: none; }

.budget-type-card i {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.budget-type-card span {
    display: block;
    font-weight: 600;
    color: #374151;
}

/* Upload zone */
.upload-section {
    margin-bottom: 1.5rem;
}

.upload-section-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.upload-section-title i {
    color: #667eea;
}

/* Boutons d'action upload */
.upload-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.upload-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.25rem 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
}

.upload-action-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.upload-action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.upload-action-btn i {
    font-size: 1.5rem;
}

.upload-action-btn span {
    font-size: 0.9rem;
}

/* Zone drag & drop (desktop only) */
.upload-zone.desktop-only {
    display: none;
}

@media (min-width: 768px) {
    .upload-zone.desktop-only {
        display: block;
    }
}

.upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9fafb;
    margin-top: 0.5rem;
}

.upload-zone:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.upload-zone.drag-over {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

.upload-zone i {
    font-size: 2rem;
    color: #9ca3af;
    margin-bottom: 0.5rem;
}

.upload-zone p {
    color: #6b7280;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.upload-zone small {
    color: #9ca3af;
    font-size: 0.8rem;
}

/* Preview médias */
.media-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}

.media-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
}

.media-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-item.video {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 0.5rem;
}

.media-item.video i {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 0.25rem;
}

.media-item.video span {
    font-size: 0.7rem;
    color: #6b7280;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 100%;
}

.remove-media {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.media-item:hover .remove-media {
    opacity: 1;
}

/* Récapitulatif */
.summary-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.summary-title {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-title i {
    color: #667eea;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 500px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.summary-label {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-weight: 600;
    color: #1e293b;
}

/* Navigation */
.step-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #f3f4f6;
}

.btn {
    padding: 0.875rem 1.5rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
}

.btn-outline {
    background: transparent;
    border: 2px solid #e5e7eb;
    color: #374151;
}

.btn-outline:hover {
    border-color: #667eea;
    color: #667eea;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-success:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

/* Messages */
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
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
}

/* Spinner */
.spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 640px) {
    .tender-card {
        padding: 1.5rem;
    }
    
    .step-title {
        flex-direction: column;
        text-align: center;
    }
    
    .step-navigation {
        flex-direction: column;
        gap: 1rem;
    }
    
    .step-navigation .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@section('content')
<div class="tender-page" x-data="tenderWizard()" x-cloak>
    <div class="tender-container">
        
        {{-- Header --}}
        <div class="tender-header">
            <h1><i class="fas fa-file-invoice"></i> Nouvelle demande de devis</h1>
            <p>Décrivez votre besoin en quelques étapes</p>
        </div>

        {{-- Barre de progression --}}
        <div class="progress-bar-container">
            <div class="progress-steps">
                <div class="progress-step" :class="{ 'active': step === 1, 'completed': step > 1 }">
                    <div class="step-circle">
                        <i class="fas fa-check" x-show="step > 1"></i>
                        <span x-show="step <= 1">1</span>
                    </div>
                    <span class="step-label">Description</span>
                </div>
                <div class="progress-step" :class="{ 'active': step === 2, 'completed': step > 2 }">
                    <div class="step-circle">
                        <i class="fas fa-check" x-show="step > 2"></i>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="step-label">Lieu & Date</span>
                </div>
                <div class="progress-step" :class="{ 'active': step === 3, 'completed': step > 3 }">
                    <div class="step-circle">
                        <i class="fas fa-check" x-show="step > 3"></i>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span class="step-label">Budget & Photos</span>
                </div>
                <div class="progress-step" :class="{ 'active': step === 4 }">
                    <div class="step-circle">4</div>
                    <span class="step-label">Confirmation</span>
                </div>
            </div>
        </div>

        {{-- Carte principale --}}
        <div class="tender-card">
            
            {{-- Message --}}
            <div x-show="message" x-transition class="alert" :class="messageType === 'success' ? 'alert-success' : 'alert-error'">
                <i :class="messageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                <span x-text="message"></span>
            </div>

            {{-- ÉTAPE 1: Description --}}
            <div x-show="step === 1" x-transition>
                <div class="step-title">
                    <div class="step-title-icon"><i class="fas fa-edit"></i></div>
                    <div class="step-title-text">
                        <h2>Décrivez votre besoin</h2>
                        <p>Expliquez ce dont vous avez besoin aux prestataires</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Titre de votre demande</label>
                    <input type="text" 
                           x-model="form.title"
                           class="form-input"
                           placeholder="Ex: Recherche plombier pour fuite d'eau">
                </div>

                <div class="form-group">
                    <label class="form-label required">Description détaillée</label>
                    <textarea x-model="form.description"
                              class="form-textarea"
                              rows="5"
                              placeholder="Décrivez précisément votre besoin : le problème, les dimensions, les contraintes d'accès, le matériel nécessaire..."></textarea>
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i>
                        Plus vous êtes précis, meilleures seront les propositions
                        <span class="char-counter" :class="form.description.length >= 50 ? 'valid' : 'invalid'">
                            <span x-text="form.description.length"></span>/50 min
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Catégorie</label>
                    <select x-model="form.category_id" @change="loadSubcategories()" class="form-select">
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories->where('parent_id', null) as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" x-show="subcategories.length > 0" x-transition>
                    <label class="form-label">Sous-catégorie</label>
                    <select x-model="form.subcategory_id" class="form-select">
                        <option value="">Sélectionnez une sous-catégorie (optionnel)</option>
                        <template x-for="sub in subcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- ÉTAPE 2: Lieu et Date --}}
            <div x-show="step === 2" x-transition>
                <div class="step-title">
                    <div class="step-title-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="step-title-text">
                        <h2>Où et quand ?</h2>
                        <p>Indiquez le lieu et la date souhaitée</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Ville</label>
                        <div class="city-autocomplete">
                            <input type="text" 
                                   x-model="form.city"
                                   @input.debounce.300ms="searchCities()"
                                   @focus="showCitySuggestions = citySuggestions.length > 0"
                                   @click.outside="showCitySuggestions = false"
                                   class="form-input"
                                   placeholder="Commencez à taper une ville..."
                                   autocomplete="off">
                            <div class="city-suggestions" x-show="showCitySuggestions && citySuggestions.length > 0" x-transition>
                                <template x-for="city in citySuggestions" :key="city.id || city.city">
                                    <div class="city-suggestion" @click="selectCity(city)">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="city-suggestion-text">
                                            <div class="city-suggestion-name" x-text="city.city || city.name"></div>
                                            <div class="city-suggestion-details" x-text="city.postal_code ? city.postal_code + ' - ' + (city.department || '') : ''"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code postal</label>
                        <input type="text" 
                               x-model="form.postal_code"
                               class="form-input"
                               placeholder="Ex: 75001">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse (optionnel)</label>
                    <input type="text" 
                           x-model="form.address"
                           class="form-input"
                           placeholder="Adresse complète si vous la connaissez">
                    <div class="form-hint">
                        <i class="fas fa-lock"></i>
                        L'adresse exacte ne sera visible que par le prestataire choisi
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date souhaitée</label>
                        <input type="date" 
                               x-model="form.start_date"
                               class="form-input"
                               :min="minDate">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date limite (optionnel)</label>
                        <input type="date" 
                               x-model="form.end_date"
                               class="form-input"
                               :min="form.start_date || minDate">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Niveau d'urgence</label>
                    <div class="urgency-grid">
                        <label class="urgency-card low" :class="{ 'selected': form.urgency === 'low' }">
                            <input type="radio" value="low" x-model="form.urgency">
                            <i class="fas fa-clock"></i>
                            <span>Pas pressé</span>
                        </label>
                        <label class="urgency-card normal" :class="{ 'selected': form.urgency === 'normal' }">
                            <input type="radio" value="normal" x-model="form.urgency">
                            <i class="fas fa-calendar-check"></i>
                            <span>Normal</span>
                        </label>
                        <label class="urgency-card urgent" :class="{ 'selected': form.urgency === 'urgent' }">
                            <input type="radio" value="urgent" x-model="form.urgency">
                            <i class="fas fa-bolt"></i>
                            <span>Urgent</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 3: Budget et Médias --}}
            <div x-show="step === 3" x-transition>
                <div class="step-title">
                    <div class="step-title-icon"><i class="fas fa-euro-sign"></i></div>
                    <div class="step-title-text">
                        <h2>Budget et photos</h2>
                        <p>Définissez votre budget et ajoutez des visuels</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Votre budget</label>
                    <div class="budget-type-grid">
                        <label class="budget-type-card" :class="{ 'selected': form.budget_type === 'defined' }">
                            <input type="radio" value="defined" x-model="form.budget_type">
                            <i class="fas fa-coins"></i>
                            <span>J'ai un budget</span>
                        </label>
                        <label class="budget-type-card" :class="{ 'selected': form.budget_type === 'negotiable' }">
                            <input type="radio" value="negotiable" x-model="form.budget_type">
                            <i class="fas fa-question-circle"></i>
                            <span>Je ne sais pas</span>
                        </label>
                    </div>
                </div>

                <div class="form-row" x-show="form.budget_type === 'defined'" x-transition>
                    <div class="form-group">
                        <label class="form-label">Budget minimum (€)</label>
                        <input type="number" 
                               x-model.number="form.budget_min"
                               class="form-input"
                               placeholder="0"
                               min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Budget maximum (€)</label>
                        <input type="number" 
                               x-model.number="form.budget_max"
                               class="form-input"
                               placeholder="1000"
                               min="0">
                    </div>
                </div>

                {{-- Upload Photos --}}
                <div class="upload-section">
                    <div class="upload-section-title">
                        <i class="fas fa-camera"></i>
                        Photos (optionnel - jusqu'à 5)
                    </div>
                    
                    {{-- Boutons d'action pour photos --}}
                    <div class="upload-actions">
                        <button type="button" class="upload-action-btn" @click="$refs.photoCameraInput.click()">
                            <i class="fas fa-camera"></i>
                            <span>Prendre une photo</span>
                        </button>
                        <button type="button" class="upload-action-btn" @click="$refs.photoGalleryInput.click()">
                            <i class="fas fa-images"></i>
                            <span>Galerie</span>
                        </button>
                    </div>
                    
                    {{-- Input caméra (capture) --}}
                    <input type="file" 
                           x-ref="photoCameraInput" 
                           @change="handlePhotoUpload($event)"
                           accept="image/*"
                           capture="environment"
                           class="hidden">
                    
                    {{-- Input galerie (sans capture) --}}
                    <input type="file" 
                           x-ref="photoGalleryInput" 
                           @change="handlePhotoUpload($event)"
                           accept="image/*"
                           multiple
                           class="hidden">
                    
                    {{-- Zone drag & drop (desktop) --}}
                    <div class="upload-zone desktop-only" 
                         @click="$refs.photoGalleryInput.click()"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handlePhotoDrop($event)"
                         :class="{ 'drag-over': dragOver }">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Ou glissez vos photos ici</p>
                        <small>JPG, PNG • Max 5 Mo par fichier</small>
                    </div>
                    
                    <div class="media-preview" x-show="photos.length > 0">
                        <template x-for="(photo, index) in photos" :key="index">
                            <div class="media-item">
                                <img :src="photo.preview" :alt="photo.name">
                                <button type="button" @click="removePhoto(index)" class="remove-media">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Upload Vidéo --}}
                <div class="upload-section">
                    <div class="upload-section-title">
                        <i class="fas fa-video"></i>
                        Vidéo (optionnel - 1 max)
                    </div>
                    
                    {{-- Boutons d'action pour vidéo --}}
                    <div class="upload-actions">
                        <button type="button" class="upload-action-btn" @click="$refs.videoCameraInput.click()" :disabled="video">
                            <i class="fas fa-video"></i>
                            <span>Filmer</span>
                        </button>
                        <button type="button" class="upload-action-btn" @click="$refs.videoGalleryInput.click()" :disabled="video">
                            <i class="fas fa-film"></i>
                            <span>Galerie</span>
                        </button>
                    </div>
                    
                    {{-- Input caméra vidéo (capture) --}}
                    <input type="file" 
                           x-ref="videoCameraInput" 
                           @change="handleVideoUpload($event)"
                           accept="video/*"
                           capture="environment"
                           class="hidden">
                    
                    {{-- Input galerie vidéo (sans capture) --}}
                    <input type="file" 
                           x-ref="videoGalleryInput" 
                           @change="handleVideoUpload($event)"
                           accept="video/*"
                           class="hidden">
                    
                    <div class="media-preview" x-show="video">
                        <div class="media-item video" x-show="video">
                            <i class="fas fa-play-circle"></i>
                            <span x-text="video ? video.name : ''"></span>
                            <button type="button" @click="removeVideo()" class="remove-media">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-hint" x-show="!video">
                        <i class="fas fa-info-circle"></i>
                        Une vidéo aide les prestataires à mieux comprendre votre besoin
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 4: Récapitulatif --}}
            <div x-show="step === 4" x-transition>
                <div class="step-title">
                    <div class="step-title-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-title-text">
                        <h2>Vérifiez et envoyez</h2>
                        <p>Relisez votre demande avant de l'envoyer</p>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-title">
                        <i class="fas fa-clipboard-list"></i>
                        Récapitulatif de votre demande
                    </div>
                    
                    <div class="summary-grid">
                        <div class="summary-item" style="grid-column: 1 / -1;">
                            <span class="summary-label">Titre</span>
                            <span class="summary-value" x-text="form.title || '-'"></span>
                        </div>
                        <div class="summary-item" style="grid-column: 1 / -1;">
                            <span class="summary-label">Description</span>
                            <span class="summary-value" style="white-space: pre-wrap;" x-text="form.description || '-'"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Catégorie</span>
                            <span class="summary-value" x-text="getCategoryName()"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Lieu</span>
                            <span class="summary-value" x-text="form.city + (form.postal_code ? ' (' + form.postal_code + ')' : '')"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Date souhaitée</span>
                            <span class="summary-value" x-text="form.start_date ? formatDate(form.start_date) : 'Flexible'"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Urgence</span>
                            <span class="summary-value" x-text="getUrgencyLabel()"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Budget</span>
                            <span class="summary-value" x-text="getBudgetDisplay()"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Photos / Vidéo</span>
                            <span class="summary-value" x-text="photos.length + ' photo(s)' + (video ? ', 1 vidéo' : '')"></span>
                        </div>
                    </div>
                </div>

                <div class="form-hint" style="text-align: center; margin-bottom: 1rem;">
                    <i class="fas fa-shield-alt"></i>
                    Votre demande sera envoyée aux prestataires qualifiés de votre zone
                </div>
            </div>

            {{-- Navigation --}}
            <div class="step-navigation">
                <button type="button" 
                        class="btn btn-outline"
                        @click="previousStep()"
                        x-show="step > 1">
                    <i class="fas fa-arrow-left"></i>
                    Précédent
                </button>
                <div x-show="step === 1"></div>

                <button type="button" 
                        class="btn btn-primary"
                        @click="nextStep()"
                        :disabled="!isStepValid()"
                        x-show="step < 4">
                    Suivant
                    <i class="fas fa-arrow-right"></i>
                </button>

                <button type="button" 
                        class="btn btn-success"
                        @click="submitForm()"
                        :disabled="saving || !isStepValid()"
                        x-show="step === 4">
                    <span x-show="!saving"><i class="fas fa-paper-plane"></i> Envoyer ma demande</span>
                    <span x-show="saving"><i class="fas fa-spinner spinner"></i> Envoi...</span>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('client-scripts')
<script>
function tenderWizard() {
    return {
        step: 1,
        saving: false,
        message: '',
        messageType: 'success',
        subcategories: [],
        dragOver: false,
        photos: [],
        video: null,
        
        // Autocomplétion ville
        citySuggestions: [],
        showCitySuggestions: false,
        
        form: {
            title: '',
            description: '',
            category_id: '',
            subcategory_id: '',
            city: '',
            postal_code: '',
            address: '',
            start_date: '',
            end_date: '',
            urgency: 'normal',
            budget_type: 'negotiable',
            budget_min: null,
            budget_max: null,
        },
        
        categories: @json($categories),
        
        get minDate() {
            return new Date().toISOString().split('T')[0];
        },
        
        // Recherche de villes
        async searchCities() {
            if (this.form.city.length < 2) {
                this.citySuggestions = [];
                this.showCitySuggestions = false;
                return;
            }
            
            try {
                const response = await fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(this.form.city)}&limit=10`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    this.citySuggestions = data.data;
                } else if (Array.isArray(data)) {
                    this.citySuggestions = data;
                } else {
                    this.citySuggestions = [];
                }
                
                this.showCitySuggestions = this.citySuggestions.length > 0;
            } catch (error) {
                console.error('Error searching cities:', error);
                this.citySuggestions = [];
            }
        },
        
        selectCity(city) {
            this.form.city = city.city || city.name;
            if (city.postal_code) {
                this.form.postal_code = city.postal_code;
            }
            this.showCitySuggestions = false;
            this.citySuggestions = [];
        },
        
        isStepValid() {
            switch (this.step) {
                case 1:
                    return this.form.title.length >= 5 
                        && this.form.description.length >= 50 
                        && this.form.category_id;
                case 2:
                    return this.form.city.length >= 2;
                case 3:
                    return true; // Budget et photos optionnels
                case 4:
                    return true;
                default:
                    return true;
            }
        },
        
        nextStep() {
            if (this.isStepValid() && this.step < 4) {
                this.step++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        previousStep() {
            if (this.step > 1) {
                this.step--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        async loadSubcategories() {
            this.form.subcategory_id = '';
            if (!this.form.category_id) {
                this.subcategories = [];
                return;
            }
            
            try {
                const response = await fetch(`/api/categories/${this.form.category_id}/subcategories`);
                this.subcategories = await response.json();
            } catch (error) {
                console.error('Error loading subcategories:', error);
                this.subcategories = [];
            }
        },
        
        getCategoryName() {
            const cat = this.categories.find(c => c.id == this.form.category_id);
            if (!cat) return '-';
            
            if (this.form.subcategory_id) {
                const sub = this.subcategories.find(s => s.id == this.form.subcategory_id);
                return sub ? `${cat.name} > ${sub.name}` : cat.name;
            }
            return cat.name;
        },
        
        getUrgencyLabel() {
            const labels = { low: 'Pas pressé', normal: 'Normal', urgent: 'Urgent' };
            return labels[this.form.urgency] || 'Normal';
        },
        
        getBudgetDisplay() {
            if (this.form.budget_type === 'negotiable') return 'À définir avec le prestataire';
            if (!this.form.budget_min && !this.form.budget_max) return 'Non défini';
            if (this.form.budget_min && this.form.budget_max) {
                return `${this.form.budget_min}€ - ${this.form.budget_max}€`;
            }
            if (this.form.budget_max) return `Jusqu'à ${this.form.budget_max}€`;
            return `À partir de ${this.form.budget_min}€`;
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
        },
        
        // Upload photos
        handlePhotoUpload(event) {
            const files = Array.from(event.target.files);
            this.addPhotos(files);
        },
        
        handlePhotoDrop(event) {
            this.dragOver = false;
            const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            this.addPhotos(files);
        },
        
        addPhotos(files) {
            files.forEach(file => {
                if (this.photos.length < 5 && file.size <= 5 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.photos.push({
                            file: file,
                            name: file.name,
                            preview: e.target.result
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        },
        
        removePhoto(index) {
            this.photos.splice(index, 1);
        },
        
        // Upload vidéo
        handleVideoUpload(event) {
            const file = event.target.files[0];
            if (file && file.size <= 50 * 1024 * 1024) {
                this.video = {
                    file: file,
                    name: file.name
                };
            }
        },
        
        removeVideo() {
            this.video = null;
        },
        
        async submitForm() {
            this.saving = true;
            this.message = '';
            
            const formData = new FormData();
            formData.append('title', this.form.title);
            formData.append('description', this.form.description);
            formData.append('categories[]', this.form.category_id);
            if (this.form.subcategory_id) {
                formData.append('categories[]', this.form.subcategory_id);
            }
            formData.append('city', this.form.city);
            formData.append('postal_code', this.form.postal_code || '');
            formData.append('address', this.form.address || '');
            formData.append('start_date', this.form.start_date || '');
            formData.append('end_date', this.form.end_date || '');
            formData.append('urgency', this.form.urgency);
            formData.append('budget_type', this.form.budget_type);
            
            if (this.form.budget_type === 'defined') {
                formData.append('budget_min', this.form.budget_min || '');
                formData.append('budget_max', this.form.budget_max || '');
            }
            
            // Ajouter les photos
            this.photos.forEach((photo, index) => {
                formData.append('photos[]', photo.file);
            });
            
            // Ajouter la vidéo
            if (this.video) {
                formData.append('video', this.video.file);
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    this.message = 'Erreur de sécurité. Veuillez recharger la page.';
                    this.messageType = 'error';
                    this.saving = false;
                    return;
                }
                
                const response = await fetch('/client/tenders/quick-create', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                    }
                });
                
                // Gérer les erreurs HTTP
                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = await response.json();
                        const errorMessages = errors.errors ? Object.values(errors.errors).flat().join(', ') : errors.message;
                        this.message = 'Erreur de validation: ' + errorMessages;
                    } else if (response.status === 401) {
                        this.message = 'Session expirée. Veuillez vous reconnecter.';
                        setTimeout(() => window.location.href = '/login', 2000);
                    } else if (response.status === 500) {
                        const errorData = await response.json();
                        this.message = errorData.message || 'Erreur serveur. Veuillez réessayer.';
                    } else {
                        this.message = 'Erreur ' + response.status + '. Veuillez réessayer.';
                    }
                    this.messageType = 'error';
                    this.saving = false;
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    this.message = 'Votre demande a été envoyée avec succès !';
                    this.messageType = 'success';
                    
                    setTimeout(() => {
                        window.location.href = result.redirect || '/client/tenders';
                    }, 2000);
                } else {
                    this.message = result.message || 'Une erreur est survenue';
                    this.messageType = 'error';
                }
            } catch (error) {
                console.error('Error:', error);
                this.message = 'Erreur de connexion. Veuillez réessayer.';
                this.messageType = 'error';
            }
            
            this.saving = false;
        },
    }
}
</script>
@endpush
