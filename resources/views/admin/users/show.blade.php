@extends('layouts.admin-modern')

@section('title', 'Détails de l\'utilisateur')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/admin-user-details.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!-- En-tête de la page -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-left">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3">Détails de l'utilisateur</h1>
                    <nav class="text-sm sm:text-base text-blue-100">
                        <a href="{{ route('administrateur.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                        <span class="mx-2">•</span>
                        <a href="{{ route('administrateur.users.index') }}" class="hover:text-white transition-colors">Utilisateurs</a>
                        <span class="mx-2">•</span>
                        <span class="text-white font-medium">{{ $user->name }}</span>
                    </nav>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button id="refreshBtn" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 sm:py-2.5 px-4 sm:px-5 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" title="Actualiser">
                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                    </button>
                    <a href="{{ route('administrateur.users.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 sm:py-2.5 px-4 sm:px-5 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Colonne principale -->
            <div class="lg:col-span-2">
                <!-- Informations de l'utilisateur -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 sm:px-6 py-4 border-b border-blue-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <h3 class="text-xl sm:text-2xl font-bold text-blue-800 flex items-center">
                                <i class="fas fa-user-circle mr-2"></i>Informations de l'utilisateur
                            </h3>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button id="toggleBlockBtn" class="{{ $user->is_blocked ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-red-600 hover:bg-red-700' }} text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm" title="{{ $user->is_blocked ? 'Débloquer cet utilisateur' : 'Bloquer cet utilisateur' }}">
                                    <i class="fas fa-{{ $user->is_blocked ? 'unlock' : 'lock' }} mr-2"></i>
                                    {{ $user->is_blocked ? 'Débloquer' : 'Bloquer' }}
                                </button>
                                <button id="deleteUserBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm" title="Supprimer définitivement cet utilisateur">
                                    <i class="fas fa-trash mr-2"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <!-- Profil utilisateur -->
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl
                                @if($user->role === 'administrateur') bg-gradient-to-br from-blue-500 to-blue-700
                                @elseif($user->role === 'prestataire') bg-gradient-to-br from-green-500 to-green-700
                                @else bg-gradient-to-br from-cyan-500 to-cyan-700 @endif" title="{{ $user->name }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center mb-2">
                                    <h4 class="text-xl font-bold text-gray-900 mr-2">{{ $user->name }}</h4>
                                    @if($user->email_verified_at)
                                        <i class="fas fa-check-circle text-green-500" title="Email vérifié"></i>
                                    @else
                                        <i class="fas fa-exclamation-circle text-yellow-500" title="Email non vérifié"></i>
                                    @endif
                                </div>
                                <p class="text-gray-600 mb-3 flex items-center">
                                    <i class="fas fa-envelope mr-2"></i>
                                    <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-800 transition-colors">{{ $user->email }}</a>
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_blocked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        <i class="fas fa-{{ $user->is_blocked ? 'times-circle' : 'check-circle' }} mr-1"></i>
                                        {{ $user->is_blocked ? 'Bloqué' : 'Actif' }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($user->role === 'administrateur') bg-blue-100 text-blue-800
                                        @elseif($user->role === 'prestataire') bg-green-100 text-green-800
                                        @else bg-cyan-100 text-cyan-800 @endif">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="bi bi-calendar-plus text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Date d'inscription</h4>
                                <p class="text-sm text-gray-600">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                                <span class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center">
                                    <i class="bi bi-clock-history text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Dernière mise à jour</h4>
                                <p class="text-sm text-gray-600">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                                <span class="text-xs text-gray-500">{{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    @if($user->phone)
                    <div class="mt-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="bi bi-telephone text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Téléphone</h4>
                                <p class="text-sm">
                                    <a href="tel:{{ $user->phone }}" class="text-blue-600 hover:text-blue-800 transition-colors">{{ $user->phone }}</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if($user->email_verified_at)
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="bi bi-patch-check text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Email vérifié le</h6>
                                    <p class="mb-0 text-muted">{{ $user->email_verified_at->format('d/m/Y à H:i') }}</p>
                                    <small class="text-muted">{{ $user->email_verified_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($user->role === 'prestataire' && $user->prestataire)
                        <div class="card mt-4 border-0 shadow-sm">
                            <div class="card-header bg-gradient-info text-white">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="bi bi-briefcase me-2"></i>
                                    Informations Prestataire
                                    @if($user->prestataire->approved_at)
                                        <span class="badge bg-success ms-auto">
                                            <i class="bi bi-patch-check me-1"></i>Vérifié
                                        </span>
                                    @else
                                        <span class="badge bg-warning ms-auto">
                                            <i class="bi bi-clock me-1"></i>En attente
                                        </span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="bi bi-building text-success me-2"></i>
                                            <div>
                                                <h6 class="mb-1 font-weight-bold">Secteur d'activité</h6>
                                                <p class="mb-0 fw-medium">{{ $user->prestataire->sector ?? 'Non défini' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="bi bi-geo-alt text-danger me-2"></i>
                                            <div>
                                                <h6 class="mb-1 font-weight-bold">Localisation</h6>
                                                <p class="mb-0 fw-medium">{{ $user->prestataire->location ?? 'Non définie' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <div class="info-item text-center">
                                            <i class="bi bi-list-task text-primary me-2"></i>
                                            <div>
                                                <h6 class="mb-1">Services</h6>
                                                <p class="mb-0 fs-4 fw-bold text-primary">{{ $user->prestataire->services->count() ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item text-center">
                                            <i class="bi bi-star text-warning me-2"></i>
                                            <div>
                                                <h6 class="mb-1">Note moyenne</h6>
                                                <p class="mb-0 fs-4 fw-bold text-warning">
                                                    {{ number_format($user->prestataire->average_rating ?? 0, 1) }}/5
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('administrateur.prestataires.show', $user->prestataire->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-person-badge"></i> Voir profil prestataire
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->role === 'client' && $user->client)
                        <div class="card mt-4 border-0 shadow-sm">
                            <div class="card-header bg-gradient-secondary text-white">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="bi bi-person-heart me-2"></i>
                                    Informations Client
                                    <span class="badge bg-light text-dark ms-auto">
                                        <i class="bi bi-calendar me-1"></i>
                                        Membre depuis {{ $user->created_at->format('M Y') }}
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="info-item text-center">
                                            <i class="bi bi-clipboard-check text-primary me-2"></i>
                                            <div>
                                                <h6 class="mb-1">Demandes</h6>
                                                <p class="mb-0 fs-4 fw-bold text-primary">{{ $user->client->bookings->count() ?? 0 }}</p>
                                                <small class="text-muted">Total créées</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item text-center">
                                            <i class="bi bi-star text-warning me-2"></i>
                                            <div>
                                                <h6 class="mb-1">Avis</h6>
                                                <p class="mb-0 fs-4 fw-bold text-warning">{{ $user->client->reviews->count() ?? 0 }}</p>
                                                <small class="text-muted">Avis laissés</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item text-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            <div>
                                                <h6 class="mb-1">Complétées</h6>
                                                <p class="mb-0 fs-4 fw-bold text-success">{{ $user->client->bookings->where('status', 'completed')->count() ?? 0 }}</p>
                                                <small class="text-muted">Demandes finalisées</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2 flex-wrap">
                                    @if($user->client)
                                    <a href="{{ route('administrateur.clients.show', $user->client->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Voir le profil complet
                                    </a>
                                    @endif
                                    {{-- TODO: Add admin routes for client requests and reviews --}}
                                    {{-- @if($user->client)
                                    <a href="{{ route('administrateur.requests.index', ['client_id' => $user->client->id]) }}" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-list"></i> Voir les demandes
                                    </a>
                                    @endif --}}
                                    {{-- @if($user->client && $user->client->reviews && $user->client->reviews->count() > 0)
                                    <a href="{{ route('administrateur.reviews.index', ['client_id' => $user->client->id]) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-star"></i> Voir les avis
                                    </a>
                                    @endif --}}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <!-- Activity Timeline Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="bi bi-activity mr-2"></i>
                        Activité récente
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="bi bi-clock text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Dernière connexion</h4>
                                <p class="text-sm text-gray-600">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->format('d/m/Y à H:i') }}
                                        <br><span class="text-blue-600 text-xs">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-amber-600">Jamais connecté</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($user->role === 'prestataire' && $user->prestataire)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                        <i class="bi bi-briefcase text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">Services proposés</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $user->prestataire->services()->count() }} service(s) au total
                                        <br><span class="text-green-600 text-xs">{{ $user->prestataire->services->where('is_active', true)->count() }} actif(s)</span>
                                    </p>
                                </div>
                            </div>
                            
                            @if($user->prestataire->services->count() > 0)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center">
                                        <i class="bi bi-calendar-check text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">Dernière mission</h4>
                                    <p class="text-sm text-gray-600">
                                        @if($user->prestataire->last_mission_at)
                                            {{ $user->prestataire->last_mission_at->diffForHumans() }}
                                        @else
                                            Aucune mission encore
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif
                        @endif
                        @if($user->role === 'client' && $user->client)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                        <i class="bi bi-clipboard-check text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">Demandes créées</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $user->client->bookings->count() }} réservation(s) au total
                                        <br><span class="text-cyan-600 text-xs">{{ $user->client->bookings->where('status', 'active')->count() }} en cours</span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <i class="bi bi-star text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">Avis et évaluations</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $user->client->reviews()->count() }} avis laissé(s)
                                        @if($user->client->reviews->count() > 0)
                                            <br><span class="text-yellow-600 text-xs">Note moyenne donnée: {{ number_format($user->client->reviews->avg('rating'), 1) }}/5</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('scripts')
    <script src="{{ asset('js/admin-user-details.js') }}"></script>
@endsection