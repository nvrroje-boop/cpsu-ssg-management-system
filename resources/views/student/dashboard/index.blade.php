@extends('layouts.app')

@php
    $studentActions = [
        ['label' => 'Browse Events', 'href' => route('student.events.index'), 'variant' => 'accent'],
        ['label' => 'View Announcements', 'href' => route('student.announcements.index'), 'variant' => 'secondary'],
        ['label' => 'Submit Concern', 'href' => route('student.concerns.create'), 'variant' => 'primary'],
        ['label' => 'Update Profile', 'href' => route('student.profile'), 'variant' => 'secondary'],
    ];

    $studentMetrics = [
        [
            'label' => 'Signed-in student',
            'value' => $student['name'],
            'caption' => 'Your current account session in the portal.',
            'tone' => 'green',
            'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z',
        ],
        [
            'label' => 'Attendance rate',
            'value' => $attendanceRate,
            'caption' => 'Your recorded check-ins across available events.',
            'tone' => 'amber',
            'icon' => 'M5 9.2h3V19H5V9.2Zm5.5-4.2h3V19h-3V5Zm5.5 7h3V19h-3v-7Z',
        ],
        [
            'label' => 'Upcoming events',
            'value' => count($upcomingEvents),
            'caption' => 'Events currently visible to your student account.',
            'tone' => 'info',
            'icon' => 'M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h3V2Zm13 8H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8Z',
        ],
        [
            'label' => 'Fresh notices',
            'value' => count($announcements),
            'caption' => 'Latest announcement cards loaded on your dashboard.',
            'tone' => 'green',
            'icon' => 'M4 10v4c0 .55.45 1 1 1h1l2 4h2l-1-4h6l4 2V7l-4 2H5c-.55 0-1 .45-1 1Z',
        ],
    ];
@endphp

@section('title', 'Student Dashboard')
@section('page_title', 'Student Dashboard')
@section('page_subtitle', 'Check announcements, upcoming events, and your current participation status.')

@push('page-css')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="dashboard dashboard--student">
        <section class="dashboard__hero dashboard-hero">
            <div class="dashboard-hero__content">
                <span class="dashboard-hero__eyebrow">Student workspace</span>
                <h1 class="dashboard-hero__title">Welcome back, {{ $student['name'] }}</h1>
                <p class="dashboard-hero__description">
                    Track campus updates, monitor your attendance progress, and reach the tools you use most on mobile or desktop.
                </p>
                <div class="dashboard-hero__meta">
                    <span class="dashboard-chip">{{ $student['email'] }}</span>
                    <span class="dashboard-chip">{{ $attendanceRate }} attendance rate</span>
                    <span class="dashboard-chip">{{ count($upcomingEvents) }} upcoming events</span>
                </div>
            </div>

            <div class="dashboard-hero__actions">
                @foreach ($studentActions as $action)
                    <x-button :href="$action['href']" :variant="$action['variant']" class="student-action-btn">{{ $action['label'] }}</x-button>
                @endforeach
            </div>
        </section>

        <section class="dashboard__metrics" aria-label="Student metrics">
            @foreach ($studentMetrics as $metric)
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--{{ $metric['tone'] }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="{{ $metric['icon'] }}" fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <p class="dashboard-metric__label">{{ $metric['label'] }}</p>
                        <h2 class="dashboard-metric__value">{{ $metric['value'] }}</h2>
                        <p class="dashboard-metric__caption">{{ $metric['caption'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard__grid dashboard__grid--two">
            <x-card title="Latest Announcements" subtitle="Newest notices available to your account right now.">
                <div class="dashboard-list">
                    @forelse ($announcements as $announcement)
                        <article class="dashboard-list__item">
                            <div class="dashboard-list__top">
                                <h3 class="dashboard-list__title">{{ $announcement['title'] }}</h3>
                                <span class="badge badge-info">{{ $announcement['status'] }}</span>
                            </div>
                            <p class="dashboard-list__meta">Announcements remain visible here once they are published for students.</p>
                        </article>
                    @empty
                        <div class="dashboard-list__empty">
                            <strong>No announcements yet.</strong>
                            <p class="dashboard-list__note">Published announcements will appear here when they are available for your account.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Upcoming Events" subtitle="Activities you can prepare for from your phone or desktop.">
                <div class="dashboard-list">
                    @forelse ($upcomingEvents as $event)
                        <article class="dashboard-list__item">
                            <div class="dashboard-list__top">
                                <h3 class="dashboard-list__title">{{ $event['title'] }}</h3>
                                <span class="badge badge-warning">Upcoming</span>
                            </div>
                            <p class="dashboard-list__meta">{{ $event['schedule'] }}</p>
                        </article>
                    @empty
                        <div class="dashboard-list__empty">
                            <strong>No upcoming events.</strong>
                            <p class="dashboard-list__note">New activities will appear here once they are scheduled for your account.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </section>

        <section class="dashboard__grid dashboard__grid--two">
            <x-card title="Student Actions" subtitle="Jump straight to the pages you are most likely to use next.">
                <div class="dashboard-actions">
                    @foreach ($studentActions as $action)
                        <x-button :href="$action['href']" :variant="$action['variant']">{{ $action['label'] }}</x-button>
                    @endforeach
                </div>
            </x-card>

            <x-card title="Portal Notes" subtitle="A quick reminder of how your student access works.">
                <div class="dashboard-note">
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Attendance QR</h3>
                        <p class="dashboard-note__text">Keep your event QR email accessible during attendance scanning so officers can verify your check-in quickly.</p>
                    </div>
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Concern replies</h3>
                        <p class="dashboard-note__text">Submitted concerns stay in your portal history, and replies from officers or admins appear once they are posted.</p>
                    </div>
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Profile updates</h3>
                        <p class="dashboard-note__text">You can update your profile details anytime, while password resets remain an administrator task.</p>
                    </div>
                </div>
            </x-card>
        </section>
    </div>
@endsection
