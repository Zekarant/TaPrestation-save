@extends('layouts.admin-modern')

@section('title', 'Gestion des Ambassadeurs')

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Ambassadeurs
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez les ambassadeurs et leurs commissions.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-800">Total :</span>
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $ambassadors->total() }} ambassadeur(s)
                    </span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.ambassadors.payouts.index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-money-bill-wave mr-2"></i>Payouts
                    </a>
                    <a href="{{ route('admin.ambassadors.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-200 shadow-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Nouvel ambassadeur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 mb-6">
        <form method="GET" class="bg-white rounded-xl shadow-lg border border-blue-200 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou email..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm">
                    <i class="fas fa-search mr-1"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pb-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-50 border-b border-blue-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Ambassadeur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Code</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase">Prestataires</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Commission totale</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Non payé</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase">Statut</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ambassadors as $ambassador)
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $ambassador->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $ambassador->user->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{ $ambassador->referral_code }}</code>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                    {{ $ambassador->assignments_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                {{ number_format($ambassador->total_commission_earned, 2, ',', ' ') }} &euro;
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-orange-600">
                                {{ number_format($ambassador->unpaid_commission, 2, ',', ' ') }} &euro;
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ambassador->status === 'active')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Actif</span>
                                @elseif($ambassador->status === 'suspended')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">Suspendu</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="text-blue-600 hover:text-blue-800" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.ambassadors.edit', $ambassador) }}" class="text-yellow-600 hover:text-yellow-800" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-handshake text-4xl text-gray-300 mb-3 block"></i>
                                Aucun ambassadeur pour le moment.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ambassadors->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $ambassadors->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
