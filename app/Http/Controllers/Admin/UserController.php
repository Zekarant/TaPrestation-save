<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filtrage par email
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        // Filtrage par rôle
        if ($request->has('role')) {
            if ($request->role === 'client') {
                $query->whereHas('client');
            } elseif ($request->role === 'prestataire') {
                $query->whereHas('prestataire');
            } elseif ($request->role === 'administrateur') {
                $query->where('role', 'administrateur');
            }
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $users = $query->with(['client', 'prestataire'])->paginate(15);
        
        return view('admin.users.index-modern', [
            'users' => $users,
        ]);
    }

    /**
     * Affiche les détails d'un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::with(['client', 'prestataire'])->findOrFail($id);
        
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Bloque ou débloque un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher le blocage de son propre compte
        if ($user->id === Auth::id()) {
            return redirect()->route('administrateur.users.index')
                ->with('error', 'Vous ne pouvez pas bloquer votre propre compte.');
        }
        
        $user->is_blocked = !$user->is_blocked;
        $user->save();
        
        $status = $user->is_blocked ? 'bloqué' : 'débloqué';
        
        return redirect()->route('administrateur.users.index')
            ->with('success', "L'utilisateur a été {$status} avec succès.");
    }

    /**
     * Supprime un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::id()) {
            return redirect()->route('administrateur.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();

        return response()->json(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    }

    /**
     * Bulk block users
     */
    public function bulkBlock(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        
        // Prevent blocking current user
        $currentUserId = auth()->id();
        if (in_array($currentUserId, $userIds)) {
            return response()->json([
                'success' => false, 
                'message' => 'Vous ne pouvez pas vous bloquer vous-même'
            ]);
        }

        User::whereIn('id', $userIds)->update(['is_blocked' => true]);

        return response()->json([
            'success' => true, 
            'message' => count($userIds) . ' utilisateur(s) bloqué(s) avec succès'
        ]);
    }

    /**
     * Bulk unblock users
     */
    public function bulkUnblock(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        User::whereIn('id', $userIds)->update(['is_blocked' => false]);

        return response()->json([
            'success' => true, 
            'message' => count($userIds) . ' utilisateur(s) débloqué(s) avec succès'
        ]);
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        
        // Prevent deleting current user
        $currentUserId = auth()->id();
        if (in_array($currentUserId, $userIds)) {
            return response()->json([
                'success' => false, 
                'message' => 'Vous ne pouvez pas vous supprimer vous-même'
            ]);
        }

        User::whereIn('id', $userIds)->delete();

        return response()->json([
            'success' => true, 
            'message' => count($userIds) . ' utilisateur(s) supprimé(s) avec succès'
        ]);
    }

    /**
     * Export users to CSV
     */
    public function export()
    {
        $users = User::with('roles')->get();
        
        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Nom',
                'Email',
                'Rôle',
                'Statut',
                'Date de création',
                'Dernière connexion'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->is_blocked ? 'Bloqué' : 'Actif',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}