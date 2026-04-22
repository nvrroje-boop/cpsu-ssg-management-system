<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->must_change_password) {
            return $next($request);
        }

        if (
            $request->routeIs('logout')
            || $request->routeIs('*.profile')
            || $request->routeIs('*.profile.update')
            || $request->routeIs('*.profile.password')
        ) {
            return $next($request);
        }

        $prefix = $user->isAdmin() ? 'admin' : ($user->isOfficer() ? 'officer' : 'student');

        return redirect()
            ->route($prefix.'.profile')
            ->with('error', 'Please change your temporary password before continuing.');
    }
}
