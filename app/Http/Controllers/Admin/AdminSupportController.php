<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminSupportController extends Controller
{
    /**
     * 46. Gestion des tickets de support
     */
    public function tickets(Request $request)
    {
        $query = DB::table('support_tickets')
            ->leftJoin('users', 'support_tickets.user_id', '=', 'users.id')
            ->select('support_tickets.*', 'users.name as user_name', 'users.email as user_email');

        // Filtres
        if ($request->filled('status')) {
            $query->where('support_tickets.status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('support_tickets.priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('support_tickets.category', $request->category);
        }
        if ($request->filled('assigned_to')) {
            $query->where('support_tickets.assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('support_tickets.subject', 'like', "%{$search}%")
                    ->orWhere('support_tickets.ticket_number', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderByRaw("FIELD(status, 'open', 'in_progress', 'pending', 'resolved', 'closed')")
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiques
        $stats = [
            'total' => DB::table('support_tickets')->count(),
            'open' => DB::table('support_tickets')->where('status', 'open')->count(),
            'in_progress' => DB::table('support_tickets')->where('status', 'in_progress')->count(),
            'pending' => DB::table('support_tickets')->where('status', 'pending')->count(),
            'resolved_today' => DB::table('support_tickets')
                ->where('status', 'resolved')
                ->whereDate('resolved_at', Carbon::today())
                ->count(),
        ];

        // Admins pour assignation
        $admins = DB::table('users')->where('role', 'admin')->get();

        return view('admin.support.tickets', compact('tickets', 'stats', 'admins'));
    }

    public function ticketDetails($id)
    {
        $ticket = DB::table('support_tickets')
            ->leftJoin('users', 'support_tickets.user_id', '=', 'users.id')
            ->leftJoin('users as assigned', 'support_tickets.assigned_to', '=', 'assigned.id')
            ->select(
                'support_tickets.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'assigned.name as assigned_name'
            )
            ->where('support_tickets.id', $id)
            ->first();

        if (!$ticket) {
            return redirect()->route('admin.support.tickets')->with('error', 'Ticket introuvable.');
        }

        // Messages du ticket
        $messages = DB::table('support_ticket_messages')
            ->leftJoin('users', 'support_ticket_messages.user_id', '=', 'users.id')
            ->where('support_ticket_messages.ticket_id', $id)
            ->select('support_ticket_messages.*', 'users.name as author_name', 'users.role as author_role')
            ->orderBy('created_at', 'asc')
            ->get();

        // Historique
        $history = DB::table('support_ticket_history')
            ->leftJoin('users', 'support_ticket_history.user_id', '=', 'users.id')
            ->where('support_ticket_history.ticket_id', $id)
            ->select('support_ticket_history.*', 'users.name as user_name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Admins pour assignation
        $admins = DB::table('users')->where('role', 'admin')->get();

        return view('admin.support.ticket-details', compact('ticket', 'messages', 'history', 'admins'));
    }

    public function replyTicket(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        DB::table('support_ticket_messages')->insert([
            'ticket_id' => $id,
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'is_internal' => $request->has('is_internal'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mettre à jour le statut si nécessaire
        if ($request->new_status) {
            $this->updateTicketStatus($id, $request->new_status);
        }

        DB::table('support_tickets')->where('id', $id)->update([
            'last_reply_at' => now(),
            'last_reply_by' => auth()->user()->id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Réponse envoyée.');
    }

    public function updateTicket(Request $request, $id)
    {
        $oldTicket = DB::table('support_tickets')->where('id', $id)->first();

        $data = [
            'status' => $request->status,
            'priority' => $request->priority,
            'category' => $request->category,
            'assigned_to' => $request->assigned_to,
            'updated_at' => now(),
        ];

        if ($request->status === 'resolved' && $oldTicket->status !== 'resolved') {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->user()->id;
        }

        DB::table('support_tickets')->where('id', $id)->update($data);

        // Historique
        $changes = [];
        if ($oldTicket->status !== $request->status) {
            $changes[] = "Statut: {$oldTicket->status} → {$request->status}";
        }
        if ($oldTicket->priority !== $request->priority) {
            $changes[] = "Priorité: {$oldTicket->priority} → {$request->priority}";
        }
        if ($oldTicket->assigned_to !== $request->assigned_to) {
            $changes[] = "Assignation modifiée";
        }

        if (!empty($changes)) {
            DB::table('support_ticket_history')->insert([
                'ticket_id' => $id,
                'user_id' => auth()->user()->id,
                'action' => 'update',
                'details' => implode(', ', $changes),
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Ticket mis à jour.');
    }

    public function closeTicket($id)
    {
        DB::table('support_tickets')->where('id', $id)->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->user()->id,
            'updated_at' => now(),
        ]);

        DB::table('support_ticket_history')->insert([
            'ticket_id' => $id,
            'user_id' => auth()->user()->id,
            'action' => 'close',
            'details' => 'Ticket fermé',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Ticket fermé.');
    }

    /**
     * 47. Gestion des messages de contact
     */
    public function contactMessages(Request $request)
    {
        $query = DB::table('contact_messages');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => DB::table('contact_messages')->count(),
            'unread' => DB::table('contact_messages')->where('status', 'unread')->count(),
            'pending' => DB::table('contact_messages')->where('status', 'pending')->count(),
        ];

        return view('admin.support.contact-messages', compact('messages', 'stats'));
    }

    public function viewContactMessage($id)
    {
        $message = DB::table('contact_messages')->where('id', $id)->first();

        if ($message && $message->status === 'unread') {
            DB::table('contact_messages')->where('id', $id)->update([
                'status' => 'read',
                'read_at' => now(),
                'read_by' => auth()->user()->id,
            ]);
        }

        return view('admin.support.contact-message-details', compact('message'));
    }

    public function replyContactMessage(Request $request, $id)
    {
        $message = DB::table('contact_messages')->where('id', $id)->first();

        $request->validate([
            'reply' => 'required|string',
        ]);

        // Envoyer l'email de réponse
        // Mail::to($message->email)->send(new ContactReplyMail($message, $request->reply));

        DB::table('contact_messages')->where('id', $id)->update([
            'status' => 'replied',
            'reply' => $request->reply,
            'replied_at' => now(),
            'replied_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'Réponse envoyée.');
    }

    public function deleteContactMessage($id)
    {
        DB::table('contact_messages')->where('id', $id)->delete();
        return back()->with('success', 'Message supprimé.');
    }

    /**
     * 48. Gestion des litiges
     */
    public function disputes(Request $request)
    {
        $query = DB::table('disputes')
            ->leftJoin('users as client', 'disputes.client_id', '=', 'client.id')
            ->leftJoin('users as prestataire', 'disputes.prestataire_id', '=', 'prestataire.id')
            ->leftJoin('bookings', 'disputes.booking_id', '=', 'bookings.id')
            ->select(
                'disputes.*',
                'client.name as client_name',
                'prestataire.name as prestataire_name',
                'bookings.total_price as booking_amount'
            );

        if ($request->filled('status')) {
            $query->where('disputes.status', $request->status);
        }

        $disputes = $query->orderBy('disputes.created_at', 'desc')->paginate(20);

        $stats = [
            'total' => DB::table('disputes')->count(),
            'open' => DB::table('disputes')->where('status', 'open')->count(),
            'investigating' => DB::table('disputes')->where('status', 'investigating')->count(),
            'resolved_this_month' => DB::table('disputes')
                ->where('status', 'resolved')
                ->where('resolved_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
        ];

        return view('admin.support.disputes', compact('disputes', 'stats'));
    }

    public function disputeDetails($id)
    {
        $dispute = DB::table('disputes')
            ->leftJoin('users as client', 'disputes.client_id', '=', 'client.id')
            ->leftJoin('users as prestataire', 'disputes.prestataire_id', '=', 'prestataire.id')
            ->leftJoin('bookings', 'disputes.booking_id', '=', 'bookings.id')
            ->select(
                'disputes.*',
                'client.name as client_name',
                'client.email as client_email',
                'prestataire.name as prestataire_name',
                'prestataire.email as prestataire_email',
                'bookings.*'
            )
            ->where('disputes.id', $id)
            ->first();

        // Messages du litige
        $messages = DB::table('dispute_messages')
            ->leftJoin('users', 'dispute_messages.user_id', '=', 'users.id')
            ->where('dispute_messages.dispute_id', $id)
            ->select('dispute_messages.*', 'users.name as author_name', 'users.role as author_role')
            ->orderBy('created_at', 'asc')
            ->get();

        // Preuves
        $evidence = DB::table('dispute_evidence')
            ->where('dispute_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.support.dispute-details', compact('dispute', 'messages', 'evidence'));
    }

    public function updateDispute(Request $request, $id)
    {
        $data = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'updated_at' => now(),
        ];

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->user()->id;
            $data['resolution'] = $request->resolution;
        }

        DB::table('disputes')->where('id', $id)->update($data);

        // Si remboursement
        if ($request->status === 'resolved' && $request->refund_amount > 0) {
            // Créer le remboursement
            $dispute = DB::table('disputes')->where('id', $id)->first();
            DB::table('refunds')->insert([
                'transaction_id' => $dispute->transaction_id ?? null,
                'booking_id' => $dispute->booking_id,
                'user_id' => $dispute->client_id,
                'amount' => $request->refund_amount,
                'reason' => 'Résolution litige #' . $id,
                'status' => 'pending',
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Litige mis à jour.');
    }

    public function replyDispute(Request $request, $id)
    {
        DB::table('dispute_messages')->insert([
            'dispute_id' => $id,
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'is_admin' => true,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * 49. Centre d'aide - Articles
     */
    public function helpArticles(Request $request)
    {
        $query = DB::table('help_articles')
            ->leftJoin('help_categories', 'help_articles.category_id', '=', 'help_categories.id')
            ->select('help_articles.*', 'help_categories.name as category_name');

        if ($request->filled('category_id')) {
            $query->where('help_articles.category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('help_articles.title', 'like', "%{$search}%")
                    ->orWhere('help_articles.content', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderBy('help_articles.order')->paginate(20);
        $categories = DB::table('help_categories')->orderBy('order')->get();

        return view('admin.support.help-articles', compact('articles', 'categories'));
    }

    public function createHelpArticle()
    {
        $categories = DB::table('help_categories')->orderBy('name')->get();
        return view('admin.support.help-articles-create', compact('categories'));
    }

    public function storeHelpArticle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:help_categories,id',
        ]);

        $maxOrder = DB::table('help_articles')->where('category_id', $request->category_id)->max('order') ?? 0;

        DB::table('help_articles')->insert([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'order' => $maxOrder + 1,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.support.help-articles')->with('success', 'Article créé.');
    }

    public function editHelpArticle($id)
    {
        $article = DB::table('help_articles')->where('id', $id)->first();
        $categories = DB::table('help_categories')->orderBy('name')->get();
        return view('admin.support.help-articles-edit', compact('article', 'categories'));
    }

    public function updateHelpArticle(Request $request, $id)
    {
        DB::table('help_articles')->where('id', $id)->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Article mis à jour.');
    }

    public function deleteHelpArticle($id)
    {
        DB::table('help_articles')->where('id', $id)->delete();
        return back()->with('success', 'Article supprimé.');
    }

    /**
     * 50. Statistiques du support
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Statistiques générales
        $stats = [
            'total_tickets' => DB::table('support_tickets')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'resolved_tickets' => DB::table('support_tickets')
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'avg_resolution_time' => DB::table('support_tickets')
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
            'total_disputes' => DB::table('disputes')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'resolved_disputes' => DB::table('disputes')
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'contact_messages' => DB::table('contact_messages')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
        ];

        // Tickets par catégorie
        $byCategory = DB::table('support_tickets')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        // Tickets par statut
        $byStatus = DB::table('support_tickets')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Performance par agent
        $agentPerformance = DB::table('support_tickets')
            ->join('users', 'support_tickets.assigned_to', '=', 'users.id')
            ->whereBetween('support_tickets.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('users.name, COUNT(*) as total_tickets, 
                SUM(CASE WHEN support_tickets.status = "resolved" THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN support_tickets.status = "resolved" THEN TIMESTAMPDIFF(HOUR, support_tickets.created_at, support_tickets.resolved_at) ELSE NULL END) as avg_resolution_hours')
            ->groupBy('support_tickets.assigned_to', 'users.name')
            ->get();

        // Évolution journalière
        $dailyTrend = DB::table('support_tickets')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.support.statistics', compact('stats', 'byCategory', 'byStatus', 'agentPerformance', 'dailyTrend', 'dateFrom', 'dateTo'));
    }

    // Méthodes privées
    private function updateTicketStatus($ticketId, $status)
    {
        $data = ['status' => $status, 'updated_at' => now()];

        if ($status === 'resolved') {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->user()->id;
        }

        DB::table('support_tickets')->where('id', $ticketId)->update($data);

        DB::table('support_ticket_history')->insert([
            'ticket_id' => $ticketId,
            'user_id' => auth()->user()->id,
            'action' => 'status_change',
            'details' => "Statut changé en: {$status}",
            'created_at' => now(),
        ]);
    }
}
