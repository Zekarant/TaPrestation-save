@extends('layouts.admin-modern')

@section('title', 'Transactions')

@section('content')
<div class="page-header">
    <h1 class="page-title">Transactions</h1>
    <p class="page-subtitle">Historique et gestion des paiements</p>
</div>

<div class="card-base mb-6">
    <form method="GET" action="{{ route('admin.finance.transactions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>En attente</option>
                <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Terminé</option>
                <option value="failed" {{ request('status')=='failed' ? 'selected' : '' }}>Échoué</option>
            </select>
        </div>

        <div>
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
                <option value="">Tous</option>
                <option value="booking" {{ request('type')=='booking' ? 'selected' : '' }}>Réservation</option>
                <option value="subscription" {{ request('type')=='subscription' ? 'selected' : '' }}>Abonnement</option>
            </select>
        </div>

        <div>
            <label class="form-label">Période</label>
            <div class="flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" />
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" />
            </div>
        </div>

        <div>
            <label class="form-label">Recherche</label>
            <div class="flex gap-2">
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="référence, utilisateur, email" />
                <button class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>
    <div class="mt-4 flex justify-between items-center">
        <div>
            <a href="{{ route('admin.finance.transactions.export', request()->all()) }}" class="btn btn-outline-secondary">Exporter CSV</a>
        </div>
        <div class="text-sm text-gray-600">Résultats : {{ $transactions->total() ?? 0 }}</div>
    </div>
</div>

<div class="card-base">
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-3 px-4">Référence</th>
                    <th class="py-3 px-4">Utilisateur</th>
                    <th class="py-3 px-4">Montant</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Statut</th>
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-mono text-sm">{{ $t->reference ?? '—' }}</td>
                    <td class="py-3 px-4">{{ $t->user_name ?? ($t->user_email ?? '—') }}</td>
                    <td class="py-3 px-4 font-semibold">{{ number_format($t->amount ?? 0, 2, ',', ' ') }} €</td>
                    <td class="py-3 px-4">{{ ucfirst($t->type ?? '—') }}</td>
                    <td class="py-3 px-4">
                        @php
                            $status = $t->status ?? '';
                        @endphp
                        <span class="badge badge-{{ $status == 'completed' ? 'success' : ($status=='pending' ? 'warning' : 'danger') }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ 
                        \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.finance.transactions.show', $t->id) }}" class="btn btn-sm btn-primary">Détails</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-500">Aucune transaction trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
</div>

@endsection
