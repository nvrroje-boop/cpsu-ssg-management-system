@extends('layouts.app')

@php
    $isOfficerPortal = request()->routeIs('officer.*');
    $routePrefix = $isOfficerPortal ? 'officer' : 'admin';
    $dashboardLabel = $isOfficerPortal ? 'Officer Dashboard' : 'Admin Dashboard';
    $dashboardSubtitle = $isOfficerPortal
        ? 'Monitor announcements, events, attendance, and current student activity.'
        : 'Monitor campus operations, attendance activity, and current publishing status.';
    $dashboardEyebrow = $isOfficerPortal ? 'Officer workspace' : 'Administrator workspace';
    $primaryActionLabel = $isOfficerPortal ? 'Create Event' : 'Manage Accounts';
    $primaryActionRoute = $isOfficerPortal ? route('officer.events.create') : route('admin.students.index');
    $secondaryActionLabel = $isOfficerPortal ? 'Review Concerns' : 'Open Attendance';
    $secondaryActionRoute = $isOfficerPortal ? route('officer.concerns.index') : route('admin.attendance.index');

    $metricCards = [
        [
            'label' => $isOfficerPortal ? 'Student audience' : 'Managed accounts',
            'value' => $stats['students'],
            'caption' => $isOfficerPortal ? 'Students included in the current portal audience.' : 'Student records available in the governance portal.',
            'tone' => 'green',
            'icon' => 'M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4Zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4Zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.96 1.97 3.45V20H23v-3.5c0-2.33-4.67-3.5-7-3.5Z',
        ],
        [
            'label' => 'Active events',
            'value' => $stats['active_events'],
            'caption' => 'Events currently open for attendance or nearing schedule time.',
            'tone' => 'amber',
            'icon' => 'M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h3V2Zm13 8H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8Z',
        ],
        [
            'label' => 'Attendance scans today',
            'value' => $stats['today_scans'],
            'caption' => 'QR check-ins recorded today across campus activities.',
            'tone' => 'info',
            'icon' => 'M3 5a2 2 0 0 1 2-2h5v2H5v14h14v-5h2v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5Zm13.59-2L21 7.41 11.41 17H7v-4.41L16.59 3Z',
        ],
        [
            'label' => 'Attendance rate',
            'value' => $stats['attendance_rate'],
            'caption' => 'Current check-in completion against tracked participation.',
            'tone' => 'green',
            'icon' => 'M5 9.2h3V19H5V9.2Zm5.5-4.2h3V19h-3V5Zm5.5 7h3V19h-3v-7Z',
        ],
    ];

    $quickActions = [
        [
            'label' => $primaryActionLabel,
            'href' => $primaryActionRoute,
            'variant' => 'accent',
        ],
        [
            'label' => 'Publish Announcement',
            'href' => route($routePrefix.'.announcements.create'),
            'variant' => 'primary',
        ],
        [
            'label' => 'Attendance Scanner',
            'href' => route($routePrefix.'.attendance.index'),
            'variant' => 'secondary',
        ],
        [
            'label' => $secondaryActionLabel,
            'href' => $secondaryActionRoute,
            'variant' => 'secondary',
        ],
    ];
@endphp

@section('title', $dashboardLabel)
@section('page_title', 'Dashboard')
@section('page_subtitle', $dashboardSubtitle)

@push('page-css')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="dashboard dashboard--management">
        <section class="dashboard__hero dashboard-hero">
            <div class="dashboard-hero__content">
                <span class="dashboard-hero__eyebrow">{{ $dashboardEyebrow }}</span>
                <h1 class="dashboard-hero__title">{{ $dashboardLabel }}</h1>
                <p class="dashboard-hero__description">{{ $dashboardSubtitle }}</p>
                <div class="dashboard-hero__meta">
                    <span class="dashboard-chip">{{ $stats['active_events'] }} active events</span>
                    <span class="dashboard-chip">{{ $stats['today_scans'] }} scans today</span>
                    <span class="dashboard-chip">{{ $stats['attendance_rate'] }} attendance rate</span>
                </div>
            </div>

            <div class="dashboard-hero__actions">
                @foreach ($quickActions as $action)
                    <x-button :href="$action['href']" :variant="$action['variant']">{{ $action['label'] }}</x-button>
                @endforeach
            </div>
        </section>

        <section class="dashboard__metrics" aria-label="Dashboard metrics">
            @foreach ($metricCards as $metric)
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
            <x-card title="Attendance by Event" subtitle="Top events ranked by recorded check-ins this cycle.">
                <div class="dashboard-chart">
                    <canvas id="attendanceChart" aria-label="Attendance by event chart"></canvas>
                </div>
            </x-card>

            <x-card title="Recent Announcements" subtitle="Newest announcements published for the campus portal.">
                <div class="dashboard-list">
                    @forelse ($recentAnnouncements as $announcement)
                        <article class="dashboard-list__item">
                            <div class="dashboard-list__top">
                                <h3 class="dashboard-list__title">{{ $announcement['title'] }}</h3>
                                <span class="badge badge-info">{{ $announcement['status'] }}</span>
                            </div>
                            <p class="dashboard-list__meta">Publishing status is synced from the announcement workflow.</p>
                        </article>
                    @empty
                        <div class="dashboard-list__empty">
                            <strong>No announcements yet.</strong>
                            <p class="dashboard-list__note">Published notices will appear here once they are created.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </section>

        <section class="dashboard__grid dashboard__grid--two">
            <x-card title="Upcoming Events" subtitle="What the campus calendar is expecting next.">
                <div class="dashboard-list">
                    @forelse ($upcomingEvents as $event)
                        <article class="dashboard-list__item">
                            <div class="dashboard-list__top">
                                <h3 class="dashboard-list__title">{{ $event['title'] }}</h3>
                                <span class="badge badge-warning">Scheduled</span>
                            </div>
                            <p class="dashboard-list__meta">{{ $event['date'] }}</p>
                        </article>
                    @empty
                        <div class="dashboard-list__empty">
                            <strong>No upcoming events.</strong>
                            <p class="dashboard-list__note">Scheduled activities will appear here after events are created.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Operations Checklist" subtitle="Fast links for the tasks management users handle most often.">
                <div class="dashboard-note">
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Announcements</h3>
                        <p class="dashboard-note__text">Keep student-facing notices current so the public feed and dashboards stay aligned.</p>
                    </div>
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Attendance flow</h3>
                        <p class="dashboard-note__text">Use the scanner during events and check the latest attendance table for immediate verification.</p>
                    </div>
                    <div class="dashboard-note__item">
                        <h3 class="dashboard-note__title">Concern handling</h3>
                        <p class="dashboard-note__text">Review pending concerns frequently so responses are visible to students without delay.</p>
                    </div>
                </div>
            </x-card>
        </section>

        <x-card title="Recent Attendance" subtitle="Latest scans captured by the QR attendance workflow." padding="flush">
            <div class="dashboard-table-wrap">
                <table class="dashboard-table" aria-label="Recent attendance records">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Event</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentAttendance as $entry)
                            <tr>
                                <td data-label="Student">{{ $entry['student'] }}</td>
                                <td data-label="Event">{{ $entry['event'] }}</td>
                                <td data-label="Time">{{ $entry['time'] }}</td>
                                <td data-label="Status"><span class="badge badge-success">{{ $entry['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="dashboard-table__empty">
                                    <h3>No attendance entries yet</h3>
                                    <p>Scans recorded today will appear in this table.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection

@push('page-js')
<script>
    Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6B7280';

    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Attendance Count',
                data: {!! json_encode($chartData) !!},
                backgroundColor: 'rgba(46, 139, 74, 0.15)',
                borderColor: '#2E8B4A',
                borderWidth: 2,
                borderRadius: 10,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#0F2D1C',
                        font: {
                            weight: '600',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#0F2D1C',
                    titleColor: '#F5EDE0',
                    bodyColor: 'rgba(245,237,224,0.82)',
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(15,45,28,0.06)'
                    },
                    ticks: {
                        color: '#6B7280'
                    }
                }
            }
        }
    });
</script>
@endpush
