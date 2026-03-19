<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-mail {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'taprestation01@gmail.com';
        
        try {
            Mail::raw('This is a test email from the Laravel application.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email');
            });
            
            $this->info("Test email sent successfully to {$email}!");
        } catch (\Exception $e) {
            $this->error("Failed to send test email: " . $e->getMessage());
        }
    }
}