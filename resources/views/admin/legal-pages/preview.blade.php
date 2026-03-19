<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $legalPage->title }} - Prévisualisation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prose h1 { font-size: 2rem; font-weight: 700; margin-bottom: 1rem; color: #1f2937; }
        .prose h2 { font-size: 1.5rem; font-weight: 600; margin-top: 2rem; margin-bottom: 0.75rem; color: #374151; }
        .prose h3 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #4b5563; }
        .prose p { margin-bottom: 1rem; line-height: 1.75; color: #4b5563; }
        .prose ul, .prose ol { margin-left: 1.5rem; margin-bottom: 1rem; }
        .prose li { margin-bottom: 0.5rem; color: #4b5563; }
        .prose a { color: #4f46e5; text-decoration: underline; }
        .prose a:hover { color: #4338ca; }
        .prose strong { font-weight: 600; color: #1f2937; }
        .prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1rem; margin: 1rem 0; font-style: italic; color: #6b7280; }
    </style>
</head>
<body class="bg-gray-100">
    {{-- Barre admin --}}
    <div class="bg-gray-900 text-white px-4 py-2 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="bg-amber-500 text-black px-2 py-1 rounded text-xs font-bold">
                <i class="fas fa-eye mr-1"></i> PRÉVISUALISATION
            </span>
            <span class="text-gray-300">{{ $legalPage->title }}</span>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.legal-pages.edit', $legalPage) }}" 
               class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 rounded text-sm transition">
                <i class="fas fa-edit mr-1"></i> Modifier
            </a>
            <a href="{{ route('admin.legal-pages.index') }}" 
               class="px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded text-sm transition">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    {{-- Contenu --}}
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $legalPage->title }}</h1>
                @if($legalPage->updated_at)
                    <p class="text-indigo-200 text-sm mt-2">
                        Dernière mise à jour : {{ $legalPage->updated_at->format('d/m/Y à H:i') }}
                    </p>
                @endif
            </div>

            {{-- Contenu principal --}}
            <div class="px-8 py-6">
                @if($legalPage->file_path)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Document attaché</p>
                                <p class="text-sm text-gray-500">{{ basename($legalPage->file_path) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($legalPage->file_path) }}" 
                           target="_blank"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                            <i class="fas fa-download mr-2"></i>
                            Télécharger
                        </a>
                    </div>
                @endif

                @if($legalPage->content)
                    @php
                        $safePreviewContent = \App\Support\HtmlSanitizer::sanitize((string) $legalPage->content);
                    @endphp
                    <div class="prose max-w-none">
                        {!! $safePreviewContent !!}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Aucun contenu défini</h3>
                        <p class="text-gray-500 mt-1">Cette page utilise le contenu par défaut.</p>
                        <a href="{{ route('admin.legal-pages.edit', $legalPage) }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                            <i class="fas fa-edit mr-2"></i>
                            Ajouter du contenu
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
