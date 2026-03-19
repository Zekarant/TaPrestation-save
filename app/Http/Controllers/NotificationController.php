<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Constructeur du contrôleur.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher toutes les notifications de l'utilisateur connecté.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Attempt to get Laravel standard notifications
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // If no standard notifications, try custom notification model
        if ($notifications->total() == 0) {
            // Use custom Notification model as fallback
            $notifications = \App\Models\Notification::where('notifiable_type', get_class($user))
                ->where('notifiable_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue.
     *
     * @param  Notification  $notification
     * @return RedirectResponse
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        $notification->markAsRead();

        return redirect()->back()
            ->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues.
     *
     * @return RedirectResponse
     */
    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()
            ->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification.
     *
     * @param  Notification  $notification
     * @return RedirectResponse
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Récupère le nombre de notifications non lues (pour AJAX)
     */
    public function getUnreadCount()
    {
        // If user is not authenticated, return 0
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        try {
            $user = Auth::user();
            $count = $user->notifications()
                ->whereNull('read_at')
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Throwable $e) {
            \Log::warning('Notification unread-count failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['count' => 0]);
        }
    }

    /**
     * Récupère les dernières notifications non lues (pour AJAX)
     */
    public function getRecent()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => $notification->action_url,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'type' => $notification->getDecodedData()['type'] ?? 'info'
                ];
            });
        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Récupère les nouvelles notifications non-pushées (pour polling push natif)
     * Cette méthode est utilisée par le service-worker pour afficher les notifications push
     */
    public function getUnpushed()
    {
        if (!Auth::check()) {
            return response()->json(['notifications' => []]);
        }

        try {
            $user = Auth::user();

            // Endpoint en lecture seule: ne pas modifier l'état via GET.
            // On limite la fenêtre temporelle pour éviter de renvoyer un historique trop ancien.
            $notifications = $user->notifications()
                ->whereNull('read_at')
                ->where('created_at', '>=', now()->subDay())
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $result = $notifications->map(function ($notification) {
                $rawData = $notification->data ?? [];
                $data = is_array($rawData) ? $rawData : (json_decode((string) $rawData, true) ?: []);
                $type = (string) ($notification->type ?? '');

                return [
                    'id' => $notification->id,
                    'title' => $this->extractNotificationTitle($type, $data),
                    'body' => $this->extractNotificationBody($data),
                    'icon' => '/icons/icon-192x192.png',
                    'badge' => '/icons/icon-72x72.png',
                    'url' => $data['url'] ?? $data['action_url'] ?? '/notifications',
                    'tag' => 'notification-' . $notification->id,
                    'type' => $data['type'] ?? 'info'
                ];
            });

            return response()->json(['notifications' => $result]);
        } catch (\Throwable $e) {
            \Log::warning('Notification unpushed failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['notifications' => []]);
        }
    }

    /**
     * Extraire le titre de la notification en fonction de son type
     */
    private function extractNotificationTitle(string $type, array $data): string
    {
        // Mapping des types de notification vers des titres
        $typeToTitle = [
            'App\\Notifications\\NewFoodOrder' => '🍽️ Nouvelle commande',
            'App\\Notifications\\FoodOrderAccepted' => '✅ Commande acceptée',
            'App\\Notifications\\FoodOrderRejected' => '❌ Commande refusée',
            'App\\Notifications\\FoodOrderReady' => '🔔 Commande prête',
            'App\\Notifications\\FoodOrderCompleted' => '✅ Commande livrée',
            'App\\Notifications\\NewBookingNotification' => '📅 Nouvelle réservation',
            'App\\Notifications\\NewMessageNotification' => '💬 Nouveau message',
            'App\\Notifications\\NewReviewNotification' => '⭐ Nouvel avis',
            'App\\Notifications\\NewTenderResponseNotification' => '📋 Nouvelle réponse',
            'App\\Notifications\\NewEquipmentRentalRequestNotification' => '🔧 Nouvelle demande de location',
        ];

        // Chercher dans le mapping
        if (isset($typeToTitle[$type])) {
            return $typeToTitle[$type];
        }

        // Chercher dans les données
        if (!empty($data['title'])) {
            return $data['title'];
        }

        // Titre par défaut
        return 'TaPrestation';
    }

    /**
     * Extraire le corps de la notification
     */
    private function extractNotificationBody(array $data): string
    {
        // Ordre de priorité pour le corps du message
        if (!empty($data['message'])) {
            return $data['message'];
        }
        if (!empty($data['body'])) {
            return $data['body'];
        }
        if (!empty($data['content'])) {
            return $data['content'];
        }
        
        // Essayer de construire un message à partir des données disponibles
        if (!empty($data['client_name']) && !empty($data['order_number'])) {
            return 'De ' . $data['client_name'] . ' - Commande #' . $data['order_number'];
        }
        if (!empty($data['sender_name'])) {
            return 'Message de ' . $data['sender_name'];
        }
        
        return 'Vous avez une nouvelle notification';
    }

    /**
     * Redirect to notification URL and mark as read in one action.
     *
     * @param  Notification  $notification
     * @return RedirectResponse
     */
    public function redirectAndMarkRead(Notification $notification): RedirectResponse
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        // Marquer comme lue
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        // Déterminer l'URL de redirection
        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
        $url = $data['url'] ?? $data['action_url'] ?? null;

        if (!empty($url)) {
            // Corriger les anciennes URLs de messaging qui n'ont pas le préfixe /client/
            if (preg_match('#^/messaging/(\d+)$#', $url, $matches)) {
                $url = '/client/messaging/' . $matches[1];
            }
            // Aussi corriger les URLs absolues avec l'ancien format
            if (preg_match('#https?://[^/]+/messaging/(\d+)$#', $url, $matches)) {
                $url = '/client/messaging/' . $matches[1];
            }

            // Sécurité: ne jamais rediriger vers un domaine externe.
            if (preg_match('#^https?://#i', $url)) {
                $parts = parse_url($url);
                $targetHost = strtolower((string) ($parts['host'] ?? ''));
                $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

                if ($targetHost !== '' && $appHost !== '' && $targetHost === $appHost) {
                    $path = (string) ($parts['path'] ?? '/');
                    $query = (string) ($parts['query'] ?? '');
                    $url = $query !== '' ? $path . '?' . $query : $path;
                } else {
                    $url = null;
                }
            }

            if (is_string($url) && $url !== '') {
                if (!str_starts_with($url, '/')) {
                    $url = '/' . ltrim($url, '/');
                }
                return redirect()->to($url);
            }
        }

        return redirect()->route('notifications.index');
    }
}
