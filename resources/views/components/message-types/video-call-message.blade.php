@props(['message'])

@php
    $callData = json_decode($message->video_call_data, true) ?? [];
    $duration = $callData['duration'] ?? 0;
    $participants = $callData['participants'] ?? [];
    $callStatus = $callData['status'] ?? 'ended';
    $startTime = $callData['start_time'] ?? null;
    $endTime = $callData['end_time'] ?? null;
    
    // Formatage de la durée
    $durationFormatted = '';
    if ($duration > 0) {
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        
        if ($hours > 0) {
            $durationFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            $durationFormatted = sprintf('%02d:%02d', $minutes, $seconds);
        }
    }
    
    // Icône et couleur selon le statut
    $iconClass = 'fas fa-video';
    $statusColor = 'text-gray-600';
    $bgColor = 'bg-gray-100';
    $statusText = 'Appel terminé';
    
    switch ($callStatus) {
        case 'missed':
            $iconClass = 'fas fa-phone-slash';
            $statusColor = 'text-red-600';
            $bgColor = 'bg-red-100';
            $statusText = 'Appel manqué';
            break;
        case 'declined':
            $iconClass = 'fas fa-phone-slash';
            $statusColor = 'text-red-600';
            $bgColor = 'bg-red-100';
            $statusText = 'Appel refusé';
            break;
        case 'ended':
            $iconClass = 'fas fa-video';
            $statusColor = 'text-green-600';
            $bgColor = 'bg-green-100';
            $statusText = 'Appel terminé';
            break;
        case 'ongoing':
            $iconClass = 'fas fa-video animate-pulse';
            $statusColor = 'text-blue-600';
            $bgColor = 'bg-blue-100';
            $statusText = 'Appel en cours';
            break;
    }
@endphp

<div class="message-bubble">
    <div class="video-call-info {{ $bgColor }} rounded-lg p-4 border border-gray-200">
        <!-- En-tête de l'appel -->
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-12 h-12 {{ $bgColor }} rounded-full flex items-center justify-center">
                <i class="{{ $iconClass }} {{ $statusColor }} text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900">Visioconférence</h4>
                <p class="text-sm {{ $statusColor }}">{{ $statusText }}</p>
            </div>
            @if($callStatus === 'ongoing')
                <button type="button" 
                        onclick="joinVideoCall('{{ $callData['room_id'] ?? '' }}')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    <i class="fas fa-video mr-2"></i>Rejoindre
                </button>
            @endif
        </div>
        
        <!-- Détails de l'appel -->
        <div class="space-y-2">
            @if($duration > 0)
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-clock mr-2 w-4"></i>
                    <span>Durée: {{ $durationFormatted }}</span>
                </div>
            @endif
            
            @if(count($participants) > 0)
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-users mr-2 w-4"></i>
                    <span>{{ count($participants) }} participant(s)</span>
                </div>
                
                <!-- Liste des participants -->
                <div class="flex items-center space-x-2 mt-2">
                    @foreach(array_slice($participants, 0, 4) as $participant)
                        <div class="flex items-center space-x-1 bg-white bg-opacity-50 rounded-full px-2 py-1">
                            @if(isset($participant['avatar']))
                                <img src="{{ $participant['avatar'] }}" 
                                     alt="{{ $participant['name'] }}" 
                                     class="w-4 h-4 rounded-full object-cover">
                            @else
                                <div class="w-4 h-4 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-xs text-gray-600"></i>
                                </div>
                            @endif
                            <span class="text-xs text-gray-700">{{ $participant['name'] ?? 'Utilisateur' }}</span>
                        </div>
                    @endforeach
                    
                    @if(count($participants) > 4)
                        <div class="bg-white bg-opacity-50 rounded-full px-2 py-1">
                            <span class="text-xs text-gray-700">+{{ count($participants) - 4 }}</span>
                        </div>
                    @endif
                </div>
            @endif
            
            @if($startTime)
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-calendar mr-2 w-4"></i>
                    <span>{{ \Carbon\Carbon::parse($startTime)->format('d/m/Y à H:i') }}</span>
                </div>
            @endif
        </div>
        
        <!-- Actions selon le statut -->
        @if($callStatus === 'missed' && $message->sender_id !== auth()->id())
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex space-x-2">
                    <button type="button" 
                            onclick="startVideoCall({{ $message->sender_id }})"
                            class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        <i class="fas fa-video mr-2"></i>Rappeler
                    </button>
                    <button type="button" 
                            onclick="sendMessage('Désolé, j\'ai manqué votre appel. Pouvons-nous programmer un autre moment ?')"
                            class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        <i class="fas fa-comment mr-2"></i>Répondre
                    </button>
                </div>
            </div>
        @elseif($callStatus === 'ended' && $duration > 0)
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex space-x-2">
                    @if(isset($callData['recording_url']))
                        <button type="button" 
                                onclick="playRecording('{{ $callData['recording_url'] }}')"
                                class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                            <i class="fas fa-play mr-2"></i>Voir l'enregistrement
                        </button>
                    @endif
                    
                    <button type="button" 
                            onclick="startVideoCall({{ $message->sender_id !== auth()->id() ? $message->sender_id : ($message->recipient_id ?? 'null') }})"
                            class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        <i class="fas fa-video mr-2"></i>Nouvel appel
                    </button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Horodatage -->
    <div class="flex items-center justify-between mt-2">
        <span class="text-xs opacity-75">{{ $message->created_at->format('H:i') }}</span>
        
        @if($message->sender_id === auth()->id())
            <div class="flex items-center space-x-1">
                @if($message->read_at)
                    <i class="fas fa-check-double text-blue-400 text-xs" title="Lu"></i>
                @else
                    <i class="fas fa-check text-gray-400 text-xs" title="Envoyé"></i>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Modal de lecture d'enregistrement -->
<div id="recording-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50">
    <div class="flex items-center justify-center h-full p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-full overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Enregistrement de l'appel</h3>
                <button type="button" 
                        onclick="closeRecordingModal()"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <video id="recording-video" controls class="w-full h-auto max-h-96">
                    Votre navigateur ne supporte pas la lecture vidéo.
                </video>
                
                <!-- Informations sur l'enregistrement -->
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Durée: <span id="recording-duration">{{ $durationFormatted }}</span></span>
                        <span>Date: {{ $message->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-4 flex space-x-3">
                    <button type="button" 
                            onclick="downloadRecording()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Télécharger
                    </button>
                    <button type="button" 
                            onclick="shareRecording()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-share mr-2"></i>Partager
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Gestion des appels vidéo
function joinVideoCall(roomId) {
    if (!roomId) {
        alert('ID de salle invalide');
        return;
    }
    
    // Rediriger vers la salle de visioconférence
    window.open(`/video-call/${roomId}`, '_blank', 'width=1200,height=800');
}

function startVideoCall(recipientId) {
    if (!recipientId) {
        alert('Destinataire invalide');
        return;
    }
    
    // Démarrer un nouvel appel vidéo
    fetch('/video-call/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            recipient_id: recipientId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ouvrir la salle de visioconférence
            window.open(`/video-call/${data.room_id}`, '_blank', 'width=1200,height=800');
        } else {
            alert('Erreur lors du démarrage de l\'appel: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du démarrage de l\'appel');
    });
}

function sendMessage(content) {
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.value = content;
        messageInput.focus();
        
        // Auto-resize si la fonction existe
        if (typeof autoResizeTextarea === 'function') {
            autoResizeTextarea();
        }
    }
}

function playRecording(recordingUrl) {
    const modal = document.getElementById('recording-modal');
    const video = document.getElementById('recording-video');
    
    if (!recordingUrl) {
        alert('Enregistrement non disponible');
        return;
    }
    
    video.src = recordingUrl;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRecordingModal() {
    const modal = document.getElementById('recording-modal');
    const video = document.getElementById('recording-video');
    
    video.pause();
    video.src = '';
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function downloadRecording() {
    const video = document.getElementById('recording-video');
    if (video.src) {
        const a = document.createElement('a');
        a.href = video.src;
        a.download = `enregistrement-${new Date().toISOString().slice(0, 10)}.mp4`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

function shareRecording() {
    const video = document.getElementById('recording-video');
    if (video.src && navigator.share) {
        navigator.share({
            title: 'Enregistrement d\'appel vidéo',
            text: 'Voici l\'enregistrement de notre appel vidéo',
            url: video.src
        }).catch(error => {
            console.error('Erreur de partage:', error);
            // Fallback: copier le lien
            copyToClipboard(video.src);
        });
    } else {
        // Fallback: copier le lien
        copyToClipboard(video.src);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Lien copié dans le presse-papiers');
        }).catch(error => {
            console.error('Erreur de copie:', error);
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        alert('Lien copié dans le presse-papiers');
    } catch (error) {
        console.error('Erreur de copie:', error);
        alert('Impossible de copier le lien');
    }
    
    document.body.removeChild(textArea);
}

// Fermer le modal avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRecordingModal();
    }
});

// Fermer le modal en cliquant sur l'arrière-plan
document.getElementById('recording-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRecordingModal();
    }
});

// Gestion des notifications d'appel
if ('Notification' in window) {
    // Demander la permission pour les notifications
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

// Fonction pour afficher une notification d'appel entrant
function showCallNotification(callerName, roomId) {
    if (Notification.permission === 'granted') {
        const notification = new Notification(`Appel vidéo entrant de ${callerName}`, {
            body: 'Cliquez pour répondre',
            icon: '/images/video-call-icon.png',
            tag: 'video-call',
            requireInteraction: true,
            actions: [
                { action: 'answer', title: 'Répondre' },
                { action: 'decline', title: 'Refuser' }
            ]
        });
        
        notification.onclick = function() {
            window.focus();
            joinVideoCall(roomId);
            notification.close();
        };
        
        // Auto-fermer après 30 secondes
        setTimeout(() => {
            notification.close();
        }, 30000);
    }
}

// Écouter les événements Socket.IO pour les appels entrants
if (typeof io !== 'undefined') {
    const socket = io();
    
    socket.on('incoming-video-call', (data) => {
        showCallNotification(data.caller_name, data.room_id);
        
        // Afficher également une modal dans l'interface
        showIncomingCallModal(data);
    });
}

function showIncomingCallModal(callData) {
    // Créer une modal pour l'appel entrant
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4 text-center">
            <div class="mb-4">
                <i class="fas fa-video text-4xl text-green-600 mb-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Appel vidéo entrant</h3>
                <p class="text-gray-600">${callData.caller_name}</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="declineCall('${callData.room_id}'); this.closest('.fixed').remove();" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-phone-slash mr-2"></i>Refuser
                </button>
                <button onclick="joinVideoCall('${callData.room_id}'); this.closest('.fixed').remove();" 
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-video mr-2"></i>Répondre
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Auto-supprimer après 30 secondes
    setTimeout(() => {
        if (modal.parentNode) {
            modal.remove();
        }
    }, 30000);
}

function declineCall(roomId) {
    fetch('/video-call/decline', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            room_id: roomId
        })
    })
    .catch(error => {
        console.error('Erreur lors du refus de l\'appel:', error);
    });
}
</script>
@endpush

@push('styles')
<style>
.video-call-info {
    transition: all 0.2s ease;
}

.video-call-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

#recording-modal {
    backdrop-filter: blur(4px);
}

.participant-avatar {
    transition: transform 0.2s ease;
}

.participant-avatar:hover {
    transform: scale(1.1);
}

@media (max-width: 640px) {
    .video-call-info {
        padding: 0.75rem;
    }
    
    .video-call-info .flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .video-call-info button {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush