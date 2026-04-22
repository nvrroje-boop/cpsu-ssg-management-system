@extends('layouts.app')

@section('title', 'Create Event')
@section('page_title', 'Create Event')
@section('page_subtitle', 'Create a campus event with schedule, audience, and attendance settings.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-form-shell">
        <x-card title="New Event" subtitle="Set the schedule, target audience, and attendance requirement before publishing.">
            <form method="POST" action="{{ route($managementRoutePrefix.'.events.store') }}" class="portal-form-stack">
                @csrf

                <div class="portal-form-grid">
                    <div class="field field--full">
                        <label for="event_title">Title</label>
                        <input id="event_title" name="event_title" type="text" value="{{ old('event_title') }}" required>
                        @error('event_title')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="location">Location</label>
                        <input id="location" name="location" type="text" value="{{ old('location') }}" required>
                        @error('location')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department['id'] }}" @selected((int) old('department_id') === $department['id'])>{{ $department['department_name'] }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="event_date">Date</label>
                        <input id="event_date" name="event_date" type="date" value="{{ old('event_date') }}" required>
                        @error('event_date')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="event_time">Time</label>
                        <input id="event_time" name="event_time" type="time" value="{{ old('event_time') }}" required>
                        @error('event_time')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="visibility">Visibility</label>
                        <select id="visibility" name="visibility" required>
                            <option value="public" @selected(old('visibility', 'public') === 'public')>Public</option>
                            <option value="private" @selected(old('visibility') === 'private')>Private</option>
                        </select>
                        @error('visibility')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_required">Attendance</label>
                        <select id="attendance_required" name="attendance_required">
                            <option value="1" @selected(old('attendance_required', '1') === '1')>Required</option>
                            <option value="0" @selected(old('attendance_required') === '0')>Optional</option>
                        </select>
                        @error('attendance_required')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="event_description">Description</label>
                        <textarea id="event_description" name="event_description" rows="7" required>{{ old('event_description') }}</textarea>
                        @error('event_description')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_in_starts_at">Time-In Starts</label>
                        <input id="attendance_time_in_starts_at" name="attendance_time_in_starts_at" type="time" value="{{ old('attendance_time_in_starts_at') }}">
                        @error('attendance_time_in_starts_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_in_ends_at">Time-In Ends</label>
                        <input id="attendance_time_in_ends_at" name="attendance_time_in_ends_at" type="time" value="{{ old('attendance_time_in_ends_at') }}">
                        @error('attendance_time_in_ends_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_late_after">Late After</label>
                        <input id="attendance_late_after" name="attendance_late_after" type="time" value="{{ old('attendance_late_after') }}">
                        @error('attendance_late_after')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_out_starts_at">Time-Out Starts</label>
                        <input id="attendance_time_out_starts_at" name="attendance_time_out_starts_at" type="time" value="{{ old('attendance_time_out_starts_at') }}">
                        @error('attendance_time_out_starts_at')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="attendance_time_out_ends_at">Time-Out Ends</label>
                        <input id="attendance_time_out_ends_at" name="attendance_time_out_ends_at" type="time" value="{{ old('attendance_time_out_ends_at') }}">
                        @error('attendance_time_out_ends_at')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="actions">
                    <x-button :href="route($managementRoutePrefix.'.events.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">Create Event</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
