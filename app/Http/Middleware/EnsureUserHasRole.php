<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        $userRole = $request->user()->role?->value ?? $request->user()->role;

        if ($userRole !== $role) {
            abort(403, 'Aksi ini tidak diizinkan untuk peran Anda.');
        }

        return $next($request);
    }
}
