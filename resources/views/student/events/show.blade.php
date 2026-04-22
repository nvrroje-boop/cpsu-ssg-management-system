@extends('layouts.app')

@section('title', 'Event Details')
@section('page_title', 'Event Information')
@section('page_subtitle', 'Detailed information about the selected event and your attendance status.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $event->event_title }}</h1>
                <p class="portal-detail-header__text">
                    {{ optional($event->event_date)->format('F d, Y') ?? $event->event_date }} at {{ $event->event_time }}
                </p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('student.events.index')" variant="secondary">Back to Events</x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Event Details" subtitle="Schedule, location, and attendance expectations for this activity.">
                <div class="portal-kv">
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Title</p>
                        <p class="portal-kv__value">{{ $event->event_title }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Location</p>
                        <p class="portal-kv__value">{{ $event->location }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Date</p>
                        <p class="portal-kv__value">{{ optional($event->event_date)->format('F d, Y') ?? $event->event_date }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Time</p>
                        <p class="portal-kv__value">{{ $event->event_time }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Attendance</p>
                        <p class="portal-kv__value">
                            <span class="badge {{ $event->attendance_required ? 'badge-warning' : 'badge-info' }}">
                                {{ $event->attendance_required ? 'Required' : 'Optional' }}
                            </span>
                        </p>
                    </div>
                </div>
            </x-card>

            <x-card title="Description and Check-In" subtitle="How to prepare before arriving at the event.">
                <div class="portal-form-stack">
                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">Event Description</h2>
                        <p class="portal-note-box__text">{{ $event->event_description }}</p>
                    </div>

                    @if ($attendanceRecord)
                        <div class="portal-note-box portal-note-box--accent">
                            <h2 class="portal-note-box__title">Attendance Status</h2>
                            <p class="portal-note-box__text">
                                Status: {{ ucfirst($attendanceRecord->status) }}.
                                @if ($attendanceRecord->time_in)
                                    Time-in: {{ $attendanceRecord->time_in->format('M d, Y h:i A') }}.
                                @endif
                                @if ($attendanceRecord->time_out)
                                    Time-out: {{ $attendanceRecord->time_out->format('M d, Y h:i A') }}.
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">Attendance Instructions</h2>
                        <p class="portal-note-box__text">Present your student QR at the kiosk so an Admin or SSG Officer can record your attendance. If the secure event QR is active on site, you can also use the self-check-in link below.</p>
                    </div>

                    @if (!empty($studentQrImage))
                        <div style="max-width: 240px; margin: 0 auto;">
                            <img src="{{ $studentQrImage }}" alt="Student QR pass" style="border-radius: 18px; background: #fff; padding: 1rem;">
                        </div>
                    @endif

                    @if ($event->attendance_active && filled($event->attendance_token) && $event->attendance_token_expires_at !== null)
                        <x-button :href="route('student.events.self-scan', ['event' => $event->id, 'token' => $event->attendance_token, 'expires' => $event->attendance_token_expires_at->timestamp])">Self Check-In</x-button>
                    @endif
                </div>
            </x-card>
        </section>
    </div>
@endsection
