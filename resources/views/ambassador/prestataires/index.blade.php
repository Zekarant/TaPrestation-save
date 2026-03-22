@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes prestataires</h1>
            <p class="text-sm text-gray-500">{{ $prestataires->total() }} prestataire(s) affilié(s)</p>
        </div>
        <a href="{{ route('ambassador.prestataires.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm">
            <i class="fas fa-plus mr-1"></i>Ajouter un prestataire
        </a>
    </div>

    <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prestataire</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ville</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Source</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Inscrit le</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prestataires as $assignment)
                    <tr class="hover:bg-blue-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $assignment->prestataire->company_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->prestataire->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->prestataire->city ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($assignment->source === 'referral_link')
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Lien</span>
                            @elseif($assignment->source === 'manual_creation')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Manuel</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Admin</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('ambassador.prestataires.show', $assignment->prestataire) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            <p class="mb-2">Aucun prestataire affilié.</p>
                            <a href="{{ route('ambassador.prestataires.create') }}" class="text-blue-600 hover:underline text-sm">Ajouter votre premier prestataire</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prestataires->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $prestataires->links() }}</div>
        @endif
    </div>
</div>
@endsection
