@extends('layouts.app')

@section('title', 'Devenir Prestataire')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/become-provider.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="become-provider-page" x-data="becomeProviderForm()">
    <div class="bp-container">
        
        {{-- Header --}}
        <div class="bp-header">
            <div class="bp-logo">
                <div class="bp-logo-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <span class="bp-logo-text">TaPrestation</span>
            </div>
            <h1>Devenir Prestataire</h1>
            <p>Proposez vos services sur notre plateforme</p>
        </div>

        {{-- Carte Formulaire --}}
        <div class="bp-card">
            <form @submit.prevent="handleSubmit">
                @csrf

                {{-- Icône et titre --}}
                <div class="bp-step-header">
                    <div class="bp-step-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h2>Informations professionnelles</h2>
                    <p>Complétez votre profil prestataire</p>
                </div>

                {{-- Nom de l'enseigne --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Nom de l'enseigne <span class="required">*</span></label>
                        <input type="text" x-model="form.company_name" required placeholder="Nom de votre entreprise ou activité">
                    </div>
                </div>

                {{-- Téléphone --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Téléphone <span class="required">*</span></label>
                        <input type="tel" x-model="form.phone" required placeholder="Votre numéro de téléphone">
                    </div>
                </div>

                {{-- Adresse --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Adresse <span class="required">*</span></label>
                        <input type="text" x-model="form.city" @input="searchAddress" required placeholder="Entrez votre adresse">
                        <div class="bp-address-suggestions" x-show="addressSuggestions.length > 0" @click.outside="addressSuggestions = []">
                            <template x-for="suggestion in addressSuggestions" :key="suggestion.id">
                                <div class="bp-suggestion-item" @click="selectAddress(suggestion)">
                                    <i class="fas fa-map-pin"></i>
                                    <span x-text="suggestion.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Description courte du service <span class="required">*</span></label>
                        <textarea x-model="form.description" rows="3" required placeholder="Décrivez brièvement vos services..."></textarea>
                        <div class="bp-char-count">
                            <span x-text="form.description.length + '/500'"></span>
                        </div>
                    </div>
                </div>

                {{-- Catégorie principale --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Catégorie principale <span class="required">*</span></label>
                        <select x-model="form.category_id" @change="loadSubcategories" required>
                            <option value="">Sélectionnez une catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Sous-catégorie --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Sous-catégorie <span class="required">*</span></label>
                        <select x-model="form.subcategory_id" required :disabled="subcategories.length === 0">
                            <option value="">Sélectionnez d'abord une catégorie</option>
                            <template x-for="sub in subcategories" :key="sub.id">
                                <option :value="sub.id" x-text="sub.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Lien portfolio (optionnel) --}}
                <div class="bp-form-row">
                    <div class="bp-form-group">
                        <label>Lien vers un portfolio ou site (optionnel)</label>
                        <input type="url" x-model="form.portfolio_url" placeholder="https://votre-site.com">
                    </div>
                </div>

                {{-- Conditions --}}
                <div class="bp-terms">
                    <input type="checkbox" x-model="acceptedTerms" required>
                    <span>J'accepte les <a href="{{ route('terms') }}" target="_blank">conditions générales</a> et la <a href="{{ route('privacy') }}" target="_blank">politique de confidentialité</a></span>
                </div>

                {{-- Bouton submit --}}
                <div class="bp-navigation">
                    <a href="{{ route('client.dashboard') }}" class="bp-btn bp-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                    <button type="submit" class="bp-btn bp-btn-success" :disabled="!canSubmit || isSubmitting">
                        <i class="fas" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-rocket'"></i>
                        <span x-text="isSubmitting ? 'Création...' : 'Devenir prestataire'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function becomeProviderForm() {
    return {
        isSubmitting: false,
        acceptedTerms: false,
        addressSuggestions: [],
        subcategories: [],
        searchTimeout: null,

        form: {
            company_name: '',
            phone: '{{ $prefillData['phone'] ?? '' }}',
            city: '{{ $prefillData['city'] ?? '' }}',
            description: '',
            category_id: '',
            subcategory_id: '',
            portfolio_url: ''
        },

        // Données des catégories pour charger les sous-catégories
        categoriesData: @json($categories->mapWithKeys(fn($cat) => [$cat->id => $cat->children])),

        get canSubmit() {
            return this.acceptedTerms && 
                   this.form.company_name && 
                   this.form.phone &&
                   this.form.city &&
                   this.form.description &&
                   this.form.category_id &&
                   this.form.subcategory_id;
        },

        loadSubcategories() {
            this.form.subcategory_id = '';
            if (this.form.category_id && this.categoriesData[this.form.category_id]) {
                this.subcategories = this.categoriesData[this.form.category_id];
            } else {
                this.subcategories = [];
            }
        },

        searchAddress() {
            if (this.form.city.length < 3) {
                this.addressSuggestions = [];
                return;
            }

            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(this.form.city)}&limit=5`);
                    const data = await response.json();
                    this.addressSuggestions = data.features.map(f => ({
                        id: f.properties.id,
                        label: f.properties.label,
                        city: f.properties.city,
                        postcode: f.properties.postcode
                    }));
                } catch (e) {
                    this.addressSuggestions = [];
                }
            }, 300);
        },

        selectAddress(suggestion) {
            this.form.city = suggestion.label;
            this.addressSuggestions = [];
        },

        async handleSubmit() {
            if (!this.canSubmit || this.isSubmitting) return;
            
            this.isSubmitting = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('company_name', this.form.company_name);
            formData.append('phone', this.form.phone);
            formData.append('city', this.form.city);
            formData.append('description', this.form.description);
            formData.append('category_id', this.form.category_id);
            formData.append('subcategory_id', this.form.subcategory_id);
            if (this.form.portfolio_url) {
                formData.append('portfolio_url', this.form.portfolio_url);
            }

            try {
                const response = await fetch('{{ route('client.become-provider.finalize') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json().catch(() => ({}));
                
                if (response.ok && data.success) {
                    window.location.href = data.redirect || '{{ route('prestataire.dashboard') }}';
                } else {
                    alert(data.message || 'Une erreur est survenue');
                    this.isSubmitting = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endpush
