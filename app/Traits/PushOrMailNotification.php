<?php

namespace App\Traits;


use App\Support\TableExistenceCache;
/**
 * Trait pour gérer la logique : Push OU Mail, pas les deux
 * 
 * Si OneSignal est configuré → envoie push (via NotificationObserver), PAS de mail
 * Si OneSignal n'est pas configuré → envoie mail
 */
trait PushOrMailNotification
{
    /**
     * Détermine les canaux de notification : database + mail seulement si pas de push
     */
    protected function getNotificationChannels($notifiable, string $settingsField = 'message_notifications'): array
    {
        $channels = ['database'];
        
        // Vérifier si OneSignal est configuré (push via NotificationObserver)
        $oneSignalEnabled = config('onesignal.enabled') && config('onesignal.app_id');
        
        // Si OneSignal est activé → pas de mail, le push est géré par NotificationObserver
        if ($oneSignalEnabled) {
            return $channels;
        }
        
        // Pas de push disponible → envoyer le mail si autorisé
        $settings = null;
        if (TableExistenceCache::has('notification_settings')) {
            $settings = \App\Models\NotificationSetting::where('user_id', $notifiable->id)->first();
        }
        
        // Vérifier les préférences email de l'utilisateur
        $emailEnabled = true;
        if ($settings) {
            $emailEnabled = $settings->email_notifications;
            
            // Vérifier le champ spécifique si fourni
            if ($settingsField && property_exists($settings, $settingsField)) {
                $emailEnabled = $emailEnabled && $settings->{$settingsField};
            }
        }
        
        if ($emailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }
}
