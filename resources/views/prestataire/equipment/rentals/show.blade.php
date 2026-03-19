@extends('layouts.app')

@section('title', 'Details location materiel')

@section('content')
@php
    $status = (string) ($rental->status ?? '');
    $indexUrl = \Illuminate\Support\Facades\Route::has('prestataire.equipment-rentals.index')
        ? route('prestataire.equipment-rentals.index')
        : url('/prestataire/equipment-rentals');
    $statusLabels = [
        'confirmed' => 'Confirmee',
        'in_preparation' => 'En preparation',
        'ready_for_delivery' => 'Pret pour livraison',
        'delivered' => 'Livree',
        'in_use' => 'En cours',
        'ready_for_pickup' => 'Pret retour',
        'returned' => 'Retournee',
        'completed' => 'Terminee',
        'cancelled' => 'Annulee',
        'disputed' => 'En litige',
    ];
    $statusColor = match ($status) {
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'disputed' => 'bg-orange-100 text-orange-700',
        'in_use' => 'bg-cyan-100 text-cyan-700',
        default => 'bg-blue-100 text-blue-700',
    };
@endphp

<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <div class="text-sm text-gray-500 mb-1">Location</div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $rental->rental_number ?: ('LOC-' . $rental->id) }}
            </h1>
            <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                {{ $statusLabels[$status] ?? strtoupper($status ?: 'inconnu') }}
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ $indexUrl }}" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                Retour locations
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <section class="bg-white border rounded-xl p-5">
            <h2 class="text-lg font-semibold mb-4">Informations principales</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Equipement</div>
                    <div class="font-semibold text-gray-900">{{ optional($rental->equipment)->name ?? 'Equipement supprime' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Client</div>
                    <div class="font-semibold text-gray-900">{{ optional(optional($rental->client)->user)->name ?? 'Client inconnu' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Date debut</div>
                    <div class="font-semibold text-gray-900">{{ optional($rental->start_date)->format('d/m/Y') ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Date fin</div>
                    <div class="font-semibold text-gray-900">{{ optional($rental->end_date)->format('d/m/Y') ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Debut effectif</div>
                    <div class="font-semibold text-gray-900">{{ optional($rental->actual_start_datetime)->format('d/m/Y H:i') ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Fin effective</div>
                    <div class="font-semibold text-gray-900">{{ optional($rental->actual_end_datetime)->format('d/m/Y H:i') ?: '-' }}</div>
                </div>
            </div>
        </section>

        <section class="bg-white border rounded-xl p-5">
            <h2 class="text-lg font-semibold mb-4">Montants</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Montant base</div>
                    <div class="font-semibold text-gray-900">{{ number_format((float) ($rental->base_amount ?? 0), 2) }}€</div>
                </div>
                <div>
                    <div class="text-gray-500">Frais additionnels</div>
                    <div class="font-semibold text-gray-900">{{ number_format((float) ($rental->additional_fees ?? 0), 2) }}€</div>
                </div>
                <div>
                    <div class="text-gray-500">Montant final</div>
                    <div class="font-semibold text-gray-900">{{ number_format((float) ($rental->final_amount ?? $rental->total_amount ?? 0), 2) }}€</div>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-600">
                Paiement: <span class="font-semibold text-gray-900">{{ $rental->formatted_payment_status }}</span>
            </div>
        </section>

        @if(!empty($rental->internal_notes))
            <section class="bg-white border rounded-xl p-5">
                <h2 class="text-lg font-semibold mb-3">Notes internes</h2>
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $rental->internal_notes }}</div>
            </section>
        @endif
    </div>
</div>
@endsection
