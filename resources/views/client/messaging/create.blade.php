@extends('layouts.app')

@section('content')
<div class="py-10">
    <header>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('messaging.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-tight text-gray-900">Nouvelle conversation</h1>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form action="{{ route('prestataire.messages.start-conversation', $prestataire->user) }}" method="POST">
                            @csrf
                            
                            <!-- Informations du prestataire -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prestataire</label>
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    @if($prestataire->user->profile_photo_url)
                <img class="h-12 w-12 rounded-full mr-4" src="{{ $prestataire->user->profile_photo_url }}" alt="{{ $prestataire->user->name ?? 'Prestataire' }}">
            @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                                            <span class="text-lg font-medium text-gray-700">{{ $prestataire->user->name ? substr($prestataire->user->name, 0, 1) : 'P' }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $prestataire->user->name ?? 'Prestataire' }}</h3>
                                        <p class="text-sm text-gray-600">{{ $prestataire->bio ?? 'Prestataire de services' }}</p>
                                        @if($prestataire->location)
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $prestataire->location }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <!-- Tarif horaire supprimé pour des raisons de confidentialité -->
                                        <a href="{{ route('prestataires.show', $prestataire->id) }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                                            Voir le profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sélection de la demande (optionnel) -->
                            @if($clientRequests->count() > 0)
                                <div class="mb-6">
                                    <label for="client_request_id" class="block text-sm font-medium text-gray-700 mb-2">Associer à une demande (optionnel)</label>
                                    <select id="client_request_id" name="client_request_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Aucune demande spécifique</option>
                                        @foreach($clientRequests as $request)
                                            <option value="{{ $request->id }}" {{ old('client_request_id') == $request->id ? 'selected' : '' }}>
                                                {{ $request->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500">Sélectionnez une demande pour contextualiser votre message.</p>
                                </div>
                            @endif
                            
                            <!-- Message -->
                            <div class="mb-6">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                <textarea id="content" name="content" rows="6" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Bonjour, je souhaiterais discuter avec vous concernant..." required>{{ old('content') }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">Présentez-vous et expliquez votre besoin de manière claire et détaillée.</p>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('messaging.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Annuler
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Conseils pour une bonne communication -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Conseils pour une bonne communication</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Soyez précis sur vos besoins et vos attentes</li>
                                    <li>Mentionnez vos délais</li>
                                    <li>Posez des questions spécifiques sur l'expérience du prestataire</li>
                                    <li>Restez professionnel et courtois</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection