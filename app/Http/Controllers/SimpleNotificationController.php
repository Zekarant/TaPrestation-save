<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\SimpleNotification;

class SimpleNotificationController extends Controller
{
    /**
     * Show the notification form
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        $users = User::all();
        return view('notifications.simple_form', compact('users'));
    }

    /**
     * Send a simple notification to a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:100',
        ]);

        $user = User::findOrFail($request->user_id);
        
        $notification = new SimpleNotification(
            $request->message,
            $request->action_url,
            $request->action_text
        );
        
        $user->notify($notification);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!'
        ]);
    }
    
    /**
     * Send a notification to all users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:100',
        ]);
        
        $users = User::all();
        
        foreach ($users as $user) {
            $notification = new SimpleNotification(
                $request->message,
                $request->action_url,
                $request->action_text
            );
            
            $user->notify($notification);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notifications sent to ' . $users->count() . ' users!'
        ]);
    }
}