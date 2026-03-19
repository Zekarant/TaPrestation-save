<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Support\TableExistenceCache;
use App\Models\User;
use Carbon\Carbon;

class AdminSecurityController extends Controller
{
    /**
     * 26. Dashboard sécurité
     */
    public function dashboard()
    {
        $stats = [
            'failed_logins_today' => 0,
            'blocked_ips' => 0,
            'suspicious_activities' => 0,
            'active_sessions' => 0,
        ];

        try {
            if (TableExistenceCache::has('login_attempts')) {
                $stats['failed_logins_today'] = DB::table('login_attempts')
                    ->where('successful', false)
                    ->whereDate('created_at', today())
                    ->count();
            }
            if (TableExistenceCache::has('blocked_ips')) {
                $stats['blocked_ips'] = DB::table('blocked_ips')->where('blocked_until', '>', now())->count();
            }
            if (TableExistenceCache::has('security_logs')) {
                $stats['suspicious_activities'] = DB::table('security_logs')
                    ->where('severity', 'high')
                    ->whereDate('created_at', today())
                    ->count();
            }
            if (TableExistenceCache::has('sessions')) {
                $stats['active_sessions'] = DB::table('sessions')->count();
            }
        } catch (\Exception $e) {
            // Tables don't exist
        }

        $recentActivity = collect();
        $topBlockedIps = collect();
        
        try {
            if (TableExistenceCache::has('security_logs')) {
                $recentActivity = DB::table('security_logs')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
            if (TableExistenceCache::has('blocked_ips')) {
                $topBlockedIps = DB::table('blocked_ips')
                    ->orderBy('attempts', 'desc')
                    ->limit(10)
                    ->get();
            }
        } catch (\Exception $e) {
            // Tables don't exist
        }

        return view('admin.security.dashboard', compact('stats', 'recentActivity', 'topBlockedIps'));
    }

    /**
     * 27. Journal des connexions
     */
    public function loginLogs(Request $request)
    {
        $loginLogs = collect();

        try {
            if (TableExistenceCache::has('login_attempts')) {
                $query = DB::table('login_attempts')
                    ->orderBy('created_at', 'desc');

                if ($request->has('status')) {
                    $query->where('successful', $request->status === 'success');
                }

                if ($request->has('ip')) {
                    $query->where('ip_address', 'like', '%' . $request->ip . '%');
                }

                if ($request->has('user')) {
                    $query->where('email', 'like', '%' . $request->user . '%');
                }

                $loginLogs = $query->paginate(50);
            }
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        return view('admin.security.login-logs', compact('loginLogs'));
    }

    /**
     * 28. Gestion des IPs bloquées
     */
    public function blockedIps()
    {
        $blockedIps = collect();

        try {
            if (TableExistenceCache::has('blocked_ips')) {
                $blockedIps = DB::table('blocked_ips')
                    ->orderBy('blocked_until', 'desc')
                    ->paginate(50);
            }
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        return view('admin.security.blocked-ips', compact('blockedIps'));
    }

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string',
            'duration' => 'required|integer|min:1', // en heures
        ]);

        DB::table('blocked_ips')->updateOrInsert(
            ['ip_address' => $request->ip_address],
            [
                'reason' => $request->reason,
                'blocked_until' => Carbon::now()->addHours($request->duration),
                'blocked_by' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->logSecurityEvent('ip_blocked', "IP {$request->ip_address} bloquée: {$request->reason}");

        return back()->with('success', 'IP bloquée avec succès.');
    }

    public function unblockIp($ip)
    {
        DB::table('blocked_ips')->where('ip_address', $ip)->delete();
        $this->logSecurityEvent('ip_unblocked', "IP $ip débloquée");

        return back()->with('success', 'IP débloquée.');
    }

    /**
     * 29. Gestion des sessions actives
     */
    public function activeSessions()
    {
        $sessions = collect();

        try {
            if (TableExistenceCache::has('sessions')) {
                $sessions = DB::table('sessions')
                    ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
                    ->select('sessions.*', 'users.name', 'users.email')
                    ->orderBy('last_activity', 'desc')
                    ->paginate(50);
            }
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        return view('admin.security.sessions', compact('sessions'));
    }

    public function terminateSession($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        return back()->with('success', 'Session terminée.');
    }

    public function terminateAllSessions(Request $request)
    {
        $userId = $request->get('user_id');
        
        if ($userId) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        } else {
            DB::table('sessions')->where('user_id', '!=', auth()->user()->id)->delete();
        }

        return back()->with('success', 'Sessions terminées.');
    }

    /**
     * 30. Gestion des permissions et rôles
     */
    public function roles()
    {
        $roles = collect();
        $permissions = collect();

        try {
            if (TableExistenceCache::has('roles')) {
                $roles = DB::table('roles')->get();
            }
            if (TableExistenceCache::has('permissions')) {
                $permissions = DB::table('permissions')->get();
            }
        } catch (\Exception $e) {
            // Tables don't exist
        }

        return view('admin.security.roles', compact('roles', 'permissions'));
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
            'display_name' => 'required|string',
            'permissions' => 'array',
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        return back()->with('success', 'Rôle créé avec succès.');
    }

    public function updateRole(Request $request, $roleId)
    {
        DB::table('roles')->where('id', $roleId)->update([
            'display_name' => $request->display_name,
            'updated_at' => now(),
        ]);

        DB::table('role_has_permissions')->where('role_id', $roleId)->delete();

        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function deleteRole($roleId)
    {
        DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
        DB::table('model_has_roles')->where('role_id', $roleId)->delete();
        DB::table('roles')->where('id', $roleId)->delete();

        return back()->with('success', 'Rôle supprimé.');
    }

    /**
     * 31. Audit trail / Journal d'activité admin
     */
    public function auditLog(Request $request)
    {
        $auditLogs = collect();
        $actions = collect();
        $admins = collect();

        try {
            if (TableExistenceCache::has('audit_logs')) {
                $query = DB::table('audit_logs')
                    ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                    ->select('audit_logs.*', 'users.name as user_name', 'users.email')
                    ->orderBy('audit_logs.created_at', 'desc');

                if ($request->has('action') && $request->action) {
                    $query->where('action', $request->action);
                }

                if ($request->has('user_id') && $request->user_id) {
                    $query->where('audit_logs.user_id', $request->user_id);
                }

                if ($request->has('date_from') && $request->date_from) {
                    $query->whereDate('audit_logs.created_at', '>=', $request->date_from);
                }

                if ($request->has('date_to') && $request->date_to) {
                    $query->whereDate('audit_logs.created_at', '<=', $request->date_to);
                }

                $auditLogs = $query->paginate(50);
                $actions = DB::table('audit_logs')->distinct()->pluck('action');
            }
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        $admins = User::where('role', 'admin')->get();

        return view('admin.security.audit-log', compact('auditLogs', 'actions', 'admins'));
    }

    public function exportAuditLog(Request $request)
    {
        $logs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.name as user_name')
            ->orderBy('audit_logs.created_at', 'desc')
            ->get();

        $filename = 'audit_log_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Utilisateur', 'Action', 'Détails', 'IP']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at,
                    $log->user_name,
                    $log->action,
                    $log->details,
                    $log->ip_address,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Afficher le formulaire de changement de mot de passe admin
     */
    public function changePassword()
    {
        return view('admin.security.change-password');
    }

    /**
     * Mettre à jour le mot de passe admin
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = auth()->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->password);
        $user->save();

        // Logger l'événement de sécurité
        try {
            if (TableExistenceCache::has('security_logs')) {
                $this->logSecurityEvent('password_change', 'Changement de mot de passe administrateur', 'low');
            }
        } catch (\Exception $e) {
            // Ignorer si la table n'existe pas
        }

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }

    // Méthode helper pour logger les événements de sécurité
    private function logSecurityEvent($type, $message, $severity = 'medium')
    {
        DB::table('security_logs')->insert([
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'user_id' => auth()->user()->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
