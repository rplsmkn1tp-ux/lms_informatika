<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status === 'pending') {
            if ($request->routeIs('pending.notice') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('pending.notice');
        }

        return $next($request);
    }
}
