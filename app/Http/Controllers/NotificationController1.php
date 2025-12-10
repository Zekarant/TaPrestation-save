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
        $user = Auth::user();
        $count = $user->notifications()
            ->whereNull('read_at')
            ->count();
        return response()->json(['count' => $count]);
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
}
