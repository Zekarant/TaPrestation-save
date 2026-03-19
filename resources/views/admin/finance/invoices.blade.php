@extends('layouts.admin-modern')

@section('title', 'Factures')

@section('content')
<div class="page-header">
    <h1 class="page-title">Factures</h1>
    <p class="page-subtitle">Générer, télécharger et envoyer des factures</p>
</div>

<div class="card-base mb-4">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('admin.finance.invoices') }}" class="btn btn-outline-secondary">Rafraîchir</a>
        </div>
        <div class="text-sm text-gray-600">Total: {{ $invoices->total() ?? 0 }}</div>
    </div>
</div>

<div class="card-base overflow-x-auto">
    <table class="w-full table-auto">
        <thead>
            <tr class="border-b">
                <th class="py-3 px-4">Numéro</th>
                <th class="py-3 px-4">Client</th>
                <th class="py-3 px-4">Montant</th>
                <th class="py-3 px-4">Statut</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $i)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">{{ $i->number ?? $i->id }}</td>
                <td class="py-3 px-4">{{ $i->user_name ?? '—' }}</td>
                <td class="py-3 px-4">{{ number_format($i->total ?? 0, 2, ',', ' ') }} €</td>
                <td class="py-3 px-4">{{ ucfirst($i->status ?? '—') }}</td>
                <td class="py-3 px-4">{{ $i->created_at }}</td>
                <td class="py-3 px-4">
                    <a href="{{ route('admin.finance.invoices.download', $i->id) }}" class="btn btn-sm btn-secondary">Télécharger</a>
                    <form method="POST" action="{{ route('admin.finance.invoices.send', $i->id) }}" style="display:inline" class="confirm-send">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Envoyer</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-8 text-center">Aucune facture trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $invoices->appends(request()->query())->links() }}</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.confirm-send').forEach(function(form){
        form.addEventListener('submit', function(e){
            if (!confirm('Confirmer l\'envoi de la facture par e-mail ?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection
