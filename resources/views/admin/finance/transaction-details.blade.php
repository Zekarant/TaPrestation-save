@extends('layouts.admin-modern')

@section('title', 'Détails transaction')

@section('content')
<div class="page-header">
    <h1 class="page-title">Détails transaction</h1>
    <p class="page-subtitle">Transaction #{{ $transaction->id }}</p>
</div>

<div class="card-base">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4>Informations</h4>
            <div class="overflow-x-auto">
            <table class="table">
                <tr><th>Référence</th><td>{{ $transaction->reference ?? '—' }}</td></tr>
                <tr><th>Utilisateur</th><td>{{ $transaction->user_name ?? ($transaction->user_email ?? '—') }}</td></tr>
                <tr><th>Montant</th><td>{{ number_format($transaction->amount ?? 0, 2, ',', ' ') }} €</td></tr>
                <tr><th>Type</th><td>{{ ucfirst($transaction->type ?? '—') }}</td></tr>
                <tr><th>Statut</th><td>{{ ucfirst($transaction->status ?? '—') }}</td></tr>
                <tr><th>Date</th><td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td></tr>
            </table>
            </div>
        </div>

        <div>
            <h4>Actions</h4>
            <div class="space-y-2">
                <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline-secondary">Retour</a>
                @if($transaction->status === 'completed')
                    <form method="POST" action="#">
                        @csrf
                        <button type="button" class="btn btn-secondary" disabled>Marquer remboursé</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-6">

    <h4>Logs</h4>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>Date</th><th>Détail</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->message ?? json_encode($log) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Aucun log disponible.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
