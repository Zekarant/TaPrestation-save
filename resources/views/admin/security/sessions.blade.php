@extends('layouts.admin-modern')

@section('title', 'Sessions actives')

@section('content')
<div class="page-header">
    <h1 class="page-title">📱 Sessions actives</h1>
    <p class="page-subtitle">Visualisez les sessions utilisateur actives</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-semibold">Terminer toutes les sessions</h3>
            <p class="text-sm text-gray-500">Déconnecte tous les utilisateurs (sauf vous)</p>
        </div>
        <form action="{{ route('admin.security.sessions.terminate-all') }}" method="POST" onsubmit="return confirm('Terminer TOUTES les sessions ?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-power-off mr-2"></i> Terminer tout
            </button>
        </form>
    </div>
</div>

<div class="card-base">
    <h3 class="font-semibold mb-4">Sessions actives ({{ count($sessions ?? []) }})</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Utilisateur</th>
                    <th class="text-left py-3 px-4">IP</th>
                    <th class="text-left py-3 px-4">Navigateur</th>
                    <th class="text-left py-3 px-4">Dernière activité</th>
                    <th class="text-right py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions ?? [] as $session)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            @if($session->user)
                                {{ $session->user->name }}
                            @else
                                <span class="text-gray-400">Invité</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-sm">{{ $session->ip_address ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ Str::limit($session->user_agent ?? '-', 40) }}</td>
                        <td class="py-3 px-4 text-sm">
                            {{ $session->last_activity ? \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.security.sessions.terminate', $session->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-times"></i> Terminer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            Aucune session active
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
