@extends('layouts.admin-modern')

@section('title', 'Analyse des utilisateurs')

@section('content')
<div class="page-header">
    <h1 class="page-title">👥 Analyse des utilisateurs</h1>
    <p class="page-subtitle">Statistiques sur les utilisateurs de la plateforme</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-blue-600">{{ $totalUsers ?? 0 }}</div>
        <div class="text-sm text-gray-500">Total utilisateurs</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-green-600">{{ $newUsersThisMonth ?? 0 }}</div>
        <div class="text-sm text-gray-500">Nouveaux ce mois</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-purple-600">{{ $activeUsers ?? 0 }}</div>
        <div class="text-sm text-gray-500">Actifs (30j)</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-orange-600">{{ number_format($retentionRate ?? 0, 1) }}%</div>
        <div class="text-sm text-gray-500">Taux de rétention</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card-base">
        <h3 class="font-semibold mb-4">Répartition par type</h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-user text-blue-600"></i>
                    <span>Clients</span>
                </div>
                <span class="font-semibold">{{ $clientCount ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-briefcase text-green-600"></i>
                    <span>Prestataires</span>
                </div>
                <span class="font-semibold">{{ $prestaCount ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-shield-alt text-purple-600"></i>
                    <span>Administrateurs</span>
                </div>
                <span class="font-semibold">{{ $adminCount ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    <div class="card-base">
        <h3 class="font-semibold mb-4">Inscriptions récentes (6 mois)</h3>
        <div class="space-y-3">
            @forelse($registrationsByMonth ?? [] as $month)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ $month['label'] }}</span>
                    <div class="flex items-center gap-4">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $month['percentage'] ?? 0 }}%"></div>
                        </div>
                        <span class="font-semibold w-16 text-right">{{ $month['count'] ?? 0 }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card-base mt-6">
    <h3 class="font-semibold mb-4">Derniers inscrits</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Nom</th>
                    <th class="text-left py-3 px-4">Email</th>
                    <th class="text-left py-3 px-4">Type</th>
                    <th class="text-left py-3 px-4">Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers ?? [] as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $user->name }}</td>
                        <td class="py-3 px-4">{{ $user->email }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ $user->role == 'prestataire' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($user->role ?? 'client') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500">Aucun utilisateur récent</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
