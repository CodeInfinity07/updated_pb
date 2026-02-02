<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // For admin access, also allow users with admin_role_id assigned
        if (in_array('admin', $roles) && $user->admin_role_id) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Access denied. You do not have the required permissions.');
        }

        return $next($request);
    }
}