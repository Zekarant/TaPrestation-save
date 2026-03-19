@extends('layouts.app')

@push('styles')
    <style>
        /* Messages List - Modern Mobile-First Design */
        .messages-page {
            min-height: 100vh;
            min-height: 100dvh;
            background: #f0f2f5;
            padding-bottom: 80px;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 16px;
            padding-top: max(env(safe-area-inset-top), 20px);
            margin-bottom: 16px;
        }

        .page-header h1 {
            color: white;
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin: 0;
        }

        /* Search box */
        .search-box {
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            margin: 16px 16px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            background: transparent;
        }

        .search-box input::placeholder {
            color: #8a8d91;
        }

        .search-box .icon {
            color: #8a8d91;
        }

        /* Conversations list */
        .conversations-list {
            background: white;
            border-radius: 16px;
            margin: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #f0f2f5;
            cursor: pointer;
            transition: background 0.2s;
        }

        .conversation-item:last-child {
            border-bottom: none;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item:active {
            background: #f0f2f5;
        }

        /* Avatar */
        .avatar {
            position: relative;
            flex-shrink: 0;
        }

        .avatar img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .avatar .status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .avatar .status-dot.online {
            background: #22c55e;
        }

        .avatar .status-dot.offline {
            background: #9ca3af;
        }

        /* Content */
        .conversation-content {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .conversation-name {
            font-weight: 600;
            font-size: 15px;
            color: #1c1e21;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-time {
            font-size: 12px;
            color: #65676b;
            flex-shrink: 0;
        }

        .conversation-preview {
            font-size: 14px;
            color: #65676b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-preview.unread {
            color: #1c1e21;
            font-weight: 600;
        }

        /* Unread badge */
        .unread-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 12px;
            font-weight: 700;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            flex-shrink: 0;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-state .icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1c1e21;
            margin: 0 0 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: #65676b;
            margin: 0 0 24px;
            max-width: 280px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .empty-state .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Swipe to delete */
        .swipe-actions {
            display: none;
        }

        /* Delete modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
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

        .modal-buttons {
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-btn-cancel {
            background: #f0f2f5;
            color: #1c1e21;
        }

        .modal-btn-danger {
            background: #ef4444;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="messages-page" x-data="{ 
        showDeleteModal: false, 
        deleteUserId: null, 
        deleteUserName: '',
        searchQuery: ''
    }">
        <!-- Header -->
        <div class="page-header">
            <h1>💬 Messages</h1>
            <p>{{ $conversations->count() }} conversation{{ $conversations->count() > 1 ? 's' : '' }}</p>
        </div>

        @if($conversations->count() > 0)
            <!-- Search -->
            <div class="search-box">
                <svg class="icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" placeholder="Rechercher une conversation..." x-model="searchQuery">
            </div>

            <!-- Conversations -->
            <div class="conversations-list">
                @foreach($conversations as $conversation)
                    <div class="conversation-item"
                        x-show="searchQuery === '' || '{{ strtolower($conversation['user']->name) }}'.includes(searchQuery.toLowerCase())"
                        onclick="window.location.href='{{ Auth::user()->hasRole('client') ? route('messaging.show', $conversation['user']) : route('prestataire.messages.show', $conversation['user']) }}'">

                        <div class="avatar">
                            @if($conversation['user']->profile_photo_url)
                                <img src="{{ $conversation['user']->profile_photo_url }}" alt="{{ $conversation['user']->name }}">
                            @else
                                <div class="avatar-placeholder">{{ strtoupper(substr($conversation['user']->name, 0, 1)) }}</div>
                            @endif
                            <div class="status-dot {{ ($conversation['user']->is_online ?? false) ? 'online' : 'offline' }}"></div>
                        </div>

                        <div class="conversation-content">
                            <div class="conversation-header">
                                <h4 class="conversation-name">{{ $conversation['user']->name }}</h4>
                                @if($conversation['last_message'])
                                    <span
                                        class="conversation-time">{{ $conversation['last_message']->created_at->diffForHumans(null, true) }}</span>
                                @endif
                            </div>

                            @if($conversation['last_message'])
                                <p class="conversation-preview {{ $conversation['unread_count'] > 0 ? 'unread' : '' }}">
                                    @if($conversation['last_message']->sender_id === Auth::id())
                                        <span style="color: #8a8d91;">Vous: </span>
                                    @endif
                                    {{ Str::limit($conversation['last_message']->content ?: '📷 Photo', 45) }}
                                </p>
                            @else
                                <p class="conversation-preview" style="font-style: italic;">Nouvelle conversation</p>
                            @endif
                        </div>

                        @if($conversation['unread_count'] > 0)
                            <div class="unread-badge">{{ $conversation['unread_count'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty state -->
            <div class="conversations-list">
                <div class="empty-state">
                    <div class="icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3>Aucune conversation</h3>
                    <p>Commencez à échanger avec {{ Auth::user()->role === 'client' ? 'des prestataires' : 'des clients' }} pour
                        voir vos messages ici.</p>

                    @if(Auth::user()->role === 'client')
                        <a href="{{ route('prestataires.index') }}" class="cta-btn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Trouver des prestataires
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Delete Modal -->
        <div x-show="showDeleteModal" class="modal-overlay" style="display: none;" x-transition
            @click.self="showDeleteModal = false">
            <div class="modal-content">
                <div class="modal-icon">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3>Supprimer la conversation</h3>
                <p>Supprimer définitivement la conversation avec <strong x-text="deleteUserName"></strong> ?</p>
                <div class="modal-buttons">
                    <button @click="showDeleteModal = false" class="modal-btn modal-btn-cancel">Annuler</button>
                    <form :action="'/messaging/' + deleteUserId" method="POST" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="modal-btn modal-btn-danger" style="width: 100%;">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection