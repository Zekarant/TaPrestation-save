@extends('layouts.app')

@push('styles')
<style>
    /* Reset et base - Style Snapchat */
    .snap-container {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #000;
        z-index: 9999;
        overflow: hidden;
    }

    /* Vidéo plein écran */
    .snap-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Header overlay */
    .snap-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        padding: calc(12px + env(safe-area-inset-top, 0px)) 16px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
        background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, transparent 100%);
    }

    .snap-close {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: none;
        color: white;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .snap-close:hover {
        background: rgba(0,0,0,0.5);
    }

    .snap-flash {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: none;
        color: white;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .snap-flash.active {
        background: rgba(255,230,0,0.9);
        color: #000;
    }

    .snap-header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Contrôles du bas */
    .snap-controls {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 24px 28px calc(32px + env(safe-area-inset-bottom, 0px));
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 44px;
        background: linear-gradient(to top, rgba(0,0,0,0.25) 0%, transparent 100%);
        z-index: 10;
    }

    /* Bouton galerie */
    .snap-gallery {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1.5px solid rgba(255,255,255,0.25);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .snap-gallery:hover {
        transform: scale(1.05);
        background: rgba(255,255,255,0.2);
    }

    .snap-gallery-icon {
        color: white;
        opacity: 0.9;
    }

    /* Bouton d'enregistrement principal - Style Snapchat */
    .snap-record-btn {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: transparent;
        border: 5px solid rgba(255,255,255,0.85);
        cursor: pointer;
        position: relative;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        -webkit-tap-highlight-color: transparent;
    }

    .snap-record-btn:active {
        transform: scale(0.93);
    }

    .snap-record-inner {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: white;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .snap-record-btn.recording .snap-record-inner {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: #ff4757;
    }

    .snap-record-btn.recording {
        border-color: #ff4757;
        animation: pulse-recording 1s infinite;
    }

    @keyframes pulse-recording {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.5); }
        50% { box-shadow: 0 0 0 20px rgba(255, 71, 87, 0); }
    }

    /* Indicateur de verrouillage - glisser vers le haut */
    .snap-lock-indicator {
        position: absolute;
        left: 50%;
        top: -100px;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .snap-lock-indicator.visible {
        opacity: 1;
    }

    .snap-lock-indicator.active {
        opacity: 1;
        top: -80px;
    }

    .snap-lock-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s ease;
    }

    .snap-lock-indicator.active .snap-lock-icon {
        background: #ff4757;
        transform: scale(1.2);
    }

    .snap-lock-arrow {
        color: rgba(255,255,255,0.6);
        animation: slideUp 1s infinite;
        transform: rotate(-90deg);
    }

    @keyframes slideUp {
        0%, 100% { transform: rotate(-90deg) translateX(0); opacity: 0.6; }
        50% { transform: rotate(-90deg) translateX(-5px); opacity: 1; }
    }

    /* Bouton stop quand verrouillé */
    .snap-stop-btn {
        position: absolute;
        bottom: 150px;
        left: 50%;
        transform: translateX(-50%);
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #ff4757;
        border: 4px solid white;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 20;
        box-shadow: 0 4px 20px rgba(255, 71, 87, 0.5);
        animation: pulse-stop 1.5s infinite;
    }

    .snap-stop-btn.visible {
        display: flex;
    }

    .snap-stop-btn .stop-square {
        width: 24px;
        height: 24px;
        background: white;
        border-radius: 4px;
    }

    @keyframes pulse-stop {
        0%, 100% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.05); }
    }

    /* Bouton flip caméra */
    .snap-flip {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .snap-flip:hover {
        background: rgba(0,0,0,0.5);
    }

    /* Timer circulaire autour du bouton record */
    .snap-timer-ring {
        position: absolute;
        width: 95px;
        height: 95px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .snap-timer-ring svg {
        transform: rotate(-90deg);
    }

    .snap-timer-ring circle {
        fill: none;
        stroke-width: 4;
    }

    .snap-timer-ring .bg {
        stroke: rgba(255,255,255,0.2);
    }

    .snap-timer-ring .progress {
        stroke: #ff4757;
        stroke-linecap: round;
        stroke-dasharray: 270;
        stroke-dashoffset: 270;
        transition: stroke-dashoffset 0.1s linear;
    }

    /* Timer texte */
    .snap-timer {
        position: absolute;
        top: calc(70px + env(safe-area-inset-top, 0px));
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 15px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        display: none;
        z-index: 10;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        letter-spacing: 0.5px;
    }

    .snap-timer.visible {
        display: flex;
        align-items: center;
        gap: 6px;
        animation: fadeIn 0.3s;
    }

    .snap-timer::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ff4757;
        animation: blink-dot 1s infinite;
    }

    @keyframes blink-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Hint */
    .snap-hint {
        position: absolute;
        bottom: 160px;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255,255,255,0.6);
        font-size: 13px;
        font-weight: 400;
        z-index: 10;
        text-align: center;
        text-shadow: 0 1px 4px rgba(0,0,0,0.4);
        white-space: nowrap;
    }

    /* Preview screen */
    .snap-preview {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #000;
        z-index: 20;
        display: none;
    }

    .snap-preview.visible {
        display: flex;
        flex-direction: column;
    }

    .snap-preview-video {
        flex: 1;
        width: 100%;
        object-fit: cover;
    }

    .snap-preview-controls {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px 20px calc(20px + env(safe-area-inset-bottom, 0px));
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
    }

    .snap-form {
        margin-bottom: 16px;
    }

    .snap-input {
        width: 100%;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 16px;
        padding: 16px 20px;
        color: white;
        font-size: 16px;
        margin-bottom: 12px;
        backdrop-filter: blur(10px);
    }

    .snap-input::placeholder {
        color: rgba(255,255,255,0.5);
    }

    .snap-input:focus {
        outline: none;
        border-color: rgba(255,255,255,0.4);
        background: rgba(255,255,255,0.15);
    }

    .snap-textarea {
        resize: none;
        min-height: 70px;
    }

    .snap-preview-actions {
        display: flex;
        gap: 12px;
    }

    .snap-btn-cancel {
        flex: 1;
        padding: 16px;
        border-radius: 50px;
        border: 1.5px solid rgba(255,255,255,0.3);
        background: transparent;
        color: white;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .snap-btn-cancel:hover {
        background: rgba(255,255,255,0.08);
    }

    .snap-btn-send {
        flex: 2;
        padding: 16px;
        border-radius: 50px;
        border: none;
        background: white;
        color: #000;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .snap-btn-send:hover {
        opacity: 0.9;
    }

    .snap-btn-send:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Loading */
    .snap-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.9);
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 30;
    }

    .snap-loading.visible {
        display: flex;
    }

    .snap-spinner {
        width: 44px;
        height: 44px;
        border: 3px solid rgba(255,255,255,0.15);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .snap-loading-text {
        color: white;
        margin-top: 20px;
        font-size: 16px;
        font-weight: 500;
    }

    /* Permission screen - cachée par défaut */
    .snap-permission {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #000;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 25;
        padding: 40px;
        text-align: center;
    }

    .snap-permission-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 28px;
    }

    .snap-permission-icon svg {
        width: 50px;
        height: 50px;
        color: white;
    }

    .snap-permission h2 {
        color: white;
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .snap-permission p {
        color: rgba(255,255,255,0.5);
        font-size: 15px;
        margin-bottom: 36px;
        max-width: 260px;
        line-height: 1.5;
    }

    .snap-permission-btn {
        padding: 16px 44px;
        border-radius: 50px;
        border: none;
        background: white;
        color: #000;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .snap-permission-btn:hover {
        transform: scale(1.03);
        opacity: 0.9;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    /* Hide header/footer en mode snap */
    body.snap-mode header,
    body.snap-mode footer,
    body.snap-mode nav,
    body.snap-mode .navbar,
    body.snap-mode .bottom-nav {
        display: none !important;
    }

    body.snap-mode {
        overflow: hidden;
    }

</style>
@endpush

@section('content')
<div class="snap-container" id="snapContainer">
    <!-- Permission screen -->
    <div class="snap-permission" id="permissionScreen">
        <div class="snap-permission-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2>Créer une vidéo</h2>
        <p>Autorisez l'accès à votre caméra pour enregistrer des vidéos professionnelles</p>
        <button class="snap-permission-btn" id="requestPermission">
            Activer la caméra
        </button>
    </div>

    <!-- Live video -->
    <video class="snap-video" id="liveVideo" autoplay playsinline muted></video>

    <!-- Header -->
    <div class="snap-header">
        <a href="{{ route('prestataire.videos.manage') }}" class="snap-close">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </a>
        <div class="snap-header-right">
            <button class="snap-flash" id="flashBtn" title="Flash">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                </svg>
            </button>
            <button class="snap-flash" id="flipBtn" title="Retourner">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Timer -->
    <div class="snap-timer" id="timer">00:00</div>

    <!-- Hint -->
    <div class="snap-hint" id="hint">Maintenez pour filmer</div>

    <!-- Bouton Stop (visible quand verrouillé) -->
    <button class="snap-stop-btn" id="stopBtn">
        <div class="stop-square"></div>
    </button>

    <!-- Controls -->
    <div class="snap-controls">
        <label class="snap-gallery" id="galleryBtn">
            <input type="file" accept="video/mp4,video/webm,video/quicktime,video/mov" id="fileInput" style="display:none;">
            <div class="snap-gallery-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </label>

        <div style="position: relative;">
            <button class="snap-record-btn" id="recordBtn">
                <div class="snap-record-inner"></div>
            </button>
            <div class="snap-timer-ring" id="timerRing">
                <svg width="95" height="95">
                    <circle class="bg" cx="47.5" cy="47.5" r="43"></circle>
                    <circle class="progress" id="progressCircle" cx="47.5" cy="47.5" r="43"></circle>
                </svg>
            </div>
            <div class="snap-lock-indicator" id="lockIndicator">
                <div class="snap-lock-arrow">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
                    </svg>
                </div>
                <div class="snap-lock-icon">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <button class="snap-flip" id="flipBtnBottom" title="Changer de camera">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <!-- Preview screen -->
    <div class="snap-preview" id="previewScreen">
        <video class="snap-preview-video" id="previewVideo" playsinline loop muted></video>
        
        <div class="snap-header">
            <button class="snap-close" id="cancelPreview">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="snap-preview-controls">
            <form class="snap-form" id="uploadForm" action="{{ route('prestataire.videos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="video" id="videoFileInput" style="display:none;" accept="video/*">
                
                <input type="text" name="title" class="snap-input" placeholder="Ajouter un titre..." required maxlength="100">
                <textarea name="description" class="snap-input snap-textarea" placeholder="Description (optionnel)..." maxlength="300"></textarea>
            </form>

            <div class="snap-preview-actions">
                <button type="button" class="snap-btn-cancel" id="retakeBtn">
                    Reprendre
                </button>
                <button type="submit" form="uploadForm" class="snap-btn-send" id="sendBtn">
                    <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                    Publier
                </button>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div class="snap-loading" id="loadingScreen">
        <div class="snap-spinner"></div>
        <p class="snap-loading-text">Publication en cours...</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const permissionScreen = document.getElementById('permissionScreen');
    const requestPermissionBtn = document.getElementById('requestPermission');
    const liveVideo = document.getElementById('liveVideo');
    const recordBtn = document.getElementById('recordBtn');
    const flipBtn = document.getElementById('flipBtn');
    const flipBtnBottom = document.getElementById('flipBtnBottom');
    const flashBtn = document.getElementById('flashBtn');
    const timer = document.getElementById('timer');
    const progressCircle = document.getElementById('progressCircle');
    const hint = document.getElementById('hint');
    const previewScreen = document.getElementById('previewScreen');
    const previewVideo = document.getElementById('previewVideo');
    const cancelPreviewBtn = document.getElementById('cancelPreview');
    const retakeBtn = document.getElementById('retakeBtn');
    const uploadForm = document.getElementById('uploadForm');
    const sendBtn = document.getElementById('sendBtn');
    const loadingScreen = document.getElementById('loadingScreen');
    const lockIndicator = document.getElementById('lockIndicator');
    const stopBtn = document.getElementById('stopBtn');
    const fileInput = document.getElementById('fileInput');
    const videoFileInput = document.getElementById('videoFileInput');

    let stream = null;
    let mediaRecorder = null;
    let recordedChunks = [];
    let isRecording = false;
    let recordingStartTime = 0;
    let timerInterval = null;
    let facingMode = 'environment'; // Caméra arrière par défaut
    let flashEnabled = false;
    let recordedMimeType = null;
    const MAX_DURATION = 60;
    const CIRCLE_LENGTH = 270; // 2 * PI * 43
    
    // Variables pour le zoom tactile
    let currentZoom = 1;
    let initialDistance = 0;
    let zoomTrack = null;
    let minZoom = 1;
    let maxZoom = 5;
    let cameraRetried = false; // Éviter les boucles infinies

    // Variables pour le verrouillage d'enregistrement (style Snapchat)
    let isLocked = false;
    let touchStartX = 0;
    let touchStartY = 0;
    const LOCK_THRESHOLD = 60; // Distance en pixels pour activer le verrouillage

    document.body.classList.add('snap-mode');

    // Démarrer la caméra automatiquement au chargement
    startCamera();

    async function startCamera() {
        try {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Demander une qualité HD (720p) pour assurer la compatibilité
            const constraints = {
                video: {
                    facingMode: facingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    frameRate: { ideal: 30 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            };

            let selectedStream = null;
            
            try {
                selectedStream = await navigator.mediaDevices.getUserMedia(constraints);
                const track = selectedStream.getVideoTracks()[0];
                const settings = track.getSettings();
            } catch (e) {
                // Fallback avec contraintes minimales
                selectedStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facingMode },
                    audio: true
                });
            }

            stream = selectedStream;

            liveVideo.srcObject = stream;
            permissionScreen.style.display = 'none';
            cameraRetried = false; // Réinitialiser si succès

            const track = stream.getVideoTracks()[0];
            const settings = track.getSettings();

            zoomTrack = track;
            const caps = track.getCapabilities ? track.getCapabilities() : {};
            if (!caps.torch) flashBtn.style.display = 'none';
            
            // Configuration du zoom - réinitialiser à 1x
            if (caps.zoom) {
                minZoom = caps.zoom.min || 1;
                maxZoom = caps.zoom.max || 5;
                currentZoom = minZoom;
                // Forcer le zoom à 1x au démarrage
                try {
                    await track.applyConstraints({ advanced: [{ zoom: minZoom }] });
                } catch (e) {
                }
            }
        } catch (error) {
            console.error('Camera error:', error);
            
            // Si permission refusée, ne pas réessayer en boucle
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                permissionScreen.style.display = 'flex';
                return;
            }
            
            // Essayer avec la caméra frontale si la caméra arrière échoue (une seule fois)
            if (facingMode === 'environment' && !cameraRetried) {
                facingMode = 'user';
                cameraRetried = true;
                startCamera();
                return;
            }
            // Afficher l'écran de permission si tout échoue
            permissionScreen.style.display = 'flex';
        }
    }

    requestPermissionBtn.addEventListener('click', function() {
        permissionScreen.style.display = 'none';
        cameraRetried = false; // Permettre une nouvelle tentative
        startCamera();
    });

    function flipCamera() {
        facingMode = facingMode === 'user' ? 'environment' : 'user';
        startCamera();
    }
    flipBtn.addEventListener('click', flipCamera);
    flipBtnBottom.addEventListener('click', flipCamera);

    flashBtn.addEventListener('click', function() {
        if (!stream) return;
        const track = stream.getVideoTracks()[0];
        flashEnabled = !flashEnabled;
        track.applyConstraints({ advanced: [{ torch: flashEnabled }] });
        flashBtn.classList.toggle('active', flashEnabled);
    });

    function startRecording() {
        if (isRecording || !stream) return;

        isRecording = true;
        recordedChunks = [];
        recordBtn.classList.add('recording');
        hint.style.display = 'none';
        timer.classList.add('visible');

        // IMPORTANT: iOS Safari UNIQUEMENT supporte H.264 (avc1)
        // Chrome/Firefox supportent aussi WebM mais iOS ne le lit pas
        // On vérifie si on est sur iOS pour choisir le bon codec
        var isIOSDevice = /iPad|iPhone|iPod/.test(navigator.userAgent);
        
        // Sur iOS, utiliser uniquement MP4 H.264
        // Sur autres plateformes, essayer MP4 d'abord, puis WebM
        const candidates = isIOSDevice ? [
            'video/mp4;codecs=avc1.42E01E,mp4a.40.2', // H.264 Baseline (meilleure compatibilité iOS)
            'video/mp4;codecs=avc1.640028,mp4a.40.2', // H.264 High Profile
            'video/mp4'
        ] : [
            'video/mp4;codecs=avc1.42E01E,mp4a.40.2', // H.264 Baseline - PRIORITÉ pour compatibilité iOS
            'video/mp4;codecs=avc1.640028,mp4a.40.2', // H.264 High Profile
            'video/mp4',
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm'
        ];

        // Calculer bitrate selon résolution - OPTIMISÉ POUR LE WEB
        const track = stream.getVideoTracks()[0];
        const settings = track.getSettings();
        let videoBitrate = 2500000; // 2.5 Mbps par défaut (720p)
        
        if (settings.width >= 3840) {
            videoBitrate = 8000000; // 8 Mbps pour 4K (suffisant pour le web)
        } else if (settings.width >= 1920) {
            videoBitrate = 4500000; // 4.5 Mbps pour Full HD
        } else if (settings.width >= 1280) {
            videoBitrate = 2500000; // 2.5 Mbps pour HD
        } else {
            videoBitrate = 1500000; // 1.5 Mbps pour SD
        }
        

        let options = null;
        let selectedMimeType = null;
        
        for (const mimeType of candidates) {
            if (MediaRecorder.isTypeSupported(mimeType)) {
                selectedMimeType = mimeType;
                options = { 
                    mimeType,
                    videoBitsPerSecond: videoBitrate,
                    audioBitsPerSecond: 128000  // 128 kbps audio (suffisant)
                };
                recordedMimeType = mimeType;
                break;
            }
        }
        
        // ALERTE: Si on utilise WebM, la vidéo ne sera PAS lisible sur iOS
        if (selectedMimeType && selectedMimeType.includes('webm')) {
            console.warn('ATTENTION: Enregistrement en WebM - non compatible iOS Safari!');
            // On continue quand même car c'est le seul format disponible
        }

        if (!options) {
            options = {
                videoBitsPerSecond: videoBitrate,
                audioBitsPerSecond: 128000
            };
            recordedMimeType = null;
            console.warn('Aucun codec spécifique trouvé, utilisation du défaut');
        }

        mediaRecorder = new MediaRecorder(stream, options);
        // Certains navigateurs renseignent le mimetype réel ici
        if (mediaRecorder && mediaRecorder.mimeType) {
            recordedMimeType = mediaRecorder.mimeType;
        }
        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) recordedChunks.push(e.data); };
        mediaRecorder.onstop = finishRecording;
        mediaRecorder.start(100);
        recordingStartTime = Date.now();

        timerInterval = setInterval(() => {
            const elapsed = (Date.now() - recordingStartTime) / 1000;
            const mins = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const secs = Math.floor(elapsed % 60).toString().padStart(2, '0');
            timer.textContent = `${mins}:${secs}`;

            const progress = (elapsed / MAX_DURATION);
            progressCircle.style.strokeDashoffset = CIRCLE_LENGTH - (progress * CIRCLE_LENGTH);

            if (elapsed >= MAX_DURATION) stopRecording();
        }, 100);
    }

    function stopRecording() {
        if (!isRecording) return;
        isRecording = false;
        recordBtn.classList.remove('recording');
        clearInterval(timerInterval);
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
    }

    function finishRecording() {
        const blobType = recordedMimeType || 'video/webm';
        const blob = new Blob(recordedChunks, { type: blobType });
        const duration = (Date.now() - recordingStartTime) / 1000;
        
        if (duration < 0.5) {
            resetUI();
            return;
        }
        showPreview(blob);
    }

    function resetUI() {
        timer.classList.remove('visible');
        timer.textContent = '00:00';
        progressCircle.style.strokeDashoffset = CIRCLE_LENGTH;
        hint.style.display = 'none';
        isLocked = false;
        lockIndicator.classList.remove('visible', 'active');
        stopBtn.classList.remove('visible');
    }

    // ========== Gestion du verrouillage style Snapchat ==========
    
    // Souris - Desktop
    recordBtn.addEventListener('mousedown', function(e) {
        touchStartX = e.clientX;
        startRecording();
        lockIndicator.classList.add('visible');
    });
    
    recordBtn.addEventListener('mouseup', function() {
        if (!isLocked) {
            stopRecording();
        }
        lockIndicator.classList.remove('visible');
    });
    
    recordBtn.addEventListener('mouseleave', function() {
        if (isRecording && !isLocked) {
            stopRecording();
        }
        if (!isLocked) {
            lockIndicator.classList.remove('visible');
        }
    });

    // Touch - Mobile
    recordBtn.addEventListener('touchstart', function(e) {
        e.preventDefault();
        if (e.touches.length > 0) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }
        startRecording();
        lockIndicator.classList.add('visible');
    });

    recordBtn.addEventListener('touchmove', function(e) {
        if (!isRecording || isLocked) return;
        
        const touch = e.touches[0];
        const deltaY = touchStartY - touch.clientY; // Positif si on va vers le haut
        
        // Si on glisse vers le HAUT de plus de LOCK_THRESHOLD pixels
        if (deltaY > LOCK_THRESHOLD) {
            isLocked = true;
            lockIndicator.classList.add('active');
            stopBtn.classList.add('visible');
            
            // Vibration feedback si disponible
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        } else if (deltaY > 20) {
            // Feedback visuel pendant le glissement
            lockIndicator.style.opacity = Math.min(1, deltaY / LOCK_THRESHOLD);
        }
    });

    recordBtn.addEventListener('touchend', function(e) {
        e.preventDefault();
        if (!isLocked) {
            stopRecording();
            lockIndicator.classList.remove('visible');
        }
    });

    // Bouton stop pour arrêter quand verrouillé
    stopBtn.addEventListener('click', function() {
        if (isLocked && isRecording) {
            stopRecording();
        }
    });

    stopBtn.addEventListener('touchend', function(e) {
        e.preventDefault();
        if (isLocked && isRecording) {
            stopRecording();
        }
    });

    // Zoom par pincement tactile (pinch-to-zoom)
    liveVideo.addEventListener('touchstart', function(e) {
        if (e.touches.length === 2) {
            initialDistance = getDistance(e.touches[0], e.touches[1]);
        }
    }, { passive: true });

    liveVideo.addEventListener('touchmove', function(e) {
        if (e.touches.length === 2 && zoomTrack) {
            e.preventDefault();
            const currentDistance = getDistance(e.touches[0], e.touches[1]);
            const scale = currentDistance / initialDistance;
            let newZoom = currentZoom * scale;
            
            // Limiter le zoom
            newZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
            
            try {
                const caps = zoomTrack.getCapabilities();
                if (caps.zoom) {
                    zoomTrack.applyConstraints({ advanced: [{ zoom: newZoom }] });
                }
            } catch (err) {
            }
        }
    }, { passive: false });

    liveVideo.addEventListener('touchend', function(e) {
        if (e.touches.length < 2 && zoomTrack) {
            try {
                const caps = zoomTrack.getCapabilities();
                if (caps.zoom) {
                    const settings = zoomTrack.getSettings();
                    currentZoom = settings.zoom || 1;
                }
            } catch (err) {}
        }
    }, { passive: true });

    function getDistance(touch1, touch2) {
        const dx = touch1.clientX - touch2.clientX;
        const dy = touch1.clientY - touch2.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // File input
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        // Limite augmentée à 512 Mo pour supporter les vidéos 4K/1080p importées
        if (file.size > 512 * 1024 * 1024) {
            alert('La vidéo ne doit pas dépasser 512 Mo');
            return;
        }
        showPreview(file, true);
    });

    function showPreview(blob, isFile = false) {
        const url = URL.createObjectURL(blob);
        previewVideo.src = url;
        previewVideo.play();
        previewScreen.classList.add('visible');
        resetUI();

        let file = blob;
        if (!isFile) {
            const type = (blob && blob.type) ? blob.type : (recordedMimeType || 'video/webm');
            const isMp4 = type.includes('mp4');
            const name = isMp4 ? 'video.mp4' : 'video.webm';
            file = new File([blob], name, { type });
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        videoFileInput.files = dt.files;
    }

    function hidePreview() {
        previewScreen.classList.remove('visible');
        previewVideo.pause();
        previewVideo.src = '';
        uploadForm.reset();
    }

    cancelPreviewBtn.addEventListener('click', hidePreview);
    retakeBtn.addEventListener('click', hidePreview);

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = uploadForm.querySelector('input[name="title"]').value.trim();
        if (!title) { alert('Ajoutez un titre'); return; }
        if (!videoFileInput.files.length) { alert('Aucune vidéo'); return; }

        loadingScreen.classList.add('visible');
        sendBtn.disabled = true;

        const formData = new FormData(uploadForm);
        
        // S'assurer que le fichier vidéo est bien inclus
        if (videoFileInput.files[0]) {
            formData.set('video', videoFileInput.files[0]);
        }
        
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        fetch(uploadForm.action, {
            method: 'POST',
            body: formData,
            headers: { 
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json, text/html, */*'
            },
            credentials: 'same-origin'
        })
        .then(res => {
            // Gérer la redirection
            if (res.redirected) {
                window.location.href = res.url;
                return;
            }
            // Vérifier si c'est une réponse OK
            if (res.ok) {
                return res.text().then(text => {
                    // Essayer de parser en JSON
                    try {
                        const json = JSON.parse(text);
                        if (json.redirect) {
                            window.location.href = json.redirect;
                        } else if (json.success) {
                            window.location.href = '{{ route("prestataire.videos.manage") }}';
                        } else {
                            window.location.href = '{{ route("prestataire.videos.manage") }}';
                        }
                    } catch (e) {
                        // Ce n'est pas du JSON, probablement une page HTML de succès
                        window.location.href = '{{ route("prestataire.videos.manage") }}';
                    }
                });
            } else {
                return res.text().then(text => {
                    console.error('Erreur serveur:', text);
                    throw new Error('Erreur serveur: ' + res.status);
                });
            }
        })
        .catch(err => {
            console.error('Erreur publication:', err);
            loadingScreen.classList.remove('visible');
            sendBtn.disabled = false;
            alert('Erreur lors de la publication. Vérifiez votre connexion et réessayez.');
        });
    });

    window.addEventListener('beforeunload', () => {
        if (stream) stream.getTracks().forEach(t => t.stop());
        document.body.classList.remove('snap-mode');
    });
});
</script>
@endpush
