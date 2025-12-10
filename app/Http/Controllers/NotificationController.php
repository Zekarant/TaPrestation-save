<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Constructeur.
     *
     * On protège toutes les routes sauf les compteurs JSON,
     * qui doivent juste renvoyer 0 si l'utilisateur n'est pas connecté.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['getUnreadCount', 'getRecent']);
    }

    /**
     * Afficher toutes les notifications de l'utilisateur connecté.
     */
    public function index(): View
    {
        $user = Auth::user();

        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        $user = Auth::user();

        // Vérifier que la notification appartient bien à l'utilisateur connecté
        if (
            $notification->notifiable_id !== $user->id ||
            $notification->notifiable_type !== get_class($user)
        ) {
            return redirect()
                ->route('notifications.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        // Méthode custom sur ton modèle Notification
        if (method_exists($notification, 'markAsRead')) {
            $notification->markAsRead();
        } else {
            $notification->read_at = now();
            $notification->save();
        }

        return redirect()
            ->back()
            ->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();

        Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()
            ->back()
            ->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $user = Auth::user();

        if (
            $notification->notifiable_id !== $user->id ||
            $notification->notifiable_type !== get_class($user)
        ) {
            return redirect()
                ->route('notifications.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        $notification->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Nombre de notifications non lues (JSON pour header / icône).
     *
     * Accessible même sans être connecté :
     * - connecté  → compte réel
     * - invité    → 0
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Dernières notifications non lues (JSON pour menu déroulant).
     *
     * Accessible même sans être connecté :
     * - connecté  → liste réelle
     * - invité    → liste vide
     */
    public function getRecent(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function (Notification $notification) {
                // getDecodedData() est supposé exister sur ton modèle
                $data = method_exists($notification, 'getDecodedData')
                    ? $notification->getDecodedData()
                    : [];

                return [
                    'id'         => $notification->id,
                    'title'      => $notification->title ?? ($data['title'] ?? null),
                    'message'    => $notification->message ?? ($data['message'] ?? null),
                    'url'        => $notification->action_url ?? ($data['url'] ?? null),
                    'created_at' => optional($notification->created_at)->diffForHumans(),
                    'type'       => $data['type'] ?? 'info',
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }
}
