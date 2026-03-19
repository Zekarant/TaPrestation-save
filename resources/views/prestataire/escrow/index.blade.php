@extends('layouts.app')

@section('content')
<div class="prestataire-dashboard">
    {{-- Hero Section --}}
    <section class="py-8 bg-gradient-to-r from-emerald-600 to-teal-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="text-white">
                    <h1 class="text-2xl font-bold flex items-center gap-2">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Paiements Sécurisés
                    </h1>
                    <p class="mt-1 text-emerald-100">Suivi de vos transactions protégées</p>
                </div>
                <a href="{{ route('prestataire.dashboard') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                    ← Retour au tableau de bord
                </a>
            </div>
        </div>
    </section>

    {{-- Comment ça marche --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <div class="bg-white rounded-xl shadow-lg p-5 border border-emerald-100">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Comment ça marche ? — Le paiement sécurisé en 4 étapes</h3>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">1</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Le client paye</p>
                                <p class="text-xs text-gray-500">L'argent est bloqué en sécurité sur la plateforme</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold flex items-center justify-center">2</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Vous réalisez la prestation</p>
                                <p class="text-xs text-gray-500">Livraison, location, service… selon le type</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">3</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Le client confirme</p>
                                <p class="text-xs text-gray-500">Ou l'argent est libéré automatiquement sous 48 h</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center">4</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Vous recevez l'argent</p>
                                <p class="text-xs text-gray-500">Versé directement sur votre compte Stripe</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Statistiques --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-400">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-full bg-yellow-100 text-yellow-600">🔒</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Fonds en attente</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format($stats['total_held'] ?? 0, 2) }} €</p>
                        <p class="text-xs text-yellow-600 font-medium">{{ $stats['en_attente'] }} transaction(s)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-400">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-full bg-green-100 text-green-600">💰</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total reçu</p>
                        <p class="text-lg font-bold text-green-600">{{ number_format($stats['total_released'] ?? $stats['libérés'] ?? 0, 2) }} €</p>
                        <p class="text-xs text-green-600 font-medium">Versé sur votre compte</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-400">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-full bg-red-100 text-red-600">⚠️</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Litiges</p>
                        <p class="text-lg font-bold text-gray-900">{{ $stats['litiges'] }}</p>
                        <p class="text-xs text-red-600 font-medium">{{ $stats['litiges'] > 0 ? 'À traiter' : 'Aucun 👍' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-400">
                <a href="{{ route('prestataire.settings.payment-mode') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <div class="p-2.5 rounded-full bg-blue-100 text-blue-600">⚙️</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Mode de paiement</p>
                        <p class="text-sm font-bold text-gray-900">Configurer →</p>
                        <p class="text-xs text-blue-600 font-medium">Stripe, prélèvement…</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Alerte actions requises --}}
    @if(($stats['need_action'] ?? 0) > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
            <div class="p-2 rounded-full bg-amber-100 text-amber-700 flex-shrink-0 text-lg">⚡</div>
            <div>
                <p class="font-bold text-amber-900 text-sm">{{ $stats['need_action'] }} transaction(s) nécessitent votre action</p>
                <p class="text-xs text-amber-700 mt-0.5">Ajoutez les infos d'expédition, validez les retours ou confirmez les livraisons pour débloquer vos fonds.</p>
            </div>
        </div>
    </section>
    @endif

    {{-- Liste des transactions --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 pb-8">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Vos transactions</h2>
                <span class="text-sm text-gray-500">{{ $stats['total'] ?? $escrows->total() ?? 0 }} au total</span>
            </div>

            @if($escrows->isEmpty())
                <div class="p-12 text-center">
                    <div class="text-5xl mb-4">🛡️</div>
                    <p class="text-gray-900 font-semibold">Aucune transaction sécurisée</p>
                    <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                        Quand un client paye avec le système de paiement sécurisé, l'argent est bloqué sur la plateforme jusqu'à confirmation de la prestation. Vous voyez ici l'historique de ces transactions.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($escrows as $escrow)
                        <div class="p-5 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    {{-- Icône type --}}
                                    <div class="p-2.5 rounded-lg flex-shrink-0
                                        @if(str_contains($escrow->escrowable_type ?? '', 'Booking')) bg-blue-100 text-blue-600
                                        @elseif(str_contains($escrow->escrowable_type ?? '', 'Equipment')) bg-purple-100 text-purple-600
                                        @elseif(str_contains($escrow->escrowable_type ?? '', 'UrgentSale')) bg-orange-100 text-orange-600
                                        @else bg-gray-100 text-gray-600
                                        @endif">
                                        @if(str_contains($escrow->escrowable_type ?? '', 'Booking'))
                                            <span class="text-xl">📅</span>
                                        @elseif(str_contains($escrow->escrowable_type ?? '', 'Equipment'))
                                            <span class="text-xl">🔧</span>
                                        @elseif(str_contains($escrow->escrowable_type ?? '', 'UrgentSale'))
                                            <span class="text-xl">⚡</span>
                                        @else
                                            <span class="text-xl">💳</span>
                                        @endif
                                    </div>
                                    
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-gray-900 text-sm">
                                                @if(str_contains($escrow->escrowable_type ?? '', 'Booking'))
                                                    Réservation #{{ $escrow->escrowable_id }}
                                                @elseif(str_contains($escrow->escrowable_type ?? '', 'Equipment'))
                                                    Location équipement #{{ $escrow->escrowable_id }}
                                                @elseif(str_contains($escrow->escrowable_type ?? '', 'UrgentSale'))
                                                    Vente flash #{{ $escrow->escrowable_id }}
                                                @else
                                                    Transaction #{{ $escrow->id }}
                                                @endif
                                            </h3>
                                            {{-- Status badge --}}
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                @if($escrow->status === 'pending' || $escrow->status === 'held') bg-yellow-100 text-yellow-800
                                                @elseif($escrow->status === 'released') bg-green-100 text-green-800
                                                @elseif($escrow->status === 'refunded') bg-blue-100 text-blue-800
                                                @elseif($escrow->status === 'disputed') bg-red-100 text-red-800
                                                @elseif($escrow->status === 'partial') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @switch($escrow->status)
                                                    @case('pending')
                                                    @case('held')
                                                        🔒 Fonds bloqués
                                                        @break
                                                    @case('released')
                                                        ✅ Paiement reçu
                                                        @break
                                                    @case('refunded')
                                                        ↩️ Remboursé
                                                        @break
                                                    @case('disputed')
                                                    @case('dispute_review')
                                                        ⚠️ Litige
                                                        @break
                                                    @case('partial')
                                                        🟣 Partiel
                                                        @break
                                                    @default
                                                        {{ $escrow->status }}
                                                @endswitch
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-500 mt-1">
                                            👤 {{ $escrow->client_name ?? 'Client' }}
                                            · {{ \Carbon\Carbon::parse($escrow->created_at)->format('d/m/Y à H:i') }}
                                        </p>

                                        {{-- Block reason --}}
                                        @if($escrow->block_reason ?? null)
                                            <p class="text-xs text-amber-700 mt-1.5 bg-amber-50 rounded-md px-2 py-1 inline-block">
                                                💡 {{ Str::limit($escrow->block_reason, 120) }}
                                            </p>
                                        @endif

                                        {{-- Auto-release countdown --}}
                                        @if($escrow->auto_release_at && in_array($escrow->status, ['pending', 'held']))
                                            <p class="text-xs text-green-600 font-semibold mt-1">
                                                🕐 Libération auto {{ \Carbon\Carbon::parse($escrow->auto_release_at)->diffForHumans() }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <p class="text-lg font-bold text-gray-900">{{ number_format($escrow->total_amount ?? $escrow->amount ?? 0, 2) }} €</p>
                                    @if(($escrow->deposit_amount ?? 0) > 0)
                                        <p class="text-xs text-gray-500">+ {{ number_format($escrow->deposit_amount, 2) }} € caution</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                                {{-- Quick action pill --}}
                                @if($escrow->action_needed ?? null)
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
                                            @if(($escrow->action_needed['urgency'] ?? '') === 'high') bg-red-50 text-red-700 border border-red-200
                                            @elseif(($escrow->action_needed['urgency'] ?? '') === 'medium') bg-blue-50 text-blue-700 border border-blue-200
                                            @else bg-gray-50 text-gray-600 border border-gray-200
                                            @endif">
                                            {{ $escrow->action_needed['icon'] ?? '' }} {{ $escrow->action_needed['label'] ?? '' }}
                                        </span>
                                    </div>
                                @else
                                    <div></div>
                                @endif

                                <div class="flex items-center gap-2">
                                    {{-- Detail button --}}
                                    <a href="{{ route('prestataire.escrow.show', $escrow->id) }}" 
                                       class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                        📋 Voir détails
                                    </a>
                                    
                                    {{-- Quick action button --}}
                                    @if(($escrow->action_needed ?? null) && ($escrow->action_needed['urgency'] ?? '') !== 'wait')
                                        <a href="{{ route('prestataire.escrow.show', $escrow->id) }}#action-section" 
                                           class="inline-flex items-center gap-1 px-4 py-2 text-sm font-bold text-white rounded-lg transition-colors
                                            @if(($escrow->action_needed['color'] ?? '') === 'blue') bg-blue-600 hover:bg-blue-700
                                            @elseif(($escrow->action_needed['color'] ?? '') === 'green') bg-green-600 hover:bg-green-700
                                            @elseif(($escrow->action_needed['color'] ?? '') === 'purple') bg-purple-600 hover:bg-purple-700
                                            @elseif(($escrow->action_needed['color'] ?? '') === 'red') bg-red-600 hover:bg-red-700
                                            @else bg-gray-600 hover:bg-gray-700
                                            @endif">
                                            {{ $escrow->action_needed['icon'] ?? '' }} {{ $escrow->action_needed['label'] ?? '' }}
                                        </a>
                                    @endif

                                    {{-- Contact client --}}
                                    @if(($escrow->client_phone ?? null) && in_array($escrow->status, ['pending', 'held', 'partial', 'disputed']))
                                        <a href="tel:{{ $escrow->client_phone }}" 
                                           class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
                                           title="Appeler le client">
                                            📞
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $escrows->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- FAQ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                <span class="text-lg">❓</span> Questions fréquentes
            </h3>
            <div class="space-y-3">
                <details class="group">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-800 flex items-center justify-between py-2 hover:text-emerald-600">
                        Pourquoi mon argent est bloqué ?
                        <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="text-xs text-gray-600 mt-1 pb-2">
                        Pour protéger les deux parties, l'argent du client est sécurisé sur la plateforme pendant la prestation. 
                        Une fois que le client confirme la bonne réalisation <strong>ou après 48 h sans réaction</strong>, 
                        l'argent vous est automatiquement versé.
                    </p>
                </details>
                <details class="group">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-800 flex items-center justify-between py-2 hover:text-emerald-600">
                        Comment débloquer mes fonds plus vite ?
                        <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="text-xs text-gray-600 mt-1 pb-2">
                        <strong>Ventes :</strong> ajoutez les infos d'expédition et marquez comme livré.<br>
                        <strong>Locations :</strong> validez le retour de l'équipement.<br>
                        <strong>Réservations :</strong> la confirmation client est automatique après 48 h.<br>
                        <strong>Astuce :</strong> Contactez votre client — c'est le moyen le plus rapide !
                    </p>
                </details>
                <details class="group">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-800 flex items-center justify-between py-2 hover:text-emerald-600">
                        Que se passe-t-il en cas de litige ?
                        <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="text-xs text-gray-600 mt-1 pb-2">
                        L'argent reste bloqué pendant l'examen du dossier par la plateforme. 
                        Fournissez des preuves (photos, messages…) depuis la page de détails. 
                        Décision sous 5 jours ouvrés maximum.
                    </p>
                </details>
                <details class="group">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-800 flex items-center justify-between py-2 hover:text-emerald-600">
                        Qu'est-ce que la caution ?
                        <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="text-xs text-gray-600 mt-1 pb-2">
                        Pour les locations, une caution est bloquée en plus du prix. 
                        Si l'équipement est retourné en bon état, la caution est restituée au client. 
                        En cas de dégâts, vous pouvez retenir une partie proportionnelle aux dommages.
                    </p>
                </details>
            </div>
        </div>
    </section>
</div>
@endsection
