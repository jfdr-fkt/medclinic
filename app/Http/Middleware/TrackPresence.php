<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Bumps the authenticated user's last_seen_at on each request so the
 * availability pill in the header stays current. Throttled to one DB
 * write per minute per user to avoid hammering the row on rapid clicks.
 */
class TrackPresence
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
                DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
                $user->last_seen_at = now();
            }
        }
        return $next($request);
    }
}
