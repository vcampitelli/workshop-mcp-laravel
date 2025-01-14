<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'super admin'])) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
