@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register-form.css') }}">
<style>
.user-type-selector {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.user-type-option {
    flex: 1;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    background: white;
}

.user-type-option:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.user-type-option.selected {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.user-type-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #6b7280;
}

.user-type-option.selected .user-type-icon {
    color: #3b82f6;
}

.user-type-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.user-type-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.form-section {
    display: none;
}

.form-section.active {
    display: block;
}

/* Styles pour les sections d'importation de photo de profil */
.file-input-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-control-file {
    width: 100%;
    padding: 0.75rem;
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    background-color: #f9fafb;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    color: #6b7280;
}

.form-control-file:hover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.form-control-file:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.file-name-display {
    padding: 0.5rem;
    background-color: #f3f4f6;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #374151;
    min-height: 1.5rem;
    display: flex;
    align-items: center;
    border: 1px solid #e5e7eb;
}

.file-name-display:empty::before {
    content: "Aucun fichier sélectionné";
    color: #9ca3af;
    font-style: italic;
}

.file-input-wrapper .form-control-file::-webkit-file-upload-button {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    margin-right: 0.75rem;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.file-input-wrapper .form-control-file::-webkit-file-upload-button:hover {
    background: #2563eb;
}

.file-input-wrapper .form-control-file::file-selector-button {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    margin-right: 0.75rem;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.file-input-wrapper .form-control-file::file-selector-button:hover {
    background: #2563eb;
}

/* Profile picture styles */
.profile-picture-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.profile-picture-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #e5e7eb;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-picture-preview:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.profile-picture-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture-placeholder {
    font-size: 3rem;
    color: #9ca3af;
}

.profile-picture-text {
    font-size: 0.875rem;
    color: #6b7280;
    text-align: center;
}

.profile-picture-text strong {
    color: #3b82f6;
    text-decoration: underline;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

/* Custom styles for consistent button styling */
.submit-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 0.75rem 1rem;
    border: 1px solid transparent;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
    background-color: #3b82f6;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}

.submit-button:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.3);
}

.submit-button:disabled {
    background-color: #93c5fd;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.section-header {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.register-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.register-subtitle {
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.reassurance-banner {
    background-color: #dcfce7;
    color: #166534;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.login-link {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.875rem;
    color: #6b7280;
}

.login-link a {
    color: #3b82f6;
    text-decoration: underline;
    font-weight: 500;
}

.login-link a:hover {
    color: #2563eb;
}

.error-container {
    background-color: #fee2e2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.error-list {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0;
}

.error-list li {
    margin-bottom: 0.25rem;
}

.error-list li:last-child {
    margin-bottom: 0;
}

/* Password visibility toggle styles */
.password-container {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6b7280;
    background: none;
    border: none;
    padding: 0;
    font-size: 1rem;
}

.toggle-password:hover {
    color: #3b82f6;
}

.form-control {
    padding-right: 2.5rem;
}

/* Password requirements styles */
.password-requirements {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 0.5rem;
}

.password-requirements h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.password-requirements ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    font-size: 0.8125rem;
    color: #6b7280;
}

.password-requirements li {
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
}

.password-requirements li:last-child {
    margin-bottom: 0;
}

.password-requirements .requirement-icon {
    margin-right: 0.5rem;
    width: 1rem;
    height: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.password-requirements .requirement-met {
    color: #10b981;
}

.password-requirements .requirement-not-met {
    color: #9ca3af;
}

/* Autocomplete styles */
.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.autocomplete-item {
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.autocomplete-item:hover {
    background-color: #f3f4f6;
}

.autocomplete-item:last-child {
    border-bottom: none;
}
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Grid layout for register form and logo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Left side: Register form -->
            <div class="p-8 sm:p-10 md:p-12 lg:p-16 flex flex-col justify-center">
                <div>
                    <h2 class="register-title">
                        Créer votre compte
                    </h2>
                    <p class="register-subtitle">
                        Rejoignez TaPrestation en tant que client ou prestataire
                    </p>
                </div>

                @if ($errors->any())
                    <div class="error-container">
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Sélecteur de type d'utilisateur -->
                <div class="user-type-selector">
                    <div class="user-type-option" data-type="client">
                        <div class="user-type-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-type-title">Client</div>
                        <div class="user-type-description">Je cherche des services professionnels</div>
                    </div>

                    <div class="user-type-option" data-type="prestataire">
                        <div class="user-type-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="user-type-title">Prestataire</div>
                        <div class="user-type-description">Je propose mes services professionnels</div>
                    </div>
                </div>

                <!-- Formulaire Client -->
                <div id="client-form" class="form-section">
                    <form id="client-form-element" class="mt-8 space-y-6" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_type" value="client">

                        <!-- Profile picture section at the beginning -->
                        <div class="profile-picture-container">
                            <div class="profile-picture-preview" id="clientProfilePicturePreview">
                                <div class="profile-picture-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="profile-picture-text">
                                <strong id="clientProfilePictureText">Cliquez pour ajouter une photo de profil</strong>
                            </div>
                            <input type="file" id="client_profile_photo" name="client_profile_photo" class="form-control-file" accept="image/*" style="display: none;">
                        </div>

                        <!-- Section: Informations de connexion -->
                        <div class="mb-8">
                            <h3 class="section-header">Informations de connexion</h3>

                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="client_name" class="form-label">Nom complet</label>
                                    <input id="client_name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" class="form-control" placeholder="Votre nom complet">
                                </div>

                                <div class="form-group">
                                    <label for="client_email" class="form-label">E-mail</label>
                                    <input id="client_email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="form-control" placeholder="votre@email.com">
                                </div>

                                <div class="form-group">
                                    <label for="client_password" class="form-label">Mot de passe</label>
                                    <div class="password-container">
                                        <input id="client_password" name="password" type="password" autocomplete="new-password" required class="form-control" placeholder="Minimum 8 caractères">
                                        <button type="button" class="toggle-password" data-target="client_password">
                                            <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                        </button>
                                    </div>
                                    <div class="password-requirements">
                                        <h4>Le mot de passe doit contenir :</h4>
                                        <ul>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins 8 caractères</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins une lettre majuscule</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins une lettre minuscule</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins un chiffre</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="client_password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                    <div class="password-container">
                                        <input id="client_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-control" placeholder="Confirmez votre mot de passe">
                                        <button type="button" class="toggle-password" data-target="client_password_confirmation">
                                            <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Informations personnelles -->
                        <div>
                            <h3 class="section-header">Informations personnelles</h3>

                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="client_location" class="form-label">Adresse *</label>
                                    <input type="text" id="client_location" name="location" value="{{ old('location') }}" class="form-control" placeholder="Entrez votre adresse" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="submit-button">
                                <span class="button-text">S'inscrire en tant que Client</span>
                                <span class="button-loader"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Formulaire Prestataire -->
                <div id="prestataire-form" class="form-section">
                    <form id="prestataire-form-element" class="mt-8 space-y-6" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_type" value="prestataire">

                        <!-- Profile picture section at the beginning -->
                        <div class="profile-picture-container">
                            <div class="profile-picture-preview" id="prestataireProfilePicturePreview">
                                <div class="profile-picture-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="profile-picture-text">
                                <strong id="prestataireProfilePictureText">Cliquez pour ajouter une photo de profil</strong>
                            </div>
                            {{-- IMPORTANT : plus de required ici, champ caché --}}
                            <input
                                type="file"
                                id="prestataire_profile_photo"
                                name="prestataire_profile_photo"
                                class="form-control-file"
                                accept="image/*"
                                style="display: none;">
                        </div>

                        <!-- Section: Informations de connexion -->
                        <div class="mb-8">
                            <h3 class="section-header">Informations de connexion</h3>

                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="prestataire_name" class="form-label">Identifiant</label>
                                    <input id="prestataire_name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" class="form-control" placeholder="Votre identifiant">
                                </div>

                                <div class="form-group">
                                    <label for="prestataire_email" class="form-label">E-mail</label>
                                    <input id="prestataire_email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="form-control" placeholder="votre@email.com">
                                </div>

                                <div class="form-group">
                                    <label for="prestataire_password" class="form-label">Mot de passe</label>
                                    <div class="password-container">
                                        <input id="prestataire_password" name="password" type="password" autocomplete="new-password" required class="form-control" placeholder="Minimum 8 caractères">
                                        <button type="button" class="toggle-password" data-target="prestataire_password">
                                            <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                        </button>
                                    </div>
                                    <div class="password-requirements">
                                        <h4>Le mot de passe doit contenir :</h4>
                                        <ul>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins 8 caractères</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins une lettre majuscule</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins une lettre minuscule</span>
                                            </li>
                                            <li>
                                                <span class="requirement-icon requirement-not-met">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span>Au moins un chiffre</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="prestataire_password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                    <div class="password-container">
                                        <input id="prestataire_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-control" placeholder="Confirmez votre mot de passe">
                                        <button type="button" class="toggle-password" data-target="prestataire_password_confirmation">
                                            <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Informations professionnelles -->
                        <div>
                            <h3 class="section-header">Informations professionnelles</h3>

                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="company_name" class="form-label">Nom de l'enseigne</label>
                                    <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}" class="form-control" placeholder="Nom de votre entreprise">
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" class="form-control" placeholder="Votre numéro de téléphone">
                                </div>

                                <div class="form-group">
                                    <label for="prestataire_location" class="form-label">Adresse *</label>
                                    <input type="text" id="prestataire_location" name="city" value="{{ old('city') }}" class="form-control" placeholder="Entrez votre adresse" required>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">Description courte du service</label>
                                    <textarea id="description" name="description" rows="3" class="form-control" placeholder="Décrivez brièvement vos services...">{{ old('description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="category_id" class="form-label">Catégorie principale *</label>
                                    <select id="category_id" name="category_id" class="form-control" required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="subcategory_id" class="form-label">Sous-catégorie *</label>
                                    <select id="subcategory_id" name="subcategory_id" class="form-control" required>
                                        <option value="">Sélectionnez d'abord une catégorie</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="portfolio_url" class="form-label">Lien vers un portfolio ou site (optionnel)</label>
                                    <input id="portfolio_url" name="portfolio_url" type="url" value="{{ old('portfolio_url') }}" class="form-control" placeholder="https://votre-site.com">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="submit-button">
                                <span class="button-text">S'inscrire en tant que Prestataire</span>
                                <span class="button-loader"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="login-link">
                    Vous avez déjà un compte? <a href="{{ route('login') }}">Connectez-vous ici</a>
                </div>
            </div>

            <!-- Right side: Logo/Visual element -->
            <div class="hidden lg:flex items-center justify-center bg-gradient-to-br from-blue-50 to-white p-8 md:p-12 lg:p-16">
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-3xl p-8 w-full max-w-md flex items-center justify-center aspect-square relative">
                    <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-blue-200/20 to-transparent blur-xl"></div>

                    <div class="text-center relative z-10">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 mx-auto flex items-center justify-center mb-6">
                            <div class="absolute w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 opacity-80"></div>
                            <div class="relative w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform duration-300">
                                <i class="fas fa-handshake text-white text-4xl sm:text-5xl md:text-6xl"></i>
                            </div>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">TaPrestation</h3>
                        <p class="text-sm sm:text-base text-gray-600 max-w-xs mx-auto">
                            Rejoignez notre communauté de professionnels et de clients
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userTypeOptions = document.querySelectorAll('.user-type-option');
    const clientFormWrapper = document.getElementById('client-form');
    const prestataireFormWrapper = document.getElementById('prestataire-form');
    const clientForm = document.getElementById('client-form-element');
    const prestataireForm = document.getElementById('prestataire-form-element');

    const clientProfilePreview = document.getElementById('clientProfilePicturePreview');
    const clientProfileInput = document.getElementById('client_profile_photo');
    const clientProfileText = document.getElementById('clientProfilePictureText');

    const prestataireProfilePreview = document.getElementById('prestataireProfilePicturePreview');
    const prestataireProfileInput = document.getElementById('prestataire_profile_photo');
    const prestataireProfileText = document.getElementById('prestataireProfilePictureText');

    const clientLocationInput = document.getElementById('client_location');
    const prestataireLocationInput = document.getElementById('prestataire_location');

    const initialUserType = "{{ old('user_type', request('type', 'prestataire')) }}";

    function disableFormFields(wrapper) {
        if (!wrapper) return;
        const elements = wrapper.querySelectorAll('input, select, textarea, button');
        elements.forEach(el => {
            if (el.name !== '_token') {
                el.disabled = true;
                el.removeAttribute('required');
            }
        });
    }

    function enableFormFields(wrapper, type) {
        if (!wrapper) return;
        const elements = wrapper.querySelectorAll('input, select, textarea, button');
        elements.forEach(el => {
            if (el.name === '_token') return;
            el.disabled = false;

            if (type === 'client') {
                const requiredIds = [
                    'client_name',
                    'client_email',
                    'client_password',
                    'client_password_confirmation',
                    'client_location'
                ];
                if (requiredIds.includes(el.id)) {
                    el.setAttribute('required', 'required');
                }
            }

            if (type === 'prestataire') {
                // ATTENTION : on ne met PAS prestataire_profile_photo ici
                const requiredIds = [
                    'prestataire_name',
                    'prestataire_email',
                    'prestataire_password',
                    'prestataire_password_confirmation',
                    'company_name',
                    'phone',
                    'prestataire_location',
                    'category_id',
                    'subcategory_id'
                ];
                if (requiredIds.includes(el.id)) {
                    el.setAttribute('required', 'required');
                }
            }
        });
    }

    function updatePasswordRequirements(password, formType) {
        const passwordInputId = formType === 'client' ? 'client_password' : 'prestataire_password';
        const passwordInput = document.getElementById(passwordInputId);
        if (!passwordInput) return;

        const group = passwordInput.closest('.form-group');
        if (!group) return;

        const requirements = group.querySelector('.password-requirements');
        if (!requirements) return;

        const items = requirements.querySelectorAll('li');

        const checks = [
            password.length >= 8,
            /[A-Z]/.test(password),
            /[a-z]/.test(password),
            /[0-9]/.test(password)
        ];

        items.forEach((li, index) => {
            const iconSpan = li.querySelector('.requirement-icon');
            const icon = iconSpan ? iconSpan.querySelector('i') : null;
            const ok = checks[index];

            if (ok) {
                iconSpan.classList.add('requirement-met');
                iconSpan.classList.remove('requirement-not-met');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-check');
                }
            } else {
                iconSpan.classList.remove('requirement-met');
                iconSpan.classList.add('requirement-not-met');
                if (icon) {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-times');
                }
            }
        });
    }

    function createAutocompleteDropdown(inputElement) {
        const existingDropdown = inputElement.parentNode.querySelector('.autocomplete-dropdown');
        if (existingDropdown) {
            existingDropdown.remove();
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown';

        inputElement.parentNode.style.position = 'relative';
        inputElement.parentNode.appendChild(dropdown);

        return dropdown;
    }

    function displaySuggestions(suggestions, dropdown) {
        dropdown.innerHTML = '';

        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.textContent = suggestion.text || suggestion.city;

            item.addEventListener('click', function () {
                const input = dropdown.parentNode.querySelector('input');
                if (input) {
                    input.value = suggestion.text || suggestion.city;
                }
                dropdown.style.display = 'none';
            });

            dropdown.appendChild(item);
        });

        dropdown.style.display = suggestions.length ? 'block' : 'none';
    }

    function fetchAutocompleteSuggestions(query, dropdown) {
        fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(query)}&limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    displaySuggestions(data.data, dropdown);
                } else {
                    dropdown.style.display = 'none';
                }
            })
            .catch(() => {
                dropdown.style.display = 'none';
            });
    }

    function refreshCSRFToken() {
        fetch('/csrf-token')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = data.csrf_token;
                });
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }
            })
            .catch(error => {
                console.error('Error refreshing CSRF token:', error);
            });
    }

    // Photo profil client
    if (clientProfilePreview && clientProfileInput) {
        clientProfilePreview.addEventListener('click', function () {
            clientProfileInput.click();
        });

        clientProfileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    clientProfilePreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    clientProfilePreview.appendChild(img);
                    clientProfileText.innerHTML = '<strong>Photo sélectionnée</strong>';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Photo profil prestataire
    if (prestataireProfilePreview && prestataireProfileInput) {
        prestataireProfilePreview.addEventListener('click', function () {
            prestataireProfileInput.click();
        });

        prestataireProfileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    prestataireProfilePreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    prestataireProfilePreview.appendChild(img);
                    prestataireProfileText.innerHTML = '<strong>Photo sélectionnée</strong>';
                    prestataireProfilePreview.classList.remove('ring-2', 'ring-red-400');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Catégories & sous-catégories
    function loadMainCategories() {
        const categorySelect = document.getElementById('category_id');
        if (!categorySelect) return;

        // Utilise la route Laravel déjà définie (JSON attendu)
        fetch('{{ route('categories.main') }}', {
            headers: { 'Accept': 'application/json' }
        })
            .then(response => response.json())
            .then(data => {
                const categories = data || [];
                categorySelect.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });

                const oldCategoryId = "{{ old('category_id') }}";
                if (oldCategoryId) {
                    categorySelect.value = oldCategoryId;
                    categorySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
            });
    }

    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');

    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            if (!categoryId) {
                subcategorySelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>';
                subcategorySelect.disabled = true;
                return;
            }

            fetch(`/api/categories/${categoryId}/subcategories`)
                .then(response => response.json())
                .then(data => {
                    const subcategories = data || [];
                    subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
                    subcategories.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                    subcategorySelect.disabled = false;

                    const oldSubcategoryId = "{{ old('subcategory_id') }}";
                    if (oldSubcategoryId) {
                        subcategorySelect.value = oldSubcategoryId;
                    }
                })
                .catch(error => {
                    console.error('Error loading subcategories:', error);
                    subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    subcategorySelect.disabled = true;
                });
        });

        if (categorySelect.value) {
            setTimeout(() => {
                categorySelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }

    // Désactiver les deux formulaires au départ
    disableFormFields(clientFormWrapper);
    disableFormFields(prestataireFormWrapper);

    // Sélection type utilisateur
    userTypeOptions.forEach(option => {
        option.addEventListener('click', function () {
            userTypeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            const type = this.dataset.type;

            clientFormWrapper.classList.remove('active');
            prestataireFormWrapper.classList.remove('active');
            disableFormFields(clientFormWrapper);
            disableFormFields(prestataireFormWrapper);

            if (type === 'client') {
                clientFormWrapper.classList.add('active');
                enableFormFields(clientFormWrapper, 'client');
                const hidden = clientFormWrapper.querySelector('input[name="user_type"]');
                if (hidden) hidden.value = 'client';
            } else {
                prestataireFormWrapper.classList.add('active');
                enableFormFields(prestataireFormWrapper, 'prestataire');
                const hidden = prestataireFormWrapper.querySelector('input[name="user_type"]');
                if (hidden) hidden.value = 'prestataire';
                loadMainCategories();
            }
        });
    });

    // Sélection initiale selon old()/query
    if (initialUserType === 'client' || initialUserType === 'prestataire') {
        const defaultOption = document.querySelector(`.user-type-option[data-type="${initialUserType}"]`);
        if (defaultOption) defaultOption.click();
    }

    // Validation type cohérent au submit + loader bouton
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const selectedOption = document.querySelector('.user-type-option.selected');
            const submitButton = form.querySelector('button[type="submit"]');

            if (!selectedOption) {
                e.preventDefault();
                alert('Veuillez sélectionner un type de compte (Client ou Prestataire) avant de continuer.');
                return false;
            }

            const selectedType = selectedOption.dataset.type;
            const currentFormType = form.id === 'client-form-element' ? 'client' : 'prestataire';

            if (selectedType !== currentFormType) {
                e.preventDefault();
                return false;
            }

            // Validation personnalisée : photo obligatoire pour prestataire,
            // sans utiliser "required" sur un input caché.
            if (currentFormType === 'prestataire') {
                if (prestataireProfileInput && (!prestataireProfileInput.files || prestataireProfileInput.files.length === 0)) {
                    e.preventDefault();
                    alert('Merci d\'ajouter une photo de profil pour votre compte prestataire.');
                    if (prestataireProfilePreview) {
                        prestataireProfilePreview.classList.add('ring-2', 'ring-red-400');
                    }
                    return false;
                }
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Inscription en cours...';

                setTimeout(() => {
                    submitButton.disabled = false;
                    if (currentFormType === 'client') {
                        submitButton.innerHTML = 'S\'inscrire en tant que Client';
                    } else {
                        submitButton.innerHTML = 'S\'inscrire en tant que Prestataire';
                    }
                }, 10000);
            }

            return true;
        });
    });

    // Password requirements live
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function () {
            const password = this.value;
            const formType = this.id.includes('client') ? 'client' : 'prestataire';
            updatePasswordRequirements(password, formType);
        });

        if (input.value) {
            const formType = input.id.includes('client') ? 'client' : 'prestataire';
            updatePasswordRequirements(input.value, formType);
        }
    });

    // Autocomplétion adresses
    if (clientLocationInput) {
        const clientDropdown = createAutocompleteDropdown(clientLocationInput);

        clientLocationInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchAutocompleteSuggestions(query, clientDropdown);
            } else {
                clientDropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!clientLocationInput.contains(e.target) && !clientDropdown.contains(e.target)) {
                clientDropdown.style.display = 'none';
            }
        });
    }

    if (prestataireLocationInput) {
        const prestataireDropdown = createAutocompleteDropdown(prestataireLocationInput);

        prestataireLocationInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchAutocompleteSuggestions(query, prestataireDropdown);
            } else {
                prestataireDropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!prestataireLocationInput.contains(e.target) && !prestataireDropdown.contains(e.target)) {
                prestataireDropdown.style.display = 'none';
            }
        });
    }

    // CSRF auto-refresh
    setInterval(refreshCSRFToken, 30 * 60 * 1000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshCSRFToken();
        }
    });

    // Reset state des boutons submit
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        button.setAttribute('data-original-text', button.innerHTML);
        button.disabled = false;
    });

    // Toggle visibilité mot de passe
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (!passwordInput) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endsection
