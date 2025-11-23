@extends('layouts.admin-modern')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="mb-6">
            <a href="{{ route('administrateur.reviews.index') }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center w-fit">
                <i class="bi bi-arrow-left mr-2"></i> Retour à la liste
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 mb-6">
                    <div class="px-6 py-4 border-b border-blue-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h2 class="text-xl font-bold text-blue-900">Détails de l'avis #{{ $review->id }}</h2>
                        <div class="flex gap-2">
                            @if(!$review->moderated_by)
                                <form action="{{ route('administrateur.reviews.moderate', $review->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" title="Marquer comme modéré">
                                        <i class="bi bi-check-lg mr-2"></i> Marquer comme modéré
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('administrateur.reviews.destroy', $review->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                    <i class="bi bi-trash mr-2"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">Note</h3>
                            <div class="flex items-center mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill text-yellow-400' : 'bi-star text-gray-300' }} mr-1 text-2xl"></i>
                                @endfor
                                <span class="ml-3 text-lg font-bold text-blue-900">{{ $review->rating }}/5</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">Commentaire</h3>
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-gray-700 leading-relaxed">{{ $review->comment }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">Date de création</h3>
                                <p class="text-gray-600">{{ $review->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">Dernière mise à jour</h3>
                                <p class="text-gray-600">{{ $review->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">Statut de modération</h3>
                            @if($review->moderated_by)
                                <div class="flex items-center">
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium mr-3">Modéré</span>
                                    <span class="text-gray-600">par {{ $review->moderator->name ?? 'Administrateur' }} le {{ $review->updated_at->format('d/m/Y à H:i') }}</span>
                                </div>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Non modéré</span>
                            @endif
                        </div>
                    </div>
            </div>
        </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200">
                    <div class="px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900">Client</h3>
                    </div>
                    <div class="p-6">
                        @if($review->client)
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($review->client_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $review->client_name }}</h4>
                                    <p class="text-gray-600">{{ $review->client_email ?? 'Email non disponible' }}</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="font-semibold text-blue-900">Inscrit le:</span>
                                    <span class="text-gray-600">{{ $review->client->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-blue-900">Nombre d'avis:</span>
                                    <span class="text-gray-600">{{ $review->client->client && $review->client->client ? $review->client->client->reviews->count() : 0 }}</span>
                                </div>
                                @if($review->client->client)
                                <div class="mt-4">
                                    <a href="{{ route('administrateur.clients.show', $review->client->client->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center w-fit">
                                        <i class="bi bi-person mr-2"></i> Voir le profil
                                    </a>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">Client non disponible ou supprimé.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg border border-blue-200">
                    <div class="px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900">Prestataire</h3>
                    </div>
                    <div class="p-6">
                        @if($review->prestataire && $review->prestataire->user)
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($review->prestataire->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $review->prestataire->user->name }}</h4>
                                    <p class="text-gray-600">{{ $review->prestataire->user->email }}</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="font-semibold text-blue-900">Secteur:</span>
                                    <span class="text-gray-600">{{ $review->prestataire->sector ?? 'Non spécifié' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-blue-900">Localisation:</span>
                                    <span class="text-gray-600">{{ $review->prestataire->location ?? 'Non spécifiée' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-blue-900">Statut:</span>
                                    @if($review->prestataire->approved_at)
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium ml-2">Approuvé</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium ml-2">En attente</span>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('administrateur.prestataires.show', $review->prestataire->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center w-fit">
                                        <i class="bi bi-person mr-2"></i> Voir le profil
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">Prestataire non disponible ou supprimé.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection