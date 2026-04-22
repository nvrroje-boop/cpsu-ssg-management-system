@extends('layouts.app')

@section('title', 'My Events')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-events.css') }}">
@endpush

@section('content')
<section class="student-events">
    <h1 class="student-events__title">My Events</h1>
    <div class="student-events__table-wrapper">
        <table class="student-events__table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Attendance</th>
                </tr>
            </thead>
            <tbody>
                <!-- @foreach($events as $event) -->
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->date->format('M d, Y') }}</td>
                    <td>
                        <span class="student-events__badge student-events__badge--{{ $event->status }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="student-events__attendance student-events__attendance--{{ $event->attendance_status }}">
                            {{ ucfirst($event->attendance_status) }}
                        </span>
                    </td>
                </tr>
                <!-- @endforeach -->
            </tbody>
        </table>
    </div>
</section>
@endsection
