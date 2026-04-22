{{-- resources/views/officer/events.blade.php --}}
@extends('layouts.app')

@section('title', 'My Events')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/officer-events.css') }}">
@endpush

@section('content')
<section class="officer-events">
    <h1 class="officer-events__title">My Events</h1>
    <a href="{{ route('officer.events.create') }}" class="btn officer-events__add-btn">Create Event</a>
    <div class="officer-events__table-wrap">
        <table class="officer-events__table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Attendance</th>
                    <th>QR</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td data-label="Name">{{ $event->event_title }}</td>
                    <td data-label="Date">{{ $event->event_date ? $event->event_date->format('M d, Y') : '' }} {{ $event->event_time ? substr((string) $event->event_time, 0, 5) : '' }}</td>
                    <td data-label="Attendance">{{ $event->attendance_count ?? 0 }}</td>
                    <td data-label="QR">
                        <button class="btn btn--sm btn--qr" data-event-id="{{ $event->id }}">QR</button>
                    </td>
                    <td data-label="Actions">
                        <a href="{{ route('officer.events.edit', $event) }}" class="btn btn--sm">Edit</a>
                        <form action="{{ route('officer.events.destroy', $event) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="officer-events__empty">No events found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="officer-events__pagination">
        {{ $events->links() }}
    </div>
    {{-- QR Modal --}}
    <x-modal id="qrModal">
        <div class="modal__content modal__content--qr">
            <h2 class="modal__title">Event QR Code</h2>
            <div id="qrCodeContainer" class="qr-code-container"></div>
            <button class="btn modal__close">Close</button>
        </div>
    </x-modal>
</section>
@push('scripts')
<script src="{{ asset('js/officer-events.js') }}" defer></script>
@endpush
@endsection
