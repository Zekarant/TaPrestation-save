@extends('layouts.app')

@section('title', 'Locations materiel')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Locations materiel</h1>
            <p class="text-sm text-gray-500">Suivi des locations en cours et terminees</p>
        </div>
        <a href="{{ route('prestataire.dashboard') }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
            Retour dashboard
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">Total</div>
            <div class="text-xl font-bold">{{ (int) ($stats['total'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">Actives</div>
            <div class="text-xl font-bold text-blue-600">{{ (int) ($stats['active'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">Terminees</div>
            <div class="text-xl font-bold text-green-600">{{ (int) ($stats['completed'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">En retard</div>
            <div class="text-xl font-bold text-amber-600">{{ (int) ($stats['overdue'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">CA total</div>
            <div class="text-xl font-bold">{{ number_format((float) ($stats['total_revenue'] ?? 0), 2) }}€</div>
        </div>
        <div class="bg-white rounded-lg border p-3">
            <div class="text-xs text-gray-500">Paiements attente</div>
            <div class="text-xl font-bold text-red-600">{{ number_format((float) ($stats['pending_payment'] ?? 0), 2) }}€</div>
        </div>
    </div>

    <form method="GET" class="bg-white border rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous statuts</option>
                @foreach(['confirmed','in_preparation','ready_for_delivery','delivered','in_use','ready_for_pickup','returned','completed','cancelled','disputed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <select name="payment_status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous paiements</option>
                @foreach(['pending','deposit_paid','full_paid','refund_pending','completed','paid'] as $ps)
                    <option value="{{ $ps }}" {{ request('payment_status') === $ps ? 'selected' : '' }}>{{ $ps }}</option>
                @endforeach
            </select>

            <select name="equipment" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous equipements</option>
                @foreach(($equipments ?? collect()) as $equipment)
                    <option value="{{ $equipment->id }}" {{ (string) request('equipment') === (string) $equipment->id ? 'selected' : '' }}>
                        {{ $equipment->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-blue-600 text-white px-3 py-2 text-sm font-semibold hover:bg-blue-700">
                Filtrer
            </button>

            <a href="{{ route('prestataire.equipment-rentals.index') }}" class="inline-flex justify-center items-center rounded-lg bg-gray-100 text-gray-700 px-3 py-2 text-sm font-semibold hover:bg-gray-200">
                Reinitialiser
            </a>
        </div>
    </form>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Location</th>
                        <th class="text-left px-4 py-3">Equipement</th>
                        <th class="text-left px-4 py-3">Client</th>
                        <th class="text-left px-4 py-3">Periode</th>
                        <th class="text-left px-4 py-3">Statut</th>
                        <th class="text-left px-4 py-3">Montant</th>
                        <th class="text-right px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($rentals as $rental)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ $rental->rental_number ?: ('LOC-' . $rental->id) }}
                            </td>
                            <td class="px-4 py-3">{{ optional($rental->equipment)->name ?? 'Equipement supprime' }}</td>
                            <td class="px-4 py-3">{{ optional(optional($rental->client)->user)->name ?? 'Client inconnu' }}</td>
                            <td class="px-4 py-3">
                                {{ optional($rental->start_date)->format('d/m/Y') ?: '-' }}
                                -
                                {{ optional($rental->end_date)->format('d/m/Y') ?: '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $rental->formatted_status }}</div>
                                <div class="text-xs text-gray-500">{{ $rental->formatted_payment_status }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                {{ number_format((float) ($rental->final_amount ?? $rental->total_amount ?? 0), 2) }}€
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('prestataire.equipment-rentals.show', $rental) }}" class="inline-flex items-center rounded-lg bg-blue-600 text-white px-3 py-1.5 text-xs font-semibold hover:bg-blue-700">
                                    Ouvrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">Aucune location trouvee.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $rentals->links() }}
    </div>
</div>
@endsection
