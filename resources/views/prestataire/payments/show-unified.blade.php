@extends('layouts.prestataire')

@section('title', 'Détail Transaction')

@push('styles')
<style>
    .receipt-page { padding: 4px 0; }
    .receipt-card { border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .receipt-header { padding: 10px 12px; }
    .receipt-body { padding: 10px 12px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 15px; }
    .info-row.highlight { background: #f1f5f9; margin: 0 -8px; padding: 6px 8px; border-radius: 6px; }
    .section-title { font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; }
    .section-box { background: #f8fafc; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; }
    .divider { border-top: 1px dashed #cbd5e1; margin: 4px 0; }
    .net-box { background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 8px; padding: 10px; margin-top: 6px; display: flex; justify-content: space-between; align-items: center; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .mini-card { background: #f1f5f9; border-radius: 6px; padding: 6px 8px; }
    .mini-card-label { font-size: 11px; color: #64748b; font-weight: 600; }
    .mini-card-value { font-size: 14px; font-weight: 700; color: #1e293b; }
    .icon-circle { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
    .back-btn { display: inline-flex; align-items: center; gap: 4px; font-size: 14px; color: #6366f1; margin-bottom: 6px; text-decoration: none; font-weight: 500; }
    .action-btns { display: flex; gap: 6px; margin-top: 8px; }
    .action-btns button, .action-btns a { flex: 1; padding: 8px; border-radius: 6px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 4px; text-decoration: none; }
    .btn-outline { background: white; border: 1px solid #d1d5db; color: #374151; }
    .btn-primary { background: #6366f1; color: white; border: none; }
    @media print {
        @page { size: A4; margin: 10mm; }
        body { margin: 0; padding: 0; }
        body * { visibility: hidden; }
        .receipt-card, .receipt-card * { visibility: visible; }
        .receipt-card { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            max-width: 100%;
            box-shadow: none;
            border: 1px solid #ccc;
        }
        .receipt-header { padding: 8px 10px !important; }
        .receipt-body { padding: 8px 10px !important; }
        .section-box { padding: 6px 8px !important; margin-bottom: 6px !important; }
        .info-row { padding: 3px 0 !important; font-size: 12px !important; }
        .section-title { font-size: 11px !important; margin-bottom: 4px !important; }
        .net-box { padding: 8px !important; }
        .net-box span:last-child { font-size: 16px !important; }
        .grid-2 { gap: 4px !important; }
        .mini-card { padding: 4px 6px !important; }
        .mini-card-label { font-size: 9px !important; }
        .mini-card-value { font-size: 11px !important; }
        .divider { margin: 3px 0 !important; }
        .action-btns, .back-btn { display: none !important; }
        .icon-circle { width: 18px !important; height: 18px !important; font-size: 10px !important; }
    }
</style>
@endpush

@section('content')
<div class="receipt-page max-w-lg mx-auto">
    {{-- Retour --}}
    <a href="{{ route('prestataire.payments.unified') }}" class="back-btn">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour
    </a>

    @php
        $statusConfig = [
            'completed' => ['label' => 'Complété', 'bg' => 'from-green-500 to-emerald-600', 'icon' => '✓', 'color' => 'text-green-600'],
            'pending' => ['label' => 'En attente', 'bg' => 'from-yellow-500 to-orange-500', 'icon' => '⏳', 'color' => 'text-yellow-600'],
            'processing' => ['label' => 'En cours', 'bg' => 'from-blue-500 to-indigo-600', 'icon' => '🔄', 'color' => 'text-blue-600'],
            'refunded' => ['label' => 'Remboursé', 'bg' => 'from-red-500 to-pink-600', 'icon' => '↩️', 'color' => 'text-red-600'],
            'cancelled' => ['label' => 'Annulé', 'bg' => 'from-gray-500 to-gray-600', 'icon' => '✗', 'color' => 'text-gray-600'],
        ];
        $sConfig = $statusConfig[$payment['status']] ?? ['label' => ucfirst($payment['status']), 'bg' => 'from-gray-500 to-gray-600', 'icon' => '•', 'color' => 'text-gray-600'];

        $typeConfig = [
            'booking' => ['icon' => '📅', 'label' => 'Réservation', 'bg' => 'bg-blue-500'],
            'equipment' => ['icon' => '🔧', 'label' => 'Location', 'bg' => 'bg-purple-500'],
            'urgent_sale' => ['icon' => '🔥', 'label' => 'Vente Flash', 'bg' => 'bg-orange-500'],
            'food' => ['icon' => '🍽️', 'label' => 'Food', 'bg' => 'bg-green-500'],
        ];
        $tConfig = $typeConfig[$payment['type']] ?? ['icon' => '💰', 'label' => 'Transaction', 'bg' => 'bg-gray-500'];
        
        $normalizedType = \App\Services\CommissionService::normalizeType((string) ($payment['type'] ?? 'service'));
        $platformCommission = $payment['platform_commission'] ?? \App\Services\CommissionService::feeAmount($payment['amount'], $normalizedType, 'prestataire');
        $platformCommissionRate = isset($payment['platform_commission_rate'])
            ? (float) $payment['platform_commission_rate']
            : \App\Services\CommissionService::ratePercent($normalizedType, 'prestataire');
        if (($payment['type'] ?? '') === 'stripe' && !isset($payment['platform_commission_rate'])) {
            $platformCommissionRate = ((float) ($payment['amount'] ?? 0) > 0)
                ? round(((float) $platformCommission / (float) $payment['amount']) * 100, 2)
                : 0;
        }
        $stripeFee = $payment['stripe_fee'] ?? \App\Services\CommissionService::stripeFeesAmount($payment['amount']);
        $totalDeductions = $payment['total_deductions'] ?? $payment['commission'];
        $stripeFeePercent = (float) get_setting('stripe_fee_percent', '1.4');
        $stripeFeeFixed = (float) get_setting('stripe_fee_fixed', '0.25');
        $securityDeposit = (float) ($payment['security_deposit'] ?? 0);
        $grossAmount = (float) ($payment['gross_amount'] ?? $payment['amount']);
        $platformRateLabel = rtrim(rtrim(number_format($platformCommissionRate, 2, '.', ''), '0'), '.');
        $baseAmountLabel = (($payment['type'] ?? '') === 'equipment' && $securityDeposit > 0)
            ? 'Montant location (hors caution)'
            : 'Prix de vente';
    @endphp

    <div class="receipt-card bg-white">
        {{-- Header compact --}}
        <div class="receipt-header bg-gradient-to-r {{ $sConfig['bg'] }} text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ $tConfig['icon'] }}</span>
                    <div>
                        <div class="font-bold text-base truncate max-w-[200px]">{{ $payment['title'] }}</div>
                        <div class="text-white/80 text-sm">{{ $tConfig['label'] }} • {{ $payment['reference'] }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xl sm:text-2xl lg:text-3xl font-bold">{{ $sConfig['icon'] }}</div>
                    <div class="text-sm font-medium">{{ $sConfig['label'] }}</div>
                </div>
            </div>
        </div>

        <div class="receipt-body">
            {{-- SECTION: Détail financier --}}
            <div class="section-box">
                <div class="section-title">💰 Détail financier</div>
                
                <div class="info-row">
                    <span class="flex items-center gap-2">
                        <span class="icon-circle bg-blue-100 text-blue-600">💵</span>
                        <span class="font-medium">{{ $baseAmountLabel }}</span>
                    </span>
                    <span class="font-bold text-lg">{{ number_format($payment['amount'], 2) }} €</span>
                </div>

                @if(($payment['type'] ?? '') === 'equipment' && $securityDeposit > 0)
                    <div class="info-row text-blue-700">
                        <span class="flex items-center gap-2">
                            <span class="icon-circle bg-blue-100">🔒</span>
                            Caution client (séquestre)
                        </span>
                        <span class="font-semibold">{{ number_format($securityDeposit, 2) }} €</span>
                    </div>
                    <div class="info-row text-xs text-gray-500">
                        <span>Montant total débité client</span>
                        <span class="font-medium">{{ number_format($grossAmount, 2) }} €</span>
                    </div>
                @endif
                
                <div class="divider"></div>
                
                <div class="info-row text-red-600">
                    <span class="flex items-center gap-2">
                        <span class="icon-circle bg-red-100">🏢</span>
                        Commission ({{ $platformRateLabel }}%)
                    </span>
                    <span class="font-bold">-{{ number_format($platformCommission, 2) }} €</span>
                </div>
                
                <div class="info-row text-orange-600">
                    <span class="flex items-center gap-2">
                        <span class="icon-circle bg-orange-100">💳</span>
                        Stripe ({{ rtrim(rtrim(number_format($stripeFeePercent, 2, '.', ''), '0'), '.') }}%+{{ number_format($stripeFeeFixed, 2) }}€)
                    </span>
                    <span class="font-bold">-{{ number_format($stripeFee, 2) }} €</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="info-row highlight">
                    <span class="font-semibold text-gray-700">Total déductions</span>
                    <span class="font-bold text-red-600 text-base">-{{ number_format($totalDeductions, 2) }} €</span>
                </div>
                
                <div class="net-box">
                    <span class="font-bold text-base">✅ Net viré</span>
                    <span class="font-bold text-2xl">{{ number_format($payment['net_amount'], 2) }} €</span>
                </div>
            </div>

            {{-- SECTION: Client + Dates (côte à côte) --}}
            <div class="grid-2">
                <div class="mini-card">
                    <div class="mini-card-label">👤 Client</div>
                    <div class="mini-card-value truncate">{{ $payment['client_name'] }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ $payment['client_email'] }}</div>
                </div>
                <div class="mini-card">
                    <div class="mini-card-label">📅 Date</div>
                    <div class="mini-card-value">{{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y') }}</div>
                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($payment['date'])->format('H:i') }}</div>
                </div>
            </div>

            {{-- SECTION: Détails spécifiques (compact) --}}
            @if(($payment['type'] === 'booking' && isset($payment['booking'])) || 
                ($payment['type'] === 'equipment' && isset($payment['rental'])) ||
                ($payment['type'] === 'urgent_sale' && isset($payment['purchase'])) ||
                ($payment['type'] === 'food' && isset($payment['order'])))
                
                <div class="section-box mt-3">
                    <div class="section-title">📋 Détails</div>
                    
                    @switch($payment['type'])
                        @case('booking')
                            @php $booking = $payment['booking']; @endphp
                            <div class="info-row"><span class="text-gray-500">Service</span><span class="font-medium truncate max-w-[55%] text-right">{{ $booking->service->name ?? 'N/A' }}</span></div>
                            @if($booking->event_date)
                                <div class="info-row"><span class="text-gray-500">Date événement</span><span class="font-medium">{{ \Carbon\Carbon::parse($booking->event_date)->format('d/m/Y') }}</span></div>
                            @endif
                            @if($booking->guests)
                                <div class="info-row"><span class="text-gray-500">Invités</span><span class="font-medium">{{ $booking->guests }}</span></div>
                            @endif
                            @break
                            
                        @case('equipment')
                            @php $rental = $payment['rental']; @endphp
                            <div class="info-row"><span class="text-gray-500">Équipement</span><span class="font-medium truncate max-w-[55%] text-right">{{ $rental->equipment->name ?? 'N/A' }}</span></div>
                            @if($rental->start_date && $rental->end_date)
                                <div class="info-row"><span class="text-gray-500">Période</span><span class="font-medium">{{ \Carbon\Carbon::parse($rental->start_date)->format('d/m') }} → {{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y') }}</span></div>
                            @endif
                            @if($rental->quantity)
                                <div class="info-row"><span class="text-gray-500">Quantité</span><span class="font-medium">{{ $rental->quantity }}</span></div>
                            @endif
                            @php
                                $escrowRow = is_object($payment['escrow'] ?? null) ? (array) $payment['escrow'] : [];
                                $escrowMeta = [];
                                try {
                                    $escrowMeta = !empty($escrowRow['metadata'] ?? null)
                                        ? (json_decode((string) $escrowRow['metadata'], true) ?: [])
                                        : [];
                                } catch (\Throwable $e) {
                                    $escrowMeta = [];
                                }
                                $depositAmount = (float) ($rental->equipment->security_deposit ?? $rental->security_deposit ?? ($escrowRow['deposit_amount'] ?? 0));
                                $depositStatus = strtolower((string) (($rental->deposit_status ?? null) ?: ($escrowMeta['deposit_status'] ?? 'pending')));
                                $depositRetained = (float) (($rental->deposit_retained ?? null) ?? ($escrowMeta['deposit_retained'] ?? 0));
                                $depositReturned = max(0, $depositAmount - $depositRetained);
                                if (isset($escrowMeta['deposit_returned'])) {
                                    $depositReturned = max(0, (float) $escrowMeta['deposit_returned']);
                                }
                            @endphp
                            @if($depositAmount > 0)
                                <div class="divider"></div>
                                <div class="info-row text-xs">
                                    <span class="text-gray-500">Statut caution</span>
                                    @if($depositStatus === 'returned')
                                        <span class="font-semibold text-green-700">Remboursée ({{ number_format($depositReturned, 2) }} €)</span>
                                    @elseif($depositStatus === 'partial')
                                        <span class="font-semibold text-amber-700">Partielle (retenu {{ number_format($depositRetained, 2) }} €)</span>
                                    @elseif($depositStatus === 'retained')
                                        <span class="font-semibold text-red-700">Retenue ({{ number_format($depositRetained, 2) }} €)</span>
                                    @else
                                        <span class="font-semibold text-gray-600">En attente</span>
                                    @endif
                                </div>
                            @endif
                            @break
                            
                        @case('urgent_sale')
                            @php $purchase = $payment['purchase']; @endphp
                            <div class="info-row"><span class="text-gray-500">Article</span><span class="font-medium truncate max-w-[55%] text-right">{{ $purchase->urgentSale->title ?? 'N/A' }}</span></div>
                            @if($purchase->quantity)
                                <div class="info-row"><span class="text-gray-500">Quantité</span><span class="font-medium">{{ $purchase->quantity }}</span></div>
                            @endif
                            @if($purchase->urgentSale && $purchase->urgentSale->original_price)
                                <div class="info-row"><span class="text-gray-500">Prix original</span><span class="font-medium text-gray-400 line-through">{{ number_format($purchase->urgentSale->original_price, 2) }} €</span></div>
                            @endif
                            @break
                            
                        @case('food')
                            @php $order = $payment['order']; @endphp
                            <div class="info-row"><span class="text-gray-500">N° commande</span><span class="font-medium">{{ $order->order_number ?? '#'.$order->id }}</span></div>
                            @if($order->items && $order->items->count() > 0)
                                <div class="divider"></div>
                                @foreach($order->items->take(3) as $item)
                                    <div class="info-row text-xs">
                                        <span class="truncate max-w-[55%]">{{ $item->quantity }}x {{ $item->product_name ?? 'Produit' }}</span>
                                        <span>{{ number_format($item->total_price ?? 0, 2) }}€</span>
                                    </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <div class="text-xs text-gray-400 text-center">+{{ $order->items->count() - 3 }} autres</div>
                                @endif
                            @endif
                            @break
                    @endswitch
                </div>
            @endif

            {{-- Boutons d'action --}}
            <div class="action-btns">
                <button onclick="window.print()" class="btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer
                </button>
                <a href="{{ route('prestataire.payments.unified') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
