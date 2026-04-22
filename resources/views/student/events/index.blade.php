@extends('layouts.app')

@section('title', 'Student Events')
@section('page_title', 'Events')
@section('page_subtitle', 'Review event schedules and prepare the QR code sent to your email for attendance validation.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-page-stack">
        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">Upcoming student events</h1>
                <p class="portal-page-lead__text">
                    Review upcoming schedules and keep your emailed QR ready before arriving, especially for events marked as attendance-required.
                </p>
            </div>
            <span class="badge badge-primary portal-page-lead__badge">Email QR check-in</span>
        </section>

        <x-card title="Event schedule" subtitle="Tap any row to open the event details and attendance notes." padding="flush">
            <table class="portal-responsive-table" aria-label="Student events">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Location</th>
                        <th>Schedule</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th>Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td data-label="Event"><strong>{{ $event['event_title'] }}</strong></td>
                            <td data-label="Location">{{ $event['location'] }}</td>
                            <td data-label="Schedule">{{ $event['event_date'] }} {{ $event['event_time'] }}</td>
                            <td data-label="Required">
                                @if ($event['attendance_required'])
                                    <span class="badge badge-warning">Required</span>
                                @else
                                    <span class="badge badge-info">Optional</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if ($event['has_attended'])
                                    <span class="badge badge-success">Checked In</span>
                                @else
                                    <span class="badge badge-secondary">Present QR at Event</span>
                                @endif
                            </td>
                            <td data-label="Open">
                                <x-button :href="route('student.events.show', $event['id'])" size="sm" variant="secondary">View</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="dashboard-table__empty">
                                <h3>No events available</h3>
                                <p>New events will appear here once they are published for your account.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
@endsection
