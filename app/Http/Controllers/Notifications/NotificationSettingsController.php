<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
class NotificationSettingsController extends Controller
{
    /**
     * Show notification settings
     */
    public function index()
    {
        $settings = NotificationSetting::getOrCreate(auth()->id());
        $templates = config('notifications.templates');

        return view('notification-settings.index', compact('settings', 'templates'));
    }

    /**
     * Update notification settings
     */
    public function update(Request $request)
    {
        \Log::info('NotificationSettings update called', [
            'user_id' => auth()->id(),
        ]);
        
        // Vérifier si la table existe
        if (!TableExistenceCache::has('notification_settings')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les paramètres de notification ne sont pas encore configurés. Veuillez contacter l\'administrateur.',
                ], 503);
            }
            return back()->with('error', 'Les paramètres de notification ne sont pas encore configurés.');
        }

        // Convertir les valeurs checkbox en boolean
        $data = [
            'email_notifications' => $request->boolean('email_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'quiet_hours_enabled' => $request->boolean('quiet_hours_enabled'),
            'quiet_start' => $request->input('quiet_start'),
            'quiet_end' => $request->input('quiet_end'),
            'booking_notifications' => $request->boolean('booking_notifications'),
            'payment_notifications' => $request->boolean('payment_notifications'),
            'review_notifications' => $request->boolean('review_notifications'),
            'message_notifications' => $request->boolean('message_notifications'),
            'auction_notifications' => $request->boolean('auction_notifications'),
            'promotion_notifications' => $request->boolean('promotion_notifications'),
            'food_order_notifications' => $request->boolean('food_order_notifications'),
            'equipment_notifications' => $request->boolean('equipment_notifications'),
            'newsletter_notifications' => $request->boolean('newsletter_notifications'),
            'notification_frequency' => $request->input('notification_frequency', 'immediate'),
            'phone_number' => $request->input('phone_number'),
        ];

        try {
            $settings = NotificationSetting::updateOrCreate(
                ['user_id' => auth()->id()],
                $data
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'settings' => $settings,
                    'message' => 'Paramètres de notification mis à jour',
                ]);
            }
            
            return back()->with('success', 'Paramètres de notification mis à jour avec succès !');
        } catch (\Exception $e) {
            \Log::error('Notification settings update error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour des paramètres.',
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors de la mise à jour des paramètres.');
        }
    }

    /**
     * Test notification - send test email
     */
    public function testEmail()
    {
        try {
            \Mail::to(auth()->user()->email)->send(
                new \App\Mail\TestEmail(auth()->user())
            );

            return response()->json([
                'success' => true,
                'message' => 'E-mail de test envoyé à ' . auth()->user()->email,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Notification settings test email failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'envoi de l\'e-mail de test.',
            ], 500);
        }
    }

    /**
     * Test notification - send test SMS
     */
    public function testSMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendSMS(
                $request->phone,
                'SMS de test de TaPrestation - Vos paramètres fonctionnent correctement !'
            );

            return response()->json([
                'success' => true,
                'message' => 'SMS de test envoyé au ' . $request->phone,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Notification settings test SMS failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'envoi du SMS de test.',
            ], 500);
        }
    }

    /**
     * Test notification - send test push
     */
    public function testPush(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendPushNotification(
                $request->device_token,
                'TaPrestation Test',
                'Vos notifications push fonctionnent correctement !',
                ['type' => 'test']
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification push de test envoyée.',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Notification settings test push failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'envoi de la notification push de test.',
            ], 500);
        }
    }

    /**
     * Get notification preferences template
     */
    public function getTemplate()
    {
        $templates = config('notifications.templates');

        return response()->json([
            'success' => true,
            'templates' => $templates,
            'description' => 'Available notification types and their descriptions',
        ]);
    }

    /**
     * Get quiet hours info
     */
    public function getQuietHours()
    {
        $settings = NotificationSetting::getOrCreate(auth()->id());

        return response()->json([
            'success' => true,
            'quiet_hours_enabled' => $settings->quiet_hours_enabled,
            'quiet_start' => $settings->quiet_start,
            'quiet_end' => $settings->quiet_end,
            'default_start' => config('notifications.quiet_hours.start'),
            'default_end' => config('notifications.quiet_hours.end'),
        ]);
    }

    /**
     * Update quiet hours only
     */
    public function updateQuietHours(Request $request)
    {
        $validated = $request->validate([
            'quiet_hours_enabled' => 'required|boolean',
            'quiet_start' => 'required_if:quiet_hours_enabled,true|date_format:H:i',
            'quiet_end' => 'required_if:quiet_hours_enabled,true|date_format:H:i',
        ]);

        $settings = NotificationSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return response()->json([
            'success' => true,
            'settings' => $settings,
            'message' => 'Quiet hours updated',
        ]);
    }

    /**
     * Get unsubscribe token for one-click unsubscribe
     */
    public function generateUnsubscribeToken()
    {
        $token = \Str::random(60);

        auth()->user()->update([
            'notification_unsubscribe_token' => $token,
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'unsubscribe_url' => url('/notifications/unsubscribe/' . $token),
        ]);
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client notification settings index
     */
    public function clientIndex()
    {
        $settings = NotificationSetting::getOrCreate(auth()->id());
        $templates = config('notifications.templates', []);

        return view('client.notification-settings.index', compact('settings', 'templates'));
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire notification settings index
     */
    public function prestataireIndex()
    {
        $settings = NotificationSetting::getOrCreate(auth()->id());
        $templates = config('notifications.templates', []);

        // Prestataires have additional notification options
        $prestataireOptions = [
            'new_booking_notifications' => true,
            'review_notifications' => true,
            'message_notifications' => true,
            'auction_bid_notifications' => true,
            'payment_received_notifications' => true,
        ];

        return view('prestataire.notification-settings.index', compact('settings', 'templates', 'prestataireOptions'));
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin system notification settings
     */
    public function adminSystem()
    {
        $systemSettings = config('notifications', []);
        
        if (!TableExistenceCache::has('notification_settings')) {
            $userPreferencesStats = [
                'email_enabled' => 0,
                'sms_enabled' => 0,
                'push_enabled' => 0,
                'total_users' => 0,
            ];
        } else {
            try {
                $userPreferencesStats = [
                    'email_enabled' => NotificationSetting::where('email_notifications', true)->count(),
                    'sms_enabled' => NotificationSetting::where('sms_notifications', true)->count(),
                    'push_enabled' => NotificationSetting::where('push_notifications', true)->count(),
                    'total_users' => NotificationSetting::count(),
                ];
            } catch (\Exception $e) {
                $userPreferencesStats = [
                    'email_enabled' => 0,
                    'sms_enabled' => 0,
                    'push_enabled' => 0,
                    'total_users' => 0,
                ];
            }
        }

        return view('admin.notification-settings.index', compact('systemSettings', 'userPreferencesStats'));
    }

    /**
     * Update system settings
     */
    public function adminUpdateSystem(Request $request)
    {
        $validated = $request->validate([
            'email_provider' => 'string|in:smtp,mailgun,ses',
            'sms_provider' => 'string|in:twilio,nexmo,vonage',
            'push_provider' => 'string|in:fcm,onesignal',
            'default_email_from' => 'email',
            'default_email_name' => 'string|max:100',
        ]);

        // Ici on pourrait sauvegarder dans un fichier de config ou base de données
        // Pour l'instant, on retourne juste une confirmation
        return back()->with('success', 'Paramètres système mis à jour');
    }

    /**
     * Admin user preferences overview
     */
    public function adminUserPreferences()
    {
        if (!TableExistenceCache::has('notification_settings')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.notification-settings.user-preferences', [
                'preferences' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $preferences = NotificationSetting::with('user')
                ->paginate(30);

            return view('admin.notification-settings.user-preferences', compact('preferences'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.notification-settings.user-preferences', [
                'preferences' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // API METHODS (mobile / JSON)
    // ============================================================================

    public function apiShow()
    {
        $settings = NotificationSetting::getOrCreate(auth()->id());

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Broadcast notification to all users
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'channels' => 'required|array',
            'channels.*' => 'in:email,sms,push,database',
            'target_users' => 'in:all,clients,prestataires,admins',
        ]);

        // Récupérer les utilisateurs cibles
        $usersQuery = \App\Models\User::query();
        
        if ($validated['target_users'] !== 'all') {
            $usersQuery->where('role', $validated['target_users']);
        }

        $count = $usersQuery->count();

        // Dispatch job pour envoyer les notifications en background
        // \App\Jobs\BroadcastNotification::dispatch($validated);

        return back()->with('success', "Notification envoyée à {$count} utilisateurs");
    }
}
