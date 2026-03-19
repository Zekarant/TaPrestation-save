/**
 * Snap Camera - Interface camera style Snapchat
 * Enregistrement video in-browser avec MediaRecorder API
 * Compatible iOS Safari 14.6+, Android Chrome, desktop
 */
(function () {
    'use strict';

    const MAX_DURATION = 60; // secondes
    const VIDEO_BITRATE = 2_500_000; // 2.5 Mbps
    const AUDIO_BITRATE = 128_000;   // 128 kbps

    // State
    let stream = null;
    let mediaRecorder = null;
    let chunks = [];
    let recordedBlob = null;
    let isRecording = false;
    let facingMode = 'environment'; // 'user' = front, 'environment' = back
    let timerInterval = null;
    let elapsed = 0;
    let onVideoReady = null; // callback(file)
    let torchTrack = null;
    let torchOn = false;

    // DOM refs (resolved lazily)
    const $ = (id) => document.getElementById(id);

    function getEls() {
        return {
            overlay:       $('snap-camera-overlay'),
            viewfinder:    $('snap-camera-viewfinder'),
            closeBtn:      $('snap-camera-close'),
            timer:         $('snap-camera-timer'),
            timerText:     $('snap-camera-timer-text'),
            flashBtn:      $('snap-camera-flash'),
            flashOff:      $('snap-flash-off'),
            flashOn:       $('snap-flash-on'),
            progress:      $('snap-camera-progress'),
            galleryBtn:    $('snap-camera-gallery'),
            recordBtn:     $('snap-camera-record'),
            recordRing:    $('snap-record-ring'),
            flipBtn:       $('snap-camera-flip'),
            preview:       $('snap-camera-preview'),
            previewVideo:  $('snap-preview-video'),
            retakeBtn:     $('snap-preview-retake'),
            previewDur:    $('snap-preview-duration'),
            useBtn:        $('snap-preview-use'),
            denied:        $('snap-camera-denied'),
            deniedClose:   $('snap-camera-denied-close'),
        };
    }

    // ─── Camera stream ─────────────────────────────────────

    async function startCamera(els) {
        stopCamera();

        const constraints = {
            video: {
                facingMode: facingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 },
                frameRate: { ideal: 30 }
            },
            audio: true
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            els.viewfinder.srcObject = stream;
            await els.viewfinder.play();

            // Check torch support
            torchTrack = stream.getVideoTracks()[0];
            const caps = torchTrack.getCapabilities ? torchTrack.getCapabilities() : {};
            if (!caps.torch) {
                els.flashBtn.style.display = 'none';
            } else {
                els.flashBtn.style.display = '';
            }

            return true;
        } catch (err) {
            console.warn('Camera access denied:', err);
            els.denied.classList.remove('hidden');
            return false;
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        torchTrack = null;
        torchOn = false;
    }

    // ─── Recording ──────────────────────────────────────────

    function startRecording(els) {
        if (!stream || isRecording) return;

        chunks = [];
        elapsed = 0;
        isRecording = true;

        // Choose mime type (webm preferred, mp4 fallback for Safari)
        let mimeType = 'video/webm;codecs=vp8,opus';
        if (!MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = 'video/webm';
        }
        if (!MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = 'video/mp4';
        }
        if (!MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = ''; // let browser choose
        }

        const options = { mimeType };
        if (mimeType) {
            options.videoBitsPerSecond = VIDEO_BITRATE;
            options.audioBitsPerSecond = AUDIO_BITRATE;
        }

        try {
            mediaRecorder = new MediaRecorder(stream, options);
        } catch (e) {
            mediaRecorder = new MediaRecorder(stream);
        }

        mediaRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) chunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            const type = mediaRecorder.mimeType || 'video/webm';
            recordedBlob = new Blob(chunks, { type });
            chunks = [];
            showPreview(els);
        };

        mediaRecorder.start(200); // collect data every 200ms

        // UI: recording state
        els.recordBtn.classList.remove('bg-red-500');
        els.recordBtn.classList.add('bg-red-600');
        els.recordBtn.style.borderRadius = '8px';
        els.recordBtn.style.width = '32px';
        els.recordBtn.style.height = '32px';
        els.recordRing.classList.add('border-red-500');
        els.timer.classList.remove('hidden');
        els.progress.style.width = '0%';

        // Timer
        timerInterval = setInterval(() => {
            elapsed++;
            const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const secs = String(elapsed % 60).padStart(2, '0');
            els.timerText.textContent = mins + ':' + secs;
            els.progress.style.width = Math.min((elapsed / MAX_DURATION) * 100, 100) + '%';

            if (elapsed >= MAX_DURATION) {
                stopRecording(els);
            }
        }, 1000);
    }

    function stopRecording(els) {
        if (!isRecording || !mediaRecorder) return;
        isRecording = false;

        clearInterval(timerInterval);
        timerInterval = null;

        if (mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }

        // Reset record button UI
        els.recordBtn.classList.remove('bg-red-600');
        els.recordBtn.classList.add('bg-red-500');
        els.recordBtn.style.borderRadius = '9999px';
        els.recordBtn.style.width = '';
        els.recordBtn.style.height = '';
        els.recordRing.classList.remove('border-red-500');
        els.timer.classList.add('hidden');
        els.progress.style.width = '0%';
    }

    // ─── Preview ────────────────────────────────────────────

    function showPreview(els) {
        if (!recordedBlob) return;

        const url = URL.createObjectURL(recordedBlob);
        els.previewVideo.src = url;
        els.previewVideo.play();
        els.preview.classList.remove('hidden');

        // Show duration
        const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
        const secs = String(elapsed % 60).padStart(2, '0');
        els.previewDur.textContent = mins + ':' + secs;

        // Stop live camera to save battery
        stopCamera();
    }

    function hidePreview(els) {
        els.preview.classList.add('hidden');
        els.previewVideo.pause();
        els.previewVideo.src = '';
        recordedBlob = null;
    }

    // ─── Torch ──────────────────────────────────────────────

    async function toggleTorch(els) {
        if (!torchTrack) return;
        torchOn = !torchOn;
        try {
            await torchTrack.applyConstraints({ advanced: [{ torch: torchOn }] });
            els.flashOff.classList.toggle('hidden', torchOn);
            els.flashOn.classList.toggle('hidden', !torchOn);
        } catch (e) {
            // torch not supported
        }
    }

    // ─── Public API ─────────────────────────────────────────

    /**
     * Open the camera interface
     * @param {Function} callback - Called with File object when user confirms video
     */
    function open(callback) {
        const els = getEls();
        if (!els.overlay) {
            console.error('snap-camera: component not found in DOM. Add <x-snap-camera /> to your template.');
            return;
        }

        onVideoReady = callback || null;
        facingMode = 'environment';
        torchOn = false;

        // Show overlay
        els.overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset UI
        els.denied.classList.add('hidden');
        els.preview.classList.add('hidden');
        els.flashOff.classList.remove('hidden');
        els.flashOn.classList.add('hidden');
        els.timer.classList.add('hidden');
        els.progress.style.width = '0%';

        // Start camera
        startCamera(els);

        // Bind events (once)
        bindEvents(els);
    }

    function close() {
        const els = getEls();
        stopRecording(els);
        stopCamera();
        hidePreview(els);

        els.overlay.classList.add('hidden');
        document.body.style.overflow = '';

        isRecording = false;
        recordedBlob = null;
        onVideoReady = null;
    }

    let eventsBound = false;
    function bindEvents(els) {
        if (eventsBound) return;
        eventsBound = true;

        // Close
        els.closeBtn.addEventListener('click', close);
        els.deniedClose.addEventListener('click', close);

        // Record: tap to start/stop
        els.recordBtn.addEventListener('click', () => {
            if (isRecording) {
                stopRecording(els);
            } else {
                startRecording(els);
            }
        });

        // Flip camera
        els.flipBtn.addEventListener('click', async () => {
            if (isRecording) return; // no flip during recording
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            // Mirror front camera
            els.viewfinder.style.transform = facingMode === 'user' ? 'scaleX(-1)' : '';
            await startCamera(els);
        });

        // Flash
        els.flashBtn.addEventListener('click', () => toggleTorch(els));

        // Gallery: hidden file input
        const galleryInput = document.createElement('input');
        galleryInput.type = 'file';
        galleryInput.accept = 'video/*';
        galleryInput.style.display = 'none';
        document.body.appendChild(galleryInput);

        els.galleryBtn.addEventListener('click', () => galleryInput.click());

        galleryInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validate duration client-side
            const tempVideo = document.createElement('video');
            tempVideo.preload = 'metadata';
            tempVideo.src = URL.createObjectURL(file);
            tempVideo.onloadedmetadata = () => {
                URL.revokeObjectURL(tempVideo.src);
                if (tempVideo.duration > MAX_DURATION) {
                    alert('La video ne doit pas depasser ' + MAX_DURATION + ' secondes. Duree: ' + Math.round(tempVideo.duration) + 's.');
                    galleryInput.value = '';
                    return;
                }
                // Deliver file directly
                if (onVideoReady) onVideoReady(file);
                close();
            };
            tempVideo.onerror = () => {
                // Can't read metadata, let server validate
                if (onVideoReady) onVideoReady(file);
                close();
            };
        });

        // Preview: retake
        els.retakeBtn.addEventListener('click', async () => {
            hidePreview(els);
            await startCamera(els);
        });

        // Preview: use video
        els.useBtn.addEventListener('click', () => {
            if (!recordedBlob) return;

            // Determine extension
            const mimeType = recordedBlob.type || 'video/webm';
            let ext = 'webm';
            if (mimeType.includes('mp4')) ext = 'mp4';
            if (mimeType.includes('mov')) ext = 'mov';

            const fileName = 'snap-video-' + Date.now() + '.' + ext;
            const file = new File([recordedBlob], fileName, { type: mimeType });

            if (onVideoReady) onVideoReady(file);
            close();
        });
    }

    // Expose globally
    window.snapCamera = { open, close };
})();
