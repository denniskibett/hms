<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Allow the request only if the user has one of the given roles.
     * Admins always pass.
     *
     * Usage: ->middleware('role:manager,receptionist')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $userRoles = $user->loadMissing('roles')->roles->pluck('name');

        if ($userRoles->contains('admin') || $userRoles->intersect($roles)->isNotEmpty()) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
