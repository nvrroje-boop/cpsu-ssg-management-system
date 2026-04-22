{{-- resources/views/partials/_nav.blade.php --}}
<nav class="nav">
    <div class="nav__container">
        <a href="{{ route('welcome') }}" class="nav__brand">
            <img src="{{ asset('ssg-logo.png') }}" alt="SSG Logo" class="nav__logo">
            <span class="nav__title">SSG Management System</span>
        </a>
        <button class="nav__toggle" aria-label="Open navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <div class="nav__menu">
            <a href="{{ route('welcome') }}" class="nav__item">Home</a>
            <a href="{{ route('student.events') }}" class="nav__item">Events</a>
            <a href="{{ route('student.announcements') }}" class="nav__item">Announcements</a>
            @auth
                @role('admin')
                    <a href="{{ route('admin.dashboard') }}" class="nav__item">Admin</a>
                @endrole
                @role('officer')
                    <a href="{{ route('officer.dashboard') }}" class="nav__item">Officer</a>
                @endrole
                @role('student')
                    <a href="{{ route('student.dashboard') }}" class="nav__item">Dashboard</a>
                @endrole
                <form method="POST" action="{{ route('logout') }}" class="nav__logout-form">
                    @csrf
                    <button type="submit" class="nav__item nav__item--logout">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav__item nav__item--login">Login</a>
            @endauth
        </div>
    </div>
</nav>
