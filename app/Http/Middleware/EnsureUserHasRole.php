<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasRole('super_admin') || $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'This action is not authorized for the authenticated role.');
    }
}
