<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoComment;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $videos = collect();

        if ($user && $user->client) {
            $followedPrestataires = $user->client->followedPrestataires()->pluck('prestataires.id');
            if ($followedPrestataires->isNotEmpty()) {
                $videos = Video::with(['likes', 'comments.user', 'prestataire'])
                    ->whereIn('prestataire_id', $followedPrestataires)
                    ->where('status', 'processed') // Only show processed videos
                    ->where('is_public', true)
                    ->latest()
                    ->get();
            }
        }

        // Si l'utilisateur n'est pas connecté ou ne suit personne, ou si les prestataires suivis n'ont pas de vidéos
        if ($videos->isEmpty()) {
            $videos = Video::with(['likes', 'comments.user', 'prestataire'])
                ->where('status', 'processed') // Only show processed videos
                ->where('is_public', true)
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        // Ajouter les informations de like pour l'utilisateur connecté
        if ($user) {
            $videos->each(function ($video) use ($user) {
                $video->is_liked_by_user = $video->isLikedBy($user);
            });
        }

        return view('videos.feed', compact('videos'));
    }

    public function show(Video $video)
    {
        // Only allow viewing of processed videos
        if ($video->status !== 'processed') {
            abort(404);
        }
        
        $video->increment('views_count');
        return view('videos.show', compact('video'));
    }

    public function follow(Request $request, Prestataire $prestataire)
    {
        $client = Auth::user()->client;
        $client->followedPrestataires()->toggle($prestataire->id);

        return back()->with('success', 'Action effectuée avec succès.');
    }

    public function like(Request $request, Video $video)
    {
        $request->validate([
            'liked' => 'required|boolean'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // Only allow liking of processed videos
        if ($video->status !== 'processed') {
            return response()->json(['error' => 'Cette vidéo n\'est pas disponible'], 400);
        }

        $existingLike = VideoLike::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        if ($request->liked) {
            // Ajouter un like si il n'existe pas déjà
            if (!$existingLike) {
                VideoLike::create([
                    'user_id' => $user->id,
                    'video_id' => $video->id
                ]);
                $video->increment('likes_count');
            }
        } else {
            // Supprimer le like si il existe
            if ($existingLike) {
                $existingLike->delete();
                $video->decrement('likes_count');
            }
        }

        return response()->json([
            'success' => true,
            'likes_count' => $video->fresh()->likes_count,
            'is_liked' => $video->isLikedBy($user)
        ]);
    }

    public function comment(Request $request, Video $video)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }
        
        // Only allow commenting on processed videos
        if ($video->status !== 'processed') {
            return response()->json(['error' => 'Cette vidéo n\'est pas disponible'], 400);
        }
        
        $request->validate([
            'comment' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        
        // Create the comment
        $comment = VideoComment::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
            'content' => $request->comment
        ]);

        // Update the counter
        $video->increment('comments_count');

        return response()->json([
            'success' => true,
            'message' => 'Commentaire ajouté avec succès',
            'comments_count' => $video->fresh()->comments_count,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => $user->name,
                'created_at' => $comment->created_at->diffForHumans()
            ]
        ]);
    }

    public function getComments(Video $video)
    {
        // Only allow getting comments for processed videos
        if ($video->status !== 'processed') {
            return response()->json(['error' => 'Cette vidéo n\'est pas disponible'], 400);
        }
        
        try {
            $comments = $video->comments()
                ->with('user')
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user_name' => $comment->user->name,
                        'created_at' => $comment->created_at->diffForHumans()
                    ];
                });

            return response()->json([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des commentaires'
            ], 500);
        }
    }

    public function incrementViewCount(Request $request, Video $video)
    {
        // Only allow view counting for processed videos
        if ($video->status !== 'processed') {
            return response()->json(['error' => 'Cette vidéo n\'est pas disponible'], 400);
        }
        
        // Validate the request
        $request->validate([
            'watched_duration' => 'nullable|integer|min:0',
            'video_duration' => 'nullable|integer|min:0'
        ]);

        // Check if we should count this view (at least 10 seconds or 30% of video duration)
        $shouldCountView = true;
        if ($request->watched_duration && $request->video_duration) {
            $minDuration = min(10, $request->video_duration * 0.3); // 10 seconds or 30% of video, whichever is smaller
            $shouldCountView = $request->watched_duration >= $minDuration;
        }

        if ($shouldCountView) {
            // Increment the view count
            $video->increment('views_count');
            
            return response()->json([
                'success' => true,
                'views_count' => $video->fresh()->views_count,
                'message' => 'View count incremented'
            ]);
        }

        return response()->json([
            'success' => true,
            'views_count' => $video->views_count,
            'message' => 'View not counted (insufficient watch time)'
        ]);
    }
}