@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête -->
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6 border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-1 sm:mb-2">Gestion des vidéos</h1>
                        <p class="text-gray-600 text-sm sm:text-base">Gérez vos vidéos et modifiez leurs informations</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('prestataire.videos.create') }}" 
                           class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-medium text-sm sm:font-bold">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Ajouter une vidéo
                        </a>
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

            <!-- Video cards grid -->
            @if($videos->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($videos as $video)
                        <div class="bg-white rounded-lg sm:rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200">
                            <!-- Video preview -->
                            <div class="aspect-video bg-gray-100">
                                @if($video->video_path)
                                    <video class="w-full h-full object-cover" controls>
                                        <source src="{{ $video->video_url }}" type="{{ $video->getMimeType() }}">
                                        Votre navigateur ne supporte pas la balise vidéo.
                                    </video>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Video info -->
                            <div class="p-3 sm:p-4">
                                <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-1 line-clamp-1">{{ $video->title }}</h3>
                                
                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 mb-3">
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @if($video->duration > 0)
                                            {{ gmdate('H:i:s', intval($video->duration)) }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                    
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ $video->views_count }}
                                    </span>
                                    
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                        </svg>
                                        {{ $video->likes_count }}
                                    </span>
                                </div>
                                
                                
                                
                                <!-- Action buttons -->
                                <div class="flex gap-2">
                                    <a href="{{ route('prestataire.videos.edit', $video) }}" 
                                       class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200 text-xs sm:text-sm font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Modifier
                                    </a>
                                    
                                    <form action="{{ route('prestataire.videos.destroy', $video) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition duration-200 text-xs sm:text-sm font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-8 sm:p-12 text-center border border-gray-200">
                    <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 sm:mb-6">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-medium text-gray-900 mb-2">Aucune vidéo trouvée</h3>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Vous n'avez pas encore publié de vidéos.</p>
                    <a href="{{ route('prestataire.videos.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition duration-200 shadow-md font-medium text-sm sm:text-base">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Publier une vidéo
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush