@extends('layouts.prestataire')

@section('title', 'Relevé ' . $invoice->invoice_number)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-4 sm:py-6">
    <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

        {{-- Actions --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('prestataire.invoices.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <button onclick="window.print()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
        </div>

        {{-- Relevé --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden" id="invoice">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 sm:px-8 py-6 text-white">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold">RELEVÉ DE VENTE</h1>
                        <p class="text-emerald-200 mt-1">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-white text-emerald-600">
                            ✓ VIRÉ SUR VOTRE COMPTE
                        </span>
                        <p class="text-emerald-200 mt-2 text-sm">{{ $invoice->issued_at?->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                {{-- Parties --}}
                <div class="mb-8 pb-6 border-b border-gray-200">
                    <div class="sm:flex sm:items-start sm:justify-between gap-6">
                        {{-- Vendeur --}}
                        <div>
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendeur</h3>
                            <p class="font-bold text-gray-900 text-lg">{{ $invoice->billing_name }}</p>
                            <p class="text-gray-600 text-sm">{{ $invoice->billing_email }}</p>
                            @if($invoice->billing_address)
                                <p class="text-gray-600 text-sm mt-1">{{ $invoice->billing_address }}</p>
                            @endif
                            @if($invoice->billing_siret)
                                <p class="text-gray-500 text-xs mt-2">SIRET: {{ $invoice->billing_siret }}</p>
                            @endif
                        </div>

                        {{-- Virement (à côté) --}}
                        <div class="mt-4 sm:mt-0 sm:text-right">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Paiement</h3>
                            <p class="font-semibold text-gray-900 text-sm">💳 Virement Stripe Connect</p>
                            <p class="text-sm text-gray-600">Le {{ $invoice->paid_at?->format('d/m/Y') }}</p>
                            <p class="font-bold text-green-600 text-lg">{{ number_format($invoice->net_amount, 2, ',', ' ') }}€</p>
                        </div>
                    </div>
                </div>

                {{-- Informations client original --}}
                @if($clientInvoice ?? null)
                    <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <h3 class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Transaction originale</h3>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Facture client:</span>
                                <span class="font-medium text-gray-900">{{ $clientInvoice->invoice_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Client:</span>
                                <span class="font-medium text-gray-900">{{ $clientInvoice->billing_name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Montant payé:</span>
                                <span class="font-medium text-gray-900">{{ number_format($clientInvoice->total, 2, ',', ' ') }}€</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Détail des articles vendus --}}
                @php
                    $detailItems = $clientInvoice && $clientInvoice->line_items ? $clientInvoice->line_items : ($invoice->line_items ?? []);
                @endphp
                
                <div class="mb-8 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Quantité</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Prix unit.</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if($detailItems && is_array($detailItems))
                                @foreach($detailItems as $item)
                                    @php
                                        $isDeduction = ($item['total'] ?? 0) < 0 || str_contains(strtolower($item['description'] ?? ''), 'commission');
                                    @endphp
                                    @if(!$isDeduction)
                                    <tr>
                                        <td class="py-4 px-4">
                                            <p class="font-medium text-gray-900">{{ $item['description'] ?? 'Article' }}</p>
                                            @if(isset($item['details']))
                                                <p class="text-sm text-gray-500">{{ $item['details'] }}</p>
                                            @endif
                                            @if(isset($item['reference']))
                                                <p class="text-sm text-gray-500">Réf: {{ $item['reference'] }}</p>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center text-gray-700">
                                            {{ $item['quantity'] ?? 1 }}{{ isset($item['unit']) ? ' ' . $item['unit'] : '' }}
                                        </td>
                                        <td class="py-4 px-4 text-right text-gray-700">
                                            @if(isset($item['unit_price']))
                                                {{ number_format($item['unit_price'], 2, ',', ' ') }} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right font-semibold text-gray-900">
                                            {{ number_format($item['total'] ?? 0, 2, ',', ' ') }} €
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="py-3 px-4 text-right font-semibold text-gray-700">Sous-total</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Récapitulatif Détaillé --}}
                @php
                    $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : $invoice->notes;
                    $stripeFee = $notes['stripe_fee'] ?? 0;
                    $totalDeductions = $notes['total_deductions'] ?? ($invoice->commission_amount + $stripeFee);
                @endphp
                
                <div class="flex justify-end">
                    <div class="w-full sm:w-96">
                        <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-5 border border-gray-200">
                            <h4 class="font-bold text-gray-800 mb-4 text-center uppercase tracking-wide text-sm">Détail des déductions</h4>
                            
                            <div class="space-y-3">
                                {{-- Montant brut --}}
                                <div class="flex justify-between items-center text-gray-700">
                                    <span class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs">💰</span>
                                        Prix de vente
                                    </span>
                                    <span class="font-bold text-lg text-gray-900">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</span>
                                </div>
                                
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                
                                {{-- Commission plateforme --}}
                                <div class="flex justify-between items-center text-red-600">
                                    <span class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xs">🏢</span>
                                        Commission TaPrestation ({{ number_format($invoice->commission_rate, 0) }}%)
                                    </span>
                                    <span class="font-semibold">-{{ number_format($invoice->commission_amount, 2, ',', ' ') }} €</span>
                                </div>
                                
                                {{-- Frais Stripe --}}
                                @if($stripeFee > 0)
                                <div class="flex justify-between items-center text-orange-600">
                                    <span class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs">💳</span>
                                        Frais Stripe (transaction)
                                    </span>
                                    <span class="font-semibold">-{{ number_format($stripeFee, 2, ',', ' ') }} €</span>
                                </div>
                                @endif
                                
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                
                                {{-- Total déductions --}}
                                <div class="flex justify-between items-center text-gray-700 bg-gray-200/50 -mx-2 px-2 py-2 rounded-lg">
                                    <span class="font-medium">Total des déductions</span>
                                    <span class="font-bold text-red-600">-{{ number_format($totalDeductions, 2, ',', ' ') }} €</span>
                                </div>
                                
                                <div class="border-t-2 border-gray-300 my-3"></div>
                                
                                {{-- Net versé --}}
                                <div class="flex justify-between items-center bg-gradient-to-r from-green-500 to-emerald-500 text-white -mx-2 px-4 py-4 rounded-xl shadow-lg">
                                    <span class="flex items-center gap-2 font-bold text-lg">
                                        <span class="text-2xl">✅</span>
                                        Net viré sur votre compte
                                    </span>
                                    <span class="font-bold text-2xl">{{ number_format($invoice->net_amount, 2, ',', ' ') }} €</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Explication --}}
                        <div class="mt-4 text-xs text-gray-500 text-center">
                            <p>Le montant net a été automatiquement viré sur votre compte Stripe Connect</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Reset et base */
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
    
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Masquer tout sauf la facture */
    body * { 
        visibility: hidden; 
    }
    
    #invoice, #invoice * { 
        visibility: visible; 
    }
    
    #invoice { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100%; 
        max-width: 100% !important;
        box-shadow: none !important; 
        border-radius: 0 !important;
        margin: 0 !important;
    }
    
    button, a { 
        display: none !important; 
    }
    
    /* Réduire les espacements pour tenir sur une page */
    #invoice .p-6, #invoice .p-8, #invoice .sm\:p-8 {
        padding: 12px !important;
    }
    
    #invoice .px-6, #invoice .px-8, #invoice .sm\:px-8 {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
    
    #invoice .py-6 {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    
    #invoice .py-4 {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }
    
    #invoice .mb-8 {
        margin-bottom: 12px !important;
    }
    
    #invoice .mb-6 {
        margin-bottom: 10px !important;
    }
    
    #invoice .mt-8 {
        margin-top: 12px !important;
    }
    
    #invoice .mt-6 {
        margin-top: 10px !important;
    }
    
    #invoice .mt-4 {
        margin-top: 6px !important;
    }
    
    #invoice .pb-6 {
        padding-bottom: 10px !important;
    }
    
    #invoice .pt-6 {
        padding-top: 10px !important;
    }
    
    #invoice .gap-6 {
        gap: 10px !important;
    }
    
    #invoice .gap-4 {
        gap: 6px !important;
    }
    
    #invoice .space-y-3 > * + * {
        margin-top: 6px !important;
    }
    
    /* Réduire les tailles de police */
    #invoice h1 {
        font-size: 20px !important;
    }
    
    #invoice .text-2xl {
        font-size: 16px !important;
    }
    
    #invoice .text-xl {
        font-size: 14px !important;
    }
    
    #invoice .text-lg {
        font-size: 13px !important;
    }
    
    #invoice .text-sm {
        font-size: 11px !important;
    }
    
    #invoice .text-xs {
        font-size: 10px !important;
    }
    
    /* Réduire la taille des icônes */
    #invoice .w-12 {
        width: 32px !important;
        height: 32px !important;
    }
    
    #invoice .w-6 {
        width: 18px !important;
        height: 18px !important;
    }
    
    /* Ajuster les arrondis */
    #invoice .rounded-xl {
        border-radius: 8px !important;
    }
    
    #invoice .rounded-lg {
        border-radius: 6px !important;
    }
    
    /* Section déductions plus compacte */
    #invoice .sm\:w-96 {
        width: 280px !important;
    }
    
    #invoice .p-5 {
        padding: 10px !important;
    }
    
    #invoice .px-4 {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    #invoice .py-4 {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
    
    /* Éviter les coupures de page */
    #invoice, #invoice > * {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
@endsection
