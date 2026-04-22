<span class="sidebar-section-label portal-sidebar__section-label">Workspace</span>
<nav class="portal-sidebar__nav" aria-label="{{ $portalLabel }} workspace navigation">
    <ul class="sidebar-nav portal-sidebar__nav-list">
        @if ($isManagementPortal)
            <li class="portal-sidebar__nav-item">
                <a href="{{ route($managementRoutePrefix.'.dashboard') }}" class="portal-sidebar__link {{ request()->routeIs($managementRoutePrefix.'.dashboard') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-5H4v5Zm10-7h6v-3h-6v3Z" fill="currentColor"/></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            @if ($portal === 'admin')
                <li class="portal-sidebar__nav-item">
                    <a href="{{ route('admin.students.index') }}" class="portal-sidebar__link {{ request()->routeIs('admin.students.*') ? 'active portal-sidebar__link--active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4Zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4Zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.96 1.97 3.45V20H23v-3.5c0-2.33-4.67-3.5-7-3.5Z" fill="currentColor"/></svg>
                        <span>Accounts</span>
                    </a>
                </li>
            @endif

            <li class="portal-sidebar__nav-item">
                <a href="{{ route($managementRoutePrefix.'.announcements.index') }}" class="portal-sidebar__link {{ request()->routeIs($managementRoutePrefix.'.announcements.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10v4c0 .55.45 1 1 1h1l2 4h2l-1-4h6l4 2V7l-4 2H5c-.55 0-1 .45-1 1Z" fill="currentColor"/></svg>
                    <span>Announcements</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route($managementRoutePrefix.'.events.index') }}" class="portal-sidebar__link {{ request()->routeIs($managementRoutePrefix.'.events.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h3V2Zm13 8H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8Z" fill="currentColor"/></svg>
                    <span>Events</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route($managementRoutePrefix.'.concerns.index') }}" class="portal-sidebar__link {{ request()->routeIs($managementRoutePrefix.'.concerns.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 5.94 2 10.8c0 2.78 1.46 5.26 3.74 6.87L5 22l4.33-2.37c.85.12 1.74.17 2.67.17 5.52 0 10-3.94 10-8.8S17.52 2 12 2Zm1 13h-2v-2h2v2Zm1.92-5.75-.9.92c-.72.73-1.02 1.33-1.02 2.33h-2v-.5c0-1.1.3-2.1 1.02-2.83l1.24-1.26c.36-.35.56-.84.56-1.41 0-1.09-.83-2-1.92-2-1.06 0-1.9.83-1.98 1.87H8c.09-2.16 1.84-3.87 4-3.87 2.21 0 4 1.79 4 4 0 .88-.36 1.68-1.08 2.42Z" fill="currentColor"/></svg>
                    <span>Concerns</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route($managementRoutePrefix.'.attendance.index') }}" class="portal-sidebar__link {{ request()->routeIs($managementRoutePrefix.'.attendance.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 16.17-3.88-3.88L3.7 13.7 9 19l12-12-1.41-1.41L9 16.17Z" fill="currentColor"/></svg>
                    <span>Attendance</span>
                </a>
            </li>

            @if ($portal === 'admin')
                <li class="portal-sidebar__nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="portal-sidebar__link {{ request()->routeIs('admin.reports.*') ? 'active portal-sidebar__link--active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 9.2h3V19H5V9.2Zm5.5-4.2h3V19h-3V5Zm5.5 7h3V19h-3v-7Z" fill="currentColor"/></svg>
                        <span>Reports</span>
                    </a>
                </li>
            @endif
        @else
            <li class="portal-sidebar__nav-item">
                <a href="{{ route('student.dashboard') }}" class="portal-sidebar__link {{ request()->routeIs('student.dashboard') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-5H4v5Zm10-7h6v-3h-6v3Z" fill="currentColor"/></svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route('student.announcements.index') }}" class="portal-sidebar__link {{ request()->routeIs('student.announcements.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10v4c0 .55.45 1 1 1h1l2 4h2l-1-4h6l4 2V7l-4 2H5c-.55 0-1 .45-1 1Z" fill="currentColor"/></svg>
                    <span>Announcements</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route('student.events.index') }}" class="portal-sidebar__link {{ request()->routeIs('student.events.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h3V2Zm13 8H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8Z" fill="currentColor"/></svg>
                    <span>Events</span>
                </a>
            </li>
            <li class="portal-sidebar__nav-item">
                <a href="{{ route('student.concerns.index') }}" class="portal-sidebar__link {{ request()->routeIs('student.concerns.*') ? 'active portal-sidebar__link--active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 5.94 2 10.8c0 2.78 1.46 5.26 3.74 6.87L5 22l4.33-2.37c.85.12 1.74.17 2.67.17 5.52 0 10-3.94 10-8.8S17.52 2 12 2Zm1 13h-2v-2h2v2Zm1.92-5.75-.9.92c-.72.73-1.02 1.33-1.02 2.33h-2v-.5c0-1.1.3-2.1 1.02-2.83l1.24-1.26c.36-.35.56-.84.56-1.41 0-1.09-.83-2-1.92-2-1.06 0-1.9.83-1.98 1.87H8c.09-2.16 1.84-3.87 4-3.87 2.21 0 4 1.79 4 4 0 .88-.36 1.68-1.08 2.42Z" fill="currentColor"/></svg>
                    <span>Concerns</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
