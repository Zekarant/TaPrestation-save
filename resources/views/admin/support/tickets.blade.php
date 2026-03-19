@extends('layouts.admin-modern')

@section('title', 'Tickets de support')
@section('page-title', 'Tickets de support')

@section('content')
<div class="page-header">
    <h1 class="page-title">🎫 Tickets de support</h1>
    <p class="page-subtitle">Gérez les demandes d'assistance des utilisateurs</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-8">
    <div class="card-base stat-card primary">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Total tickets</p>
                <p class="text-value">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card warning">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Ouverts</p>
                <p class="text-value text-red-600">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-inbox text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card info">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">En cours</p>
                <p class="text-value text-yellow-600">{{ $stats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-spinner text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card success">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Résolus aujourd'hui</p>
                <p class="text-value text-green-600">{{ $stats['resolved_today'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card-base mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select name="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Tous</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
            <select name="priority" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Toutes</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Basse</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigné à</label>
            <select name="assigned_to" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Tous</option>
                @foreach($admins ?? [] as $admin)
                <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                    {{ $admin->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Numéro, sujet..." 
                class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tickets Table -->
<div class="card-base">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Ticket</th>
                    <th class="text-left py-3 px-4">Utilisateur</th>
                    <th class="text-left py-3 px-4">Sujet</th>
                    <th class="text-left py-3 px-4">Priorité</th>
                    <th class="text-left py-3 px-4">Statut</th>
                    <th class="text-left py-3 px-4">Assigné à</th>
                    <th class="text-left py-3 px-4">Date</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets ?? [] as $ticket)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <span class="font-mono text-sm text-blue-600">#{{ $ticket->ticket_number }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="font-medium">{{ $ticket->user_name }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->user_email }}</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="font-medium truncate max-w-xs">{{ $ticket->subject }}</p>
                        <p class="text-sm text-gray-500">{{ $ticket->category ?? 'Non catégorisé' }}</p>
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $priorityColors = [
                                'low' => 'gray',
                                'medium' => 'blue',
                                'high' => 'orange',
                                'urgent' => 'red'
                            ];
                            $color = $priorityColors[$ticket->priority] ?? 'gray';
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $statusColors = [
                                'open' => 'red',
                                'in_progress' => 'yellow',
                                'pending' => 'purple',
                                'resolved' => 'green',
                                'closed' => 'gray'
                            ];
                            $sColor = $statusColors[$ticket->status] ?? 'gray';
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $sColor }}-100 text-{{ $sColor }}-800">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        {{ $ticket->assigned_name ?? '-' }}
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-sm">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($ticket->created_at)->format('H:i') }}</p>
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.support.tickets.show', $ticket->id) }}" 
                            class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Aucun ticket trouvé</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($tickets) && $tickets->hasPages())
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endsection
