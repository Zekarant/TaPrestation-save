<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vidéos - TaPrestation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .feed-container {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .feed-container::-webkit-scrollbar { display: none; }

        .video-item {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            scroll-snap-align: start;
            position: relative;
            background: #000;
        }

        .video-player {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }

        .spinner {
            position: absolute;
            top: 50%; left: 50%;
            width: 44px; height: 44px;
            margin: -22px 0 0 -22px;
            border: 3px solid rgba(255,255,255,0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            z-index: 30;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .top-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            z-index: 100;
            background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);
        }

        .btn-icon {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: rgba(0,0,0,0.4);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
        }

        .actions-sidebar {
            position: absolute;
            right: 10px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            z-index: 50;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            padding: 0;
        }

        .action-btn .icon-circle {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn .icon-circle svg { width: 26px; height: 26px; }
        .action-btn.liked .icon-circle { background: #ff2d55; }
        .action-btn .count { font-size: 12px; font-weight: 600; }

        .video-info {
            position: absolute;
            left: 12px; right: 80px; bottom: 30px;
            z-index: 50;
            color: #fff;
        }
        .video-info .username { font-weight: 700; font-size: 16px; margin-bottom: 6px; }
        .video-info .title { font-size: 14px; margin-bottom: 4px; opacity: 0.95; }
        .video-info .description {
            font-size: 13px; opacity: 0.7;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .progress-track {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 3px;
            background: rgba(255,255,255,0.3);
            z-index: 60;
        }
        .progress-track .progress-fill {
            height: 100%;
            background: #fff;
            width: 0;
        }

        .play-pause-indicator {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 80px; height: 80px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            z-index: 40;
        }
        .play-pause-indicator.visible {
            animation: fadeOut 0.5s ease-out forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.2); }
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #fff;
            text-align: center;
            padding: 40px;
        }
        .empty-state svg { width: 64px; height: 64px; opacity: 0.5; margin-bottom: 20px; }
        .empty-state h2 { font-size: 20px; margin-bottom: 10px; }
        .empty-state p { opacity: 0.6; margin-bottom: 24px; }
        .empty-state a {
            padding: 12px 32px;
            background: #fff;
            color: #000;
            border-radius: 24px;
            text-decoration: none;
            font-weight: 600;
        }

        /* Modal commentaires */
        .comments-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 200;
            display: none;
            flex-direction: column;
        }
        .comments-modal.open { display: flex; }
        
        .comments-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .comments-header h3 { color: #fff; font-size: 18px; }
        .comments-close {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        .comment-item {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .comment-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .comment-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .comment-content { flex: 1; }
        .comment-author { color: #fff; font-weight: 600; font-size: 14px; margin-bottom: 4px; }
        .comment-text { color: rgba(255,255,255,0.8); font-size: 14px; line-height: 1.4; }
        .comment-time { color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 4px; }
        .no-comments { color: rgba(255,255,255,0.5); text-align: center; padding: 40px; }

        .comments-input {
            display: flex;
            gap: 12px;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.5);
        }
        .comments-input input {
            flex: 1;
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 24px;
            padding: 12px 20px;
            color: #fff;
            font-size: 15px;
            outline: none;
        }
        .comments-input input::placeholder { color: rgba(255,255,255,0.5); }
        .comments-input button {
            background: #8b5cf6;
            border: none;
            border-radius: 50%;
            width: 44px; height: 44px;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .comments-input button:disabled { opacity: 0.5; }
    </style>
</head>
<body>
    @if($videos->isEmpty())
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <h2>Aucune vidéo disponible</h2>
            <p>Revenez plus tard pour découvrir du contenu</p>
            <a href="{{ url('/') }}">Retour à l'accueil</a>
        </div>
    @else
        <div class="top-bar">
            <a href="{{ url('/') }}" class="btn-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <button type="button" class="btn-icon" id="muteBtn">
                <svg id="mutedIcon" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/>
                    <path d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
                <svg id="unmutedIcon" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                    <path d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/>
                </svg>
            </button>
        </div>

        <div class="feed-container" id="feedContainer">
            @foreach($videos as $index => $video)
            <div class="video-item" data-index="{{ $index }}" data-video-id="{{ $video->id }}">
                <div class="spinner"></div>
                
                <video class="video-player"
                       playsinline
                       webkit-playsinline
                       x-webkit-airplay="allow"
                       loop
                       muted
                       preload="none"
                       poster=""
                       data-src="{{ $video->direct_url }}">
                </video>

                <div class="play-pause-indicator">
                    <svg width="40" height="40" fill="#fff" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>

                <div class="progress-track"><div class="progress-fill"></div></div>

                <div class="actions-sidebar">
                    <a href="{{ route('prestataires.show', $video->prestataire) }}" class="action-btn">
                        <div class="icon-circle" style="overflow:hidden; background:rgba(255,255,255,0.3);">
                            @if($video->prestataire->user->photo ?? false)
                                <img src="{{ asset('storage/' . $video->prestataire->user->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <svg width="26" height="26" fill="#fff" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                        </div>
                    </a>

                    <button type="button" class="action-btn like-btn {{ ($video->is_liked_by_user ?? false) ? 'liked' : '' }}" data-video-id="{{ $video->id }}">
                        <div class="icon-circle">
                            <svg fill="{{ ($video->is_liked_by_user ?? false) ? '#fff' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <span class="count">{{ $video->likes_count ?? 0 }}</span>
                    </button>

                    <button type="button" class="action-btn comment-btn" data-video-id="{{ $video->id }}">
                        <div class="icon-circle">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <span class="count">{{ $video->comments_count ?? 0 }}</span>
                    </button>

                    <button type="button" class="action-btn share-btn" data-video-id="{{ $video->id }}">
                        <div class="icon-circle">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </div>
                        <span class="count">Partager</span>
                    </button>
                </div>

                <div class="video-info">
                    <div class="username">{{ $video->prestataire->company_name ?? $video->prestataire->user->name ?? 'Anonyme' }}</div>
                    @if($video->title)<div class="title">{{ $video->title }}</div>@endif
                    @if($video->description)<div class="description">{{ $video->description }}</div>@endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Modal commentaires -->
        <div class="comments-modal" id="commentsModal">
            <div class="comments-header">
                <h3>Commentaires <span id="commentsCount"></span></h3>
                <button class="comments-close" id="closeComments">&times;</button>
            </div>
            <div class="comments-list" id="commentsList">
                <div class="no-comments">Chargement...</div>
            </div>
            @auth
            <div class="comments-input">
                <input type="text" id="commentInput" placeholder="Ajouter un commentaire..." maxlength="500">
                <button type="button" id="sendComment">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
            @else
            <div class="comments-input">
                <a href="{{ route('login') }}" style="flex:1;text-align:center;color:#fff;opacity:0.7;text-decoration:none;">Connectez-vous pour commenter</a>
            </div>
            @endauth
        </div>
    @endif

    <script>
    (function() {
        'use strict';

        var container = document.getElementById('feedContainer');
        if (!container) return;

        var items = Array.from(document.querySelectorAll('.video-item'));
        var muteBtn = document.getElementById('muteBtn');
        var mutedIcon = document.getElementById('mutedIcon');
        var unmutedIcon = document.getElementById('unmutedIcon');
        var commentsModal = document.getElementById('commentsModal');
        var commentsList = document.getElementById('commentsList');
        var commentsCount = document.getElementById('commentsCount');
        var closeComments = document.getElementById('closeComments');
        var commentInput = document.getElementById('commentInput');
        var sendComment = document.getElementById('sendComment');
        
        var isMuted = true;
        var currentVideoId = null;
        var loadedVideos = {};
        var currentIndex = 0;
        var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        // DÉSACTIVER ET DÉSINSCRIRE TOUS LES SERVICE WORKERS (Cause fréquente de problèmes de cache vidéo sur iOS)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                    console.log('Service Worker unregistered');
                }
            });
        }

        // Charger et jouer la première vidéo
        preloadVideo(0);
        setTimeout(function() { playVideo(0); }, 100);

        function preloadVideo(idx) {
            if (loadedVideos[idx]) return;
            var item = items[idx];
            if (!item) return;
            
            var video = item.querySelector('.video-player');
            var spinner = item.querySelector('.spinner');
            
            if (video && video.dataset.src && !video.src) {
                loadedVideos[idx] = true;
                
                // Charger directement l'URL
                video.src = video.dataset.src;
                
                // Événements pour cacher le spinner
                var hideSpinner = function() {
                    if (spinner) spinner.style.display = 'none';
                };
                
                video.addEventListener('loadeddata', hideSpinner, { once: true });
                video.addEventListener('canplay', hideSpinner, { once: true });
                video.addEventListener('canplaythrough', hideSpinner, { once: true });
                video.addEventListener('error', function(e) {
                    hideSpinner();
                    console.error('Video error:', video.error);
                }, { once: true });
                
                video.load();
            }
        }

        function playVideo(idx) {
            var item = items[idx];
            if (!item) return;
            
            currentIndex = idx;
            var video = item.querySelector('.video-player');
            var spinner = item.querySelector('.spinner');
            var indicator = item.querySelector('.play-pause-indicator');
            
            if (!video) return;
            
            // Charger si pas encore fait
            if (!video.src && video.dataset.src) {
                loadedVideos[idx] = true;
                video.src = video.dataset.src;
                video.load();
            }
            
            video.muted = isMuted;
            
            // Tenter de jouer la vidéo
            var attemptPlay = function() {
                var playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        if (spinner) spinner.style.display = 'none';
                        if (indicator) indicator.style.opacity = '0';
                    }).catch(function(error) {
                        console.log('Play error:', error.name);
                        if (spinner) spinner.style.display = 'none';
                        // Montrer l'indicateur de play pour indiquer que l'utilisateur doit taper
                        if (indicator) {
                            indicator.innerHTML = '<svg width="40" height="40" fill="#fff" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
                            indicator.style.opacity = '1';
                        }
                    });
                }
            };
            
            // Si la vidéo n'est pas encore prête, attendre
            if (video.readyState >= 2) {
                attemptPlay();
            } else {
                video.addEventListener('canplay', attemptPlay, { once: true });
            }
            
            // Précharger la suivante avec un léger délai pour prioriser la lecture actuelle
            setTimeout(function() {
                preloadVideo(idx + 1);
            }, 1000);
        }

        function pauseVideo(idx) {
            var item = items[idx];
            if (!item) return;
            var video = item.querySelector('.video-player');
            if (video) video.pause();
        }

        // Intersection Observer
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                var idx = parseInt(entry.target.dataset.index);
                if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                    playVideo(idx);
                } else {
                    pauseVideo(idx);
                }
            });
        }, { root: container, threshold: [0.6] });

        items.forEach(function(item) { observer.observe(item); });

        // Tap play/pause
        items.forEach(function(item, idx) {
            var video = item.querySelector('.video-player');
            var indicator = item.querySelector('.play-pause-indicator');
            var progress = item.querySelector('.progress-fill');
            var spinner = item.querySelector('.spinner');

            if (video) {
                video.addEventListener('waiting', function() {
                    if (spinner) spinner.style.display = 'block';
                });
                video.addEventListener('playing', function() {
                    if (spinner) spinner.style.display = 'none';
                    if (indicator) indicator.style.opacity = '0';
                });
                video.addEventListener('timeupdate', function() {
                    if (progress && video.duration) {
                        progress.style.width = (video.currentTime / video.duration * 100) + '%';
                    }
                });
                // iOS: gérer stalled et suspend
                video.addEventListener('stalled', function() {
                    console.log('Video stalled, attempting reload...');
                });
                video.addEventListener('suspend', function() {
                    // Normal sur iOS quand la vidéo est mise en buffer
                });
                // Gestion des erreurs
                video.addEventListener('error', function(e) {
                    console.error('Video error:', video.error);
                    if (spinner) spinner.style.display = 'none';
                    // Afficher une icône d'erreur ou un message
                    if (indicator) {
                        indicator.innerHTML = '<svg width="40" height="40" fill="#ff4757" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
                        indicator.style.opacity = '1';
                        indicator.classList.add('visible');
                    }
                });
            }

            item.addEventListener('click', function(e) {
                if (e.target.closest('.action-btn') || e.target.closest('.btn-icon') || e.target.closest('a')) return;
                if (!video) return;

                if (video.paused) {
                    video.play().catch(function(err) {
                        console.log('Play on tap failed:', err);
                    });
                    if (indicator) indicator.innerHTML = '<svg width="40" height="40" fill="#fff" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
                } else {
                    video.pause();
                    if (indicator) indicator.innerHTML = '<svg width="40" height="40" fill="#fff" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
                }
                
                if (indicator) {
                    indicator.classList.remove('visible');
                    void indicator.offsetWidth;
                    indicator.classList.add('visible');
                }
            });
        });

        // Mute toggle
        if (muteBtn) {
            muteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                isMuted = !isMuted;
                items.forEach(function(item) {
                    var v = item.querySelector('.video-player');
                    if (v) v.muted = isMuted;
                });
                mutedIcon.style.display = isMuted ? 'block' : 'none';
                unmutedIcon.style.display = isMuted ? 'none' : 'block';
            });
        }

        // Like
        document.querySelectorAll('.like-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var videoId = this.dataset.videoId;
                var countEl = this.querySelector('.count');
                var isLiked = this.classList.contains('liked');
                var self = this;

                fetch('/videos/' + videoId + '/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ liked: !isLiked })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.is_liked) {
                        self.classList.add('liked');
                    } else {
                        self.classList.remove('liked');
                    }
                    if (countEl && data.likes_count !== undefined) {
                        countEl.textContent = data.likes_count;
                    }
                })
                .catch(function() {});
            });
        });

        // Comments
        document.querySelectorAll('.comment-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                currentVideoId = this.dataset.videoId;
                commentsModal.classList.add('open');
                loadComments(currentVideoId);
            });
        });

        if (closeComments) {
            closeComments.addEventListener('click', function() {
                commentsModal.classList.remove('open');
                currentVideoId = null;
            });
        }

        function loadComments(videoId) {
            commentsList.innerHTML = '<div class="no-comments">Chargement...</div>';
            
            fetch('/videos/' + videoId + '/comments', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var comments = data.comments || data || [];
                commentsCount.textContent = '(' + comments.length + ')';
                
                if (comments.length === 0) {
                    commentsList.innerHTML = '<div class="no-comments">Aucun commentaire. Soyez le premier !</div>';
                    return;
                }
                
                var html = '';
                comments.forEach(function(c) {
                    html += '<div class="comment-item">';
                    html += '<div class="comment-avatar">';
                    if (c.user && c.user.photo) {
                        html += '<img src="/storage/' + c.user.photo + '">';
                    } else {
                        html += '<svg width="20" height="20" fill="#fff" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
                    }
                    html += '</div>';
                    html += '<div class="comment-content">';
                    html += '<div class="comment-author">' + (c.user ? c.user.name : 'Anonyme') + '</div>';
                    html += '<div class="comment-text">' + (c.content || c.comment || '') + '</div>';
                    html += '</div></div>';
                });
                commentsList.innerHTML = html;
            })
            .catch(function() {
                commentsList.innerHTML = '<div class="no-comments">Erreur de chargement</div>';
            });
        }

        if (sendComment) {
            sendComment.addEventListener('click', function() {
                if (!currentVideoId || !commentInput.value.trim()) return;
                
                var text = commentInput.value.trim();
                sendComment.disabled = true;
                
                fetch('/videos/' + currentVideoId + '/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: text })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    commentInput.value = '';
                    loadComments(currentVideoId);
                    
                    // Mettre à jour le compteur
                    var btn = document.querySelector('.comment-btn[data-video-id="' + currentVideoId + '"]');
                    if (btn && data.comments_count !== undefined) {
                        btn.querySelector('.count').textContent = data.comments_count;
                    }
                })
                .catch(function() {
                    alert('Erreur lors de l\'envoi');
                })
                .finally(function() {
                    sendComment.disabled = false;
                });
            });
            
            if (commentInput) {
                commentInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') sendComment.click();
                });
            }
        }

        // Share
        document.querySelectorAll('.share-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var url = window.location.origin + '/videos/' + this.dataset.videoId;
                if (navigator.share) {
                    navigator.share({ title: 'Vidéo TaPrestation', url: url });
                } else if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() { alert('Lien copié !'); });
                } else {
                    prompt('Copier ce lien:', url);
                }
            });
        });

    })();
    </script>
</body>
</html>
