@extends('layouts.app')

@section('title', 'Events')
@section('page_title', 'Event Management')

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--sp-lg);">
        <div>
            <p style="color: var(--muted);">Create and manage campus events</p>
        </div>
        <a href="{{ route($managementRoutePrefix.'.events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Event
        </a>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Location</th>
                            <th>Schedule</th>
                            <th>Visibility</th>
                            <th>Department</th>
                            <th>Attendance</th>
                            <th>QR Issued</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td><strong>{{ $event->event_title }}</strong></td>
                                <td>{{ $event->location }}</td>
                                <td>
                                    <small>{{ optional($event->event_date)->format('Y-m-d') ?? $event->event_date }} {{ $event->event_time }}</small>
                                </td>
                                <td><span class="badge badge-secondary">{{ $event->visibility }}</span></td>
                                <td>{{ $event->department?->department_name ?? 'All departments' }}</td>
                                <td>
                                    @if ($event->attendance_required)
                                        <span class="badge badge-warning">Required</span>
                                    @else
                                        <span class="badge badge-primary">Optional</span>
                                    @endif
                                </td>
                                <td>{{ $event->event_qrs_count }}</td>
                                <td>
                                    <div style="display: flex; gap: var(--sp-sm);">
                                        <a href="{{ route($managementRoutePrefix.'.events.show', $event->id) }}" class="btn btn-sm btn-ghost" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route($managementRoutePrefix.'.events.edit', $event->id) }}" class="btn btn-sm btn-ghost" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route($managementRoutePrefix.'.events.destroy', $event->id) }}" style="display: inline;" onsubmit="return confirm('Delete this event?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--danger);" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: var(--sp-xl); color: var(--muted);">
                                    <i class="fas fa-calendar-times" style="font-size: 2rem; opacity: 0.3; margin-bottom: var(--sp-md); display: block;"></i>
                                    No events scheduled
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
