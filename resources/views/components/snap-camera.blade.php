{{-- Composant Camera Snapchat-style --}}
{{-- Usage: <x-snap-camera /> puis appeler window.snapCamera.open() depuis JS --}}

<div id="snap-camera-overlay" class="fixed inset-0 z-[10000] bg-black hidden" style="touch-action: none;">
    {{-- Viewfinder (video stream) --}}
    <video id="snap-camera-viewfinder" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover"></video>

    {{-- Top bar --}}
    <div class="absolute top-0 left-0 right-0 z-10 flex items-center justify-between px-4" style="padding-top: calc(12px + env(safe-area-inset-top, 0px));">
        {{-- Close --}}
        <button id="snap-camera-close" type="button" class="w-10 h-10 flex items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Timer display --}}
        <div id="snap-camera-timer" class="px-3 py-1.5 rounded-full bg-red-600 text-white text-sm font-bold tracking-wider hidden">
            <span id="snap-camera-timer-text">00:00</span>
        </div>

        {{-- Flash toggle --}}
        <button id="snap-camera-flash" type="button" class="w-10 h-10 flex items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm">
            <svg id="snap-flash-off" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <svg id="snap-flash-on" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </button>
    </div>

    {{-- Duration limit bar (progresse pendant l'enregistrement) --}}
    <div class="absolute top-0 left-0 right-0 h-1 z-20">
        <div id="snap-camera-progress" class="h-full bg-red-500 transition-all ease-linear" style="width: 0%;"></div>
    </div>

    {{-- Bottom controls --}}
    <div class="absolute bottom-0 left-0 right-0 z-10 pb-6" style="padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));">
        <div class="flex items-end justify-center gap-12">
            {{-- Gallery import --}}
            <button id="snap-camera-gallery" type="button" class="w-12 h-12 flex items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </button>

            {{-- Record button --}}
            <div class="relative">
                <div id="snap-record-ring" class="w-20 h-20 rounded-full border-4 border-white flex items-center justify-center transition-all duration-200">
                    <button id="snap-camera-record" type="button" class="w-16 h-16 rounded-full bg-red-500 transition-all duration-200 active:scale-90"></button>
                </div>
            </div>

            {{-- Flip camera --}}
            <button id="snap-camera-flip" type="button" class="w-12 h-12 flex items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    {{-- Preview screen (after recording) --}}
    <div id="snap-camera-preview" class="absolute inset-0 bg-black hidden z-30">
        <video id="snap-preview-video" playsinline loop class="absolute inset-0 w-full h-full object-cover"></video>

        {{-- Preview top bar --}}
        <div class="absolute top-0 left-0 right-0 z-10 flex items-center justify-between px-4" style="padding-top: calc(12px + env(safe-area-inset-top, 0px));">
            <button id="snap-preview-retake" type="button" class="px-4 py-2 rounded-full bg-black/50 text-white text-sm font-semibold backdrop-blur-sm">
                Reprendre
            </button>
            <div id="snap-preview-duration" class="px-3 py-1.5 rounded-full bg-white/20 text-white text-sm font-medium backdrop-blur-sm"></div>
        </div>

        {{-- Preview bottom: Use video --}}
        <div class="absolute bottom-0 left-0 right-0 z-10 px-6 pb-6" style="padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));">
            <button id="snap-preview-use" type="button" class="w-full py-4 rounded-2xl bg-white text-black text-base font-bold shadow-lg active:scale-[0.98] transition-transform">
                Utiliser cette video
            </button>
        </div>
    </div>

    {{-- Permission denied screen --}}
    <div id="snap-camera-denied" class="absolute inset-0 bg-black flex flex-col items-center justify-center text-white px-8 hidden z-30">
        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <h3 class="text-xl font-bold mb-2">Acces camera requis</h3>
        <p class="text-gray-400 text-center text-sm mb-6">Autorisez l'acces a la camera dans les parametres de votre navigateur pour enregistrer des videos.</p>
        <button id="snap-camera-denied-close" type="button" class="px-6 py-3 rounded-xl bg-white text-black font-semibold">Fermer</button>
    </div>
</div>
