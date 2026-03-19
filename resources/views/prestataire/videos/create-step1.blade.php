@extends('layouts.app')

@section('content')
<x-snap-camera />

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6">
        <div class="max-w-2xl mx-auto">

            {{-- En-tete --}}
            <div class="mb-4 sm:mb-6 text-center">
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-1">Nouvelle video</h1>
                <p class="text-sm text-gray-500">Etape 1 / 2 : Importation</p>
            </div>

            {{-- Progress bar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('prestataire.videos.manage') }}" class="text-gray-500 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div class="flex-1 flex items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 bg-gray-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div>
                            <span class="text-xs font-medium text-gray-700 hidden sm:inline">Import</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded-full">
                            <div class="h-1 bg-gray-900 rounded-full" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                            <span class="text-xs font-medium text-gray-400 hidden sm:inline">Infos</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('prestataire.videos.create.step1.store') }}" enctype="multipart/form-data" id="step1Form">
                @csrf

                {{-- Zone d'import / camera --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-4">

                    {{-- Contraintes --}}
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4 bg-gray-50 rounded-lg p-3">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <span>Max <strong>60 sec</strong> &bull; Max <strong>100 Mo</strong></span>
                    </div>

                    {{-- 3 options: Camera Snap, Import fichier, Drag & Drop desktop --}}

                    {{-- Mobile: 2 boutons cote a cote --}}
                    <div class="grid grid-cols-2 gap-3 mb-4 md:hidden">
                        <button type="button" id="openSnapCamera" class="flex flex-col items-center justify-center gap-2 py-5 px-3 rounded-xl bg-gradient-to-br from-yellow-400 via-red-500 to-purple-600 text-white font-semibold shadow-lg active:scale-95 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="text-sm">Enregistrer</span>
                        </button>

                        <button type="button" id="importFromFileBtn" class="flex flex-col items-center justify-center gap-2 py-5 px-3 rounded-xl bg-white border-2 border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 active:scale-95 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-sm">Importer</span>
                        </button>
                    </div>

                    {{-- Desktop: Drag & Drop + bouton camera --}}
                    <div class="hidden md:block">
                        <div id="drop-area" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-gray-400 hover:bg-gray-50 transition-all cursor-pointer group">
                            <svg class="w-12 h-12 mx-auto text-gray-400 group-hover:text-gray-500 mb-3 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-gray-600 font-medium mb-1">Glissez votre video ici</p>
                            <p class="text-gray-400 text-sm">ou cliquez pour parcourir vos fichiers</p>
                        </div>

                        <div class="mt-3 text-center">
                            <button type="button" id="openSnapCameraDesktop" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Utiliser la camera
                            </button>
                        </div>
                    </div>

                    {{-- Hidden file inputs --}}
                    <input type="file" name="video" id="video" accept="video/*" class="hidden">
                    <input type="file" id="video-mobile" accept="video/*" class="hidden">

                    {{-- Video preview --}}
                    <div id="video-preview-container" class="mt-4 hidden">
                        <div class="relative rounded-xl overflow-hidden bg-black">
                            <video id="video-preview" controls playsinline webkit-playsinline class="w-full max-h-[400px] object-contain"></video>
                            <button type="button" id="remove-video" class="absolute top-2 right-2 w-8 h-8 bg-black/60 text-white rounded-full flex items-center justify-center hover:bg-black/80 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div id="video-info" class="mt-2 flex items-center gap-3 text-sm text-gray-500">
                            <span id="video-name"></span>
                            <span id="video-size"></span>
                            <span id="video-duration"></span>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('prestataire.videos.manage') }}" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Retour
                    </a>
                    <button type="submit" id="submit-btn" disabled class="px-6 py-2.5 text-sm font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
                        Suivant
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoInput = document.getElementById('video');
    const videoMobile = document.getElementById('video-mobile');
    const dropArea = document.getElementById('drop-area');
    const submitBtn = document.getElementById('submit-btn');
    const previewContainer = document.getElementById('video-preview-container');
    const previewVideo = document.getElementById('video-preview');
    const removeBtn = document.getElementById('remove-video');
    const videoName = document.getElementById('video-name');
    const videoSize = document.getElementById('video-size');
    const videoDuration = document.getElementById('video-duration');

    const MAX_SECONDS = 60;
    const MAX_BYTES = 100 * 1024 * 1024;

    // ── Snap Camera buttons ──
    var snapBtnMobile = document.getElementById('openSnapCamera');
    var snapBtnDesktop = document.getElementById('openSnapCameraDesktop');

    function openSnap() {
        if (window.snapCamera) {
            window.snapCamera.open(function(file) {
                setVideoFile(file);
            });
        } else {
            // Fallback: open native camera
            videoMobile.setAttribute('capture', 'environment');
            videoMobile.click();
        }
    }

    if (snapBtnMobile) snapBtnMobile.addEventListener('click', openSnap);
    if (snapBtnDesktop) snapBtnDesktop.addEventListener('click', openSnap);

    // ── Import from files (mobile) ──
    var importBtn = document.getElementById('importFromFileBtn');
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            videoMobile.removeAttribute('capture');
            videoMobile.click();
        });
    }

    // ── Desktop drag & drop ──
    if (dropArea) {
        dropArea.addEventListener('click', function(e) {
            e.stopPropagation();
            videoInput.click();
        });
        dropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropArea.classList.add('border-gray-500', 'bg-gray-100');
        });
        dropArea.addEventListener('dragleave', function() {
            dropArea.classList.remove('border-gray-500', 'bg-gray-100');
        });
        dropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            dropArea.classList.remove('border-gray-500', 'bg-gray-100');
            if (e.dataTransfer.files.length > 0) {
                setVideoFile(e.dataTransfer.files[0]);
            }
        });
    }

    // ── File input change ──
    videoInput.addEventListener('change', function(e) {
        if (e.target.files[0]) setVideoFile(e.target.files[0]);
    });
    videoMobile.addEventListener('change', function(e) {
        if (e.target.files[0]) setVideoFile(e.target.files[0]);
    });

    // ── Remove video ──
    removeBtn.addEventListener('click', function() {
        clearVideo();
    });

    // ── Core: set video file ──
    function setVideoFile(file) {
        // Size check
        if (file.size > MAX_BYTES) {
            alert('Le fichier depasse 100 Mo (' + (file.size / (1024*1024)).toFixed(1) + ' Mo).');
            return;
        }

        // Copy to main input for form submission
        var dt = new DataTransfer();
        dt.items.add(file);
        videoInput.files = dt.files;

        // Show preview
        var url = URL.createObjectURL(file);
        previewVideo.src = url;
        previewContainer.classList.remove('hidden');
        videoName.textContent = file.name;
        videoSize.textContent = formatSize(file.size);

        previewVideo.onloadedmetadata = function() {
            if (isFinite(previewVideo.duration) && previewVideo.duration > 0) {
                var dur = Math.round(previewVideo.duration);
                videoDuration.textContent = dur + 's';

                if (previewVideo.duration > MAX_SECONDS) {
                    alert('La video ne doit pas depasser ' + MAX_SECONDS + ' secondes (' + dur + 's detectees).');
                    clearVideo();
                    return;
                }
            }
            submitBtn.disabled = false;
        };

        previewVideo.onerror = function() {
            submitBtn.disabled = false;
        };
    }

    function clearVideo() {
        videoInput.value = '';
        videoMobile.value = '';
        previewVideo.src = '';
        previewContainer.classList.add('hidden');
        videoName.textContent = '';
        videoSize.textContent = '';
        videoDuration.textContent = '';
        submitBtn.disabled = true;
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
});
</script>
@endpush
