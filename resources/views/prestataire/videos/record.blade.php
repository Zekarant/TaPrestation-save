@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen">
        <div class="container mx-auto px-2 sm:px-4 md:px-6 py-3 sm:py-4 md:py-6">
            <div class="max-w-4xl mx-auto">
                <!-- En-tête -->
                <div class="mb-4 sm:mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <a href="{{ route('prestataire.videos.create.step1') }}"
                                class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                                <i class="fas fa-arrow-left text-base sm:text-lg md:text-xl"></i>
                            </a>
                            <div>
                                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-900">Enregistrer une
                                    vidéo</h1>
                                <p class="text-xs sm:text-sm md:text-base text-gray-700">Enregistrez directement depuis
                                    votre caméra</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('prestataire.videos.create.step1') }}"
                                class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg transition duration-200 text-xs sm:text-sm font-medium">
                                <i class="fas fa-upload mr-2"></i>
                                <span class="hidden sm:inline">Importer depuis un fichier</span>
                                <span class="sm:hidden">Importer</span>
                            </a>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-2 sm:p-3 md:p-4 mb-3 sm:mb-4 md:mb-5 rounded-r-lg"
                        role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <div class="mt-1 text-xs sm:text-sm text-red-700">
                                    <ul class="list-disc pl-4 sm:pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Conteneur principal -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6 md:p-8">

                    <!-- Zone d'enregistrement -->
                    <div class="mb-6">
                        <!-- Video preview -->
                        <div class="relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
                            <video id="cameraPreview" autoplay muted playsinline class="w-full h-full object-cover"></video>
                            <video id="recordedPreview" controls playsinline
                                class="w-full h-full object-cover hidden"></video>

                            <!-- Compte à rebours avant enregistrement (couche séparée pour être visible) -->
                            <div id="countdown"
                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-60 hidden z-10">
                                <div class="text-white text-8xl font-bold animate-pulse drop-shadow-[0_4px_8px_rgba(0,0,0,0.8)]"
                                    id="countdownNumber">3</div>
                            </div>

                            <!-- Timer pendant l'enregistrement (discret en haut à gauche) -->
                            <div id="recordingTimer"
                                class="absolute top-4 left-4 flex items-center space-x-2 bg-red-600 bg-opacity-90 px-3 py-2 rounded-full hidden z-10 shadow-lg">
                                <span class="w-3 h-3 bg-white rounded-full animate-pulse"></span>
                                <span class="text-white text-sm font-semibold">REC</span>
                                <span class="text-white text-sm font-mono" id="timerDisplay">00:00</span>
                            </div>

                            <!-- Durée restante -->
                            <div id="remainingTime"
                                class="absolute top-4 right-4 bg-black bg-opacity-75 text-white px-3 py-2 rounded-lg font-bold hidden">
                                <span id="remainingSeconds">60</span>s restantes
                            </div>
                        </div>

                        <!-- Informations importantes -->
                        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Informations :</strong>
                                    </p>
                                    <ul class="list-disc pl-5 mt-2 text-xs text-blue-600 space-y-1">
                                        <li>Durée maximale : <strong>60 secondes</strong></li>
                                        <li>Taille maximale : <strong>500 Mo</strong></li>
                                        <li>Vous pouvez arrêter l'enregistrement avant la fin du temps imparti</li>
                                        <li>Un compte à rebours de 3 secondes précèdera l'enregistrement</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contrôles -->
                    <div class="space-y-4">
                        <!-- Boutons d'action -->
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button type="button" id="startCameraBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-camera mr-2"></i>Activer la caméra
                            </button>

                            <button type="button" id="startRecordingBtn"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center hidden"
                                disabled>
                                <i class="fas fa-circle mr-2"></i>Commencer l'enregistrement
                            </button>

                            <button type="button" id="stopRecordingBtn"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center hidden">
                                <i class="fas fa-stop mr-2"></i>Arrêter l'enregistrement
                            </button>

                            <button type="button" id="restartBtn"
                                class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center hidden">
                                <i class="fas fa-redo mr-2"></i>Recommencer
                            </button>
                        </div>

                        <!-- Formulaire de soumission -->
                        <form id="uploadForm" method="POST" action="{{ route('prestataire.videos.create.step1.store') }}"
                            enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input type="file" name="video" id="recordedVideoInput" accept="video/webm,video/mp4"
                                style="display: none;">

                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <button type="button" id="cancelBtn"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 font-medium py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>

                                <button type="submit" id="submitBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-check mr-2"></i>Utiliser cette vidéo
                                </button>
                            </div>
                        </form>

                        <!-- Informations sur le fichier -->
                        <div id="fileInfo" class="hidden bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div><strong>Durée :</strong> <span id="videoDuration">-</span></div>
                                <div><strong>Taille :</strong> <span id="videoSize">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de dépannage -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4 text-xs text-gray-600">
                    <p class="font-semibold mb-2">Problèmes courants :</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Si la caméra ne s'active pas, vérifiez les autorisations de votre navigateur</li>
                        <li>Utilisez un navigateur récent (Chrome, Firefox, Safari, Edge)</li>
                        <li>Assurez-vous d'avoir une connexion stable pour l'envoi de la vidéo</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuration
            const MAX_DURATION = 60; // 60 secondes max
            const MAX_FILE_SIZE = 500 * 1024 * 1024; // 500 Mo max
            const COUNTDOWN_DURATION = 3; // 3 secondes de compte à rebours

            // Elements
            const cameraPreview = document.getElementById('cameraPreview');
            const recordedPreview = document.getElementById('recordedPreview');
            const startCameraBtn = document.getElementById('startCameraBtn');
            const startRecordingBtn = document.getElementById('startRecordingBtn');
            const stopRecordingBtn = document.getElementById('stopRecordingBtn');
            const restartBtn = document.getElementById('restartBtn');
            const uploadForm = document.getElementById('uploadForm');
            const recordedVideoInput = document.getElementById('recordedVideoInput');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const fileInfo = document.getElementById('fileInfo');
            const countdown = document.getElementById('countdown');
            const recordingTimer = document.getElementById('recordingTimer');
            const timerDisplay = document.getElementById('timerDisplay');
            const remainingTime = document.getElementById('remainingTime');
            const remainingSeconds = document.getElementById('remainingSeconds');

            // Variables
            let mediaStream = null;
            let mediaRecorder = null;
            let recordedChunks = [];
            let recordingStartTime = null;
            let timerInterval = null;
            let recordingTimeout = null;
            let videoBlob = null;

            // Démarrer la caméra
            startCameraBtn.addEventListener('click', async function () {
                try {
                    mediaStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: { ideal: 1920 },
                            height: { ideal: 1080 },
                            facingMode: 'user'
                        },
                        audio: true
                    });

                    cameraPreview.srcObject = mediaStream;
                    startCameraBtn.classList.add('hidden');
                    startRecordingBtn.classList.remove('hidden');
                    startRecordingBtn.disabled = false;
                } catch (error) {
                    console.error('Erreur lors de l\'accès à la caméra:', error);
                    alert('Impossible d\'accéder à la caméra. Veuillez vérifier les autorisations de votre navigateur.');
                }
            });

            // Commencer l'enregistrement avec compte à rebours
            startRecordingBtn.addEventListener('click', function () {
                startRecordingBtn.disabled = true;
                countdown.classList.remove('hidden');
                const countdownNumber = document.getElementById('countdownNumber');

                let countdownValue = COUNTDOWN_DURATION;
                countdownNumber.textContent = countdownValue;

                const countdownInterval = setInterval(() => {
                    countdownValue--;
                    if (countdownValue > 0) {
                        countdownNumber.textContent = countdownValue;
                    } else {
                        clearInterval(countdownInterval);
                        countdown.classList.add('hidden');
                        startRecording();
                    }
                }, 1000);
            });

            // Fonction d'enregistrement
            function startRecording() {
                recordedChunks = [];

                // Configurer le MediaRecorder avec des options optimisées
                const options = {
                    mimeType: 'video/webm;codecs=vp9,opus',
                    videoBitsPerSecond: 2500000 // 2.5 Mbps pour un bon équilibre qualité/taille
                };

                // Fallback si vp9 n'est pas supporté
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'video/webm;codecs=vp8,opus';
                }

                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'video/webm';
                }

                mediaRecorder = new MediaRecorder(mediaStream, options);

                mediaRecorder.ondataavailable = function (event) {
                    if (event.data && event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = function () {
                    handleRecordingStop();
                };

                mediaRecorder.start(100); // Enregistrer des chunks toutes les 100ms
                recordingStartTime = Date.now();

                // Afficher le timer
                recordingTimer.classList.remove('hidden');
                remainingTime.classList.remove('hidden');
                startRecordingBtn.classList.add('hidden');
                stopRecordingBtn.classList.remove('hidden');

                // Timer d'affichage
                timerInterval = setInterval(updateTimer, 100);

                // Arrêt automatique à 60 secondes
                recordingTimeout = setTimeout(() => {
                    stopRecording();
                }, MAX_DURATION * 1000);
            }

            // Mettre à jour le timer
            function updateTimer() {
                const elapsed = (Date.now() - recordingStartTime) / 1000;
                const remaining = Math.max(0, MAX_DURATION - Math.floor(elapsed));

                const minutes = Math.floor(elapsed / 60);
                const seconds = Math.floor(elapsed % 60);
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                remainingSeconds.textContent = remaining;

                if (remaining === 0) {
                    stopRecording();
                }
            }

            // Arrêter l'enregistrement
            stopRecordingBtn.addEventListener('click', stopRecording);

            function stopRecording() {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    clearInterval(timerInterval);
                    clearTimeout(recordingTimeout);
                    stopRecordingBtn.classList.add('hidden');
                }
            }

            // Gérer la fin de l'enregistrement
            function handleRecordingStop() {
                // Créer le blob vidéo
                const mimeType = mediaRecorder.mimeType;
                videoBlob = new Blob(recordedChunks, { type: mimeType });

                // Vérifier la taille
                if (videoBlob.size > MAX_FILE_SIZE) {
                    alert('La vidéo enregistrée dépasse la taille maximale autorisée (500 Mo). Veuillez enregistrer une vidéo plus courte ou de moindre qualité.');
                    restartRecording();
                    return;
                }

                // Arrêter le flux de la caméra
                if (mediaStream) {
                    mediaStream.getTracks().forEach(track => track.stop());
                }

                // Afficher la prévisualisation
                const videoUrl = URL.createObjectURL(videoBlob);
                recordedPreview.src = videoUrl;
                cameraPreview.classList.add('hidden');
                recordedPreview.classList.remove('hidden');
                recordingTimer.classList.add('hidden');
                remainingTime.classList.add('hidden');

                // Afficher les options de soumission
                uploadForm.classList.remove('hidden');
                restartBtn.classList.remove('hidden');

                // Créer un fichier pour le formulaire
                const fileName = `recorded-video-${Date.now()}.webm`;
                const file = new File([videoBlob], fileName, { type: mimeType });

                // Créer un DataTransfer pour assigner le fichier à l'input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                recordedVideoInput.files = dataTransfer.files;

                // Afficher les informations du fichier
                displayFileInfo(file);
            }

            // Afficher les informations du fichier
            function displayFileInfo(file) {
                const duration = (Date.now() - recordingStartTime) / 1000;
                const size = (file.size / (1024 * 1024)).toFixed(2);

                document.getElementById('videoDuration').textContent = `${Math.floor(duration)}s`;
                document.getElementById('videoSize').textContent = `${size} Mo`;
                fileInfo.classList.remove('hidden');
            }

            // Recommencer
            restartBtn.addEventListener('click', restartRecording);
            cancelBtn.addEventListener('click', restartRecording);

            function restartRecording() {
                // Réinitialiser l'interface
                recordedPreview.classList.add('hidden');
                cameraPreview.classList.remove('hidden');
                uploadForm.classList.add('hidden');
                restartBtn.classList.add('hidden');
                fileInfo.classList.add('hidden');
                startCameraBtn.classList.remove('hidden');

                // Nettoyer
                if (recordedPreview.src) {
                    URL.revokeObjectURL(recordedPreview.src);
                    recordedPreview.src = '';
                }

                recordedChunks = [];
                videoBlob = null;
                recordedVideoInput.value = '';
            }

            // Empêcher la soumission si la vidéo est trop grande
            uploadForm.addEventListener('submit', function (e) {
                if (!recordedVideoInput.files || recordedVideoInput.files.length === 0) {
                    e.preventDefault();
                    alert('Aucune vidéo enregistrée.');
                    return false;
                }

                const file = recordedVideoInput.files[0];
                if (file.size > MAX_FILE_SIZE) {
                    e.preventDefault();
                    alert(`La vidéo est trop volumineuse (${(file.size / (1024 * 1024)).toFixed(2)} Mo). Maximum autorisé : 500 Mo.`);
                    return false;
                }

                // Afficher un indicateur de chargement
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
            });

            // Nettoyer lors de la fermeture de la page
            window.addEventListener('beforeunload', function () {
                if (mediaStream) {
                    mediaStream.getTracks().forEach(track => track.stop());
                }
            });
        });
    </script>
@endpush