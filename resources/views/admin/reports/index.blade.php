@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports Overview')
@section('page_subtitle', 'Quickly review report availability, reporting windows, and the headline metrics that matter for SSG operations.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-page-stack">
        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">Reports and summaries</h1>
                <p class="portal-page-lead__text">Use this overview as the starting point for attendance summaries, activity tracking, and other system-wide reporting work.</p>
            </div>
        </section>

        <section class="portal-report-grid">
            <x-card title="Available reports" subtitle="Current report sets and the period each one covers." padding="flush">
                <div class="portal-table-wrap">
                    <table class="portal-responsive-table">
                        <thead>
                            <tr>
                                <th>Report</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td data-label="Report">
                                        <span class="portal-table-cell-stack__title">{{ $report['name'] }}</span>
                                    </td>
                                    <td data-label="Period">
                                        <span class="portal-table-cell-stack__meta">{{ $report['period'] }}</span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge badge-{{ $report['status'] === 'Ready' ? 'primary' : 'warning' }}">
                                            {{ $report['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Key highlights" subtitle="A compact summary of the most important current metrics from across the portal.">
                <div class="portal-highlight-list">
                    @foreach ($highlights as $label => $value)
                        <div class="portal-highlight-list__item">
                            <p class="portal-highlight-list__label">{{ $label }}</p>
                            <p class="portal-highlight-list__value">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </section>

        <x-card
            :title="'Semester clearance summary - '.$clearanceSummary['label']"
            :subtitle="'Attendance and absence totals based on recorded scans for '.$clearanceSummary['range'].'. Use this table when reviewing clearance requirements.'"
            padding="flush"
        >
            <div class="portal-card__body portal-card__body--compact">
                <div class="portal-stat-grid">
                    <div class="portal-stat-tile portal-stat-tile--info">
                        <p class="portal-stat-tile__label">Students</p>
                        <h2 class="portal-stat-tile__value">{{ $clearanceSummary['student_count'] }}</h2>
                        <p class="portal-helper">Tracked student records in the active semester</p>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--success">
                        <p class="portal-stat-tile__label">Total Present</p>
                        <h2 class="portal-stat-tile__value">{{ $clearanceSummary['total_present_count'] }}</h2>
                        <p class="portal-helper">Recorded required-event check-ins</p>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--danger">
                        <p class="portal-stat-tile__label">Total Absences</p>
                        <h2 class="portal-stat-tile__value">{{ $clearanceSummary['total_absence_count'] }}</h2>
                        <p class="portal-helper">Required attendance slots missed</p>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--warning">
                        <p class="portal-stat-tile__label">Students With Absences</p>
                        <h2 class="portal-stat-tile__value">{{ $clearanceSummary['students_with_absences'] }}</h2>
                        <p class="portal-helper">Review these accounts before clearance signing</p>
                    </div>
                </div>
            </div>

            <div class="portal-table-wrap">
                <table class="portal-responsive-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Department</th>
                            <th>Section</th>
                            <th>Required Events</th>
                            <th>Present</th>
                            <th>Absences</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clearanceSummary['rows'] as $row)
                            <tr>
                                <td data-label="Student">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $row['name'] }}</p>
                                        <p class="portal-table-cell-stack__meta">{{ $row['student_number'] }}</p>
                                    </div>
                                </td>
                                <td data-label="Department"><span class="portal-table-cell-stack__meta">{{ $row['department'] }}</span></td>
                                <td data-label="Section"><span class="portal-table-cell-stack__meta">{{ $row['section'] }}</span></td>
                                <td data-label="Required Events">{{ $row['required_events'] }}</td>
                                <td data-label="Present">{{ $row['present_count'] }}</td>
                                <td data-label="Absences">
                                    <span class="badge badge-{{ $row['absence_count'] > 0 ? 'danger' : 'success' }}">
                                        {{ $row['absence_count'] }}
                                    </span>
                                </td>
                                <td data-label="Attendance Rate">{{ $row['attendance_rate'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="portal-responsive-table__empty">
                                    <div class="portal-empty">
                                        <div class="portal-empty__icon">
                                            <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                                        </div>
                                        <h3 class="portal-empty__title">No clearance records yet</h3>
                                        <p class="portal-empty__text">Attendance totals will appear here once student accounts and required events are present for the semester.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
