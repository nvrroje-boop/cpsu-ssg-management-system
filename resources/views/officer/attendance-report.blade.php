@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="attendance-report">
    <h1 class="attendance-report__title">Attendance Report</h1>
    <div class="attendance-report__event-select">
        <label for="event-select" class="attendance-report__label">Select Event:</label>
        <select id="event-select" class="attendance-report__select">
            <option value="">-- Choose Event --</option>
            <!-- @foreach($events as $event) -->
            <option value="{{ $event->id }}">{{ $event->title }}</option>
            <!-- @endforeach -->
        </select>
    </div>
    <div class="attendance-report__table-wrapper">
        <table class="attendance-report__table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- @foreach($students as $student) -->
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->name }}</td>
                    <td>
                        <span class="attendance-report__badge attendance-report__badge--{{ $student->attendance_status }}">
                            {{ ucfirst($student->attendance_status) }}
                        </span>
                    </td>
                </tr>
                <!-- @endforeach -->
            </tbody>
        </table>
    </div>
    <div class="attendance-report__summary">
        <span>Total: <span id="total-count">0</span></span>
        <span>Present: <span id="present-count">0</span></span>
        <span>Absent: <span id="absent-count">0</span></span>
    </div>
    <button class="attendance-report__print-btn" onclick="window.print()">Print Report</button>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-report.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/attendance-report.js') }}" defer></script>
@endpush
