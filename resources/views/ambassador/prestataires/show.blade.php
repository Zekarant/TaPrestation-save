@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('ambassador.prestataires.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left mr-1"></i>Retour
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $prestataire->company_name }}</h1>
        <p class="text-sm text-gray-500">{{ $prestataire->user->email ?? '' }} &middot; {{ $prestataire->city ?? '' }}</p>
    </div>

    <!-- Info -->
    <div class="bg-white rounded-xl shadow border border-blue-200 p-5 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Source</p>
                <p class="text-sm font-semibold">{{ ucfirst(str_replace('_', ' ', $assignment->source)) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Affilié depuis</p>
                <p class="text-sm font-semibold">{{ $assignment->assigned_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Services actifs</p>
                <p class="text-sm font-semibold">{{ $prestataire->services->count() }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Réservations</p>
                <p class="text-sm font-semibold">{{ $prestataire->bookings->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Commissions for this prestataire -->
    <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200">
            <h2 class="font-bold text-gray-900"><i class="fas fa-coins mr-2 text-green-600"></i>Commissions générées</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Type</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Transaction</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Commission</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commissions as $c)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $c->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-sm">{{ ucfirst($c->order_type) }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format($c->base_amount, 2, ',', ' ') }}&euro;</td>
                        <td class="px-4 py-2 text-sm text-right font-bold text-green-700">{{ number_format($c->commission_amount, 2, ',', ' ') }}&euro;</td>
                        <td class="px-4 py-2 text-center">
                            @if($c->status === 'paid')
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Payé</span>
                            @elseif($c->status === 'pending')
                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">En attente</span>
                            @elseif($c->status === 'cancelled')
                                <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">Annulé</span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">{{ ucfirst($c->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">Aucune commission pour ce prestataire.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
