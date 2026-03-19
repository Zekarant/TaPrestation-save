@extends('layouts.app')

@section('content')
<div class="prestataire-dashboard" x-data="{ 
    showRatingModal: false,
    showShipmentModal: false,
    showReturnModal: false,
    equipmentStatus: 'good',
    retainPercent: 0,
    rating: 5,
    setRating(value) { this.rating = value; },
    openReturnModal() {
        this.equipmentStatus = 'good';
        this.retainPercent = 0;
        this.showReturnModal = true;
    },
    setEquipmentStatus(value) {
        this.equipmentStatus = value;
        if (value !== 'damaged') {
            this.retainPercent = 0;
        }
    }
}">
    @php
        $escrowMetaUnified = [];
        try {
            $escrowMetaUnified = !empty($escrow->metadata) ? (json_decode((string) $escrow->metadata, true) ?: []) : [];
        } catch (\Throwable $e) {
            $escrowMetaUnified = [];
        }

        $relatedDepositStatusRaw = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'deposit_status'))
            ? (string) ($relatedItem->deposit_status ?? '')
            : '';

        $effectiveEscrowDepositAmount = (float) ($escrow->deposit_amount ?? 0);
        if ($effectiveEscrowDepositAmount <= 0) {
            $effectiveEscrowDepositAmount = (float) ($escrowMetaUnified['deposit_amount'] ?? ($escrowMetaUnified['security_deposit'] ?? 0));
        }
        if ($effectiveEscrowDepositAmount <= 0 && is_object($relatedItem ?? null) && property_exists($relatedItem, 'security_deposit')) {
            $effectiveEscrowDepositAmount = (float) ($relatedItem->security_deposit ?? 0);
        }

        $depositProcessedStatuses = ['returned', 'partial', 'retained', 'released', 'refunded', 'completed', 'done', 'paid', 'partially_refunded', 'partially_returned', 'mixed', 'withheld', 'kept'];
        $metaDepositStatus = strtolower(trim((string) ($escrowMetaUnified['deposit_status'] ?? 'pending')));
        $relatedDepositStatus = strtolower(trim((string) ($relatedDepositStatusRaw !== '' ? $relatedDepositStatusRaw : 'pending')));
        $relatedReturnedAt = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'equipment_returned_at'))
            ? ($relatedItem->equipment_returned_at ?? null)
            : null;
        $metaHasDepositBreakdown = array_key_exists('deposit_retained', $escrowMetaUnified)
            || array_key_exists('deposit_returned', $escrowMetaUnified);
        if (in_array($relatedDepositStatus, $depositProcessedStatuses, true)) {
            $effectiveDepositStatus = $relatedDepositStatus;
        } elseif (in_array($metaDepositStatus, $depositProcessedStatuses, true)) {
            $effectiveDepositStatus = $metaDepositStatus;
        } else {
            $effectiveDepositStatus = $relatedDepositStatusRaw !== '' ? $relatedDepositStatus : $metaDepositStatus;
        }
        $effectiveDepositProcessed = in_array($effectiveDepositStatus, $depositProcessedStatuses, true)
            || !empty($escrowMetaUnified['deposit_processed_at'] ?? null)
            || !empty($relatedReturnedAt)
            || $metaHasDepositBreakdown
            || !empty($escrow->prestataire_confirmed_at ?? null);

        $effectiveEscrowAmount = (float) ($escrow->total_amount ?? $escrow->amount ?? 0);
        if ($effectiveEscrowAmount <= 0) {
            $effectiveEscrowAmount = (float) ($escrowMetaUnified['rental_amount'] ?? ($escrowMetaUnified['breakdown']['client_pays'] ?? 0));
        }

        $effectiveEscrowCommission = (float) ($escrow->commission_amount ?? $escrow->platform_fee ?? 0);
        if ($effectiveEscrowCommission <= 0) {
            $effectiveEscrowCommission = (float) ($escrowMetaUnified['platform_commission'] ?? ($escrowMetaUnified['breakdown']['platform_commission'] ?? 0));
        }

        $effectiveEscrowStripeFee = (float) ($escrow->stripe_fees ?? 0);
        if ($effectiveEscrowStripeFee <= 0) {
            $effectiveEscrowStripeFee = (float) ($escrowMetaUnified['stripe_fees'] ?? ($escrowMetaUnified['breakdown']['stripe_fees'] ?? 0));
        }

        $effectiveEscrowNet = (float) ($escrow->prestataire_amount ?? 0);
        if ($effectiveEscrowNet <= 0) {
            $effectiveEscrowNet = (float) ($escrowMetaUnified['prestataire_receives'] ?? ($escrowMetaUnified['breakdown']['prestataire_receives'] ?? 0));
        }
        if ($effectiveEscrowNet <= 0 && $effectiveEscrowAmount > 0) {
            $effectiveEscrowNet = max(0, round($effectiveEscrowAmount - $effectiveEscrowStripeFee - $effectiveEscrowCommission, 2));
        }
    @endphp
    {{-- Hero Section --}}
    <section class="py-8 bg-gradient-to-r from-emerald-600 to-teal-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="text-white">
                    <h1 class="text-2xl font-bold flex items-center gap-2">
                        @if(str_contains($escrow->escrowable_type ?? '', 'Booking'))
                            📅 Réservation #{{ $escrow->escrowable_id }}
                        @elseif(str_contains($escrow->escrowable_type ?? '', 'Equipment'))
                            🔧 Location #{{ $escrow->escrowable_id }}
                        @elseif(str_contains($escrow->escrowable_type ?? '', 'UrgentSale'))
                            ⚡ Vente #{{ $escrow->escrowable_id }}
                        @else
                            🛡️ Transaction #{{ $escrow->id }}
                        @endif
                    </h1>
                    <p class="mt-1 text-emerald-100">Détails du paiement sécurisé</p>
                </div>
                <a href="{{ route('prestataire.escrow.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                    ← Retour à la liste
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Bloc Action Requise en haut --}}
        @if(!empty($actionNeeded))
            <div id="action-section" class="mb-6 p-5 rounded-xl border-2
                @if(($actionNeeded['urgency'] ?? '') === 'high') bg-red-50 border-red-300
                @elseif(($actionNeeded['urgency'] ?? '') === 'medium') bg-blue-50 border-blue-300
                @else bg-amber-50 border-amber-300
                @endif">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-2xl">{{ $actionNeeded['icon'] ?? '⚡' }}</span>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900">Action requise : {{ $actionNeeded['label'] ?? '' }}</h3>
                        @if(!empty($blockReason))
                            <p class="text-sm text-gray-600 mt-1">💡 {{ $blockReason }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @elseif(!empty($blockReason) && in_array($escrow->status, ['pending', 'held', 'partial', 'disputed']))
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                <span class="text-xl flex-shrink-0">💡</span>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Pourquoi les fonds sont bloqués ?</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $blockReason }}</p>
                </div>
            </div>
        @endif

        {{-- Bouton Demander le versement (quand client a confirmé et statut pas encore released) --}}
        @if(in_array($escrow->status, ['pending', 'held', 'partial']) && $escrow->client_confirmed_at)
            <div class="mb-6 p-5 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-3xl">💸</span>
                    <div class="flex-1">
                        <h3 class="font-bold text-green-900 text-lg">Fonds prêts à être versés</h3>
                        <p class="text-sm text-green-700 mt-1">
                            Le client a confirmé 
                            @if($escrow->client_confirmed_at)
                                le {{ \Carbon\Carbon::parse($escrow->client_confirmed_at)->format('d/m/Y à H:i') }}
                            @endif
                            @if($escrow->auto_release_at && now()->gt(\Carbon\Carbon::parse($escrow->auto_release_at)))
                                — le délai automatique de 48 h est dépassé.
                            @else
                                — vous pouvez demander le versement maintenant.
                            @endif
                        </p>
                    </div>
                </div>
                <form action="{{ route('prestataire.escrow.request-release', $escrow->id) }}" method="POST"
                      onsubmit="return confirm('Confirmer la demande de versement de {{ number_format($escrow->amount ?? 0, 2) }} € ?')">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-6 py-4 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all font-bold text-base shadow-lg hover:shadow-xl">
                        💰 Demander le versement — {{ number_format($escrow->amount ?? 0, 2) }} €
                    </button>
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Colonne principale --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Statut + Timeline --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Suivi de la transaction</h2>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                            @if(in_array($escrow->status, ['pending', 'held'])) bg-yellow-100 text-yellow-800
                            @elseif($escrow->status === 'partial') bg-purple-100 text-purple-800
                            @elseif($escrow->status === 'released') bg-green-100 text-green-800
                            @elseif($escrow->status === 'refunded') bg-blue-100 text-blue-800
                            @elseif($escrow->status === 'disputed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @switch($escrow->status)
                                @case('pending')
                                @case('held')
                                    🔒 Fonds bloqués
                                    @break
                                @case('partial')
                                    🟣 Paiement partiel reçu
                                    @break
                                @case('released')
                                    ✅ Paiement reçu
                                    @break
                                @case('refunded')
                                    ↩️ Remboursé au client
                                    @break
                                @case('disputed')
                                    ⚠️ Litige en cours
                                    @break
                                @default
                                    {{ $escrow->status }}
                            @endswitch
                        </span>
                    </div>

                    {{-- Rich Timeline --}}
                    @if(!empty($timeline))
                        <div class="relative">
                            <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                            <div class="space-y-5">
                                @foreach($timeline as $event)
                                    <div class="flex items-start gap-4 relative">
                                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center z-10
                                            @if(($event['status'] ?? '') === 'done') bg-green-500
                                            @elseif(($event['status'] ?? '') === 'waiting') bg-yellow-400
                                            @elseif(($event['status'] ?? '') === 'alert') bg-red-500
                                            @else bg-gray-300
                                            @endif">
                                            <span class="text-white text-sm">{{ $event['icon'] ?? '●' }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-sm text-gray-900">{{ $event['label'] ?? '' }}</p>
                                            @if(!empty($event['detail']))
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $event['detail'] }}</p>
                                            @endif
                                            @if(!empty($event['date']))
                                                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y à H:i') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Fallback timeline --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</div>
                                <div>
                                    <p class="font-medium text-sm">Paiement client reçu</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($escrow->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @if($escrow->client_confirmed_at)
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</div>
                                    <div>
                                        <p class="font-medium text-sm">Confirmé par le client</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($escrow->client_confirmed_at)->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @elseif(in_array($escrow->status, ['pending', 'held', 'partial']))
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-white text-xs">⏳</div>
                                    <div>
                                        <p class="font-medium text-sm text-yellow-600">En attente de confirmation client</p>
                                        @if($escrow->auto_release_at)
                                            <p class="text-xs text-green-600">Libération auto {{ \Carbon\Carbon::parse($escrow->auto_release_at)->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($escrow->released_at)
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</div>
                                    <div>
                                        <p class="font-medium text-sm text-green-600">Paiement reçu sur votre compte</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($escrow->released_at)->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Suivi livraison (vente urgente) --}}
                @if($shipment)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            📦 Suivi expédition
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Transporteur</p>
                                <p class="font-semibold text-sm">{{ ucfirst(str_replace('_', ' ', $shipment->carrier)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">N° de suivi</p>
                                <p class="font-semibold text-sm font-mono">{{ $shipment->tracking_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Statut</p>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($shipment->status === 'delivered') bg-green-100 text-green-800
                                    @elseif($shipment->status === 'in_transit') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($shipment->status === 'delivered') ✅ Livré
                                    @elseif($shipment->status === 'in_transit') 🚚 En transit
                                    @else {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if(str_contains($escrow->escrowable_type ?? '', 'UrgentSale') && $shipment->status !== 'delivered')
                            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-xs text-green-800 mb-2">
                                    <strong>💡 Prochaine étape :</strong> Quand le colis est arrivé, marquez-le comme livré. 
                                    Le client aura 48 h pour confirmer, sinon l'argent est automatiquement libéré.
                                </p>
                                <form action="{{ route('prestataire.escrow.mark-delivered', $escrow->id) }}" method="POST"
                                      onsubmit="return confirm('Marquer comme livré ? Cela démarre le délai de confirmation côté client.')">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                        ✅ Marquer comme livré
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @elseif(str_contains($escrow->escrowable_type ?? '', 'UrgentSale') && in_array($escrow->status, ['pending', 'held', 'partial']))
                    <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-blue-200" id="add-shipment">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">📦 Ajouter une expédition</h2>
                        <div class="p-3 bg-blue-50 rounded-lg mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>🔑 Pour débloquer vos fonds :</strong> Ajoutez les informations de livraison ci-dessous. 
                                Le client sera informé et devra confirmer la réception. Sans retour de sa part, l'argent sera libéré automatiquement sous 48 h.
                            </p>
                        </div>
                        
                        <button type="button" @click.prevent="showShipmentModal = true"
                                class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-sm">
                            📋 Ajouter les infos d'expédition
                        </button>
                    </div>
                @endif

                {{-- Validation retour équipement --}}
                @php
                    $isEquipmentEscrow = str_contains((string) ($escrow->escrowable_type ?? ''), 'Equipment');
                    $escrowStatusLower = strtolower((string) ($escrow->status ?? ''));
                    $canOpenReturnValidation = $isEquipmentEscrow
                        && !in_array($escrowStatusLower, ['refunded', 'cancelled', 'disputed'], true)
                        && empty($escrow->prestataire_confirmed_at)
                        && !$effectiveDepositProcessed;
                @endphp
                @if($canOpenReturnValidation)
                    <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-purple-200" id="validate-return">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">🔧 Valider le retour de l'équipement</h2>
                        <div class="p-3 bg-purple-50 rounded-lg mb-4">
                            <p class="text-xs text-purple-800">
                                <strong>🔑 Pour débloquer vos fonds :</strong> Inspectez l'équipement retourné et validez son état. 
                                Si l'équipement est en bon état, la caution sera restituée au client. 
                                En cas de dégâts, vous pouvez retenir un pourcentage de la caution.
                            </p>
                            @if($escrowStatusLower === 'released')
                                <p class="text-xs text-purple-700 mt-2">
                                    Le paiement de location est déjà libéré. Il reste à traiter la caution client.
                                </p>
                            @endif
                        </div>
                        
                        <button type="button" @click.prevent="openReturnModal()"
                                class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold text-sm">
                            🔍 Inspecter et valider le retour
                        </button>
                    </div>
                @endif

                {{-- Dossier litige (si existant) --}}
                @if(!empty($dispute))
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-400">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">⚠️ Dossier litige</h2>
                        <div class="p-3 bg-red-50 rounded-lg mb-4">
                            <p class="text-xs text-red-800">
                                Le client a ouvert un litige. L'argent reste bloqué le temps que la plateforme examine les preuves des deux côtés. 
                                <strong>La plateforme ne prend parti de personne</strong> — la décision sera basée uniquement sur les faits. Délai max : 5 jours ouvrés.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm"><span class="text-gray-500">Motif :</span> <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $dispute->reason ?? '')) }}</span></p>
                            <p class="text-sm"><span class="text-gray-500">Description :</span> <span class="text-gray-900">{{ $dispute->description }}</span></p>
                            @php
                                $evidence = [];
                                try { $evidence = $dispute->evidence ? json_decode($dispute->evidence, true) : []; } catch (\Exception $e) { $evidence = []; }
                            @endphp
                            @if(!empty($evidence))
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($evidence as $idx => $path)
                                        <a class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-600 hover:text-blue-800 text-sm rounded-lg border border-blue-200" href="{{ asset('storage/' . $path) }}" target="_blank">
                                            📎 Preuve {{ $idx + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(str_contains($escrow->escrowable_type ?? '', 'UrgentSale') && !in_array($escrow->status, ['released', 'refunded', 'cancelled'], true) && !empty($shipment) && ($shipment->status ?? null) !== 'returned')
                            <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-200">
                                <p class="text-xs text-red-700 mb-3">
                                    <strong>⚠️ Remboursement :</strong> À utiliser uniquement si vous avez bien reçu le retour du produit et que vous êtes d'accord pour un remboursement total.
                                </p>
                                <form action="{{ route('prestataire.escrow.return-received', $escrow->id) }}" method="POST"
                                      onsubmit="return confirm('Confirmer le retour reçu et rembourser le client en totalité ?')">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm">
                                        ↩️ Retour reçu — rembourser le client
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Colonne latérale --}}
            <div class="space-y-6">
                {{-- Info client --}}
                @if(!empty($client))
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">👤 Client</h2>
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-900">{{ $client->name ?? ($client->first_name ?? '') . ' ' . ($client->last_name ?? '') }}</p>
                            @if($client->phone ?? null)
                                <a href="tel:{{ $client->phone }}" class="inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-800">
                                    📞 {{ $client->phone }}
                                </a>
                            @endif
                            @if($client->email ?? null)
                                <p class="text-xs text-gray-500">{{ $client->email }}</p>
                            @endif
                        </div>
                        @if(in_array($escrow->status, ['pending', 'held', 'partial']) && ($client->phone ?? null))
                            <div class="mt-3 p-2 bg-emerald-50 rounded-lg">
                                <p class="text-xs text-emerald-700">💡 <strong>Astuce :</strong> Contactez le client pour lui rappeler de confirmer la prestation — ce sera plus rapide !</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Résumé financier --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">💰 Montants</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between py-2">
                            <span class="text-xs text-gray-500">Montant bloqué</span>
                            <span class="font-bold text-lg">{{ number_format($escrow->total_amount ?? $escrow->amount ?? 0, 2) }} €</span>
                        </div>
                        
                        @if($effectiveEscrowDepositAmount > 0)
                            <div class="flex justify-between py-2 border-t border-gray-100">
                                <span class="text-xs text-gray-500">Caution client</span>
                                <span class="font-medium text-sm">{{ number_format($effectiveEscrowDepositAmount, 2) }} €</span>
                            </div>

                            @if(str_contains((string) ($escrow->escrowable_type ?? ''), 'EquipmentRental'))
                                @php
                                    $relatedDepositStatus = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'deposit_status'))
                                        ? (string) ($relatedItem->deposit_status ?? '')
                                        : '';
                                    $relatedDepositRetained = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'deposit_retained'))
                                        ? (float) ($relatedItem->deposit_retained ?? 0)
                                        : null;
                                    $relatedDepositReason = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'deposit_retention_reason'))
                                        ? trim((string) ($relatedItem->deposit_retention_reason ?? ''))
                                        : '';
                                    $relatedReturnedAt = (is_object($relatedItem ?? null) && property_exists($relatedItem, 'equipment_returned_at'))
                                        ? ($relatedItem->equipment_returned_at ?? null)
                                        : null;

                                    $metaDepositStatus = strtolower((string) ($escrowMetaUnified['deposit_status'] ?? 'pending'));
                                    $relatedDepositStatusNorm = strtolower((string) ($relatedDepositStatus !== '' ? $relatedDepositStatus : 'pending'));
                                    if (in_array($relatedDepositStatusNorm, ['returned', 'partial', 'retained', 'released', 'refunded', 'completed', 'done', 'paid', 'partially_refunded', 'partially_returned', 'mixed', 'withheld', 'kept'], true)) {
                                        $depositStatus = $relatedDepositStatusNorm;
                                    } elseif (in_array($metaDepositStatus, ['returned', 'partial', 'retained', 'released', 'refunded', 'completed', 'done', 'paid', 'partially_refunded', 'partially_returned', 'mixed', 'withheld', 'kept'], true)) {
                                        $depositStatus = $metaDepositStatus;
                                    } else {
                                        $depositStatus = $relatedDepositStatus !== '' ? $relatedDepositStatusNorm : $metaDepositStatus;
                                    }
                                    $depositRetained = (float) (($relatedDepositRetained ?? null) ?? ($escrowMetaUnified['deposit_retained'] ?? 0));
                                    $depositReason = trim((string) (($relatedDepositReason !== '' ? $relatedDepositReason : null) ?: ($escrowMetaUnified['deposit_retention_reason'] ?? '')));
                                    $depositReturned = max(0, (float) $effectiveEscrowDepositAmount - $depositRetained);
                                    if (isset($escrowMetaUnified['deposit_returned'])) {
                                        $depositReturned = max(0, (float) $escrowMetaUnified['deposit_returned']);
                                    }

                                    $depositProcessedAt = null;
                                    $processedCandidate = $relatedReturnedAt ?: ($escrowMetaUnified['deposit_processed_at'] ?? null);
                                    if (!empty($processedCandidate)) {
                                        try {
                                            $depositProcessedAt = \Illuminate\Support\Carbon::parse($processedCandidate)->format('d/m/Y à H:i');
                                        } catch (\Throwable $e) {
                                            $depositProcessedAt = null;
                                        }
                                    }

                                    $isReturned = in_array($depositStatus, ['returned', 'released', 'refunded', 'completed', 'done', 'paid'], true) || ($depositRetained <= 0 && !empty($depositProcessedAt));
                                    $isRetained = in_array($depositStatus, ['retained', 'withheld', 'kept'], true) || ($depositRetained >= (float) $effectiveEscrowDepositAmount && (float) $effectiveEscrowDepositAmount > 0);
                                    $isPartial = $depositStatus === 'partial' || (!$isReturned && !$isRetained && $depositRetained > 0);
                                @endphp

                                <div class="mt-2 rounded-lg p-3 border {{ $isReturned ? 'bg-emerald-50 border-emerald-200' : ($isPartial ? 'bg-amber-50 border-amber-200' : ($isRetained ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200')) }}">
                                    <p class="text-xs font-medium mb-1 {{ $isReturned ? 'text-emerald-700' : ($isPartial ? 'text-amber-700' : ($isRetained ? 'text-red-700' : 'text-gray-700')) }}">
                                        Suivi caution
                                    </p>
                                    @if($isReturned)
                                        <p class="text-sm font-semibold text-emerald-800">Restituée au client: {{ number_format($depositReturned, 2) }} €</p>
                                    @elseif($isPartial)
                                        <p class="text-sm font-semibold text-amber-800">Retenue: {{ number_format($depositRetained, 2) }} € • Restituée: {{ number_format($depositReturned, 2) }} €</p>
                                    @elseif($isRetained)
                                        <p class="text-sm font-semibold text-red-800">Retenue intégrale: {{ number_format($depositRetained, 2) }} €</p>
                                    @else
                                        <p class="text-sm font-semibold text-gray-800">En attente de traitement après retour</p>
                                    @endif
                                    @if($depositReason !== '')
                                        <p class="text-xs mt-1 {{ $isRetained || $isPartial ? 'text-red-700' : 'text-gray-600' }}">{{ $depositReason }}</p>
                                    @endif
                                    @if($depositProcessedAt)
                                        <p class="text-xs mt-1 text-gray-600">Traitée le {{ $depositProcessedAt }}</p>
                                    @endif
                                </div>
                            @endif
                        @endif

                        @if($escrow->status === 'released')
                            <div class="flex justify-between py-2 border-t border-gray-100">
                                <span class="text-xs text-gray-500">Commission plateforme</span>
                                <span class="font-medium text-sm text-red-600">-{{ number_format($effectiveEscrowCommission, 2) }} €</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-100">
                                <span class="text-xs text-gray-500">Frais Stripe</span>
                                <span class="font-medium text-sm text-orange-600">-{{ number_format($effectiveEscrowStripeFee, 2) }} €</span>
                            </div>
                            <div class="flex justify-between py-2 border-t-2 border-gray-200">
                                <span class="text-sm text-gray-900 font-bold">Net perçu</span>
                                <span class="font-bold text-lg text-green-600">{{ number_format($effectiveEscrowNet, 2) }} €</span>
                            </div>
                        @endif
                    </div>

                    @if(in_array($escrow->status, ['pending', 'held', 'partial']))
                        <div class="mt-4 p-3 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-700">
                                @if($escrow->client_confirmed_at)
                                    ✅ Le client a confirmé ! Utilisez le bouton « Demander le versement » en haut de la page.
                                @else
                                    🕐 Le paiement sera libéré automatiquement sous 48 h si le client ne réagit pas.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Notation client --}}
                @if($canRate)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">⭐ Noter ce client</h2>
                        <p class="text-xs text-gray-500 mb-4">Votre retour aide la communauté des prestataires.</p>
                        
                        <button type="button" @click.prevent="showRatingModal = true"
                                class="w-full flex items-center justify-center px-4 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold text-sm">
                            ⭐ Donner une note
                        </button>
                    </div>
                @endif

                {{-- Aide rapide --}}
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">❓ Besoin d'aide ?</h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        <p>• <strong>Fonds bloqués</strong> — C'est normal. Ils sont sécurisés pour protéger les deux parties.</p>
                        <p>• <strong>Pour débloquer</strong> — Complétez les étapes demandées (expédition, validation…).</p>
                        <p>• <strong>Litige</strong> — Fournissez vos preuves dans la page de détails.</p>
                        <p>• <strong>Libération auto</strong> — Si le client ne réagit pas sous 48 h, vous êtes payé automatiquement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Expédition --}}
    <div x-show="showShipmentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showShipmentModal" @click="showShipmentModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showShipmentModal" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form action="{{ route('prestataire.escrow.shipment', $escrow->id) }}" method="POST">
                    @csrf
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <h3 class="mt-3 text-lg font-medium text-gray-900">Informations d'expédition</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transporteur</label>
                            <select name="carrier" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez...</option>
                                <option value="mondial_relay">Mondial Relay</option>
                                <option value="chronopost">Chronopost</option>
                                <option value="colissimo">Colissimo (La Poste)</option>
                                <option value="ups">UPS</option>
                                <option value="dhl">DHL</option>
                                <option value="hand_delivery">Remise en main propre</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro de suivi</label>
                            <input type="text" name="tracking_number" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ex: 1Z999AA10123456784">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Poids (kg)</label>
                            <input type="number" name="weight" step="0.1" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ex: 2.5">
                        </div>
                    </div>

                    <div class="mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 sm:col-start-2">Enregistrer</button>
                        <button type="button" @click="showShipmentModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Validation retour équipement --}}
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 z-[10000] overflow-y-auto pb-20 sm:pb-0">
        <div class="flex items-center justify-center min-h-screen px-4 py-6 pb-24 text-center sm:p-0">
            <div x-show="showReturnModal" @click="showReturnModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div x-show="showReturnModal" class="inline-block w-full max-w-lg max-h-[calc(100vh-8rem)] overflow-y-auto align-middle bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform transition-all sm:my-8 sm:p-6">
                <form action="{{ route('prestataire.escrow.confirm', $escrow->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-3 text-lg font-medium text-gray-900">Validation du retour</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">État de l'équipement</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button type="button" @click="setEquipmentStatus('good')"
                                    class="h-full text-left p-3 border rounded-lg transition-colors"
                                    :class="equipmentStatus === 'good' ? 'border-green-500 bg-green-50 ring-1 ring-green-200' : 'border-gray-200 hover:border-green-300'">
                                    <span class="block font-medium text-gray-900 leading-tight">✅ Bon état</span>
                                    <span class="block text-sm text-gray-500 leading-tight mt-1">L'équipement est retourné en bon état</span>
                                </button>
                                <button type="button" @click="setEquipmentStatus('damaged')"
                                    class="h-full text-left p-3 border rounded-lg transition-colors"
                                    :class="equipmentStatus === 'damaged' ? 'border-red-500 bg-red-50 ring-1 ring-red-200' : 'border-gray-200 hover:border-red-300'">
                                    <span class="block font-medium text-gray-900 leading-tight">⚠️ Endommagé</span>
                                    <span class="block text-sm text-gray-500 leading-tight mt-1">L'équipement présente des dégâts</span>
                                </button>
                            </div>
                            <input type="hidden" name="equipment_status" :value="equipmentStatus">
                        </div>

                        <div x-show="equipmentStatus === 'damaged'" :class="{ 'hidden': equipmentStatus !== 'damaged' }" class="space-y-4 p-4 bg-red-50 rounded-lg border border-red-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description des dégâts</label>
                                <textarea name="damage_description" :required="equipmentStatus === 'damaged'" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Décrivez les dommages constatés..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Caution à retenir (%)</label>
                                <input type="range" name="retain_deposit_percent" min="0" max="100" x-model="retainPercent" class="mt-1 w-full">
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>0%</span>
                                    <span class="font-medium text-red-600" x-text="retainPercent + '%'"></span>
                                    <span>100%</span>
                                </div>
                                @if($effectiveEscrowDepositAmount > 0)
                                    <p class="mt-1 text-sm text-red-600" x-text="'Montant retenu: ' + ({{ $effectiveEscrowDepositAmount }} * retainPercent / 100).toFixed(2) + ' €'"></p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Photos des dégâts (optionnel)</label>
                                <input type="file" name="damage_photos[]" multiple accept="image/*" class="mt-1 block w-full text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" @click="showReturnModal = false" class="h-11 w-full inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 bg-white text-gray-700 hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="h-11 w-full inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-white hover:bg-purple-700">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Notation --}}
    <div x-show="showRatingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showRatingModal" @click="showRatingModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showRatingModal" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form action="{{ route('prestataire.escrow.rate', $escrow->id) }}" method="POST">
                    @csrf
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <h3 class="mt-3 text-lg font-medium text-gray-900">Noter ce client</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 text-center mb-3">Votre note</label>
                            <div class="flex justify-center space-x-2">
                                <template x-for="i in 5" :key="i">
                                    <button type="button" @click="setRating(i)" class="focus:outline-none">
                                        <svg class="w-10 h-10 transition-colors" :class="i <= rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="rating" x-bind:value="rating">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Commentaire</label>
                            <textarea name="comment" rows="3" maxlength="500" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="would_recommend" value="1" checked class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Client fiable, je travaillerais de nouveau avec</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-white hover:bg-yellow-600 sm:col-start-2">Envoyer</button>
                        <button type="button" @click="showRatingModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
