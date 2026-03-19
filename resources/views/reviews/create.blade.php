@extends('layouts.app')

@section('title', 'Laisser un avis')

@section('content')
@php
    // Utiliser les variables passées par le contrôleur, avec fallback
    $booking = $booking ?? ($bookingId ? \App\Models\Booking::with(['service', 'prestataire.user'])->find($bookingId) : null);
    $prestataire = $prestataire ?? ($prestataireId ? \App\Models\Prestataire::with('user')->find($prestataireId) : null);
@endphp

<div class="bg-slate-50 min-h-screen pb-24 sm:pb-12">
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        
        <!-- Header Minimaliste -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Votre avis compte</h1>
            @if($booking && $booking->service)
                <p class="text-slate-500 mt-1 text-sm">
                    {{ $booking->service->name ?? $booking->service->title ?? 'Service' }} • {{ $booking->start_datetime ? $booking->start_datetime->format('d/m') : '' }}
                </p>
            @elseif($prestataire && $prestataire->user)
                <p class="text-slate-500 mt-1 text-sm">{{ $prestataire->user->name }}</p>
            @elseif($prestataire)
                <p class="text-slate-500 mt-1 text-sm">{{ $prestataire->company_name ?? 'Prestataire' }}</p>
            @endif
        </div>

        <!-- Flash Messages (Modernisés) -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 text-emerald-700 px-4 py-3 rounded-2xl flex items-center shadow-sm border border-emerald-100">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-rose-50 text-rose-700 px-4 py-3 rounded-2xl flex items-center shadow-sm border border-rose-100">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="prestataire_id" value="{{ $prestataireId }}">
            @if($bookingId)
                <input type="hidden" name="booking_id" value="{{ $bookingId }}">
            @endif

            <!-- Carte Note Globale -->
            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 text-center">
                <label class="block text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Note Globale</label>
                <div class="flex justify-center items-center gap-2 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer group relative">
                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only rating-input" required>
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 text-slate-200 transition-all duration-200 star-icon transform group-hover:scale-110" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </label>
                    @endfor
                </div>
                <div class="text-blue-600 font-medium h-6" id="rating-label"></div>
                @error('rating')
                    <p class="text-rose-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Carte Détails -->
            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                    <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-3 text-sm">
                        <i class="fas fa-sliders-h"></i>
                    </span>
                    Détails
                </h3>
                
                <div class="space-y-6">
                    @foreach([
                        'punctuality' => 'Ponctualité',
                        'quality' => 'Qualité du service',
                        'value' => 'Rapport qualité/prix',
                        'communication' => 'Communication'
                    ] as $key => $label)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium text-sm">{{ $label }}</span>
                        <div class="flex space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="{{ $key }}_rating" value="{{ $i }}" class="sr-only criteria-rating" data-criteria="{{ $key }}">
                                    <svg class="w-6 h-6 text-slate-200 hover:text-yellow-400 transition-colors criteria-star" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Carte Commentaire -->
            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <label for="comment" class="block text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <span class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mr-3 text-sm">
                        <i class="fas fa-pen"></i>
                    </span>
                    Votre expérience
                </label>
                <textarea name="comment" id="comment" rows="4" 
                    class="w-full px-4 py-3 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400 resize-none transition-all"
                    placeholder="Racontez-nous comment ça s'est passé...">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-rose-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Carte Médias -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Photos -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <label class="block font-bold text-slate-800 mb-3 flex items-center">
                        <i class="fas fa-camera text-pink-500 mr-2"></i> Photos
                    </label>
                    <div class="relative">
                        <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                        <label for="photos" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-2xl cursor-pointer hover:border-pink-400 hover:bg-pink-50 transition-all group">
                            <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-plus text-pink-500"></i>
                            </div>
                            <span class="text-xs text-slate-500 font-medium">Ajouter des photos</span>
                        </label>
                    </div>
                    <div id="image-preview" class="mt-3 grid grid-cols-3 gap-2"></div>
                </div>

                <!-- Vidéo -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <label class="block font-bold text-slate-800 mb-3 flex items-center">
                        <i class="fas fa-video text-red-500 mr-2"></i> Vidéo
                    </label>
                    
                    <div id="video-upload-area">
                        <input type="file" name="video" id="video-input" accept="video/*" class="hidden" onchange="handleVideoSelect(this)">
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" onclick="openCameraModal()" class="flex flex-col items-center justify-center h-32 bg-red-50 rounded-2xl border border-red-100 hover:bg-red-100 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-circle text-red-500 text-xs"></i>
                                </div>
                                <span class="text-xs text-red-600 font-bold">Enregistrer</span>
                            </button>
                            <button type="button" onclick="document.getElementById('video-input').click()" class="flex flex-col items-center justify-center h-32 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-upload text-slate-500 text-xs"></i>
                                </div>
                                <span class="text-xs text-slate-600 font-bold">Importer</span>
                            </button>
                        </div>
                    </div>

                    <div id="video-preview-container" class="hidden relative rounded-2xl overflow-hidden bg-black aspect-video shadow-lg">
                        <video id="final-video-preview" controls playsinline webkit-playsinline x-webkit-airplay="allow" class="w-full h-full object-contain"></video>
                        <button type="button" onclick="removeVideo()" class="absolute top-2 right-2 bg-black/50 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-500 backdrop-blur-sm transition-all">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="fixed bottom-20 left-0 right-0 px-4 sm:static sm:p-0 z-40 pointer-events-none">
                <div class="container mx-auto max-w-3xl pointer-events-auto">
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center text-lg">
                        <span>Publier l'avis</span>
                        <i class="fas fa-paper-plane ml-2 text-sm opacity-70"></i>
                    </button>
                </div>
            </div>
            
            <!-- Spacer for fixed button on mobile -->
            <div class="h-16 sm:hidden"></div>

        </form>
    </div>
</div>

<!-- Camera Modal -->
<div id="camera-modal" class="fixed inset-0 z-[60] hidden bg-black sm:bg-opacity-95 flex flex-col items-center justify-center sm:p-4">
    <div class="relative w-full h-[100dvh] sm:w-full sm:max-w-md sm:h-auto sm:max-h-[85vh] sm:aspect-[9/16] bg-black sm:rounded-3xl overflow-hidden flex flex-col shadow-2xl sm:border border-gray-800">
        <!-- Camera View -->
        <video id="camera-feed" autoplay playsinline webkit-playsinline muted class="w-full h-full object-cover"></video>
        
        <!-- UI Overlays -->
        <div class="absolute top-4 right-4 z-20 pt-[env(safe-area-inset-top)]">
            <button type="button" onclick="closeCameraModal()" class="text-white bg-black/50 backdrop-blur-md rounded-full w-10 h-10 flex items-center justify-center hover:bg-black/70 transition-all">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Recording Controls -->
        <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 pb-[calc(1.5rem+env(safe-area-inset-bottom))] bg-linear-to-t from-black/90 via-black/50 to-transparent flex flex-col items-center justify-center space-y-4 z-10">
            <div class="text-white font-mono text-lg font-bold tracking-wider drop-shadow-md" id="recording-timer">00:00 / 00:30</div>
            
            <!-- Record Button with Progress Ring -->
            <div class="relative w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center">
                <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="44" stroke="white" stroke-width="4" fill="none" class="opacity-30" />
                    <circle id="progress-ring" cx="50" cy="50" r="44" stroke="#ef4444" stroke-width="4" fill="none" 
                        stroke-dasharray="276.46" stroke-dashoffset="276.46" class="transition-all duration-100" />
                </svg>
                <button id="record-btn" onclick="toggleRecording()" class="w-14 h-14 sm:w-16 sm:h-16 bg-white rounded-full border-4 border-transparent transition-all duration-200 hover:scale-105 active:scale-95 ring-4 ring-transparent shadow-lg"></button>
            </div>
            
            <p class="text-white text-sm opacity-90 font-medium drop-shadow-md">Appuyez pour démarrer/arrêter</p>
        </div>

        <!-- Preview Mode (Hidden by default) -->
        <div id="modal-preview-layer" class="absolute inset-0 bg-black hidden flex-col z-30">
            <video id="modal-video-preview" class="w-full h-full object-contain bg-black" loop playsinline webkit-playsinline></video>
            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 pb-[calc(1.5rem+env(safe-area-inset-bottom))] bg-linear-to-t from-black/90 via-black/50 to-transparent flex justify-around items-center">
                <button type="button" onclick="retakeVideo()" class="bg-white/10 backdrop-blur-md border border-white/20 text-white px-6 py-3 rounded-full font-medium hover:bg-white/20 transition-all flex items-center">
                    <i class="fas fa-undo mr-2"></i>Refaire
                </button>
                <button type="button" onclick="confirmVideo()" class="bg-blue-600 text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition-all flex items-center shadow-lg shadow-blue-600/30 transform hover:scale-105">
                    <i class="fas fa-check mr-2"></i>Valider
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des étoiles pour la note globale
const ratingInputs = document.querySelectorAll('.rating-input');
const starIcons = document.querySelectorAll('.star-icon');
const ratingLabel = document.getElementById('rating-label');

const ratingLabels = {
    1: 'Très mauvais',
    2: 'Mauvais', 
    3: 'Correct',
    4: 'Bon',
    5: 'Excellent'
};

ratingInputs.forEach((input, index) => {
    input.addEventListener('change', function() {
        const rating = parseInt(this.value);
        ratingLabel.textContent = ratingLabels[rating];
        
        starIcons.forEach((star, starIndex) => {
            if (starIndex < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    });
});

// Gestion des étoiles pour les critères
const criteriaInputs = document.querySelectorAll('.criteria-rating');
criteriaInputs.forEach(input => {
    input.addEventListener('change', function() {
        const criteria = this.dataset.criteria;
        const rating = parseInt(this.value);
        const criteriaStars = this.closest('div').querySelectorAll('.criteria-star');
        
        criteriaStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    });
});

// Prévisualisation des images
function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-20 sm:h-24 object-cover rounded-lg">
                        <button type="button" onclick="removeImage(this, ${index})" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function removeImage(button, index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.closest('div').remove();
}

// Auto-hide flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.bg-green-50, .bg-red-50');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 5000);
    });
});

// --- Video Recording Logic ---
let mediaRecorder;
let recordedChunks = [];
let stream;
let recordingTimer;
let recordingStartTime;
const MAX_RECORDING_TIME = 30000; // 30 seconds
const PROGRESS_RING_CIRCUMFERENCE = 276.46; // 2 * PI * 44

const cameraModal = document.getElementById('camera-modal');
const cameraFeed = document.getElementById('camera-feed');
const modalPreviewLayer = document.getElementById('modal-preview-layer');
const modalVideoPreview = document.getElementById('modal-video-preview');
const recordBtn = document.getElementById('record-btn');
const progressRing = document.getElementById('progress-ring');
const timerDisplay = document.getElementById('recording-timer');
const videoInput = document.getElementById('video-input');
const finalVideoPreview = document.getElementById('final-video-preview');
const videoPreviewContainer = document.getElementById('video-preview-container');
const videoUploadArea = document.getElementById('video-upload-area');

async function openCameraModal() {
    try {
        // Utiliser les contraintes de base pour meilleure compatibilité iOS
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: true, 
            audio: true 
        });
        cameraFeed.srcObject = stream;
        cameraModal.classList.remove('hidden');
        resetRecordingUI();
    } catch (err) {
        console.error("Error accessing camera:", err);
        alert("Impossible d'accéder à la caméra. Veuillez vérifier les permissions.");
    }
}

function closeCameraModal() {
    stopStream();
    cameraModal.classList.add('hidden');
}

function stopStream() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

function resetRecordingUI() {
    modalPreviewLayer.classList.add('hidden');
    recordBtn.classList.remove('bg-red-500', 'scale-75');
    recordBtn.classList.add('bg-white');
    progressRing.style.strokeDashoffset = PROGRESS_RING_CIRCUMFERENCE;
    timerDisplay.textContent = "00:00 / 00:30";
    recordedChunks = [];
}

function toggleRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        stopRecording();
    } else {
        startRecording();
    }
}

function startRecording() {
    recordedChunks = [];
    try {
        // Laisser le navigateur choisir le format natif pour meilleure compatibilité
        mediaRecorder = new MediaRecorder(stream);
    } catch (e) {
        console.error("MediaRecorder error:", e);
        alert("Votre navigateur ne supporte pas l'enregistrement vidéo.");
        return;
    }

    mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
            recordedChunks.push(event.data);
        }
    };

    mediaRecorder.onstop = () => {
        // Utiliser le type réel retourné par MediaRecorder
        const realType = mediaRecorder.mimeType || 'video/mp4';
        const blob = new Blob(recordedChunks, { type: realType });
        const url = URL.createObjectURL(blob);
        modalVideoPreview.src = url;
        modalVideoPreview.play();
        modalPreviewLayer.classList.remove('hidden');
        clearInterval(recordingTimer);
    };

    mediaRecorder.start();
    recordingStartTime = Date.now();
    
    // UI Updates
    recordBtn.classList.remove('bg-white');
    recordBtn.classList.add('bg-red-500', 'scale-75');
    
    recordingTimer = setInterval(() => {
        const elapsed = Date.now() - recordingStartTime;
        
        // Update Timer
        const seconds = Math.floor(elapsed / 1000);
        const ms = Math.floor((elapsed % 1000) / 10);
        timerDisplay.textContent = `00:${seconds.toString().padStart(2, '0')} / 00:30`;
        
        // Update Progress Ring
        const progress = Math.min(elapsed / MAX_RECORDING_TIME, 1);
        const offset = PROGRESS_RING_CIRCUMFERENCE - (progress * PROGRESS_RING_CIRCUMFERENCE);
        progressRing.style.strokeDashoffset = offset;
        
        if (elapsed >= MAX_RECORDING_TIME) {
            stopRecording();
        }
    }, 50);
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        clearInterval(recordingTimer);
        recordBtn.classList.remove('bg-red-500', 'scale-75');
        recordBtn.classList.add('bg-white');
    }
}

function retakeVideo() {
    modalPreviewLayer.classList.add('hidden');
    resetRecordingUI();
}

function confirmVideo() {
    // Utiliser le type réel pour la compatibilité
    const realType = mediaRecorder.mimeType || 'video/mp4';
    const blob = new Blob(recordedChunks, { type: realType });
    
    // Déterminer l'extension en fonction du type
    let ext = 'mp4';
    if (realType.includes('webm')) ext = 'webm';
    else if (realType.includes('quicktime') || realType.includes('mov')) ext = 'mov';
    
    const file = new File([blob], `recorded_video.${ext}`, { type: realType });
    
    // Update file input
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    videoInput.files = dataTransfer.files;
    
    // Update preview
    handleVideoSelect(videoInput);
    
    closeCameraModal();
}

function handleVideoSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        
        finalVideoPreview.src = url;
        videoPreviewContainer.classList.remove('hidden');
        videoUploadArea.classList.add('hidden');
    }
}

function removeVideo() {
    videoInput.value = '';
    finalVideoPreview.src = '';
    videoPreviewContainer.classList.add('hidden');
    videoUploadArea.classList.remove('hidden');
}
</script>
@endsection
