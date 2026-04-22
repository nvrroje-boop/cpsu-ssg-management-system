@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile')
@section('page_subtitle', 'View and update your personal information.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $routePrefix = request()->routeIs('admin.*')
            ? 'admin'
            : (request()->routeIs('officer.*') ? 'officer' : 'student');
        $isAdmin = $routePrefix === 'admin';
    @endphp

    <div class="portal-detail-stack">
        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Personal Information" subtitle="Update the account details that appear in the portal.">
                <form method="POST" action="{{ route($routePrefix.'.profile.update') }}" class="portal-form-stack">
                    @csrf
                    @method('PATCH')

                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="name">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" required>
                            @error('name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $student->email) }}" required>
                            @error('email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone) }}" placeholder="Optional contact number">
                            @error('phone')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="course">Course</label>
                            <input id="course" name="course" type="text" value="{{ old('course', $student->course) }}" placeholder="Optional course or program">
                            @error('course')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="actions">
                        <x-button type="submit">Update Profile</x-button>
                    </div>
                </form>
            </x-card>

            <x-card title="Academic Information" subtitle="Reference-only details linked to your current account record.">
                <div class="portal-kv">
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Student Number</p>
                        <p class="portal-kv__value">{{ $student->student_number }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Role</p>
                        <p class="portal-kv__value">{{ $student->role?->display_name ?? $student->role?->role_name ?? 'N/A' }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Department</p>
                        <p class="portal-kv__value">{{ $student->department?->department_name ?? 'N/A' }}</p>
                    </div>
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Section</p>
                        <p class="portal-kv__value">{{ $student->section?->section_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-card>
        </section>

        <x-card title="Change Password" subtitle="Passwords must be at least 8 characters and include uppercase, lowercase, and numbers.">
            <form method="POST" action="{{ route($routePrefix.'.profile.password') }}" class="portal-form-stack">
                @csrf

                <div class="portal-form-grid">
                    <div class="field">
                        <label for="current_password">Current Password</label>
                        <input id="current_password" name="current_password" type="password" required>
                        @error('current_password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">New Password</label>
                        <input id="password" name="password" type="password" required>
                        @error('password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>

                <div class="actions">
                    <x-button type="submit">Change Password</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Student QR Pass" subtitle="Present this QR at the attendance kiosk for secure identification.">
            <div class="portal-form-stack" style="align-items: center;">
                @if (!empty($studentQrImage))
                    <img src="{{ $studentQrImage }}" alt="Student QR pass" style="max-width: 240px; border-radius: 18px; background: #fff; padding: 1rem;">
                @endif
                <div class="portal-note-box">
                    <h2 class="portal-note-box__title">How it works</h2>
                    <p class="portal-note-box__text">Your QR identifies you to the kiosk, but attendance is still tied to the active event session. That prevents a student QR from being used as attendance by itself.</p>
                </div>
            </div>
        </x-card>

        <x-card title="Semester Attendance Summary" subtitle="Your current totals for required attendance this semester.">
            <div class="portal-stat-grid">
                <div class="portal-stat-tile portal-stat-tile--info">
                    <p class="portal-stat-tile__label">{{ $semesterSummary['label'] }}</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['required_events'] }}</h2>
                    <p class="portal-helper">Required events from {{ $semesterSummary['range'] }}</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--success">
                    <p class="portal-stat-tile__label">Present</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['present_count'] }}</h2>
                    <p class="portal-helper">Recorded check-ins</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--danger">
                    <p class="portal-stat-tile__label">Absences</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['absence_count'] }}</h2>
                    <p class="portal-helper">Required events you missed</p>
                </div>
                <div class="portal-stat-tile portal-stat-tile--warning">
                    <p class="portal-stat-tile__label">Attendance Rate</p>
                    <h2 class="portal-stat-tile__value">{{ $semesterSummary['attendance_rate'] }}</h2>
                    <p class="portal-helper">Useful for end-of-semester clearance review</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection
