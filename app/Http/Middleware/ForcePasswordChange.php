<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * If the logged-in user was created with a temporary password, hold every request
     * at /password/force until they pick a new one. Logout stays accessible so they
     * can bail out, and the force-change form itself must not redirect to itself.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            $allowed = [
                'password.force',
                'password.force.update',
                'logout',
            ];
            if (! in_array($request->route()?->getName(), $allowed, true)) {
                return redirect()->route('password.force');
            }
        }

        return $next($request);
    }
}
