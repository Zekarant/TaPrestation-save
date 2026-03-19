@extends('layouts.admin-modern')

@section('title', 'Logs de connexion')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔐 Logs de connexion</h1>
    <p class="page-subtitle">Historique des tentatives de connexion</p>
</div>

<div class="card-base">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Utilisateur</th>
                    <th class="text-left py-3 px-4">IP</th>
                    <th class="text-left py-3 px-4">Statut</th>
                    <th class="text-left py-3 px-4">Navigateur</th>
                    <th class="text-left py-3 px-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loginLogs ?? [] as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            {{ $log->user ? $log->user->name : ($log->email ?? 'Inconnu') }}
                        </td>
                        <td class="py-3 px-4 font-mono text-sm">{{ $log->ip_address ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @if($log->successful ?? false)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Succès</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Échec</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm">{{ Str::limit($log->user_agent ?? '-', 30) }}</td>
                        <td class="py-3 px-4 text-sm">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            Aucun log de connexion disponible
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(method_exists($loginLogs ?? collect(), 'links'))
        <div class="mt-4">
            {{ $loginLogs->links() }}
        </div>
    @endif
</div>
@endsection
