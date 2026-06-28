<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $minRole): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasMinRole($minRole)) {
            abort(403, 'Accés denegat: rol insuficient.');
        }

        return $next($request);
    }
}
