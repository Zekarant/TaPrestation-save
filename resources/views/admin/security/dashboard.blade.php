@extends('layouts.admin-modern')

@section('title', 'Sécurité')
@section('page-title', 'Sécurité')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔒 Tableau de bord sécurité</h1>
    <p class="page-subtitle">Surveillance et protection de la plateforme</p>
</div>

<!-- Security Stats -->
<div class="stats-grid mb-8">
    <div class="card-base stat-card success">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Connexions réussies</p>
                <p class="text-value text-green-600">{{ $stats['successful_logins_today'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Aujourd'hui</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-sign-in-alt text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card warning">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Connexions échouées</p>
                <p class="text-value text-red-600">{{ $stats['failed_logins_today'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Aujourd'hui</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card primary">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Sessions actives</p>
                <p class="text-value text-blue-600">{{ $stats['active_sessions'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card info">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">IPs bloquées</p>
                <p class="text-value text-cyan-600">{{ $stats['blocked_ips'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-ban text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <a href="{{ route('admin.security.change-password') }}" class="card-base hover:shadow-lg transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition">
                <i class="fas fa-key text-gray-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Mot de passe</p>
                <p class="text-sm text-gray-500">Changer le mot de passe</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.security.login-logs') }}" class="card-base hover:shadow-lg transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
                <i class="fas fa-history text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Logs connexion</p>
                <p class="text-sm text-gray-500">Historique des connexions</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.security.blocked-ips') }}" class="card-base hover:shadow-lg transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition">
                <i class="fas fa-shield-alt text-red-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">IPs bloquées</p>
                <p class="text-sm text-gray-500">Gérer les blocages</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.security.sessions') }}" class="card-base hover:shadow-lg transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Sessions</p>
                <p class="text-sm text-gray-500">Sessions actives</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.security.audit-log') }}" class="card-base hover:shadow-lg transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition">
                <i class="fas fa-clipboard-list text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Audit</p>
                <p class="text-sm text-gray-500">Journal d'activité</p>
            </div>
        </div>
    </a>
</div>

<!-- Recent Login Attempts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-check-circle text-green-600 mr-2"></i> Connexions récentes
        </h3>
        <div class="space-y-3">
            @forelse(($recentLogins ?? []) as $login)
            <div class="flex items-center justify-between py-2 border-b">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $login->email ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $login->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>
                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($login->created_at)->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">Aucune connexion récente</p>
            @endforelse
        </div>
    </div>

    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i> Tentatives échouées
        </h3>
        <div class="space-y-3">
            @forelse(($failedLogins ?? []) as $login)
            <div class="flex items-center justify-between py-2 border-b">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $login->email ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $login->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>
                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($login->created_at)->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">Aucune tentative échouée</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Security Alerts -->
<div class="card-base">
    <h3 class="text-lg font-bold text-gray-900 mb-4">
        <i class="fas fa-bell text-yellow-600 mr-2"></i> Alertes de sécurité
    </h3>
    @if(count($alerts ?? []) > 0)
    <div class="space-y-3">
        @foreach($alerts as $alert)
        <div class="p-4 rounded-lg bg-{{ $alert['type'] ?? 'yellow' }}-50 border border-{{ $alert['type'] ?? 'yellow' }}-200">
            <div class="flex items-start gap-3">
                <i class="fas fa-{{ $alert['icon'] ?? 'exclamation-circle' }} text-{{ $alert['type'] ?? 'yellow' }}-600 mt-1"></i>
                <div>
                    <p class="font-medium text-gray-900">{{ $alert['title'] ?? '' }}</p>
                    <p class="text-sm text-gray-600">{{ $alert['message'] ?? '' }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
        </div>
        <p class="text-gray-600">Aucune alerte de sécurité</p>
        <p class="text-sm text-gray-500">Votre plateforme est sécurisée</p>
    </div>
    @endif
</div>
@endsection
