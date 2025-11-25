<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Affiche la liste des notifications.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Notification::with(['notifiable']);
        
        // Filtrage par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filtrage par statut de lecture
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            }
        }
        
        // Filtrage par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Recherche par contenu
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data', 'like', '%' . $search . '%');
            });
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistiques
        $totalNotifications = Notification::count();
        $readNotifications = Notification::whereNotNull('read_at')->count();
        $readRate = $totalNotifications > 0 ? round(($readNotifications / $totalNotifications) * 100, 1) : 0;
        
        $stats = [
            'total' => $totalNotifications,
            'unread' => Notification::whereNull('read_at')->count(),
            'read' => $readNotifications,
            'today' => Notification::whereDate('created_at', today())->count(),
            'read_rate' => $readRate,
        ];
        
        // Types de notifications pour le filtre
        $notificationTypes = Notification::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->map(function($type) {
                return [
                    'value' => $type,
                    'label' => $this->getNotificationTypeLabel($type)
                ];
            });
        
        return view('admin.notifications.index-modern', [
            'notifications' => $notifications,
            'stats' => $stats,
            'notificationTypes' => $notificationTypes,
        ]);
    }
    
    /**
     * Affiche les détails d'une notification.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $notification = Notification::with(['notifiable'])->findOrFail($id);
        
        return view('admin.notifications.show', [
            'notification' => $notification,
        ]);
    }
    
    /**
     * Marque une notification comme lue.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marquée comme lue.');
    }
    
    /**
     * Marque toutes les notifications comme lues.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        Notification::whereNull('read_at')->update(['read_at' => now()]);
        
        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Marque les notifications sélectionnées comme lues.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markSelectedAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);
        
        $count = Notification::whereIn('id', $request->notification_ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return redirect()->back()->with('success', "{$count} notification(s) marquée(s) comme lue(s).");
    }
    
    /**
     * Supprime une notification.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        
        return redirect()->route('administrateur.notifications.index')
            ->with('success', 'La notification a été supprimée avec succès.');
    }
    
    /**
     * Supprime les notifications anciennes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);
        
        $cutoffDate = now()->subDays($request->days);
        $deletedCount = Notification::where('created_at', '<', $cutoffDate)->delete();
        
        return redirect()->back()
            ->with('success', "$deletedCount notifications anciennes ont été supprimées.");
    }
    
    /**
     * Envoie une notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        return $this->sendCustom($request);
    }
    
    /**
     * Envoie une notification personnalisée.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendCustom(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:all,role,specific',
            'role' => 'required_if:recipient_type,role|in:client,prestataire,administrateur',
            'user_ids' => 'required_if:recipient_type,specific|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|max:100',
        ]);
        
        $recipients = collect();
        
        switch ($request->recipient_type) {
            case 'all':
                $recipients = User::all();
                break;
            case 'role':
                $recipients = User::where('role', $request->role)->get();
                break;
            case 'specific':
                $recipients = User::whereIn('id', $request->user_ids)->get();
                break;
        }
        
        $notificationData = [
            'title' => $request->title,
            'message' => $request->message,
            'admin_sender' => Auth::user()->name,
        ];
        
        foreach ($recipients as $user) {
            $user->notifications()->create([
                'id' => \Str::uuid(),
                'type' => $request->type,
                'data' => $notificationData,
                'created_at' => now(),
            ]);
        }
        
        return redirect()->back()
            ->with('success', "Notification envoyée à {$recipients->count()} utilisateur(s).");
    }
    
    /**
     * Affiche les statistiques des notifications.
     *
     * @return \Illuminate\View\View
     */
    public function analytics()
    {
        // Statistiques par type
        $typeStats = Notification::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($stat) {
                return [
                    'type' => $this->getNotificationTypeLabel($stat->type),
                    'count' => $stat->count
                ];
            });
        
        // Statistiques par jour (7 derniers jours)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyStats[] = [
                'date' => $date->format('d/m'),
                'count' => Notification::whereDate('created_at', $date)->count(),
                'read' => Notification::whereDate('created_at', $date)
                    ->whereNotNull('read_at')
                    ->count(),
            ];
        }
        
        // Taux de lecture
        $totalNotifications = Notification::count();
        $readNotifications = Notification::whereNotNull('read_at')->count();
        $readRate = $totalNotifications > 0 ? round(($readNotifications / $totalNotifications) * 100, 1) : 0;
        
        return view('admin.notifications.analytics', [
            'typeStats' => $typeStats,
            'dailyStats' => $dailyStats,
            'readRate' => $readRate,
        ]);
    }
    
    /**
     * Export des notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = Notification::with(['notifiable']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            }
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data', 'like', '%' . $search . '%');
            });
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'notifications_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($notifications) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Utilisateur',
                'Email',
                'Type',
                'Titre',
                'Message',
                'Statut',
                'Date Lecture',
                'Date Création'
            ]);
            
            // Données
            foreach ($notifications as $notification) {
                $data = $notification->data;
                fputcsv($file, [
                    $notification->id,
                    $notification->notifiable->name ?? 'Utilisateur supprimé',
                    $notification->notifiable->email ?? 'N/A',
                    $this->getNotificationTypeLabel($notification->type),
                    $data['title'] ?? 'N/A',
                    $data['message'] ?? 'N/A',
                    $notification->read_at ? 'Lue' : 'Non lue',
                    $notification->read_at ? $notification->read_at->format('d/m/Y H:i') : 'N/A',
                    $notification->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtient le libellé d'un type de notification.
     *
     * @param  string  $type
     * @return string
     */
    private function getNotificationTypeLabel($type)
    {
        $labels = [
            'App\\Notifications\\NewOfferNotification' => 'Nouvelle offre',
            'App\\Notifications\\OfferAcceptedNotification' => 'Offre acceptée',
            'App\\Notifications\\OfferRejectedNotification' => 'Offre rejetée',
            'App\\Notifications\\BookingCancelledNotification' => 'Réservation annulée',
            'App\\Notifications\\MissionCompletedNotification' => 'Mission terminée',
            'App\\Notifications\\NewReviewNotification' => 'Nouvel avis',
            'App\\Notifications\\PrestataireApprovedNotification' => 'Prestataire approuvé',
            'App\\Notifications\\RequestHasOffersNotification' => 'Demande avec offres',
            'App\\Notifications\\NewMessageNotification' => 'Nouveau message',
            'App\\Notifications\\NewClientRequestNotification' => 'Demande client reçue',
            'App\\Notifications\\AnnouncementStatusNotification' => 'Statut d\'annonce',
            'App\\Notifications\\NewBookingNotification' => 'Nouvelle réservation',
            'App\\Notifications\\BookingConfirmedNotification' => 'Réservation confirmée',
        ];
        
        return $labels[$type] ?? $type;
    }

    /**
     * Suppression en masse des notifications sélectionnées.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        try {
            $deletedCount = Notification::whereIn('id', $request->notification_ids)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "$deletedCount notification(s) supprimée(s) avec succès.",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des notifications.'
            ], 500);
        }
    }
}