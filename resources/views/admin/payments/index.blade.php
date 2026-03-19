@extends('layouts.admin-modern')

@section('title', 'Pilotage des paiements')

@php
    $money = fn ($value) => number_format((float) ($value ?? 0), 2, ',', ' ') . ' EUR';
    $statusBadge = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'paid', 'completed', 'released', 'validated', 'returned' => 'bg-green-100 text-green-800 border border-green-200',
            'pending', 'processing', 'held', 'pending_capture', 'under_review' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'refunded', 'partially_refunded', 'partial', 'partial_refund' => 'bg-sky-100 text-sky-800 border border-sky-200',
            'failed', 'cancelled', 'rejected', 'retained' => 'bg-red-100 text-red-800 border border-red-200',
            'disputed', 'open' => 'bg-violet-100 text-violet-800 border border-violet-200',
            default => 'bg-slate-100 text-slate-800 border border-slate-200',
        };
    };
    $statusLabel = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'paid' => 'Paye',
            'completed' => 'Complete',
            'released' => 'Libere',
            'pending' => 'En attente',
            'processing' => 'Traitement',
            'held' => 'Bloque',
            'pending_capture' => 'Autorise',
            'refunded' => 'Rembourse',
            'partially_refunded' => 'Rembourse partiel',
            'partial_refund' => 'Rembourse partiel',
            'partial' => 'Partiel',
            'failed' => 'Echec',
            'cancelled' => 'Annule',
            'rejected' => 'Refuse',
            'disputed' => 'Litige',
            'open' => 'Ouvert',
            'under_review' => 'En revue',
            'returned' => 'Restitue',
            'retained' => 'Retenue',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    };
    $transactionSource = function ($transaction): string {
        if ($transaction->food_order_id) return 'Food';
        if ($transaction->equipment_rental_id) return 'Location';
        if ($transaction->booking_id) return 'Service';
        return 'Paiement';
    };
    $transactionReference = function ($transaction): string {
        if ($transaction->booking?->booking_number) return $transaction->booking->booking_number;
        if ($transaction->equipmentRentalRequest?->request_number) return $transaction->equipmentRentalRequest->request_number;
        if ($transaction->foodOrder?->order_number) return $transaction->foodOrder->order_number;
        return 'TX-' . $transaction->id;
    };
    $transactionClient = function ($transaction): string {
        return $transaction->foodOrder?->client?->name
            ?? $transaction->equipmentRentalRequest?->client?->user?->name
            ?? $transaction->booking?->client?->user?->name
            ?? $transaction->user?->name
            ?? 'N/A';
    };
    $transactionPrestataire = function ($transaction): string {
        return $transaction->foodOrder?->prestataire?->user?->name
            ?? $transaction->equipmentRentalRequest?->prestataire?->user?->name
            ?? $transaction->booking?->prestataire?->user?->name
            ?? 'N/A';
    };
    $escrowValue = function ($row, array $keys, $default = null) {
        foreach ($keys as $key) {
            if (isset($row->{$key}) && $row->{$key} !== null && $row->{$key} !== '') {
                return $row->{$key};
            }
        }
        return $default;
    };
    $escrowTypeLabel = function (?string $type): string {
        $type = (string) $type;
        return match (true) {
            str_contains($type, 'Booking') => 'Service',
            str_contains($type, 'EquipmentRental') => 'Location',
            str_contains($type, 'UrgentSale') => 'Vente urgente',
            default => class_basename($type ?: 'Escrow'),
        };
    };
    $fieldClass = 'rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900';
    $textAreaClass = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900';
    $primaryButtonClass = 'inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white';
    $secondaryButtonClass = 'inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700';
    $successButtonClass = 'inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white';
    $warningButtonClass = 'inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white';
    $dangerButtonClass = 'inline-flex items-center justify-center rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white';
    $infoButtonClass = 'inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white';
    $kpiCards = [
        [
            'label' => 'Volume brut',
            'value' => $money($stats['gross_volume'] ?? 0),
            'value_class' => 'text-slate-900',
            'hint' => 'Tous flux confondus',
            'icon' => 'fa-wallet',
            'icon_wrap_class' => 'bg-slate-100',
            'icon_class' => 'text-slate-700 text-xl',
        ],
        [
            'label' => 'Paiements valides',
            'value' => number_format((int) ($stats['validated_count'] ?? 0)),
            'value_class' => 'text-green-700',
            'hint' => $money($stats['deposit_amount'] ?? 0) . " d'acomptes traces",
            'icon' => 'fa-check-circle',
            'icon_wrap_class' => 'bg-green-100',
            'icon_class' => 'text-green-700 text-xl',
        ],
        [
            'label' => 'En attente / bloques',
            'value' => number_format((int) ($stats['pending_count'] ?? 0)),
            'value_class' => 'text-amber-700',
            'hint' => $money($stats['escrow_held_amount'] ?? 0) . ' sous escrow',
            'icon' => 'fa-hourglass-half',
            'icon_wrap_class' => 'bg-amber-100',
            'icon_class' => 'text-amber-700 text-xl',
        ],
        [
            'label' => 'Demandes remboursement',
            'value' => number_format((int) ($stats['refund_pending_count'] ?? 0)),
            'value_class' => 'text-rose-700',
            'hint' => $money($stats['refund_pending_amount'] ?? 0) . ' a traiter',
            'icon' => 'fa-rotate-left',
            'icon_wrap_class' => 'bg-rose-100',
            'icon_class' => 'text-rose-700 text-xl',
        ],
        [
            'label' => 'Escrows en cours',
            'value' => number_format((int) ($stats['escrow_held_count'] ?? 0)),
            'value_class' => 'text-indigo-700',
            'hint' => $money($stats['escrow_held_amount'] ?? 0),
            'icon' => 'fa-shield-halved',
            'icon_wrap_class' => 'bg-indigo-100',
            'icon_class' => 'text-indigo-700 text-xl',
        ],
        [
            'label' => 'Cautions ouvertes',
            'value' => number_format((int) ($stats['caution_open_count'] ?? 0)),
            'value_class' => 'text-cyan-700',
            'hint' => $money($stats['caution_open_amount'] ?? 0),
            'icon' => 'fa-box-open',
            'icon_wrap_class' => 'bg-cyan-100',
            'icon_class' => 'text-cyan-700 text-xl',
        ],
        [
            'label' => 'Food a capturer',
            'value' => number_format((int) ($stats['food_pending_capture_count'] ?? 0)),
            'value_class' => 'text-fuchsia-700',
            'hint' => $money($stats['food_pending_capture_amount'] ?? 0),
            'icon' => 'fa-bag-shopping',
            'icon_wrap_class' => 'bg-fuchsia-100',
            'icon_class' => 'text-fuchsia-700 text-xl',
        ],
        [
            'label' => 'Paiements en echec',
            'value' => number_format((int) ($stats['failed_count'] ?? 0)),
            'value_class' => 'text-red-700',
            'hint' => $money($stats['refunded_amount'] ?? 0) . ' deja remboursee',
            'icon' => 'fa-triangle-exclamation',
            'icon_wrap_class' => 'bg-red-100',
            'icon_class' => 'text-red-700 text-xl',
        ],
    ];
    $quickLinks = [
        ['href' => '#actions-urgentes', 'label' => 'Actions urgentes', 'class' => 'bg-slate-900 text-white'],
        ['href' => '#transactions', 'label' => 'Transactions', 'class' => 'bg-slate-100 text-slate-700'],
        ['href' => '#escrows', 'label' => 'Escrows', 'class' => 'bg-slate-100 text-slate-700'],
        ['href' => '#remboursements', 'label' => 'Remboursements', 'class' => 'bg-slate-100 text-slate-700'],
    ];
@endphp

@section('content')
<div class="page-header">
    <h1 class="page-title">Centre admin paiements</h1>
    <p class="page-subtitle">Supervision des paiements, acomptes, cautions, escrows, remboursements et actions critiques.</p>
</div>

@if(session('success'))
    <div class="card-base mb-6 border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="card-base mb-6 border-red-200 bg-red-50 text-red-800">{{ session('error') }}</div>
@endif

@if(!empty($pageError ?? null))
    <div class="card-base mb-6 border-amber-200 bg-amber-50 text-amber-900">{{ $pageError }}</div>
@endif

@if(!empty($pageWarnings ?? []))
    <div class="card-base mb-6 border-amber-200 bg-amber-50 text-amber-900">
        Certaines sections n'ont pas pu etre chargees correctement : {{ implode(', ', array_unique($pageWarnings)) }}.
    </div>
@endif

<div class="card-base mb-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Console de controle</p>
            <h2 class="mt-2 text-2xl font-black text-slate-900">Vue unique des flux et des actions admin</h2>
            <p class="mt-2 text-sm text-slate-600">Consulte les flux modernes et legacy, puis traite les dossiers urgents sans parcourir plusieurs pages.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($quickLinks as $quickLink)
                <a href="{{ $quickLink['href'] }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold {{ $quickLink['class'] }}">{{ $quickLink['label'] }}</a>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-8">
    @foreach($kpiCards as $kpiCard)
        <div class="card-base">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ $kpiCard['label'] }}</p>
                    <p class="mt-3 text-3xl font-black {{ $kpiCard['value_class'] }}">{{ $kpiCard['value'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $kpiCard['hint'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $kpiCard['icon_wrap_class'] }}">
                    <i class="fas {{ $kpiCard['icon'] }} {{ $kpiCard['icon_class'] }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card-base mb-8">
    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Filtres</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Affiner les resultats</h2>
            <p class="mt-1 text-sm text-slate-600">Recherche par reference, statut, source, mode de paiement ou periode.</p>
        </div>
        <a href="{{ route('admin.payments.analytics') }}" class="{{ $primaryButtonClass }}">Analytics</a>
    </div>
    <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Recherche
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ID, reference, client, presta..." class="{{ $fieldClass }}">
        </label>
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Statut
            <select name="status" class="{{ $fieldClass }}">@foreach(($filterOptions['statuses'] ?? []) as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>@endforeach</select>
        </label>
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Source
            <select name="source" class="{{ $fieldClass }}">@foreach(($filterOptions['sources'] ?? []) as $value => $label)<option value="{{ $value }}" @selected(($filters['source'] ?? 'all') === $value)>{{ $label }}</option>@endforeach</select>
        </label>
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Mode
            <select name="mode" class="{{ $fieldClass }}">@foreach(($filterOptions['modes'] ?? []) as $value => $label)<option value="{{ $value }}" @selected(($filters['mode'] ?? 'all') === $value)>{{ $label }}</option>@endforeach</select>
        </label>
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Date debut
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="{{ $fieldClass }}">
        </label>
        <label class="grid gap-2 text-sm font-medium text-slate-700">
            Date fin
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="{{ $fieldClass }}">
        </label>
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
            <input type="checkbox" name="actionable_only" value="1" @checked($filters['actionable_only'] ?? false) class="rounded border-slate-300">
            Actions urgentes uniquement
        </label>
        <div class="flex flex-wrap items-end gap-3">
            <button type="submit" class="{{ $infoButtonClass }}">Filtrer</button>
            <a href="{{ route('admin.payments.index') }}" class="{{ $secondaryButtonClass }}">Reinitialiser</a>
        </div>
    </form>
</div>

<section id="actions-urgentes" class="mb-10">
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Priorites</p>
        <h2 class="mt-1 text-2xl font-black text-slate-900">Actions urgentes</h2>
        <p class="mt-1 text-sm text-slate-600">Les dossiers ci-dessous concentrent les actions manuelles importantes.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 mb-6">
        <div class="card-base">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Remboursements</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Demandes a valider</h3>
                    <p class="mt-1 text-sm text-slate-600">Approuve ou rejette les demandes avec une note admin.</p>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $refundRequests->total() ?? 0 }} demandes</span>
            </div>
            <div class="space-y-4">
                @forelse(collect($refundRequests->items())->take(6) as $refund)
                    <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <summary class="list-none cursor-pointer">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">Refund #{{ $refund->id }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($refund->status) }}">{{ $statusLabel($refund->status) }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $money($refund->amount) }} sur {{ $refund->transaction?->id ? 'TX-' . $refund->transaction->id : 'transaction non reliee' }}</p>
                                    <div class="grid gap-1 text-xs text-slate-500 md:grid-cols-2">
                                        <div>Client: {{ $refund->user?->name ?? 'N/A' }}</div>
                                        <div>Reference: {{ $refund->transaction?->transaction_id ?? $refund->transaction?->stripe_payment_intent_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Ouvrir le dossier</span>
                            </div>
                        </summary>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Motif</p>
                                <p class="mt-2 text-sm text-slate-700">{{ $refund->reason ?: 'Aucun motif fourni.' }}</p>
                                <p class="mt-2 text-xs text-slate-500">Email client: {{ $refund->user?->email ?? 'N/A' }}</p>
                            </div>
                            @if($refund->status === 'pending')
                                <form method="POST" action="{{ route('admin.payments.refund-requests.process', $refund) }}" class="grid gap-3 md:grid-cols-2">
                                    @csrf
                                    <label class="grid gap-2 text-sm font-medium text-slate-700 md:col-span-2">
                                        Note admin
                                        <textarea name="notes" rows="3" placeholder="Decision, contexte, verification..." class="{{ $textAreaClass }}"></textarea>
                                    </label>
                                    <button type="submit" name="decision" value="approve" class="{{ $successButtonClass }}">Approuver</button>
                                    <button type="submit" name="decision" value="reject" class="{{ $dangerButtonClass }}">Rejeter</button>
                                </form>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">Aucune demande de remboursement a traiter.</div>
                @endforelse
            </div>
        </div>

        <div class="card-base">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Food</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Autorisations et remboursements</h3>
                    <p class="mt-1 text-sm text-slate-600">Capture, annulation d'autorisation ou remboursement selon l'etat du dossier.</p>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $foodActionQueue->count() }} dossiers</span>
            </div>
            <div class="space-y-4">
                @forelse($foodActionQueue as $foodOrder)
                    <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <summary class="list-none cursor-pointer">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">{{ $foodOrder->order_number ?? ('FO-' . $foodOrder->id) }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($foodOrder->payment_status) }}">{{ $statusLabel($foodOrder->payment_status) }}</span>
                                    </div>
                                    <div class="grid gap-1 text-xs text-slate-500 md:grid-cols-2">
                                        <div>Client: {{ $foodOrder->client?->name ?? 'N/A' }}</div>
                                        <div>Prestataire: {{ $foodOrder->prestataire?->user?->name ?? 'N/A' }}</div>
                                        <div>Escrow: {{ $statusLabel($foodOrder->escrow_status) }}</div>
                                        <div>Methode: {{ $foodOrder->payment_method ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="text-left md:text-right">
                                    <p class="text-lg font-black text-slate-900">{{ $money($foodOrder->amount_held ?? $foodOrder->total) }}</p>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Ouvrir les actions</p>
                                </div>
                            </div>
                        </summary>
                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            <form method="POST" action="{{ route('admin.payments.food.capture', $foodOrder) }}" class="rounded-xl border border-slate-200 bg-white p-4">
                                @csrf
                                <p class="text-sm font-semibold text-slate-900">Capturer le paiement</p>
                                <p class="mt-1 text-xs text-slate-500">Encaisse le montant autorise pour cette commande.</p>
                                <button type="submit" class="mt-4 w-full {{ $infoButtonClass }}">Capturer</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.food.cancel-authorization', $foodOrder) }}" class="rounded-xl border border-slate-200 bg-white p-4">
                                @csrf
                                <p class="text-sm font-semibold text-slate-900">Annuler l'autorisation</p>
                                <label class="mt-3 grid gap-2 text-sm font-medium text-slate-700">
                                    Motif
                                    <textarea name="reason" rows="3" placeholder="Motif annulation" class="{{ $textAreaClass }}"></textarea>
                                </label>
                                <button type="submit" class="mt-4 w-full {{ $warningButtonClass }}">Annuler l'autorisation</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.food.refund', $foodOrder) }}" class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2">
                                @csrf
                                <p class="text-sm font-semibold text-slate-900">Rembourser le client</p>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                                        Montant
                                        <input type="number" min="0.01" step="0.01" name="amount" placeholder="Montant" class="{{ $fieldClass }}">
                                    </label>
                                    <label class="grid gap-2 text-sm font-medium text-slate-700 md:col-span-2">
                                        Motif remboursement
                                        <textarea name="reason" rows="3" placeholder="Motif remboursement" class="{{ $textAreaClass }}"></textarea>
                                    </label>
                                </div>
                                <button type="submit" class="mt-4 {{ $dangerButtonClass }}">Rembourser</button>
                            </form>
                        </div>
                    </details>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">Aucun flux food necessitant une action immediate.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="card-base">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Litiges escrow</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Dossiers ouverts</h3>
                    <p class="mt-1 text-sm text-slate-600">Analyse le litige et tranche avec une resolution tracee.</p>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $disputeQueue->count() }} ouverts</span>
            </div>
            <div class="space-y-4">
                @forelse($disputeQueue as $dispute)
                    <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <summary class="list-none cursor-pointer">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">Litige #{{ $dispute->id }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($dispute->status) }}">{{ $statusLabel($dispute->status) }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $escrowTypeLabel($dispute->escrowable_type) }}</p>
                                    <div class="grid gap-1 text-xs text-slate-500 md:grid-cols-2">
                                        <div>Client: {{ $dispute->client_name ?? 'N/A' }}</div>
                                        <div>Prestataire: {{ $dispute->prestataire_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Ouvrir le dossier</span>
                            </div>
                        </summary>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</p>
                                <p class="mt-2 text-sm text-slate-700">{{ $dispute->description }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.payments.disputes.resolve', $dispute->id) }}" class="grid gap-3 md:grid-cols-2">
                                @csrf
                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                    Resolution
                                    <select name="resolution" class="{{ $fieldClass }}">
                                        <option value="resolved_client">Donner raison au client</option>
                                        <option value="resolved_prestataire">Donner raison au prestataire</option>
                                        <option value="resolved_partial">Resolution partielle</option>
                                    </select>
                                </label>
                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                    Montant rembourse si partiel
                                    <input type="number" min="0" step="0.01" name="refund_amount" placeholder="Montant rembourse" class="{{ $fieldClass }}">
                                </label>
                                <label class="grid gap-2 text-sm font-medium text-slate-700 md:col-span-2">
                                    Notes de resolution
                                    <textarea name="notes" rows="3" placeholder="Notes de resolution" class="{{ $textAreaClass }}"></textarea>
                                </label>
                                <button type="submit" class="{{ $primaryButtonClass }}">Clore le litige</button>
                            </form>
                        </div>
                    </details>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">Aucun litige escrow ouvert.</div>
                @endforelse
            </div>
        </div>

        <div class="card-base">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cautions materiel</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Retours a traiter</h3>
                    <p class="mt-1 text-sm text-slate-600">Traite l'etat du materiel et la retenue eventuelle sur caution.</p>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $equipmentDepositQueue->count() }} dossiers</span>
            </div>
            <div class="space-y-4">
                @forelse($equipmentDepositQueue as $entry)
                    @php($rentalRequest = $entry['request'])
                    @php($escrow = $entry['escrow'])
                    <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <summary class="list-none cursor-pointer">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">{{ $rentalRequest->request_number ?? ('LOC-' . $rentalRequest->id) }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($rentalRequest->deposit_status ?? $escrow->status ?? 'pending') }}">{{ $statusLabel($rentalRequest->deposit_status ?? $escrow->status ?? 'pending') }}</span>
                                    </div>
                                    <div class="grid gap-1 text-xs text-slate-500 md:grid-cols-2">
                                        <div>Client: {{ $rentalRequest->client?->user?->name ?? 'N/A' }}</div>
                                        <div>Prestataire: {{ $rentalRequest->prestataire?->user?->name ?? 'N/A' }}</div>
                                        <div>Materiel: {{ $rentalRequest->equipment?->name ?? 'N/A' }}</div>
                                        <div>Escrow: {{ $escrow ? ('#' . $escrow->id . ' · ' . $statusLabel($escrow->status)) : 'Aucun escrow detecte' }}</div>
                                    </div>
                                </div>
                                <div class="text-left md:text-right">
                                    <p class="text-lg font-black text-slate-900">{{ $money($rentalRequest->security_deposit) }}</p>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Ouvrir les actions</p>
                                </div>
                            </div>
                        </summary>
                        <div class="mt-4">
                            @if($escrow)
                                <form method="POST" action="{{ route('admin.payments.escrow.return-equipment', $escrow->id) }}" class="grid gap-3 md:grid-cols-2">
                                    @csrf
                                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                                        Etat du materiel
                                        <select name="condition" class="{{ $fieldClass }}">
                                            <option value="good">Bon etat</option>
                                            <option value="partial_damage">Degats partiels</option>
                                            <option value="damaged">Degats importants</option>
                                        </select>
                                    </label>
                                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                                        % de caution retenue
                                        <input type="number" min="0" max="100" step="0.01" name="damage_percent" placeholder="% de caution retenue" class="{{ $fieldClass }}">
                                    </label>
                                    <label class="grid gap-2 text-sm font-medium text-slate-700 md:col-span-2">
                                        Notes retour / retenue
                                        <textarea name="notes" rows="3" placeholder="Constat, retenue, observations..." class="{{ $textAreaClass }}"></textarea>
                                    </label>
                                    <button type="submit" class="{{ $infoButtonClass }}">Traiter la caution</button>
                                </form>
                            @else
                                <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">Aucun escrow de caution detecte pour ce dossier.</div>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">Aucune caution ouverte a traiter.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section id="transactions" class="card-base mb-8">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Flux modernes</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Transactions</h2>
            <p class="mt-1 text-sm text-slate-600">Historique detaille des paiements modernes avec action de remboursement.</p>
        </div>
        <span class="text-sm text-slate-500">{{ $transactions->total() ?? 0 }} lignes</span>
    </div>
    @if(($tableAvailability['payment_transactions'] ?? false) && $transactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Flux</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Parties</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Details et actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($transactions as $transaction)
                        <tr class="align-top">
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">
                                {{ $transactionReference($transaction) }}
                                <div class="text-xs text-slate-500">TX-{{ $transaction->id }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $transactionSource($transaction) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                <div class="font-medium text-slate-900">{{ $transactionClient($transaction) }}</div>
                                <div class="mt-1 text-xs text-slate-500">Prestataire: {{ $transactionPrestataire($transaction) }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $money($transaction->amount) }}</td>
                            <td class="px-4 py-4 text-sm"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($transaction->status) }}">{{ $statusLabel($transaction->status) }}</span></td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 text-sm">
                                <details class="rounded-xl border border-slate-200 p-3">
                                    <summary class="cursor-pointer font-medium text-indigo-700">Voir / rembourser</summary>
                                    <div class="mt-3 space-y-2 text-slate-700">
                                        <div>Type: {{ $transaction->type ?? 'payment' }}</div>
                                        <div>Methode: {{ $transaction->payment_method ?? 'N/A' }} / {{ $transaction->provider ?? 'N/A' }}</div>
                                        <div>Stripe: {{ $transaction->stripe_payment_intent_id ?? 'N/A' }}</div>
                                        <div>Transaction externe: {{ $transaction->transaction_id ?? 'N/A' }}</div>
                                        @if(in_array((string) $transaction->status, ['paid', 'completed', 'held', 'released', 'partially_refunded'], true))
                                            <form method="POST" action="{{ route('admin.payments.refund', $transaction) }}" class="grid gap-2 pt-2">
                                                @csrf
                                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                                    Montant a rembourser
                                                    <input type="number" min="0.01" step="0.01" name="amount" placeholder="Montant a rembourser" class="{{ $fieldClass }}">
                                                </label>
                                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                                    Motif remboursement
                                                    <textarea name="reason" rows="3" placeholder="Motif remboursement" class="{{ $textAreaClass }}"></textarea>
                                                </label>
                                                <button type="submit" class="{{ $dangerButtonClass }}">Rembourser cette transaction</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $transactions->appends(request()->except('transactions_page'))->links() }}</div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-500">Aucune transaction visible avec les filtres actuels.</div>
    @endif
</section>

<section class="card-base mb-8">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Source historique</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Transactions legacy</h2>
            <p class="mt-1 text-sm text-slate-600">Anciennes lignes lues depuis la table legacy `transactions`.</p>
        </div>
        <span class="text-sm text-slate-500">{{ $legacyTransactions->total() ?? 0 }} lignes</span>
    </div>
    @if(($legacyTransactions->total() ?? 0) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Prestataire</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($legacyTransactions as $legacyTransaction)
                        <tr>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $legacyTransaction->reference ?? ('LG-' . $legacyTransaction->id) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $legacyTransaction->user_name ?? $legacyTransaction->user_email ?? 'N/A' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $legacyTransaction->prestataire_name ?? 'N/A' }}</td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $money($legacyTransaction->amount) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $legacyTransaction->type ?? 'payment' }}</td>
                            <td class="px-4 py-4 text-sm"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($legacyTransaction->status) }}">{{ $statusLabel($legacyTransaction->status) }}</span></td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ \Carbon\Carbon::parse($legacyTransaction->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $legacyTransactions->appends(request()->except('legacy_transactions_page'))->links() }}</div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-500">Aucune transaction legacy visible avec les filtres actuels.</div>
    @endif
</section>

<section id="escrows" class="card-base mb-8">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Escrows</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Escrows et etapes</h2>
            <p class="mt-1 text-sm text-slate-600">Suivi des montants bloques, des etapes et des actions de liberation ou remboursement.</p>
        </div>
        <span class="text-sm text-slate-500">{{ $escrows->total() ?? 0 }} lignes</span>
    </div>
    @if(($tableAvailability['escrow_transactions'] ?? false) && $escrows->count() > 0)
        <div class="space-y-4">
            @foreach($escrows as $escrow)
                @php($escrowAmount = $escrowValue($escrow, ['total_amount', 'amount'], 0))
                @php($escrowDeposit = $escrowValue($escrow, ['deposit_amount'], 0))
                @php($escrowRemaining = $escrowValue($escrow, ['remaining_amount'], $escrowAmount))
                <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <summary class="cursor-pointer list-none">
                        <div class="grid gap-3 md:grid-cols-6 md:items-center">
                            <div class="font-semibold text-slate-900">ESC-{{ $escrow->id }}</div>
                            <div class="text-sm text-slate-700">{{ $escrowTypeLabel($escrow->escrowable_type) }}</div>
                            <div class="text-sm text-slate-700">{{ $escrow->client_name ?? 'N/A' }}</div>
                            <div class="text-sm text-slate-700">{{ $escrow->prestataire_name ?? 'N/A' }}</div>
                            <div class="text-sm font-semibold text-slate-900">{{ $money($escrowAmount) }}</div>
                            <div><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($escrow->status) }}">{{ $statusLabel($escrow->status) }}</span></div>
                        </div>
                    </summary>
                    <div class="mt-4 grid gap-4 lg:grid-cols-3 text-sm text-slate-700">
                        <div class="space-y-2">
                            <div>Caution: {{ $money($escrowDeposit) }}</div>
                            <div>Reste a debloquer: {{ $money($escrowRemaining) }}</div>
                            <div>PI Stripe: {{ $escrow->stripe_payment_intent_id ?? 'N/A' }}</div>
                            <div>Transfer Stripe: {{ $escrow->stripe_transfer_id ?? 'N/A' }}</div>
                            @if(!empty($escrow->dispute_id))
                                <div>Litige associe: #{{ $escrow->dispute_id }} · {{ $statusLabel($escrow->dispute_status) }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.payments.escrow.release', $escrow->id) }}" class="grid gap-2 rounded-xl border border-slate-200 bg-white p-3">
                            @csrf
                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                Montant a liberer
                                <input type="number" min="0.01" step="0.01" name="amount" placeholder="Montant a liberer" class="{{ $fieldClass }}">
                            </label>
                            <button type="submit" class="{{ $successButtonClass }}">Liberer au prestataire</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.escrow.refund', $escrow->id) }}" class="grid gap-2 rounded-xl border border-slate-200 bg-white p-3">
                            @csrf
                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                Montant a rembourser
                                <input type="number" min="0.01" step="0.01" name="amount" placeholder="Montant a rembourser" class="{{ $fieldClass }}">
                            </label>
                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                Motif remboursement escrow
                                <textarea name="reason" rows="3" placeholder="Motif remboursement escrow" class="{{ $textAreaClass }}"></textarea>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="allow_full_escrow_refund" value="1" class="rounded border-slate-300">Autoriser remboursement sur montant total escrow</label>
                            <button type="submit" class="{{ $dangerButtonClass }}">Rembourser le client</button>
                        </form>
                    </div>
                </details>
            @endforeach
        </div>
        <div class="mt-6">{{ $escrows->appends(request()->except('escrows_page'))->links() }}</div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-500">Aucun escrow visible avec les filtres actuels.</div>
    @endif
</section>

<section id="remboursements" class="card-base">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Historique</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Journal remboursements</h2>
            <p class="mt-1 text-sm text-slate-600">Journal de suivi des demandes et statuts de remboursement.</p>
        </div>
        <span class="text-sm text-slate-500">{{ $refundRequests->total() ?? 0 }} lignes</span>
    </div>
    @if(($tableAvailability['refunds'] ?? false) && $refundRequests->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Transaction</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Motif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($refundRequests as $refund)
                        <tr>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">RF-{{ $refund->id }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $refund->user?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $refund->transaction?->transaction_id ?? ('TX-' . ($refund->transaction_id ?? 'N/A')) }}</td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $money($refund->amount) }}</td>
                            <td class="px-4 py-4 text-sm"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($refund->status) }}">{{ $statusLabel($refund->status) }}</span></td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($refund->reason, 80) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $refundRequests->appends(request()->except('refunds_page'))->links() }}</div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-500">Aucun remboursement visible avec les filtres actuels.</div>
    @endif
</section>
@endsection
