<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Affiche la liste des messages.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'receiver']);
        
        // Filtrage par type de message
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filtrage par statut de lecture
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } else {
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
        
        // Filtrage par expéditeur
        if ($request->filled('sender')) {
            $query->whereHas('sender', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->sender . '%')
                  ->orWhere('email', 'like', '%' . $request->sender . '%');
            });
        }
        
        // Filtrage par destinataire
        if ($request->filled('receiver')) {
            $query->whereHas('receiver', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->receiver . '%')
                  ->orWhere('email', 'like', '%' . $request->receiver . '%');
            });
        }
        
        // Recherche dans le contenu
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%')
                  ->orWhere('subject', 'like', '%' . $search . '%');
            });
        }
        
        // Filtrage par signalement
        if ($request->filled('reported')) {
            if ($request->reported === 'yes') {
                $query->where('is_reported', true);
            } else {
                $query->where('is_reported', false);
            }
        }
        
        $messages = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => Message::count(),
            'unread' => Message::whereNull('read_at')->count(),
            'reported' => Message::where('is_reported', true)->count(),
            'today' => Message::whereDate('created_at', today())->count(),
            'this_week' => Message::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'avg_daily' => $this->calculateAverageDailyMessages(),
            'read_rate' => $this->calculateReadRate(),
            'avg_response_time' => $this->calculateAverageResponseTime(),
        ];
        
        return view('admin.messages.index-modern', [
            'messages' => $messages,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Affiche les détails d'un message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $message = Message::with(['sender', 'receiver'])->findOrFail($id);
        
        // Marquer comme lu par l'admin si pas encore lu
        if (!$message->admin_read_at) {
            $message->admin_read_at = now();
            $message->save();
        }
        
        // Récupérer la conversation complète
        $conversation = Message::where(function($query) use ($message) {
            $query->where('sender_id', $message->sender_id)
                  ->where('receiver_id', $message->receiver_id);
        })->orWhere(function($query) use ($message) {
            $query->where('sender_id', $message->receiver_id)
                  ->where('receiver_id', $message->sender_id);
        })->orderBy('created_at', 'asc')->get();
        
        return view('admin.messages.show', [
            'message' => $message,
            'conversation' => $conversation,
        ]);
    }
    
    /**
     * Modère un message (approuve ou supprime).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moderate(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,delete,hide',
            'moderation_reason' => 'nullable|string|max:500',
        ]);
        
        $message = Message::findOrFail($id);
        
        switch ($request->action) {
            case 'approve':
                $message->is_moderated = true;
                $message->moderation_status = 'approved';
                $message->is_reported = false;
                break;
                
            case 'hide':
                $message->is_moderated = true;
                $message->moderation_status = 'hidden';
                $message->is_hidden = true;
                break;
                
            case 'delete':
                $message->delete();
                return redirect()->route('administrateur.messages.index')
                    ->with('success', 'Le message a été supprimé avec succès.');
        }
        
        $message->moderation_reason = $request->moderation_reason;
        $message->moderated_by = Auth::id();
        $message->moderated_at = now();
        $message->save();
        
        $actionText = [
            'approve' => 'approuvé',
            'hide' => 'masqué',
        ];
        
        return redirect()->route('administrateur.messages.show', $message->id)
            ->with('success', "Le message a été {$actionText[$request->action]} avec succès.");
    }
    
    /**
     * Marque un message comme lu/non lu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleRead(Request $request, $id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->read_at) {
            $message->read_at = null;
            $status = 'marqué comme non lu';
        } else {
            $message->read_at = now();
            $status = 'marqué comme lu';
        }
        
        $message->save();
        
        return redirect()->back()
            ->with('success', "Le message a été {$status} avec succès.");
    }
    
    /**
     * Supprime plusieurs messages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        $count = Message::whereIn('id', $request->message_ids)->delete();
        
        return redirect()->route('administrateur.messages.index')
            ->with('success', "{$count} message(s) supprimé(s) avec succès.");
    }
    
    /**
     * Modère plusieurs messages en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkModerate(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
            'action' => 'required|in:approved,hidden,pending',
        ]);
        
        $messages = Message::whereIn('id', $request->message_ids)->get();
        $count = 0;
        
        foreach ($messages as $message) {
            switch ($request->action) {
                case 'approved':
                    $message->is_moderated = true;
                    $message->moderation_status = 'approved';
                    $message->is_reported = false;
                    break;
                    
                case 'hidden':
                    $message->is_moderated = true;
                    $message->moderation_status = 'hidden';
                    $message->is_hidden = true;
                    break;
                    
                case 'pending':
                    $message->is_moderated = false;
                    $message->moderation_status = 'pending';
                    break;
            }
            
            $message->moderated_by = Auth::id();
            $message->moderated_at = now();
            $message->save();
            $count++;
        }
        
        $actionText = [
            'approved' => 'approuvés',
            'hidden' => 'masqués',
            'pending' => 'marqués en attente',
        ];
        
        return redirect()->route('administrateur.messages.index')
            ->with('success', "{$count} message(s) {$actionText[$request->action]} avec succès.");
    }
    
    /**
     * Marque plusieurs messages comme lus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkMarkAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        $count = Message::whereIn('id', $request->message_ids)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return redirect()->route('administrateur.messages.index')
            ->with('success', "{$count} message(s) marqué(s) comme lu(s) avec succès.");
    }
    
    /**
     * Export des messages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = Message::with(['sender', 'receiver']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $messages = $query->get();
        
        $filename = 'messages_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Expéditeur',
                'Email Expéditeur',
                'Destinataire',
                'Email Destinataire',
                'Sujet',
                'Contenu',
                'Type',
                'Lu',
                'Signalé',
                'Date Création'
            ]);
            
            // Données
            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->sender->name ?? 'N/A',
                    $message->sender->email ?? 'N/A',
                    $message->receiver->name ?? 'N/A',
                    $message->receiver->email ?? 'N/A',
                    $message->subject ?? 'N/A',
                    strip_tags($message->content),
                    $message->type ?? 'message',
                    $message->read_at ? 'Oui' : 'Non',
                    $message->is_reported ? 'Oui' : 'Non',
                    $message->created_at
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Affiche les statistiques des messages.
     *
     * @return \Illuminate\View\View
     */
    public function analytics()
    {
        // Statistiques par mois (6 derniers mois)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'messages' => Message::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'reported' => Message::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('is_reported', true)
                    ->count(),
            ];
        }
        
        // Top utilisateurs par nombre de messages envoyés
        $topSenders = User::withCount(['sentMessages' => function($query) {
            $query->where('created_at', '>=', now()->subMonth());
        }])
            ->orderBy('sent_messages_count', 'desc')
            ->take(10)
            ->get();
        
        // Statistiques par type de message
        $messageTypes = Message::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        
        // Statistiques par heure de la journée
        $hourlyStats = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyStats[$hour] = Message::whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                ->whereTime('created_at', '<', sprintf('%02d:00:00', ($hour + 1) % 24))
                ->count();
        }
        
        // Taux de lecture
        $readRate = $this->calculateReadRate();
        
        return view('admin.messages.analytics', [
            'monthlyStats' => $monthlyStats,
            'topSenders' => $topSenders,
            'messageTypes' => $messageTypes,
            'hourlyStats' => $hourlyStats,
            'readRate' => $readRate,
        ]);
    }
    
    /**
     * Nettoie les anciens messages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
            'confirm' => 'required|accepted',
        ]);
        
        $cutoffDate = now()->subDays($request->days);
        $count = Message::where('created_at', '<', $cutoffDate)
            ->where('is_reported', false)
            ->delete();
        
        return redirect()->route('administrateur.messages.index')
            ->with('success', "{$count} ancien(s) message(s) supprimé(s) avec succès.");
    }
    
    /**
     * Calcule la moyenne quotidienne des messages.
     *
     * @return float
     */
    private function calculateAverageDailyMessages()
    {
        $days = 30; // Derniers 30 jours
        $totalMessages = Message::where('created_at', '>=', now()->subDays($days))->count();
        
        return round($totalMessages / $days, 1);
    }
    
    /**
     * Calcule le taux de lecture des messages.
     *
     * @return float
     */
    private function calculateReadRate()
    {
        $totalMessages = Message::count();
        $readMessages = Message::whereNotNull('read_at')->count();
        
        return $totalMessages > 0 ? round(($readMessages / $totalMessages) * 100, 1) : 0;
    }
    
    /**
     * Calcule le temps de réponse moyen en heures.
     *
     * @return float
     */
    private function calculateAverageResponseTime()
    {
        $avgMinutes = Message::whereNotNull('read_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, read_at)) as avg_response')
            ->value('avg_response');
        
        return $avgMinutes ? round($avgMinutes / 60, 1) : 0;
    }
}