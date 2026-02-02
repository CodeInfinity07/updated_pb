<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->canAccessAdmin()) {
            abort(403, 'Access denied. You do not have admin access.');
        }

        if (!$user->admin_role_id || !$user->adminRole) {
            abort(403, 'Access denied. Please contact an administrator to assign you an admin role.');
        }

        if ($user->adminRole->isSuperAdmin()) {
            return $next($request);
        }

        if (!empty($permissions)) {
            if (!$user->hasAnyAdminPermission($permissions)) {
                abort(403, 'Access denied. You do not have the required permissions.');
            }
        }

        return $next($request);
    }
}
