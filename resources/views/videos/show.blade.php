@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md">
        <video class="w-full h-auto" src="{{ $video->video_url }}" controls autoplay></video>
        <div class="p-4">
            <h1 class="text-2xl font-bold">{{ $video->title }}</h1>
            <p class="text-gray-700 my-2">{{ $video->description }}</p>
            <div class="flex items-center justify-between mt-4">
                <div>
                    <a href="{{ route('prestataires.show', $video->prestataire) }}" class="flex items-center">
                        <img src="{{ $video->prestataire->profile_image_url }}" alt="{{ $video->prestataire->company_name }}" class="w-12 h-12 rounded-full mr-4">
                        <span class="text-lg font-semibold">{{ $video->prestataire->company_name }}</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <form action="{{ route('prestataires.follow', $video->prestataire) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full">Suivre</button>
                    </form>
                    <a href="{{ route('messages.create', ['recipient' => $video->prestataire->user_id]) }}" class="bg-green-500 text-white px-4 py-2 rounded-full">Contacter</a>
                </div>
            </div>
            <div class="mt-4 text-gray-500">
                <span>{{ $video->views_count }} vues</span>
            </div>
        </div>
    </div>
</div>
@endsection