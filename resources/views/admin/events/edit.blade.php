@extends('layouts.app')

@section('title', 'Edit Event')
@section('page_title', 'Edit Event')
@section('page_subtitle', 'Update the event details, schedule, and attendance settings.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-detail-grid portal-detail-grid--two">
        <x-card title="Edit Event" subtitle="Update the current event information before the next attendance cycle.">
            <form method="POST" action="{{ route($managementRoutePrefix.'.events.update', $event->id) }}" class="portal-form-stack">
                @csrf
                @method('PUT')

                <div class="portal-form-grid">
                    <div class="field field--full">
                        <label for="event_title">Title</label>
                        <input id="event_title" name="event_title" type="text" value="{{ old('event_title', $event->event_title) }}" required>
                        @error('event_title')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="location">Location</label>
                        <input id="location" name="location" type="text" value="{{ old('location', $event->location) }}" required>
                        @error('location')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department['id'] }}" @selected((int) old('department_id', $event->department_id) === $department['id'])>{{ $department['department_name'] }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="event_date">Date</label>
                        <input id="event_date" name="event_date" type="date" value="{{ old('event_date', optional($event->event_date)->format('Y-m-d') ?? $event->event_date) }}" required>
                        @error('event_date')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="event_time">Time</label>
                        <input id="event_time" name="event_time" type="time" value="{{ old('event_time', substr((string) $event->event_time, 0, 5)) }}" required>
                        @error('event_time')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="visibility">Visibility</label>
                        <select id="visibility" name="visibility" required>
                            <option value="public" @selected(old('visibility', $event->visibility) === 'public')>Public</option>
                            <option value="private" @selected(old('visibility', $event->visibility) === 'private')>Private</option>
                        </select>
                        @error('visibility')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_required">Attendance</label>
                        <select id="attendance_required" name="attendance_required">
                            <option value="1" @selected((string) old('attendance_required', $event->attendance_required ? '1' : '0') === '1')>Required</option>
                            <option value="0" @selected((string) old('attendance_required', $event->attendance_required ? '1' : '0') === '0')>Optional</option>
                        </select>
                        @error('attendance_required')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="event_description">Description</label>
                        <textarea id="event_description" name="event_description" rows="7" required>{{ old('event_description', $event->event_description) }}</textarea>
                        @error('event_description')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_in_starts_at">Time-In Starts</label>
                        <input id="attendance_time_in_starts_at" name="attendance_time_in_starts_at" type="time" value="{{ old('attendance_time_in_starts_at', substr((string) $event->attendance_time_in_starts_at, 0, 5)) }}">
                        @error('attendance_time_in_starts_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_in_ends_at">Time-In Ends</label>
                        <input id="attendance_time_in_ends_at" name="attendance_time_in_ends_at" type="time" value="{{ old('attendance_time_in_ends_at', substr((string) $event->attendance_time_in_ends_at, 0, 5)) }}">
                        @error('attendance_time_in_ends_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_late_after">Late After</label>
                        <input id="attendance_late_after" name="attendance_late_after" type="time" value="{{ old('attendance_late_after', substr((string) $event->attendance_late_after, 0, 5)) }}">
                        @error('attendance_late_after')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_out_starts_at">Time-Out Starts</label>
                        <input id="attendance_time_out_starts_at" name="attendance_time_out_starts_at" type="time" value="{{ old('attendance_time_out_starts_at', substr((string) $event->attendance_time_out_starts_at, 0, 5)) }}">
                        @error('attendance_time_out_starts_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_out_ends_at">Time-Out Ends</label>
                        <input id="attendance_time_out_ends_at" name="attendance_time_out_ends_at" type="time" value="{{ old('attendance_time_out_ends_at', substr((string) $event->attendance_time_out_ends_at, 0, 5)) }}">
                        @error('attendance_time_out_ends_at')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="actions">
                    <x-button :href="route($managementRoutePrefix.'.events.index')" variant="secondary">Back</x-button>
                    <x-button type="submit">Update Event</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Attendance Configuration" subtitle="Review kiosk windows and secure event QR readiness.">
            <div class="portal-form-stack">
                <div class="portal-kv">
                    <div class="portal-kv__row"><p class="portal-kv__label">Schedule</p><p class="portal-kv__value">{{ optional($event->event_date)->format('Y-m-d') ?? $event->event_date }} {{ $event->event_time }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Location</p><p class="portal-kv__value">{{ $event->location }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Time-In Window</p><p class="portal-kv__value">{{ substr((string) $event->attendance_time_in_starts_at, 0, 5) }} - {{ substr((string) $event->attendance_time_in_ends_at, 0, 5) }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Time-Out Window</p><p class="portal-kv__value">{{ substr((string) $event->attendance_time_out_starts_at, 0, 5) }} - {{ substr((string) $event->attendance_time_out_ends_at, 0, 5) }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Recorded Attendances</p><p class="portal-kv__value">{{ $event->attendances_count }}</p></div>
                </div>

                <div class="portal-note-box">
                    <h2 class="portal-note-box__title">Kiosk Guidance</h2>
                    <p class="portal-note-box__text">Save event changes first. Attendance start/stop, QR rotation, kiosk scanning, and manual overrides are managed from the event details page.</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection
