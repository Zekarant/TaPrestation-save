@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<!-- Interface TikTok-like plein écran -->
<div class="fixed inset-0 bg-black overflow-hidden" id="video-container">
    <!-- Indication de scroll (animée) -->
    <div id="scroll-indicator" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-50 flex flex-col items-center text-white animate-bounce opacity-70 transition-opacity duration-300">
        <span class="text-sm mb-2">Swipe pour voir plus</span>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
    
    @if($videos->isEmpty())
        <div class="flex items-center justify-center h-full text-white">
            <div class="text-center">
                <div class="mx-auto w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium mb-2">{{ __('Aucune vidéo') }}</h3>
                <p class="text-gray-400 mb-6">{{ __('Aucune vidéo disponible pour le moment.') }}</p>
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('Retour à l\'accueil') }}
                </a>
            </div>
        </div>
    @else
        <!-- Conteneur des vidéos avec scroll vertical -->
        <div class="h-full overflow-y-scroll snap-y snap-mandatory" id="videos-scroll">
            @foreach($videos as $index => $video)
                <div class="relative h-screen w-full snap-start flex items-center justify-center video-slide" data-index="{{ $index }}">
                    <!-- Vidéo plein écran -->
                    <video class="absolute inset-0 w-full h-full object-cover video-player"
                           src="{{ $video->video_url }}"
                           muted
                           loop
                           playsinline
                           preload="metadata"
                           data-video-id="{{ $video->id }}"
                           {{ $index === 0 ? 'autoplay' : '' }}>
                        {{ __('Votre navigateur ne supporte pas la lecture vidéo.') }}
                    </video>
                    
                    <!-- Overlay avec contrôles -->
                    <div class="absolute inset-0 pointer-events-none">
                        <!-- Bouton play/pause central -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button class="play-pause-btn pointer-events-auto opacity-0 transition-opacity duration-300 bg-black bg-opacity-50 rounded-full p-4" data-video-id="{{ $video->id }}">
                                <svg class="w-8 h-8 text-white play-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                </svg>
                                <svg class="w-8 h-8 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 002 0v4a1 1 0 10-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Interface utilisateur superposée -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                            <div class="flex items-end justify-between">
                                <!-- Informations du prestataire -->
                                <div class="flex-1 text-white mr-4">
                                    <h3 class="font-bold text-lg mb-1">{{ $video->prestataire->user->name }}</h3>
                                    <p class="text-sm opacity-90 mb-2 line-clamp-2">{{ $video->description }}</p>
                                    <div class="flex items-center gap-4 text-sm">
                                        <span class="flex items-center gap-1 video-views-count">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            {{ $video->views_count ?? 0 }}
                                        </span>
                                        <span class="flex items-center gap-1 video-likes-count">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <span class="likes-count">{{ $video->likes_count ?? 0 }}</span>
                                        </span>
                                        <span class="flex items-center gap-1 video-comments-count">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                            <span class="comments-count">{{ $video->comments_count ?? 0 }}</span>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Actions verticales -->
                                <div class="flex flex-col items-center gap-4 pointer-events-auto">
                                    @auth
                                        <!-- Bouton son -->
                                        <button class="sound-toggle bg-black bg-opacity-50 rounded-full p-3 text-white hover:bg-opacity-70 transition-all" data-video-id="{{ $video->id }}">
                                            <svg class="w-6 h-6 sound-off" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path>
                                            </svg>
                                            <svg class="w-6 h-6 sound-on hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Bouton like -->
                                        <button class="like-btn bg-black bg-opacity-50 rounded-full p-3 text-white hover:bg-opacity-70 transition-all {{ isset($video->is_liked_by_user) && $video->is_liked_by_user ? 'liked' : '' }}" data-video-id="{{ $video->id }}" data-is-liked="{{ isset($video->is_liked_by_user) && $video->is_liked_by_user ? 'true' : 'false' }}">
                                            <svg class="w-6 h-6 like-icon" fill="{{ isset($video->is_liked_by_user) && $video->is_liked_by_user ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ isset($video->is_liked_by_user) && $video->is_liked_by_user ? '0' : '2' }}" d="M4.318 6.318a4 4 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Bouton commentaire -->
                                        <button class="comment-btn bg-black bg-opacity-50 rounded-full p-3 text-white hover:bg-opacity-70 transition-all" data-video-id="{{ $video->id }}">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Bouton profil -->
                                        <a href="{{ route('prestataires.show', $video->prestataire) }}" class="profile-btn bg-black bg-opacity-50 rounded-full p-3 text-white hover:bg-opacity-70 transition-all">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        <!-- Bouton suivre -->
                                        @if(auth()->user()->client)
                                            <button class="follow-btn bg-blue-600 rounded-full p-3 text-white hover:bg-blue-700 transition-all {{ auth()->user()->client->isFollowing($video->prestataire) ? 'following' : '' }}" 
                                                    data-prestataire-id="{{ $video->prestataire->id }}"
                                                    data-is-following="{{ auth()->user()->client->isFollowing($video->prestataire) ? 'true' : 'false' }}">
                                                @if(auth()->user()->client->isFollowing($video->prestataire))
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                @endif
                                            </button>
                                        @elseif(auth()->user()->prestataire)
                                            {{-- Hide follow button for prestataires --}}
                                        @else
                                            <a href="{{ route('login') }}" class="follow-btn bg-blue-600 rounded-full p-3 text-white hover:bg-blue-700 transition-all">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    @else
                                        <!-- Show a login prompt for non-connected users -->
                                        <a href="{{ route('login') }}" class="bg-blue-600 rounded-full p-3 text-white hover:bg-blue-700 transition-all">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        
                        <!-- Indicateur de progression -->
                        <div class="absolute top-4 left-4 right-4 pointer-events-none">
                            <div class="flex justify-between items-center text-white text-sm">
                                <span>{{ $index + 1 }} / {{ $videos->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const videosContainer = document.getElementById('videos-scroll');
    const videoSlides = document.querySelectorAll('.video-slide');
    const videoPlayers = document.querySelectorAll('.video-player');
    let currentVideoIndex = 0;
    let isScrolling = false;

    // Gestion de l'indication de scroll
    const scrollIndicator = document.getElementById('scroll-indicator');
    let indicatorHidden = false;
    
    function hideScrollIndicator() {
        if (!indicatorHidden && scrollIndicator) {
            scrollIndicator.style.opacity = '0';
            indicatorHidden = true;
            setTimeout(() => {
                scrollIndicator.style.display = 'none';
            }, 300);
        }
    }
    
    // Masquer après le premier scroll
    if (videosContainer) {
        videosContainer.addEventListener('scroll', hideScrollIndicator, { once: true });
    }
    
    // Masquer automatiquement après 5 secondes
    setTimeout(hideScrollIndicator, 5000);

    // Video view tracking
    const viewTracking = new Map();
    
    function trackVideoView(videoElement) {
        const videoId = videoElement.dataset.videoId;
        if (!viewTracking.has(videoId)) {
            // Start tracking
            const startTime = Date.now();
            const trackingId = setInterval(() => {
                const currentTime = Date.now();
                const watchedDuration = (currentTime - startTime) / 1000; // in seconds
                
                // Get video duration (if available)
                const videoDuration = videoElement.duration || 0;
                
                // Check if we've watched for at least 10 seconds or 30% of video duration
                const minDuration = Math.min(10, videoDuration * 0.3);
                if (watchedDuration >= minDuration && !videoElement.dataset.viewCounted) {
                    // Mark as counted to avoid multiple requests
                    videoElement.dataset.viewCounted = 'true';
                    
                    // Send AJAX request to increment view count
                    incrementVideoViewCount(videoId, Math.floor(watchedDuration), Math.floor(videoDuration));
                    
                    // Stop tracking
                    stopTrackingVideoView(videoElement);
                }
            }, 1000); // Check every second
            
            viewTracking.set(videoId, {
                trackingId: trackingId,
                startTime: startTime
            });
        }
    }
    
    function stopTrackingVideoView(videoElement) {
        const videoId = videoElement.dataset.videoId;
        if (viewTracking.has(videoId)) {
            const trackingInfo = viewTracking.get(videoId);
            clearInterval(trackingInfo.trackingId);
            viewTracking.delete(videoId);
        }
    }
    
    function incrementVideoViewCount(videoId, watchedDuration, videoDuration) {
        fetch(`/videos/${videoId}/increment-views`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                watched_duration: watchedDuration,
                video_duration: videoDuration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the view count display
                const videoSlide = document.querySelector(`.video-slide[data-index="${currentVideoIndex}"]`);
                if (videoSlide) {
                    // Select the specific view count element using the new class
                    const viewCountElement = videoSlide.querySelector('.video-views-count span');
                    if (viewCountElement) {
                        viewCountElement.textContent = data.views_count;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error incrementing view count:', error);
        });
    }

    // Configuration de l'Intersection Observer pour détecter la vidéo visible
    const observerOptions = {
        root: videosContainer,
        rootMargin: '0px',
        threshold: 0.5
    };

    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target.querySelector('.video-player');
            const playPauseBtn = entry.target.querySelector('.play-pause-btn');
            
            if (entry.isIntersecting) {
                // Vidéo visible - la lancer
                video.play().catch(e => console.log('Autoplay prevented:', e));
                currentVideoIndex = parseInt(entry.target.dataset.index);
                
                // Mettre en pause toutes les autres vidéos
                videoPlayers.forEach((otherVideo, index) => {
                    if (index !== currentVideoIndex) {
                        otherVideo.pause();
                    }
                });
                
                // Track view when video is visible and playing
                trackVideoView(video);
            } else {
                // Vidéo non visible - la mettre en pause
                video.pause();
                // Stop tracking this video
                stopTrackingVideoView(video);
            }
        });
    }, observerOptions);

    // Observer toutes les slides vidéo
    videoSlides.forEach(slide => {
        videoObserver.observe(slide);
    });

    // Gestion des contrôles vidéo
    videoPlayers.forEach((video, index) => {
        const slide = video.closest('.video-slide');
        const playPauseBtn = slide.querySelector('.play-pause-btn');
        const playIcon = playPauseBtn ? playPauseBtn.querySelector('.play-icon') : null;
        const pauseIcon = playPauseBtn ? playPauseBtn.querySelector('.pause-icon') : null;
        const soundToggle = slide.querySelector('.sound-toggle');
        const soundOffIcon = soundToggle ? soundToggle.querySelector('.sound-off') : null;
        const soundOnIcon = soundToggle ? soundToggle.querySelector('.sound-on') : null;

        // Bouton play/pause
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }

        // Afficher/masquer le bouton play/pause
        if (playPauseBtn) {
            video.addEventListener('click', () => {
                playPauseBtn.style.opacity = '1';
                setTimeout(() => {
                    if (!video.paused) {
                        playPauseBtn.style.opacity = '0';
                    }
                }, 1000);
            });
        }

        // Mettre à jour l'icône play/pause
        if (playIcon && pauseIcon) {
            video.addEventListener('play', () => {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
                playPauseBtn.style.opacity = '0';
            });

            video.addEventListener('pause', () => {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
                playPauseBtn.style.opacity = '1';
            });
        }

        // Contrôle du son
        if (soundToggle && soundOffIcon && soundOnIcon) {
            soundToggle.addEventListener('click', () => {
                video.muted = !video.muted;
                if (video.muted) {
                    soundOffIcon.classList.remove('hidden');
                    soundOnIcon.classList.add('hidden');
                } else {
                    soundOffIcon.classList.add('hidden');
                    soundOnIcon.classList.remove('hidden');
                }
            });
        }

        // Bouton like avec comptage dynamique
        const likeBtn = slide.querySelector('.like-btn');
        const likesCountElement = slide.querySelector('.video-likes-count .likes-count');
        const likeIcon = likeBtn ? likeBtn.querySelector('.like-icon') : null;
        
        if (likeBtn && likesCountElement && likeIcon) {
            let isLiked = likeBtn.dataset.isLiked === 'true';
            let likesCount = parseInt(likesCountElement.textContent) || 0;
            
            // Initialiser l'apparence du bouton like
            if (isLiked) {
                likeBtn.classList.add('liked');
                likeIcon.setAttribute('fill', 'currentColor');
                likeIcon.setAttribute('stroke-width', '0');
            } else {
                likeIcon.setAttribute('fill', 'none');
                likeIcon.setAttribute('stroke-width', '2');
            }
            
            function handleLike() {
                // Check if user is authenticated
                const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                if (!isAuthenticated) {
                    // User is not authenticated, redirect to login
                    window.location.href = '/login';
                    return;
                }
                
                const videoId = video.dataset.videoId;
                
                // Animation de like
                likeBtn.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    likeBtn.style.transform = 'scale(1)';
                }, 200);
                
                // Toggle like state
                isLiked = !isLiked;
                
                // Appel AJAX pour sauvegarder le like
                fetch(`/videos/${videoId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ liked: isLiked })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour l'état avec les données du serveur
                        isLiked = data.is_liked;
                        likesCount = data.likes_count;
                        
                        // Mettre à jour l'apparence
                        if (isLiked) {
                            likeBtn.classList.add('liked');
                            likeIcon.setAttribute('fill', '#ef4444'); // red color
                            likeIcon.setAttribute('stroke', '#ef4444'); // red color
                            likeIcon.setAttribute('stroke-width', '0');
                            
                            // Animation de cœur qui apparaît
                            showHeartAnimation(slide);
                        } else {
                            likeBtn.classList.remove('liked');
                            likeIcon.setAttribute('fill', 'none');
                            likeIcon.setAttribute('stroke', 'currentColor');
                            likeIcon.setAttribute('stroke-width', '2');
                        }
                        
                        // Mettre à jour le compteur
                        likesCountElement.textContent = likesCount;
                        
                        // Mettre à jour l'attribut data
                        likeBtn.dataset.isLiked = isLiked;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du like:', error);
                    // Revenir à l'état précédent en cas d'erreur
                    isLiked = !isLiked;
                    // Revert UI changes
                    if (isLiked) {
                        likeBtn.classList.add('liked');
                        likeIcon.setAttribute('fill', '#ef4444');
                        likeIcon.setAttribute('stroke', '#ef4444');
                        likeIcon.setAttribute('stroke-width', '0');
                    } else {
                        likeBtn.classList.remove('liked');
                        likeIcon.setAttribute('fill', 'none');
                        likeIcon.setAttribute('stroke', 'currentColor');
                        likeIcon.setAttribute('stroke-width', '2');
                    }
                });
            }
            
            likeBtn.addEventListener('click', handleLike);
        }
        
        // Bouton commentaire
        const commentBtn = slide.querySelector('.comment-btn');
        const commentsCountElement = slide.querySelector('.video-comments-count .comments-count');
        if (commentBtn && commentsCountElement) {
            // Add a check to prevent duplicate event listeners
            if (!commentBtn.hasAttribute('data-event-listener-added')) {
                commentBtn.addEventListener('click', () => {
                    // Check if user is authenticated
                    const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                    if (!isAuthenticated) {
                        // User is not authenticated, redirect to login
                        window.location.href = '/login';
                        return;
                    }
                    showCommentsOverlay(video.dataset.videoId, slide);
                });
                // Mark that we've added the event listener
                commentBtn.setAttribute('data-event-listener-added', 'true');
            }
            
            // Update the comments count display when comments are added
            // This would be called from the showCommentsOverlay function or similar
            window.updateCommentsCount = function(videoId, newCount) {
                const videoSlide = document.querySelector(`.video-slide[data-video-id="${videoId}"]`);
                if (videoSlide) {
                    const commentsCountElement = videoSlide.querySelector('.video-comments-count .comments-count');
                    if (commentsCountElement) {
                        commentsCountElement.textContent = newCount;
                    }
                }
            };
        }
        
        // Double-clic sur la vidéo pour liker
        let clickCount = 0;
        video.addEventListener('click', (e) => {
            clickCount++;
            if (clickCount === 1) {
                setTimeout(() => {
                    if (clickCount === 1) {
                        // Simple clic - afficher/masquer bouton play/pause
                        playPauseBtn.style.opacity = '1';
                        setTimeout(() => {
                            if (!video.paused) {
                                playPauseBtn.style.opacity = '0';
                            }
                        }, 1000);
                    } else if (clickCount === 2) {
                        // Double-clic - liker la vidéo
                        handleLike();
                    }
                    clickCount = 0;
                }, 300);
            }
        });

        // Bouton suivre avec confirmation visuelle
        const followBtn = slide.querySelector('.follow-btn');
        if (followBtn) {
            let isFollowing = followBtn.dataset.isFollowing === 'true';
            
            // Only add event listener if follow button is not a link and is not disabled
            if (followBtn.tagName !== 'A' && !followBtn.hasAttribute('disabled')) {
                followBtn.addEventListener('click', () => {
                    const prestataireId = followBtn.dataset.prestataireId;
                    
                    // Animation de suivi
                    followBtn.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        followBtn.style.transform = 'scale(1)';
                    }, 200);
                    
                    // Toggle follow state
                    isFollowing = !isFollowing;
                    
                    if (isFollowing) {
                        followBtn.classList.add('following');
                        followBtn.innerHTML = `
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        `;
                        showNotification('Vous suivez maintenant ce prestataire.', 'success');
                    } else {
                        followBtn.classList.remove('following');
                        followBtn.innerHTML = `
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        `;
                        showNotification('Vous ne suivez plus ce prestataire.', 'info');
                    }
                    
                    // Appel AJAX pour sauvegarder le suivi
                    fetch(`/prestataires/${prestataireId}/follow`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ following: isFollowing })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the button state based on server response
                            isFollowing = data.is_following;
                            if (isFollowing) {
                                followBtn.classList.add('following');
                                followBtn.innerHTML = `
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                `;
                            } else {
                                followBtn.classList.remove('following');
                                followBtn.innerHTML = `
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                `;
                            }
                            followBtn.dataset.isFollowing = isFollowing;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du suivi:', error);
                        // Revenir à l'état précédent en cas d'erreur
                        isFollowing = !isFollowing;
                        if (isFollowing) {
                            followBtn.classList.remove('following');
                            followBtn.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            `;
                        } else {
                            followBtn.classList.add('following');
                            followBtn.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            `;
                        }
                        // Update data attribute
                        followBtn.dataset.isFollowing = isFollowing;
                    });
                });
            }
        }
    });

    // Navigation par clavier
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp' && currentVideoIndex > 0) {
            e.preventDefault();
            scrollToVideo(currentVideoIndex - 1);
        } else if (e.key === 'ArrowDown' && currentVideoIndex < videoSlides.length - 1) {
            e.preventDefault();
            scrollToVideo(currentVideoIndex + 1);
        } else if (e.key === ' ') {
            e.preventDefault();
            const currentVideo = videoPlayers[currentVideoIndex];
            if (currentVideo.paused) {
                currentVideo.play();
            } else {
                currentVideo.pause();
            }
        }
    });

    // Fonction pour naviguer vers une vidéo spécifique
    function scrollToVideo(index) {
        if (index >= 0 && index < videoSlides.length && !isScrolling) {
            isScrolling = true;
            videoSlides[index].scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
            
            setTimeout(() => {
                isScrolling = false;
            }, 1000);
        }
    }

    // Gestion du swipe tactile
    let startY = 0;
    let endY = 0;
    const minSwipeDistance = 50;

    videosContainer.addEventListener('touchstart', (e) => {
        startY = e.touches[0].clientY;
    }, { passive: true });

    videosContainer.addEventListener('touchend', (e) => {
        endY = e.changedTouches[0].clientY;
        const swipeDistance = startY - endY;

        if (Math.abs(swipeDistance) > minSwipeDistance) {
            if (swipeDistance > 0 && currentVideoIndex < videoSlides.length - 1) {
                // Swipe vers le haut - vidéo suivante
                scrollToVideo(currentVideoIndex + 1);
            } else if (swipeDistance < 0 && currentVideoIndex > 0) {
                // Swipe vers le bas - vidéo précédente
                scrollToVideo(currentVideoIndex - 1);
            }
        }
    }, { passive: true });

    // Empêcher le scroll par défaut sur mobile
    videosContainer.addEventListener('touchmove', (e) => {
        if (!isScrolling) {
            e.preventDefault();
        }
    }, { passive: false });

    // Gestion du scroll de la souris
    let scrollTimeout;
    videosContainer.addEventListener('wheel', (e) => {
        e.preventDefault();
        
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            if (e.deltaY > 0 && currentVideoIndex < videoSlides.length - 1) {
                // Scroll vers le bas - vidéo suivante
                scrollToVideo(currentVideoIndex + 1);
            } else if (e.deltaY < 0 && currentVideoIndex > 0) {
                // Scroll vers le haut - vidéo précédente
                scrollToVideo(currentVideoIndex - 1);
            }
        }, 100);
    }, { passive: false });

    // Initialisation - s'assurer que la première vidéo est visible
    if (videoSlides.length > 0) {
        videoSlides[0].scrollIntoView({ block: 'start' });
    }
    
    // Fonctions utilitaires
    
    // Animation de cœur pour le double-clic
    function showHeartAnimation(slide) {
        const heart = document.createElement('div');
        heart.innerHTML = '&hearts;';
        heart.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            font-size: 4rem;
            pointer-events: none;
            z-index: 1000;
            animation: heartPulse 1s ease-out forwards;
        `;
        
        slide.appendChild(heart);
        
        setTimeout(() => {
            heart.remove();
        }, 1000);
    }
    
    // Système de notifications
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        if (type === 'success') {
            notification.style.backgroundColor = '#10b981';
        } else if (type === 'error') {
            notification.style.backgroundColor = '#ef4444';
        } else {
            notification.style.backgroundColor = '#3b82f6';
        }
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animation de sortie
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Système de commentaires en superposition
    function showCommentsOverlay(videoId, slide) {
        // Vérifier si une overlay existe déjà
        const existingOverlay = document.querySelector('.comments-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        const overlay = document.createElement('div');
        overlay.className = 'comments-overlay';
        overlay.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60%;
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.8) 100%);
            backdrop-filter: blur(10px);
            border-radius: 20px 20px 0 0;
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            overflow: hidden;
        `;
        
        overlay.innerHTML = `
            <div class="comments-header" style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: between; align-items: center;">
                <h3 style="color: white; margin: 0; font-size: 1.2rem; font-weight: 600;">Commentaires</h3>
                <button class="close-comments" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 5px;">×</button>
            </div>
            <div class="comments-content" style="padding: 20px; height: calc(100% - 140px); overflow-y: auto;">
                <div class="comments-list" style="margin-bottom: 20px;">
                    <div class="loading-comments" style="text-align: center; color: white; opacity: 0.6; padding: 20px;">Chargement des commentaires...</div>
                </div>
            </div>
            <div class="comments-input" style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="text" placeholder="Ajouter un commentaire..." style="flex: 1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 25px; padding: 12px 16px; color: white; outline: none;" class="comment-input">
                    <button class="send-comment" style="background: #3b82f6; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        slide.appendChild(overlay);
        
        // Animation d'entrée
        setTimeout(() => {
            overlay.style.transform = 'translateY(0)';
        }, 100);
        
        // Charger les commentaires réels
        loadComments(videoId, overlay);
        
        // Gestion des événements
        const closeBtn = overlay.querySelector('.close-comments');
        const commentInput = overlay.querySelector('.comment-input');
        const sendBtn = overlay.querySelector('.send-comment');
        
        closeBtn.addEventListener('click', () => {
            overlay.style.transform = 'translateY(100%)';
            setTimeout(() => {
                overlay.remove();
            }, 300);
        });
        
        // Envoyer un commentaire
        function sendComment() {
            const text = commentInput.value.trim();
            if (text) {
                // Vider le champ de saisie immédiatement
                commentInput.value = '';
                
                // Appel AJAX pour sauvegarder le commentaire
                fetch(`/videos/${videoId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ comment: text })
                })
                .then(response => {
                    // Check if the response is OK (2xx status)
                    if (!response.ok) {
                        // If it's a redirect to login page or authentication error, redirect the user
                        if (response.status === 401) {
                            window.location.href = '/login';
                            return;
                        }
                        // For other errors, throw an error
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the comments count display
                        const videoSlide = document.querySelector(`.video-slide[data-index="${currentVideoIndex}"]`);
                        if (videoSlide) {
                            const commentsCountElement = videoSlide.querySelector('.comments-count');
                            if (commentsCountElement) {
                                commentsCountElement.textContent = data.comments_count;
                            }
                        }
                        
                        // Recharger tous les commentaires pour avoir les données à jour
                        loadComments(videoId, overlay);
                        
                        // Scroll vers le bas après rechargement
                        setTimeout(() => {
                            const commentsContent = overlay.querySelector('.comments-content');
                            commentsContent.scrollTop = commentsContent.scrollHeight;
                        }, 100);
                    } else {
                        showNotification('Erreur lors de l\'envoi du commentaire: ' + (data.message || data.error || ''), 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'envoi du commentaire:', error);
                    showNotification('Erreur lors de l\'envoi du commentaire. Veuillez réessayer.', 'error');
                });
            }
        }
        
        sendBtn.addEventListener('click', sendComment);
        commentInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendComment();
            }
        });
        
        // Fermer en cliquant à l'extérieur
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeBtn.click();
            }
        });    
    }
    
    // Fonction pour charger les commentaires depuis la base de données
    function loadComments(videoId, overlay) {
        const commentsList = overlay.querySelector('.comments-list');
        
        fetch(`/videos/${videoId}/comments`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Vider le message de chargement
                commentsList.innerHTML = '';
                
                if (data.success && data.comments && data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const commentElement = document.createElement('div');
                        commentElement.className = 'comment-item';
                        commentElement.style.cssText = 'display: flex; gap: 12px; margin-bottom: 16px; color: white;';
                        
                        const userInitial = comment.user_name ? comment.user_name.charAt(0).toUpperCase() : 'U';
                        
                        commentElement.innerHTML = `
                            <div class="comment-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: #374151; display: flex; align-items: center; justify-content: center; font-weight: bold;">${userInitial}</div>
                            <div class="comment-content">
                                <div class="comment-author" style="font-weight: 600; margin-bottom: 4px;">${comment.user_name || 'Utilisateur'}</div>
                                <div class="comment-text" style="opacity: 0.9;">${comment.content}</div>
                                <div class="comment-time" style="font-size: 0.8rem; opacity: 0.6; margin-top: 4px;">${comment.created_at}</div>
                            </div>
                        `;
                        
                        commentsList.appendChild(commentElement);
                    });
                } else {
                    commentsList.innerHTML = '<div style="text-align: center; color: white; opacity: 0.6; padding: 20px;">Aucun commentaire pour le moment. Soyez le premier à commenter !</div>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des commentaires:', error);
                commentsList.innerHTML = '<div style="text-align: center; color: white; opacity: 0.6; padding: 20px;">Erreur lors du chargement des commentaires.</div>';
            });
    }
});
</script>
@endpush

@push('styles')
<style>
/* Styles pour l'interface TikTok-like */
#video-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

#videos-scroll {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE et Edge */
}

#videos-scroll::-webkit-scrollbar {
    display: none; /* Chrome, Safari et Opera */
}

.video-slide {
    position: relative;
    background: #000;
}

.video-player {
    object-fit: cover;
    object-position: center;
}

/* Animations pour les boutons */
.play-pause-btn,
.sound-toggle,
.like-btn,
.profile-btn,
.follow-btn {
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.play-pause-btn:hover,
.sound-toggle:hover,
.like-btn:hover,
.profile-btn:hover {
    transform: scale(1.1);
    background-color: rgba(0, 0, 0, 0.8) !important;
}

.follow-btn:hover {
    transform: scale(1.1);
    background-color: rgb(29, 78, 216) !important;
}

/* Animation de like */
@keyframes likeAnimation {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.like-btn.liked {
    animation: likeAnimation 0.6s ease;
    background-color: rgb(239, 68, 68) !important;
}

/* Animation de cœur pour double-clic */
@keyframes heartPulse {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 1;
    }
    15% {
        transform: translate(-50%, -50%) scale(1.2);
    }
    30% {
        transform: translate(-50%, -50%) scale(1);
    }
    100% {
        transform: translate(-50%, -50%) scale(1.3);
        opacity: 0;
    }
}

/* Styles pour les commentaires */
.comments-overlay {
    font-family: inherit;
}

.comment-input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.comment-input:focus {
    border-color: rgba(59, 130, 246, 0.5);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Amélioration des boutons interactifs */
.like-btn.liked .like-icon {
    fill: #ef4444;
    stroke: #ef4444;
    stroke-width: 0;
}

.follow-btn.following {
    background-color: #10b981 !important;
}

/* Scrollbar personnalisée pour les commentaires */
.comments-content::-webkit-scrollbar {
    width: 4px;
}

.comments-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

.comments-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

.comments-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Gradient overlay pour la lisibilité */
.bg-gradient-to-t {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, transparent 100%);
}

/* Responsive design */
@media (max-width: 768px) {
    .video-slide {
        height: 100vh;
        height: 100dvh; /* Dynamic viewport height pour mobile */
    }
    
    .absolute.bottom-0 {
        padding: 1rem;
    }
    
    .flex.flex-col.items-center.gap-4 {
        gap: 1.25rem;
    }
    
    .sound-toggle,
    .like-btn,
    .profile-btn {
        padding: 0.75rem;
    }
    
    .sound-toggle svg,
    .like-btn svg,
    .profile-btn svg {
        width: 1.5rem;
        height: 1.5rem;
    }
}

/* Amélioration de la lisibilité du texte */
.text-white {
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

/* Indicateur de chargement */
.video-player[data-loading="true"] {
    background: linear-gradient(45deg, #374151, #4B5563);
    background-size: 400% 400%;
    animation: loadingGradient 2s ease infinite;
}

@keyframes loadingGradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Optimisation des performances */
.video-player {
    will-change: transform;
    transform: translateZ(0);
}

/* Masquer les contrôles natifs sur mobile */
.video-player::-webkit-media-controls {
    display: none !important;
}

.video-player::-webkit-media-controls-enclosure {
    display: none !important;
}

/* Snap scroll pour une navigation fluide */
.snap-y {
    scroll-snap-type: y mandatory;
}

.snap-start {
    scroll-snap-align: start;
}

/* Transition douce pour les changements d'état */
.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Style pour les boutons actifs */
.sound-toggle.active,
.like-btn.active {
    background-color: rgba(239, 68, 68, 0.8) !important;
}

.follow-btn.following {
    background-color: rgb(34, 197, 94) !important;
}

.follow-btn.following svg {
    transform: rotate(45deg);
}

/* Amélioration de l'accessibilité */
@media (prefers-reduced-motion: reduce) {
    .video-slide,
    .play-pause-btn,
    .sound-toggle,
    .like-btn,
    .profile-btn {
        transition: none;
    }
    
    #videos-scroll {
        scroll-behavior: auto;
    }
}

/* Focus states pour l'accessibilité */
.play-pause-btn:focus,
.sound-toggle:focus,
.like-btn:focus,
.profile-btn:focus {
    outline: 2px solid white;
    outline-offset: 2px;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
