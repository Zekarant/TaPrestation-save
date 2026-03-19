@extends('layouts.app')

@push('styles')
<style>
/* Chat App - Modern Mobile-First Design */
* { box-sizing: border-box; }

.chat-container {
    height: 100vh;
    height: 100dvh;
    display: flex;
    flex-direction: column;
    background: #f0f2f5;
    overflow: hidden;
}

/* Header */
.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 12px 16px;
    padding-top: max(env(safe-area-inset-top), 12px);
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 20;
}

.chat-header .back-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: background 0.2s;
}

.chat-header .back-btn:hover {
    background: rgba(255,255,255,0.3);
}

.chat-header .avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.4);
    object-fit: cover;
    flex-shrink: 0;
}

.chat-header .avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
    flex-shrink: 0;
}

.chat-header .user-info {
    flex: 1;
    min-width: 0;
}

.chat-header .user-name {
    color: white;
    font-weight: 700;
    font-size: 16px;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-header .user-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: rgba(255,255,255,0.85);
    margin-top: 2px;
}

.chat-header .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.chat-header .status-dot.online { background: #22c55e; }
.chat-header .status-dot.offline { background: rgba(255,255,255,0.5); animation: none; }

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.chat-header .menu-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* Messages Area */
.messages-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    -webkit-overflow-scrolling: touch;
}

.messages-area::-webkit-scrollbar { width: 4px; }
.messages-area::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 2px; }
.messages-area::-webkit-scrollbar-track { background: transparent; }

/* Date separator */
.date-separator {
    text-align: center;
    padding: 8px 0;
}

.date-separator span {
    background: rgba(0,0,0,0.1);
    color: #65676b;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 12px;
}

/* Message bubbles */
.message-row {
    display: flex;
    max-width: 85%;
}

.message-row.sent {
    align-self: flex-end;
}

.message-row.received {
    align-self: flex-start;
}

.message-bubble {
    padding: 10px 14px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
    word-break: break-word;
}

.message-row.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-row.received .message-bubble {
    background: white;
    color: #1c1e21;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-bubble .content {
    font-size: 15px;
    line-height: 1.4;
}

.message-bubble .meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    font-size: 11px;
}

.message-row.sent .message-bubble .meta {
    color: rgba(255,255,255,0.7);
}

.message-row.received .message-bubble .meta {
    color: #65676b;
}

.message-bubble .meta .check {
    font-size: 12px;
}

.message-bubble .meta .check.read {
    color: #4fc3f7;
}

/* Image in message */
.message-bubble img.msg-image {
    max-width: 250px;
    max-height: 300px;
    border-radius: 12px;
    margin-bottom: 6px;
    cursor: pointer;
}

/* Empty state */
.empty-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px;
}

.empty-chat .icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.empty-chat .icon svg {
    width: 40px;
    height: 40px;
    color: white;
}

.empty-chat h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1c1e21;
    margin: 0 0 8px;
}

.empty-chat p {
    font-size: 14px;
    color: #65676b;
    max-width: 280px;
}

/* Input area */
.input-area {
    background: white;
    padding: 12px 16px;
    padding-bottom: max(env(safe-area-inset-bottom), 12px);
    border-top: 1px solid #e4e6eb;
    flex-shrink: 0;
}

.input-area .image-preview-box {
    margin-bottom: 10px;
    position: relative;
    display: inline-block;
}

.input-area .image-preview-box img {
    max-height: 80px;
    border-radius: 12px;
    border: 2px solid #667eea;
}

.input-area .image-preview-box .remove-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.input-form {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.input-area .action-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    flex-shrink: 0;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.input-area .action-btn:hover {
    background: #e4e6eb;
}

.input-area .input-wrapper {
    flex: 1;
    position: relative;
}

.input-area textarea {
    width: 100%;
    border: none;
    background: #f0f2f5;
    border-radius: 22px;
    padding: 12px 16px;
    font-size: 15px;
    line-height: 1.4;
    resize: none;
    max-height: 120px;
    min-height: 44px;
    outline: none;
    transition: background 0.2s;
}

.input-area textarea:focus {
    background: #e4e6eb;
}

.input-area textarea::placeholder {
    color: #8a8d91;
}

.input-area .send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    border: none;
}

.input-area .send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
}

.input-area .send-btn:disabled {
    background: #e4e6eb;
    color: #8a8d91;
    box-shadow: none;
    transform: none;
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    background: white;
    border-radius: 18px;
    align-self: flex-start;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.typing-indicator .dot {
    width: 8px;
    height: 8px;
    background: #667eea;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite;
}

.typing-indicator .dot:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}

/* Dropdown menu */
.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 50;
    overflow: hidden;
}

.dropdown-menu button {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    color: #1c1e21;
    text-align: left;
    transition: background 0.2s;
    border: none;
    background: none;
    cursor: pointer;
}

.dropdown-menu button:hover {
    background: #f0f2f5;
}

.dropdown-menu .danger {
    color: #ef4444;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 100;
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    border-radius: 16px;
    max-width: 400px;
    width: 100%;
    padding: 24px;
    text-align: center;
}

.modal-content .modal-icon {
    width: 56px;
    height: 56px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.modal-content .modal-icon svg {
    width: 28px;
    height: 28px;
    color: #ef4444;
}

.modal-content h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1c1e21;
    margin: 0 0 8px;
}

.modal-content p {
    font-size: 14px;
    color: #65676b;
    margin: 0 0 20px;
}

.modal-content .buttons {
    display: flex;
    gap: 12px;
}

.modal-content .btn {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.modal-content .btn-cancel {
    background: #f0f2f5;
    color: #1c1e21;
}

.modal-content .btn-cancel:hover {
    background: #e4e6eb;
}

.modal-content .btn-danger {
    background: #ef4444;
    color: white;
}

.modal-content .btn-danger:hover {
    background: #dc2626;
}
</style>
@endpush

@section('content')
<div class="chat-container" x-data="chatApp()">
    <!-- Header -->
    <header class="chat-header">
        <a href="{{ Auth::user()->hasRole('client') ? route('messaging.index') : route('prestataire.messages.index') }}" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        
        {{-- Avatar et nom cliquables vers le profil --}}
        @php
            $profileUrl = null;
            if ($otherUser->prestataire) {
                $profileUrl = route('prestataires.show', $otherUser->prestataire);
            } elseif (Route::has('users.public.show')) {
                $profileUrl = route('users.public.show', $otherUser);
            }
        @endphp
        
        @if($profileUrl)
            <a href="{{ $profileUrl }}" class="flex items-center gap-3" style="text-decoration: none;">
        @endif
        
        @if($otherUser->profile_photo_url)
            <img src="{{ $otherUser->profile_photo_url }}" alt="{{ $otherUser->name }}" class="avatar" style="cursor: {{ $profileUrl ? 'pointer' : 'default' }};">
        @else
            <div class="avatar-placeholder" style="cursor: {{ $profileUrl ? 'pointer' : 'default' }};">{{ strtoupper(substr($otherUser->name, 0, 1)) }}</div>
        @endif
        
        <div class="user-info">
            <h1 class="user-name" style="cursor: {{ $profileUrl ? 'pointer' : 'default' }};">{{ $otherUser->name }}</h1>
            <div class="user-status">
                <span class="status-dot {{ ($otherUser->is_online ?? false) ? 'online' : 'offline' }}"></span>
                <span>{{ ($otherUser->is_online ?? false) ? 'En ligne' : ($otherUser->online_status ?? 'Hors ligne') }}</span>
            </div>
        </div>
        
        @if($profileUrl)
            </a>
        @endif
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="menu-btn" type="button">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
            </button>
            
            <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                {{-- Lien vers le profil --}}
                @if($profileUrl)
                    <a href="{{ $profileUrl }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" style="text-decoration: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Voir le profil
                    </a>
                @endif
                
                <button @click="showDeleteModal = true; open = false" class="danger" type="button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer la conversation
                </button>
            </div>
        </div>
    </header>
    
    <!-- Messages -->
    <div class="messages-area" id="messages-area">
        @if($messages->count() > 0)
            @php $lastDate = null; @endphp
            @foreach($messages as $message)
                @php 
                    $msgDate = $message->created_at->format('Y-m-d');
                    $showDate = $lastDate !== $msgDate;
                    $lastDate = $msgDate;
                @endphp
                
                @if($showDate)
                    <div class="date-separator">
                        <span>{{ $message->created_at->isToday() ? "Aujourd'hui" : ($message->created_at->isYesterday() ? 'Hier' : $message->created_at->format('d M Y')) }}</span>
                    </div>
                @endif
                
                <div class="message-row {{ $message->sender_id === Auth::id() ? 'sent' : 'received' }}">
                    <div class="message-bubble">
                        @if($message->type === 'image' && $message->image)
                            <a href="{{ $message->image_url }}" target="_blank">
                                <img src="{{ $message->thumbnail_url ?? $message->image_url }}" alt="Image" class="msg-image">
                            </a>
                        @endif
                        
                        @if($message->content)
                            <div class="content">{{ $message->content }}</div>
                        @endif
                        
                        <div class="meta">
                            <span>{{ $message->created_at->format('H:i') }}</span>
                            @if($message->sender_id === Auth::id())
                                @if($message->read_at)
                                    <i class="fas fa-check-double check read"></i>
                                @else
                                    <i class="fas fa-check check"></i>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-chat">
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3>Démarrez la conversation</h3>
                <p>Envoyez votre premier message à {{ $otherUser->name }}</p>
            </div>
        @endif
        
        <!-- Typing indicator -->
        <div x-show="isTyping" class="typing-indicator" style="display: none;">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    
    <!-- Input Area -->
    <div class="input-area">
        <!-- Image preview -->
        <div x-show="imagePreview" class="image-preview-box" style="display: none;">
            <img :src="imagePreview" alt="Preview">
            <button @click="removeImage()" class="remove-btn" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        @php
            $formAction = Auth::user()->hasRole('prestataire') 
                ? route('prestataire.messages.store', $otherUser->id)
                : route('messaging.store', $otherUser->id);
        @endphp
        
        <form id="message-form" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="input-form">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
            
            <label for="image-input" class="action-btn">
                <i class="fas fa-image"></i>
            </label>
            <input type="file" id="image-input" name="image" accept="image/*" class="hidden" @change="previewImage($event)">
            
            <div class="input-wrapper">
                <textarea 
                    x-ref="messageInput"
                    x-model="message"
                    name="content"
                    @input="autoResize($event)"
                    @keydown.enter="if(!$event.shiftKey) { $event.preventDefault(); $refs.messageInput.form.submit(); }"
                    placeholder="Écrivez un message..."
                    rows="1"
                ></textarea>
            </div>
            
            <button type="submit" class="send-btn" :disabled="message.trim().length === 0 && !imageFile">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
    
    <!-- Delete Modal -->
    <div x-show="showDeleteModal" class="modal-overlay" style="display: none;" x-transition @click.self="showDeleteModal = false">
        <div class="modal-content">
            <div class="modal-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3>Supprimer la conversation</h3>
            <p>Cette action supprimera tous les messages avec <strong>{{ $otherUser->name }}</strong>. Cette action est irréversible.</p>
            <div class="buttons">
                <button @click="showDeleteModal = false" class="btn btn-cancel" type="button">Annuler</button>
                <form method="POST" action="{{ Auth::user()->hasRole('client') ? route('client.messaging.delete', $otherUser) : url('prestataire/messages/' . $otherUser->id) }}" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatApp', () => ({
        message: '',
        imagePreview: null,
        imageFile: null,
        isTyping: false,
        showDeleteModal: false,
        isSending: false,
        
        get canSend() {
            return (this.message.trim().length > 0 || this.imageFile) && !this.isSending;
        },
        
        init() {
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
        
        scrollToBottom() {
            const area = document.getElementById('messages-area');
            if (area) area.scrollTop = area.scrollHeight;
        },
        
        autoResize(event) {
            const el = event.target;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },
        
        previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (file.size > 5 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 5 Mo');
                event.target.value = '';
                return;
            }
            
            this.imageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        
        removeImage() {
            this.imagePreview = null;
            this.imageFile = null;
            document.getElementById('image-input').value = '';
        },
        
        async sendMessage() {
            if (!this.canSend) return;
            
            this.isSending = true;
            const content = this.message.trim();
            const receiverId = '{{ $otherUser->id }}';
            
            // If has image, submit form normally
            if (this.imageFile) {
                document.getElementById('message-form').removeEventListener('submit', () => {});
                document.getElementById('message-form').submit();
                return;
            }
            
            @php
                $ajaxRoute = Auth::user()->hasRole('prestataire') 
                    ? route('prestataire.messages.send-ajax')
                    : route('messaging.send.ajax');
            @endphp
            
            try {
                const response = await fetch('{{ $ajaxRoute }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        receiver_id: receiverId,
                        content: content
                    })
                });
                
                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error:', response.status, errorText);
                    throw new Error(`Erreur serveur: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = '';
                    this.$refs.messageInput.style.height = 'auto';
                    this.addMessageToUI(data.message, data.formatted_time);
                    this.scrollToBottom();
                } else {
                    console.error('Send failed:', data);
                    throw new Error(data.message || 'Erreur lors de l\'envoi');
                }
            } catch (error) {
                console.error('Error:', error);
                // Fallback to form submission - create a hidden form to bypass Alpine's prevent
                const form = document.getElementById('message-form');
                const hiddenForm = document.createElement('form');
                hiddenForm.method = 'POST';
                hiddenForm.action = form.action;
                hiddenForm.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="receiver_id" value="${receiverId}">
                    <input type="hidden" name="content" value="${content}">
                `;
                document.body.appendChild(hiddenForm);
                hiddenForm.submit();
            } finally {
                this.isSending = false;
            }
        },
        
        addMessageToUI(message, time) {
            const area = document.getElementById('messages-area');
            const emptyChat = area.querySelector('.empty-chat');
            if (emptyChat) emptyChat.remove();
            
            const div = document.createElement('div');
            div.className = 'message-row sent';
            div.innerHTML = `
                <div class="message-bubble">
                    <div class="content">${this.escapeHtml(message.content)}</div>
                    <div class="meta">
                        <span>${time}</span>
                        <i class="fas fa-check check"></i>
                    </div>
                </div>
            `;
            
            const typing = area.querySelector('.typing-indicator');
            if (typing) {
                area.insertBefore(div, typing);
            } else {
                area.appendChild(div);
            }
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }));
});
</script>

{{-- Rappel pour activer les notifications (contexte message) --}}
@include('components.notification-context-alert', ['context' => 'message'])

{{-- Masquer les éléments flottants de notification qui cachent le bouton envoyer --}}
<style>
    #notification-floating-btn,
    [id^="notification-context-alert-"],
    .notification-reminder-banner {
        display: none !important;
    }
</style>
@endsection
