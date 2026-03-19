@extends('layouts.admin-modern')

@section('title', 'Modifier - ' . $legalPage->title)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css" rel="stylesheet">
<style>
    .EasyMDEContainer {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .EasyMDEContainer .CodeMirror {
        border-radius: 0 0 0.75rem 0.75rem;
        min-height: 400px;
    }
    .editor-toolbar {
        border-radius: 0.75rem 0.75rem 0 0;
        background: #f9fafb;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('admin.legal-pages.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la liste
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $icons = [
                                'terms' => 'fa-file-contract',
                                'privacy' => 'fa-shield-alt',
                                'cookies' => 'fa-cookie-bite',
                                'mentions' => 'fa-info-circle',
                                'faq' => 'fa-question-circle',
                                'contact' => 'fa-envelope',
                                'videos' => 'fa-video',
                            ];
                            $colors = [
                                'terms' => 'from-blue-600 to-blue-700',
                                'privacy' => 'from-green-600 to-green-700',
                                'cookies' => 'from-amber-500 to-amber-600',
                                'mentions' => 'from-purple-600 to-purple-700',
                                'faq' => 'from-cyan-600 to-cyan-700',
                                'contact' => 'from-pink-600 to-pink-700',
                                'videos' => 'from-red-600 to-red-700',
                            ];
                        @endphp
                        <span class="w-12 h-12 bg-gradient-to-r {{ $colors[$legalPage->slug] ?? 'from-gray-600 to-gray-700' }} rounded-xl flex items-center justify-center mr-4">
                            <i class="fas {{ $icons[$legalPage->slug] ?? 'fa-file' }} text-white text-xl"></i>
                        </span>
                        Modifier : {{ $legalPage->title }}
                    </h1>
                    <p class="mt-2 text-gray-600">Éditez le contenu de cette page</p>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <span class="font-semibold">Erreurs de validation</span>
                </div>
                <ul class="list-disc list-inside ml-6 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulaire --}}
        <form action="{{ route('admin.legal-pages.update', $legalPage) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Informations de base --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informations de base
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Titre --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Titre de la page *
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title', $legalPage->title) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               required>
                    </div>

                    {{-- Slug (lecture seule) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL de la page
                        </label>
                        <div class="flex items-center px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-600">
                            <i class="fas fa-link mr-2"></i>
                            {{ config('app.url') }}/{{ $legalPage->slug }}
                        </div>
                    </div>

                    {{-- Statut --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Statut
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_active" value="1" 
                                       {{ old('is_active', $legalPage->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                    Actif (visible)
                                </span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_active" value="0" 
                                       {{ !old('is_active', $legalPage->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fas fa-times-circle text-red-500 mr-1"></i>
                                    Inactif (caché)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contenu --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-align-left mr-2"></i>
                        Contenu de la page
                    </h2>
                </div>
                <div class="p-6">
                    {{-- Message si contenu statique --}}
                    @if(isset($hasStaticContent) && $hasStaticContent)
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500 text-xl mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-green-800">Contenu importé depuis la page statique</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Le contenu ci-dessous a été importé automatiquement depuis le fichier de vue statique.
                                        Modifiez-le selon vos besoins et cliquez sur "Enregistrer" pour sauvegarder vos changements.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                            Contenu (HTML ou texte)
                        </label>
                        <p class="text-sm text-gray-500 mb-3">
                            <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                            Vous pouvez utiliser du HTML pour une mise en forme avancée (titres, listes, liens, etc.)
                        </p>
                        <textarea name="content" 
                                  id="content" 
                                  rows="25"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-sm"
                                  placeholder="Entrez le contenu de la page ici...">{{ old('content', $legalPage->content) }}</textarea>
                    </div>

                    {{-- Modèles rapides --}}
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Insérer un modèle :</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertTemplate('heading')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-heading mr-1"></i> Titre
                            </button>
                            <button type="button" onclick="insertTemplate('paragraph')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-paragraph mr-1"></i> Paragraphe
                            </button>
                            <button type="button" onclick="insertTemplate('list')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-list mr-1"></i> Liste
                            </button>
                            <button type="button" onclick="insertTemplate('link')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-link mr-1"></i> Lien
                            </button>
                            <button type="button" onclick="insertTemplate('email')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-envelope mr-1"></i> Email
                            </button>
                            <button type="button" onclick="insertTemplate('section')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                <i class="fas fa-th-large mr-1"></i> Section
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fichier joint --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-paperclip mr-2"></i>
                        Fichier joint (optionnel)
                    </h2>
                </div>
                <div class="p-6">
                    @if($legalPage->file_path)
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf text-red-500 text-2xl mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Fichier actuel</p>
                                    <a href="{{ Storage::url($legalPage->file_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                        {{ basename($legalPage->file_path) }}
                                    </a>
                                </div>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="remove_file" value="1" class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500 rounded">
                                <span class="ml-2 text-sm text-red-600">Supprimer ce fichier</span>
                            </label>
                        </div>
                    @endif

                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                            Uploader un nouveau fichier
                        </label>
                        <input type="file" 
                               name="file" 
                               id="file" 
                               accept=".pdf,.doc,.docx"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Formats acceptés : PDF, DOC, DOCX (max 10 Mo)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Boutons d'action --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.legal-pages.index') }}" 
                   class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </a>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.legal-pages.preview', $legalPage) }}" 
                       target="_blank"
                       class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-medium transition flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        Prévisualiser
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-medium transition flex items-center shadow-lg">
                        <i class="fas fa-save mr-2"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function insertTemplate(type) {
    const textarea = document.getElementById('content');
    const templates = {
        'heading': '<h2 class="text-2xl font-bold text-gray-900 mb-4">Titre de section</h2>\n',
        'paragraph': '<p class="text-gray-700 mb-4">Votre texte ici...</p>\n',
        'list': '<ul class="list-disc list-inside space-y-2 mb-4">\n    <li>Élément 1</li>\n    <li>Élément 2</li>\n    <li>Élément 3</li>\n</ul>\n',
        'link': '<a href="URL_ICI" class="text-indigo-600 hover:underline">Texte du lien</a>',
        'email': '<a href="mailto:contact@taprestation.com" class="text-indigo-600 hover:underline">contact@taprestation.com</a>',
        'section': '<div class="bg-gray-50 rounded-xl p-6 mb-6">\n    <h3 class="text-xl font-semibold text-gray-900 mb-3">Titre</h3>\n    <p class="text-gray-700">Contenu de la section...</p>\n</div>\n'
    };
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const template = templates[type] || '';
    
    textarea.value = text.substring(0, start) + template + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + template.length;
    textarea.focus();
}
</script>
@endpush
