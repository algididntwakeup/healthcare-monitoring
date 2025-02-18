<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
            
            return redirect('/')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}