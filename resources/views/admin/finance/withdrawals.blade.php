@extends('layouts.admin-modern')

@section('title', 'Retraits')

@section('content')
<div class="page-header">
    <h1 class="page-title">Retraits</h1>
    <p class="page-subtitle">Gestion des demandes de retrait</p>
</div>

<div class="card-base mb-4">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div class="text-sm text-gray-600">Total demandes: {{ $withdrawals->total() ?? 0 }}</div>
        <div class="flex gap-2">
            <button id="bulkApprove" class="btn btn-sm btn-success">Approuver sélection</button>
            <button id="bulkReject" class="btn btn-sm btn-danger">Rejeter sélection</button>
        </div>
    </div>
</div>

<form id="bulkForm" method="POST" action="{{ route('admin.finance.withdrawals.bulk') }}">
    @csrf
    <input type="hidden" name="action" id="bulkAction" />
    <div class="overflow-x-auto">
    <table class="table card-base">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" /></th>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $w)
            <tr>
                <td><input type="checkbox" name="withdrawal_ids[]" value="{{ $w->id }}" /></td>
                <td>{{ $w->id }}</td>
                <td>{{ $w->user_name ?? '—' }}</td>
                <td>{{ number_format($w->amount, 2, ',', ' ') }} €</td>
                <td>{{ ucfirst($w->status) }}</td>
                <td>{{ $w->created_at }}</td>
                <td>
                    @if($w->status === 'pending')
                        <form method="POST" action="{{ route('admin.finance.withdrawals.process', $w->id) }}" style="display:inline">
                            @csrf
                            <input type="hidden" name="action" value="approve" />
                            <button class="btn btn-sm btn-success">Approuver</button>
                        </form>
                        <form method="POST" action="{{ route('admin.finance.withdrawals.process', $w->id) }}" style="display:inline">
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
            <tr><td colspan="7">Aucun retrait trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-4">{{ $withdrawals->appends(request()->query())->links() }}</div>
</form>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function(e){
    document.querySelectorAll('input[name="withdrawal_ids[]"]').forEach(function(cb){ cb.checked = e.target.checked; });
});
document.getElementById('bulkApprove').addEventListener('click', function(){ document.getElementById('bulkAction').value = 'approve'; document.getElementById('bulkForm').submit(); });
document.getElementById('bulkReject').addEventListener('click', function(){ document.getElementById('bulkAction').value = 'reject'; document.getElementById('bulkForm').submit(); });
</script>
@endpush

@endsection
