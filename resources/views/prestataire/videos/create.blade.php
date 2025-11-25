@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/video-create.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
@endpush

@section('content')
<div class="bg-purple-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-purple-900 mb-2">Importer une vidéo</h1>
                <p class="text-lg text-purple-700">Partagez vos vidéos professionnelles</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-purple-200 overflow-hidden">
                <div class="p-8">
                    <form action="{{ route('prestataire.videos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="bg-purple-50 rounded-xl p-6 border-2 border-purple-200">
                                    <h3 class="text-xl font-bold text-purple-800 mb-4">Importer une vidéo</h3>
                                    <div class="file-upload-area bg-purple-100 border-2 border-dashed border-purple-300 rounded-lg p-8 text-center hover:bg-purple-200 transition duration-200 cursor-pointer" id="drop-area">
                                        <input type="file" name="video" id="video" accept=".mp4,.webm,.mov" style="display: none;">
                                        <i class="fas fa-cloud-upload-alt text-purple-600 text-4xl mb-4"></i>
                                        <p class="text-purple-700 font-medium mb-2">Choisir une vidéo depuis vos fichiers</p>
                                        <p class="text-purple-700 font-medium mb-4">ou glissez-déposez ici</p>
                                        <p id="file-name" class="font-bold text-purple-800" style="margin-top: 1rem;"></p>
                                        <div class="text-purple-600 text-sm mt-4">
                                            Formats supportés : MP4, WebM, MOV • Taille max : 100MB
                                        </div>
                                    </div>
                                    <video id="video-preview" controls style="display:none; width: 100%; margin-top: 1rem;" class="rounded-lg"></video>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl p-6 border-2 border-purple-200 shadow-lg">
                                    <h3 class="text-xl font-bold text-purple-800 mb-4">Informations de la vidéo</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="title" class="block text-md font-semibold text-gray-800 mb-2">Titre de la vidéo</label>
                                            <input type="text" name="title" id="title" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" required placeholder="Ex : Présentation de mon service">
                                        </div>
                                        <div>
                                            <label for="description" class="block text-md font-semibold text-gray-800 mb-2">Description</label>
                                            <textarea name="description" id="description" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200" required placeholder="Une brève description pour que vos clients comprennent le contenu de la vidéo" maxlength="300" rows="4"></textarea>
                                            <small class="text-gray-500">Maximum 300 caractères</small>
                                        </div>
                                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-3 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:shadow-none disabled:transform-none" disabled id="submit-btn">Importer la vidéo</button>
                                        <div class="text-center">
                                            <small class="text-gray-500">La vidéo sera traitée après l'importation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/video-upload.js'])
@endpush