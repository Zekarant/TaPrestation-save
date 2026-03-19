@extends('layouts.admin-modern')

@section('title', 'Remboursements')

@section('content')
<div class="page-header">
    <h1 class="page-title">Remboursements</h1>
    <p class="page-subtitle">Gérer les demandes de remboursement</p>
</div>

<div class="card-base mb-4">
    <form method="GET" action="{{ route('admin.finance.refunds') }}" class="flex gap-2">
        <select name="status" class="form-control">
            <option value="">Tous</option>
            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>En attente</option>
            <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Traitée</option>
            <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejetée</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence ou utilisateur" class="form-control" />
        <button class="btn btn-primary">Filtrer</button>
    </form>
</div>

<div class="card-base overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr class="border-b">
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Utilisateur</th>
                <th class="py-3 px-4">Réf. transaction</th>
                <th class="py-3 px-4">Montant</th>
                <th class="py-3 px-4">Statut</th>
                <th class="py-3 px-4">Créé le</th>
                <th class="py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($refunds as $r)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">{{ $r->id }}</td>
                <td class="py-3 px-4">{{ $r->user_name ?? '—' }}</td>
                <td class="py-3 px-4">{{ $r->transaction_ref ?? '—' }}</td>
                <td class="py-3 px-4">{{ number_format($r->amount, 2, ',', ' ') }} €</td>
                <td class="py-3 px-4">{{ ucfirst($r->status) }}</td>
                <td class="py-3 px-4">{{ $r->created_at }}</td>
                <td class="py-3 px-4">
                    @if($r->status === 'pending')
                        <form method="POST" action="{{ route('admin.finance.refunds.process', $r->id) }}" style="display:inline" class="confirm-action">
                            @csrf
                            <input type="hidden" name="action" value="approve" />
                            <button class="btn btn-sm btn-success">Approuver</button>
                        </form>
                        <form method="POST" action="{{ route('admin.finance.refunds.process', $r->id) }}" style="display:inline" class="confirm-action">
                            @csrf
                            <input type="hidden" name="action" value="reject" />
                            <button class="btn btn-sm btn-danger">Rejeter</button>
                        </form>
                    @else
                        <span class="text-sm text-gray-500">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-8 text-center">Aucun remboursement trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $refunds->appends(request()->query())->links() }}</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.confirm-action').forEach(function(form){
        form.addEventListener('submit', function(e){
            if (!confirm('Confirmer cette action ?')) { e.preventDefault(); }
        });
    });
});
</script>
@endsection
