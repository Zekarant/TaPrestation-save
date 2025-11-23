@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
        <div class="max-w-3xl mx-auto">
            <!-- En-tête -->
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6 border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Modifier la vidéo</h1>
                        <p class="text-gray-600 text-sm sm:text-base">Mettez à jour les informations de votre vidéo.</p>
                    </div>
                </div>
            </div>

            <!-- Messages de statut -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded-lg mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 sm:px-4 sm:py-3 rounded-lg mb-4 sm:mb-6 shadow-md text-xs sm:text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden border border-gray-200">
                <form action="{{ route('prestataire.videos.update', $video) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800 text-sm sm:text-base border border-gray-300 px-3 py-2 sm:px-4 sm:py-3" value="{{ old('title', $video->title) }}">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800 text-sm sm:text-base border border-gray-300 px-3 py-2 sm:px-4 sm:py-3">{{ old('description', $video->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu de la vidéo</label>
                            <div class="rounded-lg overflow-hidden border border-gray-200">
                                <video class="w-full h-auto bg-black max-h-64" controls>
                                    <source src="{{ Storage::disk('public')->url($video->video_path) }}" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gray-50 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end items-center gap-3">
                        <a href="{{ route('prestataire.videos.manage') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 shadow-md text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition duration-200 hover:shadow-lg">
                            Annuler
                        </a>
                        <button type="submit" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-medium">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection