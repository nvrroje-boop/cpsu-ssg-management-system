@extends('layouts.app')

@section('title', 'Attendance Tracking')

@section('content')
<div class="attendance-tracking">
    <h1 class="attendance-tracking__title">Attendance Tracking</h1>
    <div class="attendance-tracking__event-select">
        <label for="event-select" class="attendance-tracking__label">Select Event:</label>
        <select id="event-select" class="attendance-tracking__select">
            <option value="">-- Choose Event --</option>
            <!-- @foreach($events as $event) -->
            <option value="{{ $event->id }}">{{ $event->title }}</option>
            <!-- @endforeach -->
        </select>
    </div>
    <div class="attendance-tracking__table-wrapper">
        <table class="attendance-tracking__table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Mark Attendance</th>
                </tr>
            </thead>
            <tbody>
                <!-- @foreach($students as $student) -->
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->name }}</td>
                    <td>
                        <span class="attendance-tracking__badge attendance-tracking__badge--{{ $student->attendance_status }}">
                            {{ ucfirst($student->attendance_status) }}
                        </span>
                    </td>
                    <td>
                        <button class="attendance-tracking__mark-btn" data-student-id="{{ $student->id }}" data-status="present">Present</button>
                        <button class="attendance-tracking__mark-btn attendance-tracking__mark-btn--absent" data-student-id="{{ $student->id }}" data-status="absent">Absent</button>
                    </td>
                </tr>
                <!-- @endforeach -->
            </tbody>
        </table>
    </div>
    <div class="attendance-tracking__live-count">
        <span>Present: <span id="present-count">0</span></span>
        <span>Absent: <span id="absent-count">0</span></span>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-tracking.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/attendance-tracking.js') }}" defer></script>
@endpush
