<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webklex\PHPIMAP\ClientManager;
use App\Models\User;
use App\Notifications\PasswordResetEmailReceived;

class IMAPController extends Controller
{
    /**
     * Show the form for checking emails.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('imap.check_emails');
    }
    
    /**
     * Check for password reset emails.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function checkPasswordResetEmails(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'days' => 'nullable|integer|min:1|max:30',
            'notify' => 'nullable|boolean'
        ]);
        
        $email = $request->input('email') ?? env('IMAP_USERNAME');
        $days = $request->input('days', 1);
        $shouldNotify = $request->input('notify', false);
        
        try {
            // Create a new ClientManager instance
            $cm = new ClientManager(config('imap'));
            
            // Connect to the IMAP server
            $client = $cm->account('default');
            $client->connect();
            
            // Select the inbox
            $folder = $client->getFolder('INBOX');
            
            // Search for password reset emails
            $query = $folder->query()
                ->subject('Reset Password')
                ->since(now()->subDays($days));
                
            // If email is specified, also filter by recipient
            if ($request->filled('email')) {
                // Note: IMAP doesn't easily allow filtering by recipient
                // This is a limitation of the IMAP protocol
            }
            
            $messages = $query->get();
            
            $resetEmails = [];
            foreach ($messages as $message) {
                $body = $message->getTextBody() ?? $message->getHTMLBody();
                $resetLink = null;
                
                if ($body) {
                    // Extract the reset link (you might need to adjust this regex)
                    if (preg_match('/(http:\/\/|https:\/\/)[^\s\"]+password\/reset[^\s\"]*/', $body, $matches)) {
                        $resetLink = $matches[0];
                        
                        // Notify administrators if requested
                        if ($shouldNotify) {
                            $this->notifyAdministrators($resetLink, $email, $message->getDate());
                        }
                    }
                }
                
                $resetEmails[] = [
                    'subject' => $message->getSubject(),
                    'from' => $message->getFrom(),
                    'date' => $message->getDate(),
                    'reset_link' => $resetLink
                ];
            }
            
            // Disconnect
            $client->disconnect();
            
            $notificationMessage = $shouldNotify ? 'Found ' . count($resetEmails) . ' password reset email(s). Notifications sent to administrators.' : null;
            
            return view('imap.check_emails', compact('resetEmails', 'email', 'days', 'notificationMessage'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error checking emails: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Notify administrators about the password reset email.
     */
    protected function notifyAdministrators($resetLink, $emailAddress, $receivedAt)
    {
        // Get all administrators
        $administrators = User::whereHas('roles', function($query) {
            $query->where('name', 'administrateur');
        })->get();
        
        // Notify each administrator
        foreach ($administrators as $administrator) {
            $administrator->notify(new PasswordResetEmailReceived($resetLink, $emailAddress, $receivedAt));
        }
    }
}