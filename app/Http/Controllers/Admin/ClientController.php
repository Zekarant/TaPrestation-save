<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Affiche la liste des clients.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Client::query()
            ->whereHas('user')
            ->with('user')
            ->withCount(['clientRequests', 'reviews']);
        
        // Filtrage par nom
        if ($request->filled('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        // Filtrage par email
        if ($request->filled('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        
        // Filtrage par localisation
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filtrage par statut
        if ($request->filled('status')) {
            $status = $request->status;

            $query->whereHas('user', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('is_blocked', false);
                } elseif ($status === 'blocked') {
                    $q->where('is_blocked', true);
                }
            });
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortField === 'name' || $sortField === 'email') {
            $query->join('users', 'clients.user_id', '=', 'users.id')
                  ->orderBy('users.' . $sortField, $sortDirection)
                  ->select('clients.*');
        } elseif ($sortField === 'requests_count') {
            $query->orderBy('client_requests_count', $sortDirection);
        } elseif ($sortField === 'reviews_count') {
            $query->orderBy('reviews_count', $sortDirection);
        } else {
            $query->orderBy('clients.created_at', $sortDirection);
        }
        
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $clients = $query->paginate($perPage)->withQueryString();
        $activeClients = $clients->getCollection()->filter(function ($client) {
            return !$client->user?->is_blocked;
        })->count();
        $totalRequests = $clients->getCollection()->sum('client_requests_count');
        
        return view('admin.clients.index-modern', [
            'clients' => $clients,
            'activeClients' => $activeClients,
            'totalRequests' => $totalRequests,
        ]);
    }

    /**
     * Affiche les détails d'un client.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $client = Client::query()
            ->whereHas('user')
            ->with(['user', 'bookings.service', 'reviews', 'follows'])
            ->findOrFail($id);
        
        return view('admin.clients.show', [
            'client' => $client,
        ]);
    }

    /**
     * Bascule le statut de blocage d'un client.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function toggleBlock($id)
    {
        try {
            $client = Client::whereHas('user')->findOrFail($id);
            $user = User::findOrFail($client->user_id);
            
            $user->is_blocked = !$user->is_blocked;
            $user->blocked_at = $user->is_blocked ? now() : null;
            $user->save();
            
            $status = $user->is_blocked ? 'bloqué' : 'débloqué';
            $message = "Le client a été {$status} avec succès.";
            
            // Si c'est une requête AJAX, retourner du JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_blocked' => $user->is_blocked
                ]);
            }
            
            // Sinon, redirection classique
            return redirect()->route('administrateur.clients.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la modification du statut'
                ], 500);
            }
            
            return redirect()->route('administrateur.clients.index')
                ->with('error', 'Une erreur est survenue lors de la modification du statut');
        }
    }

    /**
     * Supprime un client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $client = Client::whereHas('user')->findOrFail($id);
            $user = User::findOrFail($client->user_id);
            
            // Archiver les infos Stripe avant suppression (éviter doublons si réinscription)
            \App\Models\DeletedStripeAccount::archiveFromUser($user);
            
            // Suppression de l'utilisateur (cascade sur le client)
            $user->delete();
            
            // Si c'est une requête AJAX, retourner du JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Le client a été supprimé avec succès.'
                ]);
            }
            
            return redirect()->route('administrateur.clients.index')
                ->with('success', 'Le client a été supprimé avec succès.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('administrateur.clients.index')
                ->with('error', 'Erreur lors de la suppression du client.');
        }
    }
    
    /**
     * Exporte les clients au format CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Client::query()
            ->whereHas('user')
            ->with(['user', 'clientRequests', 'reviews'])
            ->withCount(['clientRequests', 'reviews']);
        
        // Appliquer les mêmes filtres que dans la méthode index
        if ($request->filled('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        if ($request->filled('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('user', function($q) {
                    $q->where('is_blocked', false);
                });
            } elseif ($request->status === 'blocked') {
                $query->whereHas('user', function($q) {
                    $q->where('is_blocked', true);
                });
            }
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        if ($sortField === 'name' || $sortField === 'email') {
            $query->join('users', 'clients.user_id', '=', 'users.id')
                  ->orderBy('users.' . $sortField, $sortDirection)
                  ->select('clients.*');
        } elseif ($sortField === 'requests_count') {
            $query->orderBy('client_requests_count', $sortDirection);
        } elseif ($sortField === 'reviews_count') {
            $query->orderBy('reviews_count', $sortDirection);
        } else {
            $query->orderBy('clients.created_at', $sortDirection);
        }
        
        $clients = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clients.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Email', 'Téléphone', 'Demandes', 'Avis', 'Statut', 'Date d\'inscription']);
            
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->user->name,
                    $client->user->email,
                    $client->phone ?? 'Non renseigné',
                    $client->client_requests_count,
                    $client->reviews_count,
                    $client->user->is_blocked ? 'Bloqué' : 'Actif',
                    $client->created_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }



    /**
     * Bulk unblock clients
     */
    public function bulkUnblock(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:clients,id'
            ]);

            $clients = Client::whereIn('id', $request->ids)->with('user')->get();
            $count = 0;

            foreach ($clients as $client) {
                if (!$client->user) {
                    continue;
                }

                if ($client->user->is_blocked) {
                    $client->user->is_blocked = false;
                    $client->user->blocked_at = null;
                    $client->user->save();
                    $count++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} client(s) débloqué(s) avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du déblocage'
            ], 500);
        }
    }

    /**
     * Bulk block clients
     */
    public function bulkBlock(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:clients,id'
            ]);

            $clients = Client::whereIn('id', $request->ids)->with('user')->get();
            $count = 0;

            foreach ($clients as $client) {
                if (!$client->user) {
                    continue;
                }

                // Ne pas bloquer l'utilisateur connecté
                if ($client->user_id !== auth()->id() && !$client->user->is_blocked) {
                    $client->user->is_blocked = true;
                    $client->user->blocked_at = now();
                    $client->user->save();
                    $count++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} client(s) bloqué(s) avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du blocage'
            ], 500);
        }
    }

    /**
     * Bulk delete clients
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:clients,id'
            ]);

            $clients = Client::whereIn('id', $request->ids)->with('user')->get();
            $count = 0;

            foreach ($clients as $client) {
                // Ne pas supprimer l'utilisateur connecté
                if ($client->user_id !== auth()->id()) {
                    if ($client->user) {
                        $client->user->delete();
                    } else {
                        $client->delete();
                    }
                    $count++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} client(s) supprimé(s) avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }
}
