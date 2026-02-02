<?php

namespace App\Http\Middleware;

use App\Services\OnlineUsersService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            OnlineUsersService::trackActivity(Auth::id());
        }

        return $next($request);
    }
}
