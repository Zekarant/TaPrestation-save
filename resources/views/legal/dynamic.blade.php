@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-10">
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
                    'terms' => 'from-blue-500 to-blue-600',
                    'privacy' => 'from-green-500 to-green-600',
                    'cookies' => 'from-amber-500 to-amber-600',
                    'mentions' => 'from-purple-500 to-purple-600',
                    'faq' => 'from-cyan-500 to-cyan-600',
                    'contact' => 'from-pink-500 to-pink-600',
                    'videos' => 'from-red-500 to-red-600',
                ];
            @endphp
            
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-r {{ $colors[$page->slug] ?? 'from-indigo-500 to-purple-600' }} text-white mb-4 shadow-lg">
                <i class="fas {{ $icons[$page->slug] ?? 'fa-file-alt' }} text-2xl"></i>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ $page->title }}</h1>
            
            @if($page->updated_at)
                <p class="text-gray-500 text-sm">
                    Dernière mise à jour : {{ $page->updated_at->format('d/m/Y') }}
                </p>
            @endif
        </div>

        {{-- Contenu --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 sm:p-10">
                {{-- Fichier joint --}}
                @if($page->file_path)
                    <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Document officiel</p>
                                <p class="text-sm text-gray-500">Télécharger le document complet</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($page->file_path) }}" 
                           target="_blank"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            Télécharger
                        </a>
                    </div>
                @endif

                {{-- Contenu HTML --}}
                @if($page->content)
                    @php
                        $safeContent = \App\Support\HtmlSanitizer::sanitize((string) $page->content);
                    @endphp
                    <div class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-indigo-600 prose-strong:text-gray-900">
                        {!! $safeContent !!}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Contenu en cours de rédaction</h3>
                        <p class="text-gray-500 mt-2">Cette page sera bientôt disponible.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Navigation --}}
        <div class="mt-8 text-center">
            <a href="{{ url()->previous() }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </a>
        </div>
    </div>
</div>
@endsection
