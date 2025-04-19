<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Optional: Handle unauthenticated users
        if (!$user || !$user->userType) {
            abort(403, 'Unauthorized');
        }

        $roleName = $user->userType->user_type;

        if ($roleName === 'Admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
