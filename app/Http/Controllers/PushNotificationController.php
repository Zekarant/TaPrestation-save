<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            
            // Store subscription in user's record
            $subscription = [
                'endpoint' => $request->endpoint,
                'keys' => [
                    'p256dh' => $request->keys['p256dh'],
                    'auth' => $request->keys['auth'],
                ],
                'created_at' => now()->toISOString(),
            ];

            // Get existing subscriptions or create new array
            $existingSubscriptions = json_decode($user->push_subscriptions ?? '[]', true);
            
            // Check if this endpoint already exists
            $endpointExists = false;
            foreach ($existingSubscriptions as $key => $sub) {
                if ($sub['endpoint'] === $request->endpoint) {
                    $existingSubscriptions[$key] = $subscription;
                    $endpointExists = true;
                    break;
                }
            }
            
            if (!$endpointExists) {
                $existingSubscriptions[] = $subscription;
            }
            
            // Keep only last 5 subscriptions (for multiple devices)
            $existingSubscriptions = array_slice($existingSubscriptions, -5);
            
            $user->push_subscriptions = json_encode($existingSubscriptions);
            $user->push_enabled = true;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Push notifications activées avec succès!'
            ]);
        } catch (\Exception $e) {
            Log::error('Push subscribe error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation des notifications push.'
            ], 500);
        }
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($request->has('endpoint')) {
                // Remove specific subscription
                $existingSubscriptions = json_decode($user->push_subscriptions ?? '[]', true);
                $existingSubscriptions = array_filter($existingSubscriptions, function($sub) use ($request) {
                    return $sub['endpoint'] !== $request->endpoint;
                });
                $user->push_subscriptions = json_encode(array_values($existingSubscriptions));
                
                if (empty($existingSubscriptions)) {
                    $user->push_enabled = false;
                }
            } else {
                // Remove all subscriptions
                $user->push_subscriptions = null;
                $user->push_enabled = false;
            }
            
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Push notifications désactivées.'
            ]);
        } catch (\Exception $e) {
            Log::error('Push unsubscribe error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation.'
            ], 500);
        }
    }
}
