<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class FixNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:fix {--check : Only check for problems without fixing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix notifications that are missing title or message fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking notifications data structure...');
        
        // Count notifications in the database
        $count = DB::table('notifications')->count();
        $this->info("Found {$count} notifications in database.");

        if ($count === 0) {
            $this->warn('No notifications found in the database.');
            return;
        }
        
        // Find notifications with missing title or message
        $this->info('Checking for missing title/message fields...');

        $notificationsWithoutTitle = 0;
        $notificationsWithoutMessage = 0;
        $totalChecked = 0;
        $typesToFix = [];
        $typeStats = [];
        $batchSize = 100;
        
        $this->withProgressBar(DB::table('notifications')->count(), function () use (&$notificationsWithoutTitle, &$notificationsWithoutMessage, &$totalChecked, &$typesToFix, &$typeStats, $batchSize) {
            $offset = 0;
            
            while (true) {
                $batch = DB::table('notifications')
                    ->orderBy('id')
                    ->offset($offset)
                    ->limit($batchSize)
                    ->get();
                
                if ($batch->isEmpty()) {
                    break;
                }
                
                foreach ($batch as $notification) {
                    $totalChecked++;
                    $data = json_decode($notification->data);
                    
                    // Record statistics by notification type
                    if (!isset($typeStats[$notification->type])) {
                        $typeStats[$notification->type] = ['count' => 0, 'missing_title' => 0, 'missing_message' => 0];
                    }
                    $typeStats[$notification->type]['count']++;
                    
                    if (!$data || !isset($data->title)) {
                        $notificationsWithoutTitle++;
                        $typeStats[$notification->type]['missing_title']++;
                        
                        if (!in_array($notification->type, $typesToFix)) {
                            $typesToFix[] = $notification->type;
                        }
                    }
                    
                    if (!$data || !isset($data->message)) {
                        $notificationsWithoutMessage++;
                        $typeStats[$notification->type]['missing_message']++;
                        
                        if (!in_array($notification->type, $typesToFix)) {
                            $typesToFix[] = $notification->type;
                        }
                    }
                    
                    $this->getOutput()->progressAdvance();
                }
                
                $offset += $batchSize;
            }
        });
        
        $this->newLine(2);
        $this->info("Total notifications checked: {$totalChecked}");
        $this->info("Notifications without title: {$notificationsWithoutTitle}");
        $this->info("Notifications without message: {$notificationsWithoutMessage}");
        
        // Show statistics by notification type
        $this->newLine();
        $this->table(
            ['Type', 'Count', 'Missing Title', 'Missing Message'],
            collect($typeStats)->map(function ($stats, $type) {
                $shortType = substr($type, strrpos($type, '\\') + 1);
                return [$shortType, $stats['count'], $stats['missing_title'], $stats['missing_message']];
            })->toArray()
        );
        
        // Fix the notifications if requested
        if (!$this->option('check')) {
            if ($notificationsWithoutTitle > 0 || $notificationsWithoutMessage > 0) {
                if ($this->confirm('Do you want to add default title and message to these notifications?')) {
                    $this->fixNotifications($typesToFix, $batchSize);
                }
            } else {
                $this->info('All notifications have title and message fields. No fixes needed.');
            }
        }
    }
    
    /**
     * Fix notifications by adding missing title and message fields.
     */
    private function fixNotifications($typesToFix, $batchSize)
    {
        $totalUpdated = 0;
        
        foreach ($typesToFix as $type) {
            $shortType = substr($type, strrpos($type, '\\') + 1);
            $this->info("Processing {$shortType}...");
            
            // Generate default title and message
            $defaultTitle = preg_replace('/([a-z])([A-Z])/', '$1 $2', $shortType);
            $defaultTitle = str_replace('Notification', '', $defaultTitle);
            $defaultMessage = "Vous avez reçu une notification de type {$defaultTitle}.";
            
            // Count records to update for this type
            $countToUpdate = DB::table('notifications')->where('type', $type)->count();
            
            if ($countToUpdate === 0) {
                continue;
            }
            
            $updated = 0;
            $offset = 0;
            
            $this->output->progressStart($countToUpdate);
            
            while (true) {
                $batch = DB::table('notifications')
                    ->where('type', $type)
                    ->orderBy('id')
                    ->offset($offset)
                    ->limit($batchSize)
                    ->get();
                
                if ($batch->isEmpty()) {
                    break;
                }
                
                foreach ($batch as $notification) {
                    $data = json_decode($notification->data, true) ?: [];
                    $needsUpdate = false;
                    
                    if (!isset($data['title'])) {
                        $data['title'] = $defaultTitle;
                        $needsUpdate = true;
                    }
                    
                    if (!isset($data['message'])) {
                        $data['message'] = $defaultMessage;
                        $needsUpdate = true;
                    }
                    
                    if ($needsUpdate) {
                        DB::table('notifications')
                            ->where('id', $notification->id)
                            ->update(['data' => json_encode($data)]);
                        
                        $updated++;
                        $totalUpdated++;
                    }
                    
                    $this->output->progressAdvance();
                }
                
                $offset += $batchSize;
            }
            
            $this->output->progressFinish();
            $this->info("Updated {$updated} records for {$shortType}");
        }
        
        $this->newLine();
        $this->info("Total updated: {$totalUpdated} notifications");
        $this->info("Fix completed!");
    }
}