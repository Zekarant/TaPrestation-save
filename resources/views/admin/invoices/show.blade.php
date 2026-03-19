@extends('layouts.admin-modern')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux factures
            </a>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
        </div>

        {{-- Info boxes --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Type</p>
                <p class="font-bold text-lg text-gray-900">{{ $invoice->type_label }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Montant TTC</p>
                <p class="font-bold text-lg text-blue-600">{{ number_format($invoice->total, 2, ',', ' ') }}€</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Commission</p>
                <p class="font-bold text-lg text-purple-600">{{ number_format($invoice->commission_amount, 2, ',', ' ') }}€</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Statut</p>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 
                       ($invoice->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ $invoice->status_label }}
                </span>
            </div>
        </div>

        {{-- Facture complète --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden" id="invoice">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-8 py-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $invoice->type === 'client' ? 'FACTURE' : 'RELEVÉ' }}</h1>
                        <p class="text-gray-300 mt-1">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-300">Émise le {{ optional($invoice->issued_at)->format('d/m/Y à H:i') }}</p>
                        @if($invoice->paid_at)
                            <p class="text-sm text-green-400">Payée le {{ $invoice->paid_at->format('d/m/Y à H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-8">
                {{-- Parties --}}
                <div class="grid grid-cols-2 gap-8 mb-8 pb-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            {{ $invoice->type === 'client' ? 'Vendeur' : 'Émetteur' }}
                        </h3>
                        <p class="font-bold text-gray-900 text-lg">{{ $invoice->seller_name }}</p>
                        @if($invoice->seller_address)
                            <p class="text-gray-600">{{ $invoice->seller_address }}</p>
                        @endif
                        @if($invoice->seller_siret)
                            <p class="text-gray-500 text-sm mt-2">SIRET: {{ $invoice->seller_siret }}</p>
                        @endif
                        @if($invoice->seller_vat_number)
                            <p class="text-gray-500 text-sm">N° TVA: {{ $invoice->seller_vat_number }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            {{ $invoice->type === 'client' ? 'Client' : 'Bénéficiaire' }}
                        </h3>
                        <p class="font-bold text-gray-900 text-lg">{{ $invoice->billing_name }}</p>
                        <p class="text-gray-600">{{ $invoice->billing_email }}</p>
                        @if($invoice->billing_phone)
                            <p class="text-gray-600">{{ $invoice->billing_phone }}</p>
                        @endif
                        @if($invoice->billing_address)
                            <p class="text-gray-600 mt-1">{{ $invoice->billing_address }}</p>
                            @if($invoice->billing_postal_code || $invoice->billing_city)
                                <p class="text-gray-600">{{ $invoice->billing_postal_code }} {{ $invoice->billing_city }}</p>
                            @endif
                        @endif
                        @if($invoice->billing_siret)
                            <p class="text-gray-500 text-sm mt-2">SIRET: {{ $invoice->billing_siret }}</p>
                        @endif
                    </div>
                </div>

                {{-- Prestataire info (admin only) --}}
                @if($invoice->prestataire)
                    <div class="mb-6 p-4 bg-indigo-50 rounded-xl">
                        <h3 class="text-xs font-semibold text-indigo-600 uppercase mb-2">Prestataire associé</h3>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-700 font-bold text-lg">
                                {{ strtoupper(substr(data_get($invoice, 'prestataire.user.name', 'P'), 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ data_get($invoice, 'prestataire.user.name', 'N/A') }}</p>
                                <p class="text-sm text-gray-500">{{ data_get($invoice, 'prestataire.user.email', '') }}</p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-sm text-gray-500">Commission</p>
                                <p class="font-bold text-purple-600">{{ number_format($invoice->commission_amount, 2) }}€ ({{ $invoice->commission_rate }}%)</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Net versé</p>
                                <p class="font-bold text-green-600">{{ number_format($invoice->net_amount, 2) }}€</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                @if($invoice->description)
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2">Description</h3>
                        <p class="text-gray-700">{{ $invoice->description }}</p>
                    </div>
                @endif

                {{-- Lignes --}}
                <div class="mb-8 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Description</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Qté</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Prix unit.</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if($invoice->line_items && is_array($invoice->line_items))
                                @foreach($invoice->line_items as $item)
                                    <tr>
                                        <td class="py-4 px-4">
                                            <p class="font-medium text-gray-900">{{ $item['description'] ?? 'Article' }}</p>
                                            @if(isset($item['details']))
                                                <p class="text-sm text-gray-500">{{ $item['details'] }}</p>
                                            @endif
                                            @if(isset($item['reference']))
                                                <p class="text-xs text-gray-400">Réf: {{ $item['reference'] }}</p>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center text-gray-600">
                                            {{ $item['quantity'] ?? 1 }}{{ isset($item['unit']) ? ' ' . $item['unit'] : '' }}
                                        </td>
                                        <td class="py-4 px-4 text-right text-gray-600">
                                            {{ isset($item['unit_price']) ? number_format($item['unit_price'], 2, ',', ' ') . ' €' : '-' }}
                                        </td>
                                        <td class="py-4 px-4 text-right font-semibold {{ ($item['total'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($item['total'] ?? 0, 2, ',', ' ') }} €
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="py-4 px-4" colspan="3">
                                        <p class="font-medium text-gray-900">{{ $invoice->description }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-right font-semibold text-gray-900">
                                        {{ number_format($invoice->subtotal, 2, ',', ' ') }} €
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Totaux avec détail complet --}}
                @php
                    $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : $invoice->notes;
                    $stripeFee = $notes['stripe_fee'] ?? 0;
                    $totalDeductions = $notes['total_deductions'] ?? ($invoice->commission_amount + $stripeFee);
                @endphp
                
                <div class="flex justify-end mb-8">
                    <div class="w-96">
                        <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-5 border border-gray-200">
                            <h4 class="font-bold text-gray-700 mb-4 text-center uppercase tracking-wide text-xs">Détail financier complet</h4>
                            
                            <div class="space-y-2 text-sm">
                                {{-- Montant brut --}}
                                <div class="flex justify-between">
                                    <span class="text-gray-600">💰 Prix de vente</span>
                                    <span class="font-bold text-gray-900">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</span>
                                </div>
                                
                                @if($invoice->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">TVA ({{ number_format($invoice->tax_rate, 1) }}%)</span>
                                        <span>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} €</span>
                                    </div>
                                @endif
                                
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                
                                {{-- Commission plateforme --}}
                                <div class="flex justify-between text-red-600">
                                    <span>🏢 Commission TaPrestation ({{ $invoice->commission_rate }}%)</span>
                                    <span class="font-semibold">-{{ number_format($invoice->commission_amount, 2, ',', ' ') }} €</span>
                                </div>
                                
                                {{-- Frais Stripe --}}
                                @if($stripeFee > 0)
                                <div class="flex justify-between text-orange-600">
                                    <span>💳 Frais Stripe (1.4% + 0.25€)</span>
                                    <span class="font-semibold">-{{ number_format($stripeFee, 2, ',', ' ') }} €</span>
                                </div>
                                @endif
                                
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                
                                {{-- Total déductions --}}
                                @if($totalDeductions > 0)
                                <div class="flex justify-between bg-gray-200/50 -mx-2 px-2 py-2 rounded">
                                    <span class="font-medium text-gray-700">Total déductions</span>
                                    <span class="font-bold text-red-600">-{{ number_format($totalDeductions, 2, ',', ' ') }} €</span>
                                </div>
                                @endif
                                
                                <div class="border-t-2 border-gray-300 my-3"></div>
                                
                                {{-- Net versé au prestataire --}}
                                <div class="flex justify-between bg-green-100 -mx-2 px-3 py-3 rounded-lg">
                                    <span class="font-bold text-green-800">✅ Net versé au prestataire</span>
                                    <span class="font-bold text-lg text-green-700">{{ number_format($invoice->net_amount, 2, ',', ' ') }} €</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Paiement --}}
                @if($invoice->payment_method || $invoice->payment_reference)
                    <div class="p-4 bg-gray-50 rounded-xl mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2">Informations de paiement</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            @if($invoice->payment_method)
                                <div>
                                    <span class="text-gray-500">Méthode:</span>
                                    <span class="font-medium ml-2">{{ ucfirst($invoice->payment_method) }}</span>
                                </div>
                            @endif
                            @if($invoice->payment_reference)
                                <div>
                                    <span class="text-gray-500">Référence:</span>
                                    <span class="font-mono text-xs ml-2">{{ $invoice->payment_reference }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($invoice->notes)
                    <div class="mb-4">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2">Notes</h3>
                        <p class="text-gray-600">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Factures liées --}}
        @if($relatedInvoices->count() > 0)
            <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">📎 Factures liées</h3>
                <div class="space-y-2">
                    @foreach($relatedInvoices as $related)
                        <a href="{{ route('admin.invoices.show', $related) }}" 
                           class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div>
                                <span class="font-medium text-gray-900">{{ $related->invoice_number }}</span>
                                <span class="text-sm text-gray-500 ml-2">{{ $related->type_label }}</span>
                            </div>
                            <span class="font-semibold {{ $related->type === 'prestataire' ? 'text-green-600' : 'text-blue-600' }}">
                                {{ number_format($related->total, 2) }}€
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #invoice, #invoice * { visibility: visible; }
    #invoice { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; }
    button, a { display: none !important; }
}
</style>
@endsection
