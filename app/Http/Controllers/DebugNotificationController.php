<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebugNotificationController extends Controller
{
    /**
     * Display a detailed debugging page for notifications.
     *
     * @return View
     */
    public function debug()
    {
        $user = Auth::user();
        
        if (!$user) {
            return view('debug.notifications', [
                'error' => 'User not authenticated',
                'user' => null,
                'notifications' => null,
                'laravel_notifications' => null,
                'database_notifications' => null
            ]);
        }
        
        // Get custom model notifications
        $notifications = Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Get Laravel standard notifications
        $laravel_notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Get direct database notifications
        $database_notifications = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('debug.notifications', [
            'user' => $user,
            'notifications' => $notifications,
            'laravel_notifications' => $laravel_notifications,
            'database_notifications' => $database_notifications
        ]);
    }
}