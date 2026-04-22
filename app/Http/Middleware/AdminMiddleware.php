<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()
                ->route('login')
                ->with('status', 'Please sign in to the admin portal first.');
        }

        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('role');

        if (! $user->isAdmin()) {
            return redirect()
                ->route('welcome')
                ->with('status', 'Your account does not have admin portal access.');
        }

        return $next($request);
    }
}
