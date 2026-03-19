<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserOnlineStatus
{
    /**
     * Handle an incoming request.
     * Throttled: updates DB at most once every 5 minutes per user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cacheKey = "user_online_{$userId}";

            if (!Cache::has($cacheKey)) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $user->update([
                    'is_online' => true,
                    'last_seen_at' => now(),
                ]);
                Cache::put($cacheKey, true, 300); // 5 minutes
            }
        }

        return $next($request);
    }
}