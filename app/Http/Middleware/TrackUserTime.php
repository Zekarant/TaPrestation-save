<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackUserTime
{
    /**
     * Track time spent on site for authenticated users.
     * Uses session to track the last activity timestamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionKey = 'user_last_activity_' . $user->id;
            $now = time();
            
            // Get last activity timestamp
            $lastActivity = session($sessionKey);
            
            if ($lastActivity) {
                // Calculate time spent since last activity
                $elapsed = $now - $lastActivity;
                
                // Only count if less than 5 minutes (300 seconds) since last activity
                // This prevents counting time when user was away
                if ($elapsed > 0 && $elapsed <= 300) {
                    // Update time spent in database
                    try {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->increment('time_spent_seconds', $elapsed);
                    } catch (\Exception $e) {
                        // Column might not exist yet, silently fail
                    }
                }
            }
            
            // Update last activity timestamp
            session([$sessionKey => $now]);
        }

        return $next($request);
    }
    
    /**
     * Get user time spent in hours
     * 
     * @param int $userId
     * @return float
     */
    public static function getTimeSpentHours($userId = null): float
    {
        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return 0;
        }
        
        try {
            $seconds = DB::table('users')
                ->where('id', $userId)
                ->value('time_spent_seconds') ?? 0;
            
            return round($seconds / 3600, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Check if user has spent more than given hours on site
     * 
     * @param float $hours
     * @param int|null $userId
     * @return bool
     */
    public static function hasSpentMoreThan(float $hours, $userId = null): bool
    {
        return self::getTimeSpentHours($userId) >= $hours;
    }
    
    /**
     * Should hide help sections for this user?
     * Default threshold: 150 hours
     * 
     * @param int|null $userId
     * @return bool
     */
    public static function shouldHideHelp($userId = null): bool
    {
        return self::hasSpentMoreThan(150, $userId);
    }
}
