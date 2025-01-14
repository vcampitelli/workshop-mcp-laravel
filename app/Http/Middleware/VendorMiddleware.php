<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole(['vendor', 'vendor manager'])) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
