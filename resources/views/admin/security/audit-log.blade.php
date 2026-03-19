@extends('layouts.admin-modern')

@section('title', 'Journal d\'audit')

@section('content')
<div class="page-header">
    <h1 class="page-title">📜 Journal d'audit</h1>
    <p class="page-subtitle">Historique des actions administratives</p>
</div>

<div class="card-base mb-6">
    <form action="{{ route('admin.security.audit-log') }}" method="GET" class="flex flex-wrap gap-4">
        <select name="action_type" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Toutes les actions</option>
            <option value="create" {{ request('action_type') == 'create' ? 'selected' : '' }}>Création</option>
            <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>Modification</option>
            <option value="delete" {{ request('action_type') == 'delete' ? 'selected' : '' }}>Suppression</option>
            <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>Connexion</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="Date début">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="Date fin">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-filter mr-2"></i> Filtrer
        </button>
    </form>
</div>

<div class="card-base">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Date</th>
                    <th class="text-left py-3 px-4">Utilisateur</th>
                    <th class="text-left py-3 px-4">Action</th>
                    <th class="text-left py-3 px-4">Description</th>
                    <th class="text-left py-3 px-4">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs ?? [] as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm">
                            {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            {{ $log->user ? $log->user->name : 'Système' }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $actionColor = match($log->action_type ?? '') {
                                    'create' => 'green',
                                    'update' => 'blue',
                                    'delete' => 'red',
                                    'login' => 'purple',
                                    default => 'gray'
                                };
                            @endphp
                            <span class="px-2 py-1 bg-{{ $actionColor }}-100 text-{{ $actionColor }}-800 rounded-full text-xs">
                                {{ $log->action_type ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm">{{ Str::limit($log->description ?? '-', 60) }}</td>
                        <td class="py-3 px-4 font-mono text-sm">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            Aucune entrée dans le journal d'audit
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(method_exists($auditLogs ?? collect(), 'links'))
        <div class="mt-4">
            {{ $auditLogs->links() }}
        </div>
    @endif
</div>
@endsection
