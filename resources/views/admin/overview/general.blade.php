@extends('layouts.admin-modern')
@section('title', 'Vue détaillée - Généralités du site')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Vue détaillée - Généralités du site</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-2">Statistiques principales</h2>
            <ul>
                <li>Utilisateurs inscrits : <strong>{{ $stats['users'] ?? 0 }}</strong></li>
                <li>Prestataires actifs : <strong>{{ $stats['prestataires'] ?? 0 }}</strong></li>
                <li>Transactions totales : <strong>{{ $stats['transactions'] ?? 0 }}</strong></li>
                <li>Revenus générés : <strong>{{ $stats['revenue'] ?? 0 }} €</strong></li>
            </ul>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-2">Activité récente</h2>
            <ul>
                <li>Dernier utilisateur inscrit : <strong>{{ $stats['last_user'] ?? '-' }}</strong></li>
                <li>Dernière transaction : <strong>{{ $stats['last_transaction'] ?? '-' }}</strong></li>
                <li>Dernier prestataire vérifié : <strong>{{ $stats['last_verified'] ?? '-' }}</strong></li>
            </ul>
        </div>
    </div>
    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-2">Graphique d'évolution</h2>
        <canvas id="siteOverviewChart" height="120"></canvas>
    </div>
</div>
@endsection
@push('scripts')
<script>
    var ctx = document.getElementById('siteOverviewChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($stats['labels'] ?? []),
            datasets: [{
                label: 'Revenus',
                data: @json($stats['revenue_trend'] ?? []),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
