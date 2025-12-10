@extends('layouts.app')

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-blue-900 mb-2">
                    <i class="fas fa-comments text-blue-600 mr-3"></i>
                    Messagerie
                </h1>
                <p class="text-lg text-blue-700">Communiquez avec vos prestataires ou clients en temps réel</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6 shadow-md">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6 shadow-md">
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-visible">
                <div class="px-6 py-4 border-b border-blue-200 bg-blue-50 relative z-10">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="text-center sm:text-left">
                            <h3 class="text-2xl font-bold text-blue-800 mb-1">Mes conversations</h3>
                            <p class="text-base text-blue-600">
                                {{ $conversations->count() }} conversation{{ $conversations->count() > 1 ? 's' : '' }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center sm:justify-end space-x-3">
                        </div>
                    </div>
                </div>
                
                <div class="conversations-list">
                    @if($conversations->count() > 0)
                        @foreach($conversations as $conversation)
                            <div class="conversation-item border-b border-blue-100 last:border-b-0 hover:bg-blue-50 transition-colors cursor-pointer relative" 
                                 data-user-id="{{ $conversation['user']->id }}"
                                 onclick="window.location.href='{{ Auth::user()->hasRole('client') ? route('client.messaging.show', $conversation['user']) : url('prestataire/messages/' . $conversation['user']->id) }}'">
                                <div class="flex items-start p-6 space-x-4">
                                    <div class="conversation-avatar flex-shrink-0 relative">
                                        @if($conversation['user']->profile_photo_url)
                                            <img src="{{ $conversation['user']->profile_photo_url }}" 
                                                 alt="{{ $conversation['user']->name }}" 
                                                 class="w-14 h-14 rounded-full object-cover border-3 border-blue-200 shadow-sm">
                                        @else
                                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg border-3 border-blue-200 shadow-sm">
                                                {{ strtoupper(substr($conversation['user']->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        @if($conversation['user']->is_online ?? false)
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                                        @endif
                                    </div>
                                
                                    <div class="conversation-content flex-1 min-w-0 pr-16">
                                        <div class="mb-1">
                                            <h4 class="font-bold text-blue-900 text-lg truncate">{{ $conversation['user']->name }}</h4>
                                        </div>
                                        
                                        @if($conversation['last_message'])
                                            <p class="text-blue-600 text-sm line-clamp-1 leading-relaxed">
                                                @if($conversation['last_message']->sender_id === Auth::id())
                                                    <i class="fas fa-reply text-blue-400 mr-1"></i>
                                                @endif
                                                {{ Str::limit($conversation['last_message']->content, 100) }}
                                            </p>
                                        @else
                                            <p class="text-blue-500 italic text-sm">
                                                <i class="fas fa-comment-dots mr-1"></i>
                                                Commencer la conversation
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <!-- Timestamp and unread counter - positioned at top-right -->
                                    <div class="absolute top-6 right-16 flex flex-col items-end space-y-1 z-0">
                                        @if($conversation['last_message'])
                                            <span class="text-xs text-blue-600 whitespace-nowrap font-medium">
                                                {{ $conversation['last_message']->created_at->diffForHumans() }}
                                            </span>
                                            @if($conversation['unread_count'] > 0)
                                                <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full min-w-[1.25rem] text-center shadow-md">
                                                    {{ $conversation['unread_count'] }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Three dots menu positioned on the right -->
                                <div class="absolute top-6 right-6 z-10" onclick="event.stopPropagation()">
                                    <div class="relative" x-data="{ open: false }">
                                            </div>
                                        </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state flex flex-col items-center justify-center p-12 text-center">
                            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-comments text-3xl text-blue-400"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-blue-900 mb-3">Aucune conversation</h3>
                            <p class="text-base text-blue-700 max-w-md leading-relaxed mb-8">
                                Vous n'avez pas encore de conversations. Commencez à échanger avec des 
                                {{ Auth::user()->role === 'client' ? 'prestataires' : 'clients' }} pour voir vos messages ici.
                            </p>
                            @if(Auth::user()->role === 'client')
                                <div class="empty-action">
                                    <a href="{{ route('prestataires.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <i class="fas fa-search mr-2"></i>
                                        Trouver des prestataires
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white border-blue-200">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-blue-900 mt-3">Supprimer la conversation</h3>
            <div class="mt-4 px-7 py-3">
                <p class="text-blue-700 mb-4">
                    Êtes-vous sûr de vouloir supprimer définitivement votre conversation avec <strong id="deleteUserName"></strong> ?
                </p>
                <p class="text-red-600 text-sm mb-6">
                    <i class="fas fa-warning mr-1"></i>
                    Cette action est irréversible. Tous les messages seront définitivement supprimés.
                </p>
                
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    
                    <div class="flex justify-center space-x-4 mt-6">
                        <button type="button" onclick="closeDeleteModal()" class="px-6 py-3 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors font-bold">
                            <i class="fas fa-times mr-2"></i>
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-trash mr-2"></i>
                            Supprimer définitivement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le système de messagerie
        if (typeof MessagingSystem !== 'undefined') {
            window.messagingSystem = new MessagingSystem();
        }
    });
    
    function openDeleteModal(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteForm').action = `/messaging/${userId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    
    // Fermer le modal avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endsection