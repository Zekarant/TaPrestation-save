@extends('layouts.admin-modern')

@section('title', 'Versements prestataires')

@section('content')
<div class="page-header">
    <h1 class="page-title">Versements prestataires</h1>
    <p class="page-subtitle">Créer et traiter les versements aux prestataires</p>
</div>

<div class="card-base mb-4">
    <div class="flex justify-between items-center">
        <div class="text-sm text-gray-600">Total versements: {{ $payouts->total() ?? 0 }}</div>
        <div>
            <a href="{{ route('admin.finance.payouts') }}" class="btn btn-outline-secondary">Rafraîchir</a>
        </div>
    </div>
</div>

<div class="card-base mb-6">
    <h3 class="mb-4">Créer un versement manuel</h3>
    <form method="POST" action="{{ route('admin.finance.payouts.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        <div>
            <label class="form-label">Prestataire</label>
            <select name="prestataire_id" class="form-control" id="prestataire-select" required>
                <option value="">Choisir</option>
                @foreach($prestatairesWithBalance as $pr)
                    <option value="{{ $pr->id }}" data-balance="{{ $pr->balance }}">{{ $pr->name }} — {{ number_format($pr->balance,2,',',' ') }} €</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Montant</label>
            <input type="number" step="0.01" name="amount" class="form-control" required />
        </div>
        <div class="flex items-end">
            <button class="btn btn-primary">Créer versement</button>
        </div>
    </form>
</div>

<div class="card-base overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr class="border-b">
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Prestataire</th>
                <th class="py-3 px-4">Montant</th>
                <th class="py-3 px-4">Statut</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payouts as $p)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">{{ $p->id }}</td>
                <td class="py-3 px-4">{{ $p->prestataire_name ?? '—' }}</td>
                <td class="py-3 px-4">{{ number_format($p->amount, 2, ',', ' ') }} €</td>
                <td class="py-3 px-4">{{ ucfirst($p->status) }}</td>
                <td class="py-3 px-4">{{ $p->created_at }}</td>
                <td class="py-3 px-4">
                    @if($p->status === 'pending')
                        <form method="POST" action="{{ route('admin.finance.payouts.process', $p->id) }}" style="display:inline" class="confirm-action">
                            @csrf
                            <input type="hidden" name="action" value="complete" />
                            <button class="btn btn-sm btn-success">Marquer complété</button>
                        </form>
                        <form method="POST" action="{{ route('admin.finance.payouts.process', $p->id) }}" style="display:inline" class="confirm-action">
                            @csrf
                            <input type="hidden" name="action" value="cancel" />
                            <button class="btn btn-sm btn-danger">Annuler</button>
                        </form>
                    @else
                        <span class="text-sm text-gray-500">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6">Aucun versement.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $payouts->appends(request()->query())->links() }}</div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Confirmations pour actions critiques
    document.querySelectorAll('form.confirm-action').forEach(function(form){
        form.addEventListener('submit', function(e){
            if (!confirm('Confirmer cette action ?')) { e.preventDefault(); }
        });
    });

    // Préremplir le montant avec le solde du prestataire sélectionné
    var select = document.getElementById('prestataire-select');
    var amountInput = document.querySelector('input[name="amount"]');
    if (select && amountInput) {
        select.addEventListener('change', function(){
            var opt = select.options[select.selectedIndex];
            var balance = opt ? opt.getAttribute('data-balance') : null;
            if (balance !== null && balance !== '') {
                amountInput.value = parseFloat(balance).toFixed(2);
            }
        });
    }
});
</script>
@endsection
