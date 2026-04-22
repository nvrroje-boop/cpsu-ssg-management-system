@extends('layouts.guest')

@section('title', 'Login | SSG Management System')

@section('content')
    <span class="auth-card-eyebrow">Portal Login</span>
    <h2>Sign in to your campus workspace.</h2>
    <p>Use the email and password assigned to your account. Your destination after login depends on your role in the SSG system.</p>

    <div class="auth-meta-bar" aria-label="Portal roles">
        <span class="auth-meta-pill">Student Access</span>
        <span class="auth-meta-pill">Officer Tools</span>
        <span class="auth-meta-pill">Admin Control</span>
    </div>

    @if (session('status'))
        <div class="flash-box alert alert-info">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash-box alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="field" style="margin-bottom: 1rem;">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" autocomplete="username email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" placeholder="name@cpsu.edu.ph" required>
        </div>

        <div class="field" style="margin-bottom: 1.25rem;">
            <label for="password" class="form-label">Password</label>
            <div class="password-field-wrap">
                <input id="password" name="password" type="password" class="form-input" autocomplete="current-password" autocapitalize="none" autocorrect="off" spellcheck="false" placeholder="Enter your password" required>
                <button
                    type="button"
                    class="password-toggle"
                    id="passwordToggle"
                    aria-controls="password"
                    aria-label="Show password"
                    aria-pressed="false"
                >
                    <svg class="icon-show" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M2 12C4.9 7.8 8.2 5.7 12 5.7S19.1 7.8 22 12c-2.9 4.2-6.2 6.3-10 6.3S4.9 16.2 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                    <svg class="icon-hide" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10.6 6.1A8.4 8.4 0 0 1 12 5.9c3.8 0 7.1 2.1 10 6.1a18.5 18.5 0 0 1-4 4.4M6.4 6.8A18.7 18.7 0 0 0 2 12c2.9 4 6.2 6.1 10 6.1 1.8 0 3.5-.5 5.1-1.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.9 9.9A3 3 0 0 0 9 12a3 3 0 0 0 3 3c.8 0 1.5-.3 2.1-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="password-toggle-text">Show</span>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="auth-support-box">
        <strong>Access note</strong>
        <span>Students can update profile details after login, but only an administrator can reset or change portal passwords.</span>
    </div>

    <a href="{{ route('welcome') }}" class="auth-back-link">&larr; Back to public homepage</a>
@endsection

@push('page-scripts')
    <script>
        (() => {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordToggleText = passwordToggle?.querySelector('.password-toggle-text');

            if (!passwordInput || !passwordToggle || !passwordToggleText) {
                return;
            }

            passwordToggle.addEventListener('click', () => {
                const isVisible = passwordInput.type === 'text';
                passwordInput.type = isVisible ? 'password' : 'text';
                passwordToggle.classList.toggle('is-visible', !isVisible);
                passwordToggle.setAttribute('aria-pressed', String(!isVisible));
                passwordToggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                passwordToggleText.textContent = isVisible ? 'Show' : 'Hide';
            });
        })();
    </script>
@endpush
