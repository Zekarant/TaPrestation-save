@extends('layouts.admin-modern')

@section('title', 'Commissions')

@section('content')
<div class="page-header">
    <h1 class="page-title">Commissions</h1>
    <p class="page-subtitle">Rapport des commissions perçues</p>
</div>

<div class="card-base mb-4">
    <form method="GET" action="{{ route('admin.finance.commissions') }}" class="flex gap-2">
        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control" />
        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control" />
        <button class="btn btn-primary">Filtrer</button>
    </form>
</div>

<div class="card-base">
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Montant total</th>
                    <th>Commission</th>
                    <th>Transactions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $c)
                <tr>
                    <td>{{ $c->date }}</td>
                    <td>{{ number_format($c->total_amount, 2, ',', ' ') }} €</td>
                    <td>{{ number_format($c->total_commission, 2, ',', ' ') }} €</td>
                    <td>{{ $c->transaction_count }}</td>
                </tr>
                @empty
                <tr><td colspan="4">Aucune donnée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <strong>Total transactions: </strong> {{ $totals['total_transactions'] ?? 0 }} —
        <strong>Total montant:</strong> {{ number_format($totals['total_amount'] ?? 0, 2, ',', ' ') }} € —
        <strong>Total commission:</strong> {{ number_format($totals['total_commission'] ?? 0, 2, ',', ' ') }} €
    </div>
</div>

@endsection
