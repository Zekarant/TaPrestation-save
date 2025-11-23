<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;
use App\Models\User;
use App\Notifications\PasswordResetEmailReceived;

class CheckPasswordResetEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imap:check-password-resets {email?} {--notify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for password reset emails in the inbox';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for password reset emails...');
        
        try {
            // Get the email address to search for
            $email = $this->argument('email') ?? env('IMAP_USERNAME');
            
            // Create a new ClientManager instance
            $cm = new ClientManager(config('imap'));
            
            // Connect to the IMAP server
            $client = $cm->account('default');
            $client->connect();
            
            // Select the inbox
            $folder = $client->getFolder('INBOX');
            
            // Search for password reset emails
            $messages = $folder->query()
                ->from('no-reply@laravel.com') // Adjust this to match your app's sender
                ->subject('Reset Password')
                ->since(now()->subDays(1)) // Check emails from the last day
                ->get();
            
            if ($messages->count() > 0) {
                $this->info("Found {$messages->count()} password reset email(s):");
                
                foreach ($messages as $message) {
                    $this->line("Subject: " . $message->getSubject());
                    $this->line("From: " . $message->getFrom());
                    $this->line("Date: " . $message->getDate());
                    
                    // Get the message body
                    $body = $message->getTextBody() ?? $message->getHTMLBody();
                    $resetLink = null;
                    
                    if ($body) {
                        // Extract the reset link (you might need to adjust this regex)
                        if (preg_match('/(http:\/\/|https:\/\/)[^\s\"]+password\/reset[^\s\"]*/', $body, $matches)) {
                            $resetLink = $matches[0];
                            $this->info("Reset Link: " . $resetLink);
                            
                            // Notify administrators if requested
                            if ($this->option('notify')) {
                                $this->notifyAdministrators($resetLink, $email, $message->getDate());
                            }
                        }
                    }
                    
                    $this->line("---");
                }
            } else {
                $this->info('No password reset emails found.');
            }
            
            // Disconnect
            $client->disconnect();
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
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
        
        $this->info('Notifications sent to ' . $administrators->count() . ' administrator(s).');
    }
}