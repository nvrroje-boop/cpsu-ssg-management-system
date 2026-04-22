<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()
                ->route('login');
        }

        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('role');

        if ($roles === [] || $this->userHasAllowedRole($user, $roles)) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this resource.');
    }

    private function userHasAllowedRole(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($this->expandRole($role))) {
                return true;
            }
        }

        return false;
    }

    private function expandRole(string $role): array
    {
        return match (strtolower(trim($role))) {
            'admin' => [User::ROLE_ADMIN],
            'officer' => [User::ROLE_OFFICER, User::ROLE_SSG_OFFICER],
            'ssg-officer', 'ssg_officer', 'ssgofficer' => [User::ROLE_SSG_OFFICER],
            'student' => [User::ROLE_STUDENT],
            default => [$role],
        };
    }
};
