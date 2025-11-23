@props(['message'])

<div class="message-bubble">
    <div class="voice-player" data-src="/storage/{{ $message->file_path }}" data-duration="{{ $message->voice_duration }}">
        <button type="button" class="play-pause-btn w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-all duration-200">
            <i class="fas fa-play text-sm"></i>
        </button>
        
        <div class="flex-1 mx-3">
            <!-- Forme d'onde visualisée -->
            <div class="waveform-container relative h-8 flex items-center">
                <div class="waveform-progress absolute top-0 left-0 h-full bg-white bg-opacity-30 rounded transition-all duration-100" style="width: 0%"></div>
                <div class="waveform-bars flex items-center space-x-1 w-full">
                    <!-- Barres générées dynamiquement -->
                    @for($i = 0; $i < 20; $i++)
                        <div class="waveform-bar bg-white bg-opacity-60 rounded-full transition-all duration-100" 
                             style="height: {{ rand(20, 80) }}%; width: 2px;"></div>
                    @endfor
                </div>
            </div>
            
            <!-- Temps -->
            <div class="flex justify-between text-xs opacity-75 mt-1">
                <span class="current-time">0:00</span>
                <span class="total-time">{{ gmdate('i:s', $message->voice_duration) }}</span>
            </div>
        </div>
        
        <!-- Vitesse de lecture -->
        <div class="relative">
            <button type="button" class="speed-btn text-xs opacity-75 hover:opacity-100 px-2 py-1 rounded">
                1x
            </button>
            <div class="speed-menu hidden absolute bottom-full right-0 mb-1 bg-black bg-opacity-80 rounded px-2 py-1">
                <div class="flex flex-col space-y-1">
                    <button type="button" class="speed-option text-xs text-white hover:text-blue-300" data-speed="0.5">0.5x</button>
                    <button type="button" class="speed-option text-xs text-white hover:text-blue-300" data-speed="1">1x</button>
                    <button type="button" class="speed-option text-xs text-white hover:text-blue-300" data-speed="1.5">1.5x</button>
                    <button type="button" class="speed-option text-xs text-white hover:text-blue-300" data-speed="2">2x</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex items-center justify-between mt-2">
        <span class="text-xs opacity-75">{{ $message->created_at->format('H:i') }}</span>
        
        @if($message->sender_id === auth()->id())
            <div class="flex items-center space-x-1">
                @if($message->read_at)
                    <i class="fas fa-check-double text-blue-400" title="Lu"></i>
                @else
                    <i class="fas fa-check text-gray-400" title="Envoyé"></i>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Audio element (caché) -->
<audio class="voice-audio hidden" preload="metadata">
    <source src="/storage/{{ $message->file_path }}" type="audio/webm">
    <source src="/storage/{{ $message->file_path }}" type="audio/mp3">
    Votre navigateur ne supporte pas l'audio HTML5.
</audio>

@push('scripts')
<script>
class VoicePlayer {
    constructor(container) {
        this.container = container;
        this.audio = container.querySelector('.voice-audio');
        this.playPauseBtn = container.querySelector('.play-pause-btn');
        this.waveformProgress = container.querySelector('.waveform-progress');
        this.currentTimeSpan = container.querySelector('.current-time');
        this.speedBtn = container.querySelector('.speed-btn');
        this.speedMenu = container.querySelector('.speed-menu');
        this.waveformBars = container.querySelectorAll('.waveform-bar');
        
        this.isPlaying = false;
        this.currentSpeed = 1;
        this.duration = parseInt(container.dataset.duration) || 0;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupAudio();
    }
    
    bindEvents() {
        this.playPauseBtn.addEventListener('click', () => {
            this.togglePlayPause();
        });
        
        this.speedBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.speedMenu.classList.toggle('hidden');
        });
        
        this.speedMenu.querySelectorAll('.speed-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const speed = parseFloat(e.target.dataset.speed);
                this.setPlaybackSpeed(speed);
                this.speedMenu.classList.add('hidden');
            });
        });
        
        // Fermer le menu de vitesse en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (!this.speedBtn.contains(e.target)) {
                this.speedMenu.classList.add('hidden');
            }
        });
        
        // Événements audio
        this.audio.addEventListener('loadedmetadata', () => {
            this.duration = this.audio.duration;
        });
        
        this.audio.addEventListener('timeupdate', () => {
            this.updateProgress();
        });
        
        this.audio.addEventListener('ended', () => {
            this.onAudioEnded();
        });
        
        this.audio.addEventListener('error', (e) => {
            console.error('Erreur de lecture audio:', e);
            this.showError();
        });
        
        // Clic sur la forme d'onde pour naviguer
        this.container.querySelector('.waveform-container').addEventListener('click', (e) => {
            this.seekToPosition(e);
        });
    }
    
    setupAudio() {
        this.audio.src = this.container.dataset.src;
        this.audio.playbackRate = this.currentSpeed;
    }
    
    togglePlayPause() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }
    
    async play() {
        try {
            // Arrêter tous les autres lecteurs audio
            this.stopOtherPlayers();
            
            await this.audio.play();
            this.isPlaying = true;
            this.playPauseBtn.querySelector('i').className = 'fas fa-pause text-sm';
            this.animateWaveform();
        } catch (error) {
            console.error('Erreur de lecture:', error);
            this.showError();
        }
    }
    
    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.playPauseBtn.querySelector('i').className = 'fas fa-play text-sm';
        this.stopWaveformAnimation();
    }
    
    setPlaybackSpeed(speed) {
        this.currentSpeed = speed;
        this.audio.playbackRate = speed;
        this.speedBtn.textContent = speed + 'x';
    }
    
    updateProgress() {
        if (this.duration > 0) {
            const progress = (this.audio.currentTime / this.duration) * 100;
            this.waveformProgress.style.width = progress + '%';
            
            // Mettre à jour le temps actuel
            const currentMinutes = Math.floor(this.audio.currentTime / 60);
            const currentSeconds = Math.floor(this.audio.currentTime % 60);
            this.currentTimeSpan.textContent = `${currentMinutes}:${currentSeconds.toString().padStart(2, '0')}`;
        }
    }
    
    seekToPosition(e) {
        const rect = e.currentTarget.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = clickX / rect.width;
        const newTime = percentage * this.duration;
        
        this.audio.currentTime = newTime;
    }
    
    onAudioEnded() {
        this.isPlaying = false;
        this.playPauseBtn.querySelector('i').className = 'fas fa-play text-sm';
        this.waveformProgress.style.width = '0%';
        this.currentTimeSpan.textContent = '0:00';
        this.stopWaveformAnimation();
    }
    
    animateWaveform() {
        this.waveformBars.forEach((bar, index) => {
            const delay = index * 50;
            setTimeout(() => {
                if (this.isPlaying) {
                    bar.style.transform = 'scaleY(' + (0.3 + Math.random() * 0.7) + ')';
                    bar.style.opacity = '0.8';
                }
            }, delay);
        });
        
        if (this.isPlaying) {
            this.waveformAnimationId = setTimeout(() => {
                this.animateWaveform();
            }, 200);
        }
    }
    
    stopWaveformAnimation() {
        if (this.waveformAnimationId) {
            clearTimeout(this.waveformAnimationId);
        }
        
        this.waveformBars.forEach(bar => {
            bar.style.transform = 'scaleY(1)';
            bar.style.opacity = '0.6';
        });
    }
    
    stopOtherPlayers() {
        // Arrêter tous les autres lecteurs audio sur la page
        document.querySelectorAll('.voice-player').forEach(player => {
            if (player !== this.container) {
                const otherAudio = player.querySelector('.voice-audio');
                if (otherAudio && !otherAudio.paused) {
                    otherAudio.pause();
                    const otherBtn = player.querySelector('.play-pause-btn i');
                    if (otherBtn) {
                        otherBtn.className = 'fas fa-play text-sm';
                    }
                }
            }
        });
    }
    
    showError() {
        this.playPauseBtn.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 text-sm"></i>';
        this.playPauseBtn.title = 'Erreur de lecture';
        this.playPauseBtn.disabled = true;
    }
}

// Initialisation automatique des lecteurs vocaux
document.addEventListener('DOMContentLoaded', function() {
    initVoicePlayers();
});

// Fonction pour initialiser les nouveaux lecteurs (appelée après ajout de nouveaux messages)
function initVoicePlayers() {
    document.querySelectorAll('.voice-player:not([data-initialized])').forEach(player => {
        new VoicePlayer(player);
        player.setAttribute('data-initialized', 'true');
    });
}

// Exporter la fonction pour utilisation externe
window.initVoicePlayers = initVoicePlayers;
</script>
@endpush

@push('styles')
<style>
.waveform-bar {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.voice-player:hover .waveform-bar {
    opacity: 0.8 !important;
}

.waveform-container {
    cursor: pointer;
}

.waveform-container:hover .waveform-progress {
    background-color: rgba(255, 255, 255, 0.4);
}

.speed-menu {
    min-width: 60px;
}

.voice-player {
    min-width: 200px;
}

@media (max-width: 640px) {
    .voice-player {
        min-width: 180px;
    }
    
    .waveform-bars {
        gap: 1px;
    }
    
    .waveform-bar {
        width: 1.5px;
    }
}
</style>
@endpush