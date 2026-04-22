<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SSG Management System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('page-css')
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
@php
    $portal = $portal
        ?? (request()->routeIs('officer.*')
            ? 'officer'
            : (request()->routeIs('admin.*') ? 'admin' : 'student'));
    $user = auth()->user();
    $isManagementPortal = in_array($portal, ['admin', 'officer'], true);
    $managementRoutePrefix = $portal === 'officer' ? 'officer' : 'admin';
    $portalLabel = match ($portal) {
        'admin' => 'Admin',
        'officer' => 'Officer',
        default => 'Student',
    };
    $pageSubtitle = trim((string) $__env->yieldContent('page_subtitle'));
@endphp
<body
    class="portal-body"
    data-notifications-url="{{ route('notifications.index') }}"
    data-notifications-read-url-template="{{ route('notifications.read', ['notification' => 0]) }}"
    data-notifications-read-all-url="{{ route('notifications.read-all') }}"
>
    <a href="#main-content" class="skip-link">Skip to content</a>

    <div class="sidebar-overlay portal-overlay" id="sidebarOverlay"></div>

    <div class="shell portal-shell">
        <aside class="sidebar portal-sidebar" id="sidebar" aria-label="{{ $portalLabel }} navigation">
            <div>
                <div class="sidebar-brand portal-sidebar__brand">
                    <div class="sidebar-brand-logo">
                        <img src="{{ asset('ssg-logo.png') }}" alt="SSG Logo">
                    </div>
                    <div>
                        <div class="sidebar-brand-title">SSG Management</div>
                        <div class="sidebar-brand-sub">{{ $portalLabel }} Portal</div>
                    </div>
                </div>

                @include('partials._portal-nav', [
                    'portal' => $portal,
                    'portalLabel' => $portalLabel,
                    'isManagementPortal' => $isManagementPortal,
                    'managementRoutePrefix' => $managementRoutePrefix,
                ])
            </div>

            <div class="sidebar-footer portal-sidebar__footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar">{{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <div class="sidebar-user-name">{{ $user?->name ?? 'Unknown User' }}</div>
                        <div class="sidebar-user-role">{{ $portalLabel }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="main-content portal-main">
            <header class="topbar portal-header">
                <div class="topbar-inner portal-header__inner">
                    <div class="topbar-meta portal-header__meta">
                        <button class="sidebar-toggle portal-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                        <div class="portal-header__heading">
                            <div class="topbar-title portal-header__title">@yield('page_title', 'Dashboard')</div>
                            @if ($pageSubtitle !== '')
                                <div class="topbar-subtitle portal-header__subtitle">{{ $pageSubtitle }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="topbar-actions portal-header__actions">
                        <div class="dropdown-wrap portal-dropdown">
                            <button class="topbar-icon-btn" id="notificationButton" type="button" aria-label="Open notifications" data-dropdown-target="notificationDropdown">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Z" fill="currentColor"/></svg>
                                <span class="notification-badge" id="notification-count">0</span>
                            </button>
                            <div class="dropdown-menu portal-dropdown__menu portal-dropdown__menu--notifications" id="notificationDropdown" aria-label="Notifications menu">
                                <div class="portal-notification-menu__header">
                                    <span class="dropdown-header" id="notification-header">Notifications</span>
                                    <button type="button" class="portal-notification-menu__action" id="notificationMarkAll">Mark all as read</button>
                                </div>
                                <div id="notification-list" class="portal-notification-list">
                                    <div class="dropdown-item portal-dropdown__item--muted">Loading notifications...</div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('notifications.index') }}" class="dropdown-item">
                                    <span>View all notifications</span>
                                </a>
                            </div>
                        </div>

                        <div class="dropdown-wrap portal-dropdown">
                            <button class="topbar-avatar" id="userMenuButton" type="button" aria-label="Open user menu" data-dropdown-target="userDropdown">
                                {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                            </button>
                            <div class="dropdown-menu portal-dropdown__menu" id="userDropdown" aria-label="User menu">
                                <a href="{{ $portal === 'student' ? route('student.profile') : ($portal === 'admin' ? route('admin.profile') : route('officer.profile')) }}" class="dropdown-item">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" fill="currentColor"/></svg>
                                    <span>Profile</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 17v-2h5V9h-5V7h7v10h-7Zm-6 4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8v2H4v14h8v2H4Zm14.59-5L16 13.41 17.41 12l4 4-4 4L16 18.59 18.59 16Z" fill="currentColor"/></svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content portal-content" id="main-content">
                @if (session('status') || session('success') || session('error') || $errors->any())
                    <div class="toast-stack portal-alert-stack" aria-live="polite" aria-atomic="true">
                        @if (session('success'))
                            <x-alert type="success">{{ session('success') }}</x-alert>
                        @endif
                        @if (session('status'))
                            <x-alert type="info">{{ session('status') }}</x-alert>
                        @endif
                        @if (session('error'))
                            <x-alert type="error">{{ session('error') }}</x-alert>
                        @endif
                        @if ($errors->any())
                            <x-alert type="error">{{ $errors->first() }}</x-alert>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('page-js')
    @livewireScripts
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
