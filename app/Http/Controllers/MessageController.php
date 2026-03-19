<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Prestataire;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        
        // Envoyer une notification au destinataire
        try {
            $receiver->notify(new NewMessageNotification($message));
        } catch (\Exception $e) {
            \Log::warning('Notification message failed: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('success', 'Message envoyé avec succès.');
    }

    /**
     * Envoyer un message via AJAX.
     */
    public function sendAjax(Request $request)
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

        // Envoyer une notification au destinataire
        try {
            $receiver = User::find($request->receiver_id);
            if ($receiver) {
                $receiver->notify(new NewMessageNotification($message));
            }
        } catch (\Exception $e) {
            \Log::warning('Notification message failed: ' . $e->getMessage());
        }

        // If this endpoint is reached via a normal form POST (non-AJAX),
        // redirect back to avoid displaying raw JSON as a full page.
        if (!$request->ajax() && !$request->expectsJson() && !$request->wantsJson()) {
            return redirect()->back()->with('success', 'Message envoyé avec succès.');
        }

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
        // If user is not authenticated, return 0
        if (!Auth::check()) {
            return response()->json(['unread_count' => 0]);
        }
        
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
        $uid = $currentUser->id;

        // Audit 4.6: une seule requête avec sous-requêtes pour last_message et unread_count
        // au lieu de 2 requêtes par conversation (N+1)
        $userIds = Message::where('sender_id', $uid)
            ->orWhere('receiver_id', $uid)
            ->select('sender_id', 'receiver_id')
            ->get()
            ->map(fn($m) => $m->sender_id == $uid ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $users = User::whereIn('id', $userIds)
            ->with(['client', 'prestataire'])
            ->get()
            ->keyBy('id');

        // Dernier message par conversation en 1 requête
        $lastMessages = \Illuminate\Support\Facades\DB::table('messages as m1')
            ->joinSub(
                \Illuminate\Support\Facades\DB::table('messages')
                    ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id, MAX(id) as max_id', [$uid])
                    ->where(fn($q) => $q->where('sender_id', $uid)->orWhere('receiver_id', $uid))
                    ->groupByRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END', [$uid]),
                'm2',
                'm1.id', '=', 'm2.max_id'
            )
            ->select('m1.*', 'm2.other_id')
            ->get()
            ->keyBy('other_id');

        // Unread count par expéditeur en 1 requête
        $unreadCounts = Message::where('receiver_id', $uid)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as cnt')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $conversations = $userIds->map(function($otherId) use ($users, $lastMessages, $unreadCounts) {
            return [
                'user' => $users[$otherId] ?? null,
                'last_message' => $lastMessages[$otherId] ?? null,
                'unread_count' => (int) ($unreadCounts[$otherId] ?? 0),
            ];
        })->filter(fn($c) => $c['user'] !== null);

        return $conversations->sortByDesc(function($c) {
            return $c['last_message']->created_at ?? null;
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
