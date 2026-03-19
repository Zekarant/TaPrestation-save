<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Envoyer un message de contact
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ], [
            'name.required' => 'Veuillez entrer votre nom.',
            'email.required' => 'Veuillez entrer votre email.',
            'email.email' => 'Veuillez entrer un email valide.',
            'subject.required' => 'Veuillez sélectionner un sujet.',
            'message.required' => 'Veuillez entrer votre message.',
            'message.min' => 'Votre message doit contenir au moins 10 caractères.',
        ]);

        try {
            // Enregistrer le message de contact dans la base de données si le modèle existe
            if (class_exists(\App\Models\ContactMessage::class)) {
                \App\Models\ContactMessage::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'subject' => $validated['subject'],
                    'message' => $validated['message'],
                    'user_id' => auth()->id(),
                ]);
            }

            // Log du message pour suivi
            Log::info('Nouveau message de contact', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
            ]);

            // Envoi d'email si configuré
            $adminEmail = config('mail.admin_address', 'contact@taprestation.com');
            
            try {
                Mail::raw(
                    "Nouveau message de contact\n\n" .
                    "Nom: {$validated['name']}\n" .
                    "Email: {$validated['email']}\n" .
                    "Sujet: {$validated['subject']}\n\n" .
                    "Message:\n{$validated['message']}",
                    function ($mail) use ($validated, $adminEmail) {
                        $mail->to($adminEmail)
                            ->subject("[TaPrestation] Contact: {$validated['subject']}")
                            ->replyTo($validated['email'], $validated['name']);
                    }
                );
            } catch (\Exception $e) {
                // Ignorer les erreurs d'envoi d'email, le message est quand même enregistré
                Log::warning('Échec envoi email de contact', ['error' => $e->getMessage()]);
            }

            return redirect()->back()->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du message de contact', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.');
        }
    }
}
