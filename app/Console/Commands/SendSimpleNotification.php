<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\SimpleQueuedNotification;

class SendSimpleNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-simple 
                            {user_id? : The ID of the user to notify} 
                            {message? : The notification message} 
                            {--all : Send to all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a simple queued notification to a user or all users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->sendToAllUsers();
        } else {
            $this->sendToSingleUser();
        }

        return 0;
    }

    /**
     * Send notification to a single user.
     *
     * @return void
     */
    protected function sendToSingleUser()
    {
        $userId = $this->argument('user_id');
        $message = $this->argument('message');

        // Get user ID if not provided
        if (!$userId) {
            $userId = $this->ask('Enter the user ID');
        }

        // Get message if not provided
        if (!$message) {
            $message = $this->ask('Enter the notification message');
        }

        // Find the user
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }

        // Send notification
        $user->notify(new SimpleQueuedNotification($message));
        
        $this->info("Notification queued for user: {$user->name}");
    }

    /**
     * Send notification to all users.
     *
     * @return void
     */
    protected function sendToAllUsers()
    {
        $message = $this->argument('message');

        // Get message if not provided
        if (!$message) {
            $message = $this->ask('Enter the notification message');
        }

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->error('No users found in the database.');
            return;
        }

        // Send notification to all users
        foreach ($users as $user) {
            $user->notify(new SimpleQueuedNotification($message));
        }

        $this->info("Notification queued for {$users->count()} users.");
    }
}