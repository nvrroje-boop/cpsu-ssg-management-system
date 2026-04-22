<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SSG Management System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/design-system.css" rel="stylesheet">
    @livewireStyles
    <style>
        .auth-page {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(84, 179, 110, 0.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(212, 160, 23, 0.12), transparent 26%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0)),
                var(--page-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .auth-page::before,
        .auth-page::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(10px);
            pointer-events: none;
        }

        .auth-page::before {
            width: 220px;
            height: 220px;
            top: -60px;
            right: 4%;
            background: rgba(245, 208, 96, 0.12);
        }

        .auth-page::after {
            width: 280px;
            height: 280px;
            left: -80px;
            bottom: -100px;
            background: rgba(87, 179, 110, 0.12);
        }

        .auth-shell {
            position: relative;
            z-index: 2;
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: 1.08fr minmax(340px, 430px);
            background: rgba(255, 255, 255, 0.92);
            border: 0.5px solid var(--border);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 45, 28, 0.14);
            backdrop-filter: blur(18px);
        }

        .auth-hero {
            position: relative;
            padding: 2.5rem;
            background:
                linear-gradient(145deg, rgba(15, 45, 28, 0.96), rgba(30, 92, 53, 0.92)),
                url('/campus-building.jpeg') center/cover no-repeat;
            color: var(--cream);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 620px;
        }

        .auth-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(245, 208, 96, 0.16), transparent 24%),
                radial-gradient(circle at bottom left, rgba(87, 179, 110, 0.16), transparent 18%);
            pointer-events: none;
        }

        .auth-hero-top,
        .auth-hero-bottom {
            position: relative;
            z-index: 1;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .auth-brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            padding: 0.5rem;
            background: rgba(245, 237, 224, 0.12);
            border: 1px solid rgba(245, 237, 224, 0.16);
            backdrop-filter: blur(12px);
        }

        .auth-brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .auth-brand-name {
            color: var(--cream);
            font-size: 0.95rem;
            font-weight: 700;
        }

        .auth-brand-sub {
            color: rgba(245, 237, 224, 0.62);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .auth-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: auto;
            width: fit-content;
            padding: 0.35rem 0.85rem;
            border-radius: var(--r-pill);
            border: 1px solid rgba(245, 208, 96, 0.45);
            background: rgba(212, 160, 23, 0.16);
            color: var(--gold-300);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .auth-kicker::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold-500);
        }

        .auth-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 800;
            line-height: 1.1;
            color: var(--cream);
            margin: 1rem 0;
        }

        .auth-hero h1 em {
            color: var(--ssg-400);
            font-style: italic;
        }

        .auth-hero p {
            max-width: 42ch;
            color: var(--cream-muted);
            line-height: 1.8;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.45);
        }

        .auth-hero-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
            margin-top: 1.5rem;
        }

        .auth-summary-item {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(245, 237, 224, 0.08);
            border: 1px solid rgba(245, 237, 224, 0.12);
            backdrop-filter: blur(10px);
        }

        .auth-summary-item strong {
            display: block;
            color: var(--cream);
            margin-bottom: 0.25rem;
            font-size: 0.88rem;
        }

        .auth-summary-item span {
            color: var(--cream-muted);
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .auth-points {
            display: grid;
            gap: 0.875rem;
            margin-top: 2rem;
        }

        .auth-point {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--cream-muted);
            font-size: 0.9rem;
        }

        .auth-point span:first-child {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(245, 237, 224, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--gold-300);
        }

        .auth-card {
            position: relative;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background:
                radial-gradient(circle at top right, rgba(245, 208, 96, 0.08), transparent 18%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 244, 239, 0.95));
        }

        .auth-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(212, 160, 23, 0.03));
            pointer-events: none;
        }

        .auth-panel {
            position: relative;
            z-index: 1;
        }

        .auth-card-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: auto;
            width: fit-content;
            padding: 0.38rem 0.75rem;
            border-radius: var(--r-pill);
            background: var(--gold-50);
            color: var(--gold-700);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .auth-card h2 {
            font-size: 1.55rem;
            line-height: 1.15;
            margin-bottom: 0.4rem;
            margin-top: 0;
        }

        .auth-card p {
            color: var(--text-muted);
            margin-bottom: 1.4rem;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .auth-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 1rem;
            color: var(--ssg-700);
            font-size: 0.85rem;
            font-weight: 700;
        }

        .auth-back-link:hover {
            color: var(--ssg-900);
        }

        .auth-meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin: 1rem 0 1.25rem;
        }

        .auth-meta-pill {
            display: inline-flex;
            align-items: center;
            min-height: auto;
            padding: 0.38rem 0.72rem;
            border-radius: var(--r-pill);
            background: rgba(15, 45, 28, 0.05);
            color: var(--text-primary);
            font-size: 0.73rem;
            font-weight: 700;
        }

        .auth-support-box {
            margin-top: 1.2rem;
            padding: 1rem;
            border-radius: 18px;
            background: rgba(15, 45, 28, 0.04);
            border: 1px solid rgba(15, 45, 28, 0.08);
        }

        .auth-support-box strong {
            display: block;
            margin-bottom: 0.2rem;
            color: var(--text-primary);
            font-size: 0.86rem;
        }

        .auth-support-box span {
            display: block;
            color: var(--text-muted);
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .password-field-wrap {
            position: relative;
        }

        .password-field-wrap .form-input {
            padding-right: 4.8rem;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.6rem;
            transform: translateY(-50%);
            min-height: 36px;
            padding: 0.35rem 0.55rem;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--ssg-700);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: rgba(15, 45, 28, 0.06);
            color: var(--ssg-900);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .password-toggle .icon-hide {
            display: none;
        }

        .password-toggle.is-visible .icon-show {
            display: none;
        }

        .password-toggle.is-visible .icon-hide {
            display: block;
        }

        .btn-submit {
            width: 100%;
            background: var(--ssg-700);
            color: var(--cream);
            border: 0;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 1rem 1rem;
            min-height: 48px;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(30, 92, 53, 0.18);
            transition: background 0.16s ease;
        }

        .btn-submit:hover {
            background: var(--ssg-900);
        }

        .flash-box {
            margin-bottom: 1rem;
        }

        @media (max-width: 900px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-hero {
                min-height: auto;
                padding: 2rem;
            }

            .auth-hero-summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .auth-page {
                padding: 0.75rem;
            }

            .auth-shell {
                border-radius: 20px;
            }

            .auth-card,
            .auth-hero {
                padding: 1.5rem;
            }

            .auth-brand {
                align-items: flex-start;
                gap: 0.75rem;
            }

            .auth-brand-mark {
                width: 48px;
                height: 48px;
            }

            .auth-card h2 {
                font-size: 1.35rem;
                margin-top: 1rem;
                margin-bottom: 0.8rem;
            }

            .auth-card p {
                font-size: 0.95rem;
                line-height: 1.6;
            }

            .field {
                margin-bottom: 1.25rem;
            }

            .form-label,
            input,
            select {
                font-size: 1rem;
            }

            .password-toggle {
                right: 0.45rem;
                min-height: 48px;
                padding: 0.5rem 0.6rem;
            }

            .btn-submit {
                font-size: 1rem;
                min-height: 48px;
                padding: 1rem;
                margin-top: 0.5rem;
            }

            .auth-support-box {
                font-size: 0.9rem;
                padding: 1rem;
                margin-top: 1.25rem;
            }

            .auth-back-link {
                font-size: 0.9rem;
                margin-top: 1.5rem;
            }
        }
    </style>
    @stack('page-css')
</head>
<body>
    <a href="#auth-content" class="skip-link">Skip to login</a>
    <main class="auth-page">
        <div class="auth-shell">
            <section class="auth-hero" aria-label="Portal introduction">
                <div class="auth-hero-top">
                    <a href="{{ route('welcome') }}" class="auth-brand" aria-label="Back to public homepage">
                        <span class="auth-brand-mark">
                            <img src="/ssg-logo.png" alt="CPSU SSG logo">
                        </span>
                        <span>
                            <span class="auth-brand-name">SSG Management System</span>
                            <span class="auth-brand-sub">CPSU Hinoba-an Campus</span>
                        </span>
                    </a>
                    <span class="auth-kicker">CPSU Hinoba-an Campus</span>
                    <h1>Student governance updates, events, and requests in one <em>campus portal</em>.</h1>
                    <p>Access announcements, event attendance, student concerns, and role-based tools through a cleaner mobile-ready campus system.</p>
                    <div class="auth-hero-summary">
                        <div class="auth-summary-item">
                            <strong>Public access</strong>
                            <span>Announcements and event schedules are visible before sign-in.</span>
                        </div>
                        <div class="auth-summary-item">
                            <strong>Role-based portal</strong>
                            <span>Students, officers, and admins enter separate workspaces after login.</span>
                        </div>
                    </div>
                </div>
                <div class="auth-hero-bottom">
                    <div class="auth-points">
                    <div class="auth-point"><span>1</span><span>Fast enough for mobile data and mid-range Android devices.</span></div>
                    <div class="auth-point"><span>2</span><span>Separate student, officer, and admin workspaces with real data.</span></div>
                    <div class="auth-point"><span>3</span><span>Built for campus notices, attendance, and accountability.</span></div>
                    </div>
                </div>
            </section>
            <section class="auth-card" id="auth-content">
                <div class="auth-panel">
                    @yield('content')
                </div>
            </section>
        </div>
    </main>
    @stack('page-scripts')
    @livewireScripts
</body>
</html>
