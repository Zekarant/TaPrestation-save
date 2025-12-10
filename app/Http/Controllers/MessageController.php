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
     * Liste des conversations de l'utilisateur connecté.
     */
    public function index(): View
    {
        $currentUser   = Auth::user();
        $conversations = $this->getUserConversations($currentUser);

        return view('messaging.index', compact('conversations'));
    }

    /**
     * Afficher une conversation avec un autre utilisateur.
     */
    public function conversation(User $user): View
    {
        $currentUser = Auth::user();

        // Tous les messages entre les deux utilisateurs
        $messages = Message::where(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Marquer comme lus les messages reçus non lus
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Charger les profils liés (client / prestataire) pour les infos d'affichage
        $user->load(['client', 'prestataire']);

        $otherUser = $user;

        return view('messaging.conversation', compact('messages', 'otherUser'));
    }

    /**
     * Envoyer un message à un utilisateur (soumission classique formulaire).
     */
    public function send(Request $request, User $receiver): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        Message::create([
            'sender_id'       => Auth::id(),
            'receiver_id'     => $receiver->id,
            'content'         => $request->input('content'),
            'client_request_id' => $request->input('client_request_id'),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Message envoyé avec succès.');
    }

    /**
     * Envoyer un message via AJAX.
     */
    public function sendAjax(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->input('receiver_id'),
            'content'     => $request->input('content'),
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json([
            'success'         => true,
            'message'         => $message,
            'formatted_time'  => $message->created_at->format('H:i'),
            'formatted_date'  => $message->created_at->format('d/m/Y'),
        ]);
    }

    /**
     * Récupérer les nouveaux messages pour une conversation (polling AJAX).
     */
    public function getNewMessages(Request $request, User $user): JsonResponse
    {
        $currentUser   = Auth::user();
        $lastMessageId = (int) $request->get('last_message_id', 0);

        $newMessages = Message::where(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
            })
            ->where('id', '>', $lastMessageId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Marquer les messages reçus comme lus
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->where('id', '>', $lastMessageId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $payload = $newMessages->map(function (Message $message) {
            return [
                'id'             => $message->id,
                'content'        => $message->content,
                'sender_id'      => $message->sender_id,
                'receiver_id'    => $message->receiver_id,
                'created_at'     => $message->created_at,
                'formatted_time' => $message->created_at->format('H:i'),
                'formatted_date' => $message->created_at->format('d/m/Y'),
                'sender'         => $message->sender,
            ];
        });

        return response()->json(['messages' => $payload]);
    }

    /**
     * Nombre de messages non lus pour l'utilisateur connecté.
     *
     * Si pas connecté → 0 (utilisé dans le header, possible que la route soit appelée
     * avant que le user soit authentifié).
     */
    public function getUnreadCount(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['unread_count' => 0]);
        }

        $unreadCount = Message::where('receiver_id', $userId)
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
            'is_online'    => (bool) $user->is_online,
            'last_seen_at' => $user->last_seen_at,
            'status_text'  => $user->online_status,
        ]);
    }

    /**
     * Marquer une liste de messages comme lus.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'message_ids'   => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        Message::whereIn('id', $request->input('message_ids', []))
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Initialiser une conversation avec un prestataire (depuis sa fiche).
     */
    public function initWithPrestataire(Request $request, Prestataire $prestataire): RedirectResponse
    {
        if (!$prestataire->is_approved) {
            return redirect()
                ->back()
                ->with('error', 'Ce prestataire n\'est pas disponible.');
        }

        // Rediriger vers la conversation avec le user associé au prestataire
        return redirect()->route('messaging.conversation', $prestataire->user);
    }

    /**
     * Démarrer une conversation avec un utilisateur (redirige sans créer quoi que ce soit).
     */
    public function start(User $user): RedirectResponse
    {
        return redirect()->route('messaging.conversation', $user);
    }

    /**
     * Construit la liste des conversations de l'utilisateur
     * avec dernier message + compteur de non lus.
     */
    private function getUserConversations(User $currentUser)
    {
        // Tous les user_ids avec lesquels l'utilisateur courant a échangé
        $userIds = Message::where('sender_id', $currentUser->id)
            ->orWhere('receiver_id', $currentUser->id)
            ->select('sender_id', 'receiver_id')
            ->get()
            ->map(function (Message $message) use ($currentUser) {
                return $message->sender_id === $currentUser->id
                    ? $message->receiver_id
                    : $message->sender_id;
            })
            ->unique()
            ->values();

        // Charger les users + profils
        $users = User::whereIn('id', $userIds)
            ->with(['client', 'prestataire'])
            ->get();

        // Pour chaque user, récupérer le dernier message + nb de non lus
        $conversations = $users->map(function (User $user) use ($currentUser) {
            $lastMessage = Message::where(function ($query) use ($currentUser, $user) {
                    $query->where('sender_id', $currentUser->id)
                          ->where('receiver_id', $user->id);
                })
                ->orWhere(function ($query) use ($currentUser, $user) {
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
                'user'          => $user,
                'last_message'  => $lastMessage,
                'unread_count'  => $unreadCount,
            ];
        });

        // Trier par date du dernier message (desc)
        return $conversations
            ->sortByDesc(function (array $conversation) {
                return optional($conversation['last_message'])->created_at;
            })
            ->values();
    }
}
