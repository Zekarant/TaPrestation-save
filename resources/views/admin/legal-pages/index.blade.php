@extends('layouts.admin-modern')

@section('title', 'Gestion des pages légales')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <span class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-file-alt text-white text-xl"></i>
                        </span>
                        Pages légales & Informations
                    </h1>
                    <p class="mt-2 text-gray-600">Gérez le contenu des pages d'information de votre plateforme</p>
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

        {{-- Liste des pages --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    Toutes les pages
                </h2>
            </div>
            
            <div class="divide-y divide-gray-100">
                @forelse($pages as $page)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                {{-- Icône selon le type --}}
                                @php
                                    $icons = [
                                        'cgu' => 'fa-file-signature',
                                        'cgv' => 'fa-file-invoice-dollar',
                                        'terms' => 'fa-file-contract',
                                        'privacy' => 'fa-shield-alt',
                                        'cookies' => 'fa-cookie-bite',
                                        'mentions' => 'fa-info-circle',
                                        'faq' => 'fa-question-circle',
                                        'contact' => 'fa-envelope',
                                        'videos' => 'fa-video',
                                    ];
                                    $colors = [
                                        'cgu' => 'bg-indigo-100 text-indigo-600',
                                        'cgv' => 'bg-emerald-100 text-emerald-600',
                                        'terms' => 'bg-blue-100 text-blue-600',
                                        'privacy' => 'bg-green-100 text-green-600',
                                        'cookies' => 'bg-amber-100 text-amber-600',
                                        'mentions' => 'bg-purple-100 text-purple-600',
                                        'faq' => 'bg-cyan-100 text-cyan-600',
                                        'contact' => 'bg-pink-100 text-pink-600',
                                        'videos' => 'bg-red-100 text-red-600',
                                    ];
                                @endphp
                                <div class="w-12 h-12 rounded-xl {{ $colors[$page->slug] ?? 'bg-gray-100 text-gray-600' }} flex items-center justify-center">
                                    <i class="fas {{ $icons[$page->slug] ?? 'fa-file' }} text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $page->title }}</h3>
                                    <div class="flex items-center space-x-3 mt-1 text-sm text-gray-500">
                                        <span class="flex items-center">
                                            <i class="fas fa-link mr-1"></i>
                                            /{{ $page->slug }}
                                        </span>
                                        @if($page->content)
                                            <span class="flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Contenu défini
                                            </span>
                                        @else
                                            <span class="flex items-center text-amber-600">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Contenu par défaut
                                            </span>
                                        @endif
                                        @if($page->file_path)
                                            <span class="flex items-center text-blue-600">
                                                <i class="fas fa-paperclip mr-1"></i>
                                                Fichier joint
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                {{-- Statut --}}
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $page->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $page->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                
                                {{-- Boutons d'action --}}
                                <a href="{{ route('admin.legal-pages.edit', $page) }}" 
                                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center">
                                    <i class="fas fa-edit mr-2"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('admin.legal-pages.preview', $page) }}" 
                                   target="_blank"
                                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition flex items-center">
                                    <i class="fas fa-eye mr-2"></i>
                                    Prévisualiser
                                </a>
                            </div>
                        </div>
                        
                        @if($page->updated_at && $page->content)
                            <div class="mt-3 text-xs text-gray-400 ml-16">
                                Dernière modification : {{ $page->updated_at->format('d/m/Y à H:i') }}
                                @if($page->updatedBy)
                                    par {{ $page->updatedBy->name }}
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Aucune page trouvée</h3>
                        <p class="text-gray-500 mt-1">Les pages légales seront créées automatiquement.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Note d'information --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-semibold mb-1">Comment ça fonctionne ?</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-600">
                        <li>Cliquez sur "Modifier" pour éditer le contenu d'une page</li>
                        <li>Vous pouvez écrire du texte directement ou uploader un fichier PDF/Word</li>
                        <li>Le HTML est supporté pour une mise en forme avancée</li>
                        <li>Les pages inactives ne seront pas accessibles aux utilisateurs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
