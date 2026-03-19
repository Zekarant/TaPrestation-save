<?php

namespace App\Helpers;

use App\Mail\NotificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Support\TableExistenceCache;
class NotificationHelper
{
    /**
     * Envoyer une notification par email (même méthode que le bouton test)
     */
    public static function sendEmail($user, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null): bool
    {
        // Vérifier si l'utilisateur a les notifications email activées
        if (!self::shouldSendEmail($user)) {
            return false;
        }

        try {
            Mail::to($user->email)->send(
                new NotificationEmail($user, $title, $message, $actionUrl, $actionText)
            );
            return true;
        } catch (\Exception $e) {
            Log::error('NotificationHelper email failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Vérifier si on doit envoyer un email à cet utilisateur
     */
    public static function shouldSendEmail($user, string $notificationType = 'booking'): bool
    {
        if (!$user || !$user->email) {
            return false;
        }

        // Vérifier les paramètres de notification
        if (!TableExistenceCache::has('notification_settings')) {
            return true; // Par défaut, envoyer
        }

        $settings = \App\Models\NotificationSetting::where('user_id', $user->id)->first();
        
        if (!$settings) {
            return true; // Par défaut, envoyer
        }

        // Vérifier si les emails sont activés
        if (!$settings->email_notifications) {
            return false;
        }

        // Vérifier le type spécifique
        switch ($notificationType) {
            case 'booking':
                return $settings->booking_notifications ?? true;
            case 'message':
                return $settings->message_notifications ?? true;
            case 'review':
                return $settings->review_notifications ?? true;
            case 'payment':
                return $settings->payment_notifications ?? true;
            default:
                return true;
        }
    }

    /**
     * Envoyer push notification à un utilisateur via OneSignal
     */
    public static function sendPush($user, string $title, string $body, array $data = []): bool
    {
        if (!$user) {
            return false;
        }

        try {
            // Utiliser OneSignal en priorité
            $oneSignal = app(\App\Services\OneSignalService::class);
            if ($oneSignal->isConfigured()) {
                return $oneSignal->sendToUser($user->id, $title, $body, $data);
            }
            
            // Fallback sur l'ancien système si OneSignal non configuré
            if ($user->push_enabled) {
                $notificationService = app(\App\Services\NotificationService::class);
                return $notificationService->sendWebPushToUser($user, $title, $body, $data);
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer notification nouvelle réservation (email + push)
     */
    public static function sendNewBookingEmail($booking): bool
    {
        $prestataire = $booking->prestataire->user ?? null;
        if (!$prestataire) return false;

        $clientName = $booking->client->user->name ?? 'Un client';
        $serviceTitle = $booking->service->title ?? 'un service';
        $bookingDate = $booking->start_datetime ? $booking->start_datetime->format('d/m/Y à H:i') : 'Date à définir';

        $message = "Vous avez reçu une nouvelle demande de réservation.\n\n";
        $message .= "📋 Client: $clientName\n";
        $message .= "🛠️ Service: $serviceTitle\n";
        $message .= "📅 Date: $bookingDate\n\n";
        $message .= "Veuillez confirmer ou refuser cette réservation.";

        $url = url('/prestataire/bookings/' . $booking->id);

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        // via la notification Laravel (->notify)
        $emailSent = self::sendEmail($prestataire, '📅 Nouvelle réservation reçue', $message, $url, 'Voir la réservation');

        return $emailSent;
    }

    /**
     * Envoyer notification réservation confirmée (email + push)
     */
    public static function sendBookingConfirmedEmail($booking): bool
    {
        $client = $booking->client->user ?? null;
        if (!$client) return false;

        $serviceName = $booking->service->name ?? $booking->service->title ?? 'votre service';
        $bookingDate = $booking->start_datetime ? $booking->start_datetime->format('d/m/Y à H:i') : 'Date à confirmer';

        $message = "Bonne nouvelle ! Votre réservation a été confirmée par le prestataire.\n\n";
        $message .= "🛠️ Service: $serviceName\n";
        $message .= "📅 Date: $bookingDate\n\n";
        $message .= "Merci de votre confiance !";

        $url = url('/bookings/' . $booking->id);

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        $emailSent = self::sendEmail($client, '✅ Réservation confirmée', $message, $url, 'Voir les détails');

        return $emailSent;
    }

    /**
     * Envoyer notification réservation refusée (email + push)
     */
    public static function sendBookingRefusedEmail($booking, ?string $reason = null): bool
    {
        $client = $booking->client->user ?? null;
        if (!$client) return false;

        $serviceName = $booking->service->name ?? $booking->service->title ?? 'votre service';

        $message = "Nous sommes désolés, votre réservation a été refusée par le prestataire.\n\n";
        $message .= "🛠️ Service: $serviceName\n";
        if ($reason) {
            $message .= "💬 Raison: $reason\n";
        }
        $message .= "\nN'hésitez pas à rechercher d'autres prestataires.";

        $url = url('/services');

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        $emailSent = self::sendEmail($client, '😞 Réservation refusée', $message, $url, 'Rechercher un prestataire');

        return $emailSent;
    }

    /**
     * Envoyer notification réservation annulée (email + push)
     */
    public static function sendBookingCancelledEmail($booking, $recipient, string $cancelledBy = 'le client'): bool
    {
        if (!$recipient) return false;

        $bookingNumber = $booking->booking_number ?? $booking->id;
        $serviceName = $booking->service->name ?? $booking->service->title ?? 'le service';
        $reason = $booking->cancellation_reason ?? 'Non spécifiée';

        $message = "La réservation #$bookingNumber a été annulée par $cancelledBy.\n\n";
        $message .= "🛠️ Service: $serviceName\n";
        $message .= "💬 Raison: $reason";

        $url = url('/bookings/' . $booking->id);

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        $emailSent = self::sendEmail($recipient, '❌ Réservation annulée', $message, $url, 'Voir les détails');

        return $emailSent;
    }

    /**
     * Envoyer notification nouveau message (email + push)
     */
    public static function sendNewMessageEmail($message, $recipient): bool
    {
        if (!$recipient) return false;

        $senderName = $message->sender->name ?? 'Un utilisateur';
        $preview = \Illuminate\Support\Str::limit($message->content, 100);

        $msgContent = "$senderName vous a envoyé un message:\n\n";
        $msgContent .= "\"$preview\"\n\n";
        $msgContent .= "Répondez rapidement pour maintenir une bonne communication !";

        $url = url('/messages');

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        $emailSent = self::sendEmail($recipient, '💬 Nouveau message reçu', $msgContent, $url, 'Voir le message');

        return $emailSent;
    }

    /**
     * Envoyer notification nouvel avis
     */
    public static function sendNewReviewEmail($review): bool
    {
        $prestataire = $review->prestataire->user ?? null;
        if (!$prestataire) return false;

        $clientName = $review->client->name ?? 'Un client';
        $rating = $review->rating;
        $comment = $review->comment ?? '';

        $message = "$clientName a laissé une évaluation sur votre prestation.\n\n";
        $message .= "⭐ Note: $rating/5\n";
        if ($comment) {
            $message .= "💬 Commentaire: \"$comment\"";
        }

        $url = url('/prestataire/reviews');

        // Envoyer email uniquement - le push est géré par le listener SendOneSignalPush
        $emailSent = self::sendEmail($prestataire, "⭐ Nouvelle évaluation: $rating/5", $message, $url, 'Voir l\'avis');

        return $emailSent;
    }
}
