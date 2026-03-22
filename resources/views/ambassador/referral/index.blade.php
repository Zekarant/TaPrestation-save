@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Parrainage</h1>

    <!-- Referral Link -->
    <div class="bg-white rounded-xl shadow border border-blue-200 p-5 mb-6">
        <h2 class="font-bold text-gray-900 mb-3"><i class="fas fa-link mr-2 text-blue-600"></i>Votre lien de parrainage</h2>
        <div class="flex items-center gap-3 mb-3">
            <input type="text" value="{{ $ambassador->referral_url }}" readonly id="referral-link"
                class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 font-mono text-sm">
            <button onclick="navigator.clipboard.writeText(document.getElementById('referral-link').value); this.innerHTML='<i class=\'fas fa-check mr-1\'></i>Copié !'; setTimeout(() => this.innerHTML='<i class=\'fas fa-copy mr-1\'></i>Copier', 2000)"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm whitespace-nowrap">
                <i class="fas fa-copy mr-1"></i>Copier
            </button>
        </div>
        <p class="text-xs text-gray-500">Partagez ce lien. Les prestataires qui s'inscrivent via ce lien seront automatiquement rattachés à votre compte.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow border border-purple-200 p-5 text-center">
            <p class="text-xs text-gray-500 mb-1">Visites totales</p>
            <p class="text-3xl font-bold text-purple-700">{{ $totalVisits }}</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-green-200 p-5 text-center">
            <p class="text-xs text-gray-500 mb-1">Conversions</p>
            <p class="text-3xl font-bold text-green-700">{{ $conversions }}</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-blue-200 p-5 text-center">
            <p class="text-xs text-gray-500 mb-1">Taux de conversion</p>
            <p class="text-3xl font-bold text-blue-700">{{ $conversionRate }}%</p>
        </div>
    </div>

    <!-- Recent Visits -->
    <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200">
            <h2 class="font-bold text-gray-900"><i class="fas fa-eye mr-2 text-purple-600"></i>Dernières visites</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600">Converti</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Prestataire</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentVisits as $visit)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $visit->visited_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($visit->converted)
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Oui</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs">Non</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $visit->convertedPrestataire->company_name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-400 text-sm">Aucune visite.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
