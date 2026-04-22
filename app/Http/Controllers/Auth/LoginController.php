<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $user->loadMissing('role');

            return redirect()->route($this->defaultRouteName($user));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('role');

        if ($user->must_change_password) {
            $prefix = $user->isAdminPortalUser() ? 'admin' : ($user->isOfficerPortalUser() ? 'officer' : 'student');

            return redirect()
                ->route($prefix.'.profile')
                ->with('error', 'Please change your temporary password before continuing.');
        }

        if ($user->isOfficerPortalUser() || $user->isAdminPortalUser() || $user->isStudentPortalUser()) {
            return redirect()
                ->intended(route($this->defaultRouteName($user)))
                ->with('status', 'Welcome back, '.$user->name.'.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors([
            'email' => 'Your account role is not allowed to access this portal.',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('welcome')
            ->with('status', 'You have been signed out.');
    }

    private function defaultRouteName(User $user): string
    {
        if ($user->isAdminPortalUser()) {
            return 'admin.dashboard';
        }

        if ($user->isOfficerPortalUser()) {
            return 'officer.dashboard';
        }

        return 'student.dashboard';
    }
}
