@extends('layouts.app')

@section('title', 'Account Details')
@section('page_title', 'Student or Officer Information')
@section('page_subtitle', 'Detailed view of the selected account record.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $student->name }}</h1>
                <p class="portal-detail-header__text">Record ID: {{ $student->id }}</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('admin.students.index')" variant="secondary">Back to Accounts</x-button>
                <x-button :href="route('admin.students.edit', $student->id)">Edit Account</x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Personal Information" subtitle="Core details used for portal access and identity.">
                <div class="portal-kv">
                    <div class="portal-kv__row"><p class="portal-kv__label">Name</p><p class="portal-kv__value">{{ $student->name }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Email</p><p class="portal-kv__value">{{ $student->email }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Phone</p><p class="portal-kv__value">{{ $student->phone ?: 'N/A' }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Course</p><p class="portal-kv__value">{{ $student->course ?: 'N/A' }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Student Number</p><p class="portal-kv__value">{{ $student->student_number }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Role</p><p class="portal-kv__value">{{ $student->role?->display_name ?? $student->role?->role_name ?? 'N/A' }}</p></div>
                </div>
            </x-card>

            <x-card title="Academic and Access Information" subtitle="Program placement and admin-only account guidance.">
                <div class="portal-form-stack">
                    <div class="portal-kv">
                        <div class="portal-kv__row"><p class="portal-kv__label">Department</p><p class="portal-kv__value">{{ $student->department?->department_name ?? 'N/A' }}</p></div>
                        <div class="portal-kv__row"><p class="portal-kv__label">Section</p><p class="portal-kv__value">{{ $student->section?->section_name ?? 'N/A' }}</p></div>
                        <div class="portal-kv__row"><p class="portal-kv__label">Created</p><p class="portal-kv__value">{{ $student->created_at->format('M d, Y H:i') }}</p></div>
                    </div>

                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">Password Policy</h2>
                        <p class="portal-note-box__text">Only admins can issue or reset passwords for this account.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.students.resend-credentials', $student->id) }}" onsubmit="return confirm('Resend credentials to this account? This will generate a new temporary password.');">
                        @csrf
                        <x-button type="submit" variant="secondary">Resend Credentials Email</x-button>
                    </form>
                </div>
            </x-card>
        </section>

        <x-card title="Semester Attendance Summary" subtitle="Use this running total when reviewing attendance-based clearance requirements.">
            <div class="portal-stat-grid">
                <div class="portal-stat-tile portal-stat-tile--info">
                    <p class="portal-stat-tile__label">{{ $semesterSummary['label'] }}</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['required_events'] }}</h2>
                    <p class="portal-helper">Required events in range {{ $semesterSummary['range'] }}</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--success">
                    <p class="portal-stat-tile__label">Present</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['present_count'] }}</h2>
                    <p class="portal-helper">Recorded QR check-ins</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--danger">
                    <p class="portal-stat-tile__label">Absences</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['absence_count'] }}</h2>
                    <p class="portal-helper">Required events without attendance</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--warning">
                    <p class="portal-stat-tile__label">Attendance Rate</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['attendance_rate'] }}</h2>
                    <p class="portal-helper">Present versus required events</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection
