@extends('layouts.app')

@section('title', $user->name . ' - Profil')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header du profil --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-32"></div>
            <div class="relative px-6 pb-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-end -mt-16 sm:-mt-12">
                    {{-- Avatar --}}
                    <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white flex-shrink-0">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-4xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Infos --}}
                    <div class="mt-4 sm:mt-0 sm:ml-6 text-center sm:text-left flex-grow">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="text-gray-500 flex items-center justify-center sm:justify-start gap-2 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Membre depuis {{ $stats['member_since']->format('F Y') }}
                        </p>
                        
                        {{-- Badge client --}}
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Client
                            </span>
                        </div>
                    </div>
                    
                    {{-- Note moyenne --}}
                    <div class="mt-4 sm:mt-0 text-center sm:text-right">
                        <div class="flex items-center justify-center sm:justify-end gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($averageRating))
                                    <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                            @endfor
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ number_format($averageRating, 1) }}/5 ({{ $totalReviews }} avis)
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Statistiques --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $stats['total_bookings'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Réservations</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600">{{ $stats['completed_bookings'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Terminées</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 text-center col-span-2 sm:col-span-1">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-500">{{ number_format($averageRating, 1) }}</div>
                <div class="text-sm text-gray-500 mt-1">Note moyenne</div>
            </div>
        </div>
        
        {{-- Avis reçus --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Avis reçus ({{ $totalReviews }})</h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @forelse($reviewsReceived as $review)
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            {{-- Avatar du reviewer (prestataire) --}}
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                                @if($review->prestataire && $review->prestataire->user && $review->prestataire->user->profile_photo_url)
                                    <img src="{{ $review->prestataire->user->profile_photo_url }}" alt="{{ $review->prestataire->user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white text-sm font-bold">
                                        {{ strtoupper(substr($review->prestataire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-grow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $review->prestataire->user->name ?? 'Prestataire' }}</h3>
                                        @if($review->prestataire)
                                            <a href="{{ route('prestataires.show', $review->prestataire) }}" class="text-sm text-blue-600 hover:underline">Voir le profil</a>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                
                                {{-- Étoiles --}}
                                <div class="flex items-center gap-1 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                    
                                    {{-- Badge "Travaillerait encore avec" --}}
                                    @if($review->would_work_again)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Recommandé
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Commentaire --}}
                                @if($review->comment)
                                    <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-gray-500 mt-4">Aucun avis pour le moment</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        {{-- Bouton retour --}}
        <div class="mt-6 text-center">
            <a
                href="{{ url('/') }}"
                data-smart-back="true"
                data-fallback="{{ url('/') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white rounded-lg shadow hover:bg-gray-50 text-gray-700 font-medium transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>
</div>
@endsection
