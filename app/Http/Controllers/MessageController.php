<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    /**
     * Afficher la liste des conversations de l'utilisateur.
     */
    public function index(): View
    {
        $currentUser = Auth::user();
        
        // Récupérer les conversations avec les derniers messages et compteurs
        $conversations = $this->getUserConversations($currentUser);
        
        return view('messaging.index', compact('conversations'));
    }

    /**
     * Afficher une conversation spécifique.
     */
    public function conversation(User $user): View
    {
        $currentUser = Auth::user();
        
        // Récupérer les messages entre les deux utilisateurs
        $messages = Message::where(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
            })
            ->orWhere(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Marquer les messages non lus comme lus
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        // Charger les relations pour le statut en ligne et les photos de profil
        $user->load(['client', 'prestataire']);
        
        // Passer $user comme $otherUser pour correspondre à la vue
        $otherUser = $user;
        
        return view('messaging.conversation', compact('messages', 'otherUser'));
    }

    /**
     * Envoyer un message à un utilisateur.
     */
    public function send(Request $request, User $receiver): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
            'content' => $request->content,
            'client_request_id' => $request->client_request_id,
        ]);
        
        return redirect()->back()->with('success', 'Message envoyé avec succès.');
    }

    /**
     * Envoyer un message via AJAX.
     */
    public function sendAjax(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);
        
        $message->load(['sender', 'receiver']);
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'formatted_time' => $message->created_at->format('H:i'),
            'formatted_date' => $message->created_at->format('d/m/Y')
        ]);
    }

    /**
     * Récupérer les nouveaux messages pour une conversation.
     */
    public function getNewMessages(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $lastMessageId = $request->get('last_message_id', 0);
        
        $newMessages = Message::where(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
            })
            ->orWhere(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
            })
            ->where('id', '>', $lastMessageId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Marquer les nouveaux messages reçus comme lus
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->where('id', '>', $lastMessageId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json([
            'messages' => $newMessages->map(function($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'created_at' => $message->created_at,
                    'formatted_time' => $message->created_at->format('H:i'),
                    'formatted_date' => $message->created_at->format('d/m/Y'),
                    'sender' => $message->sender
                ];
            })
        ]);
    }

    /**
     * Compter les messages non lus pour l'utilisateur connecté.
     */
    public function getUnreadCount(): JsonResponse
    {
        $unreadCount = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();
        
        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Obtenir le statut en ligne d'un utilisateur.
     */
    public function getUserOnlineStatus(User $user): JsonResponse
    {
        return response()->json([
            'is_online' => $user->is_online,
            'last_seen_at' => $user->last_seen_at,
            'status_text' => $user->online_status
        ]);
    }

    /**
     * Marquer les messages comme lus.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id'
        ]);

        Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Récupérer les conversations d'un utilisateur.
     */
    private function getUserConversations(User $currentUser)
    {
        // Récupérer les IDs des utilisateurs avec qui l'utilisateur courant a échangé
        $userIds = Message::where('sender_id', $currentUser->id)
            ->orWhere('receiver_id', $currentUser->id)
            ->select('sender_id', 'receiver_id')
            ->get()
            ->map(function($message) use ($currentUser) {
                return $message->sender_id == $currentUser->id ? $message->receiver_id : $message->sender_id;
            })
            ->unique()
            ->values();
        
        // Récupérer les utilisateurs correspondants avec leurs profils
        $users = User::whereIn('id', $userIds)
            ->with(['client', 'prestataire'])
            ->get();
        
        // Pour chaque utilisateur, récupérer le dernier message et le nombre de messages non lus
        $conversations = $users->map(function($user) use ($currentUser) {
            $lastMessage = Message::where(function($query) use ($currentUser, $user) {
                    $query->where('sender_id', $currentUser->id)
                          ->where('receiver_id', $user->id);
                })
                ->orWhere(function($query) use ($currentUser, $user) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $currentUser->id);
                })
                ->latest()
                ->first();
            
            $unreadCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->whereNull('read_at')
                ->count();
            
            return [
                'user' => $user,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount
            ];
        });
        
        // Trier les conversations par date du dernier message
        return $conversations->sortByDesc(function($conversation) {
            return $conversation['last_message'] ? $conversation['last_message']->created_at : null;
        })->values();
    }
    
    /**
     * Initialiser une conversation avec un prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function initWithPrestataire(Request $request, Prestataire $prestataire)
    {
        // Vérifier que le prestataire est approuvé
        if (!$prestataire->is_approved) {
            return redirect()->back()->with('error', 'Ce prestataire n\'est pas disponible.');
        }
        
        // Rediriger vers la conversation avec l'utilisateur du prestataire
        return redirect()->route('messaging.conversation', $prestataire->user);
    }

    /**
     * Démarrer une conversation avec un utilisateur.
     */
    public function start(User $user): RedirectResponse
    {
        // Rediriger directement vers la conversation
        return redirect()->route('messaging.conversation', $user);
    }
}