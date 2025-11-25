@extends('layouts.app')

@section('content')
<div class="container mx-auto px-2 sm:px-4">
    <div class="py-4 sm:py-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold leading-tight">Missions en Cours</h2>
        </div>
        <div class="-mx-2 sm:-mx-4 px-2 sm:px-4 py-3 sm:py-4 overflow-x-auto">
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th
                                class="px-3 py-2 sm:px-5 sm:py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs sm:text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Titre de la Demande
                            </th>
                            <th
                                class="px-3 py-2 sm:px-5 sm:py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs sm:text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Client
                            </th>
                            <th
                                class="px-3 py-2 sm:px-5 sm:py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs sm:text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Statut
                            </th>
                            <th
                                class="px-3 py-2 sm:px-5 sm:py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs sm:text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Dernière Mise à Jour
                            </th>
                            <th class="px-3 py-2 sm:px-5 sm:py-3 border-b-2 border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missions as $mission)
                        <tr>
                            <td class="px-3 py-3 sm:px-5 sm:py-5 border-b border-gray-200 bg-white text-xs sm:text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $mission->title }}</p>
                            </td>
                            <td class="px-3 py-3 sm:px-5 sm:py-5 border-b border-gray-200 bg-white text-xs sm:text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ $mission->client && $mission->client->user ? $mission->client->user->name : 'Client non disponible' }}
                                </p>
                            </td>
                            <td class="px-3 py-3 sm:px-5 sm:py-5 border-b border-gray-200 bg-white text-xs sm:text-sm">
                                <span
                                    class="relative inline-block px-2 py-1 sm:px-3 sm:py-1 font-semibold text-green-900 leading-tight">
                                    <span aria-hidden
                                        class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                    <span class="relative text-xs sm:text-sm">{{ ucfirst(str_replace('_', ' ', $mission->status)) }}</span>
                                </span>
                            </td>
                            <td class="px-3 py-3 sm:px-5 sm:py-5 border-b border-gray-200 bg-white text-xs sm:text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ $mission->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </td>
                            <td class="px-3 py-3 sm:px-5 sm:py-5 border-b border-gray-200 bg-white text-xs sm:text-sm text-right">
                                <a href="{{ route('prestataire.requests.show', $mission) }}" class="text-indigo-600 hover:text-indigo-900 text-xs sm:text-sm">Voir Détails</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection