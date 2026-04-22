@extends('layouts.app')

@section('title', 'Concern Details')
@section('page_title', 'Concern Details')
@section('page_subtitle', 'Review the concern, update its workflow state, and send a response if needed.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $concern->title }}</h1>
                <p class="portal-detail-header__text">Submitted by {{ $concern->submitter?->name ?? 'Unknown submitter' }} on {{ $concern->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route($managementRoutePrefix.'.concerns.index')" variant="secondary">Back to Concerns</x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Concern Summary" subtitle="Original concern details and current response status.">
                <div class="portal-form-stack">
                    <div class="portal-kv">
                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Status</p>
                            <p class="portal-kv__value">{{ ucfirst(str_replace('_', ' ', $concern->status)) }}</p>
                        </div>
                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Submitter</p>
                            <p class="portal-kv__value">{{ $concern->submitter?->name ?? 'Unknown submitter' }}</p>
                        </div>
                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Email</p>
                            <p class="portal-kv__value">{{ $concern->submitter?->email ?? 'No email provided' }}</p>
                        </div>
                        @if ($concern->source)
                            <div class="portal-kv__row">
                                <p class="portal-kv__label">Related Record</p>
                                <p class="portal-kv__value">{{ $concern->source instanceof \App\Models\Announcement ? 'Announcement' : 'Event' }}</p>
                            </div>
                        @endif
                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Description</p>
                            <p class="portal-kv__value">{{ $concern->description }}</p>
                        </div>
                    </div>

                    <hr class="portal-divider">

                    @if (filled($concern->reply_message))
                        <div class="portal-note-box">
                            <h2 class="portal-note-box__title">Latest Reply</h2>
                            <p class="portal-note-box__text">{{ $concern->reply_message }}</p>
                            <p class="portal-note-box__text">{{ $concern->replier?->name ?? 'SSG Team' }} | {{ optional($concern->replied_at)->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                        </div>
                    @else
                        <div class="portal-note-box">
                            <h2 class="portal-note-box__title">No reply sent yet</h2>
                            <p class="portal-note-box__text">Use the update form to assign the concern, change its status, or send a response.</p>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card title="Update Concern" subtitle="Adjust workflow state and optionally notify the student by email.">
                <form action="{{ route($managementRoutePrefix.'.concerns.update', $concern) }}" method="POST" class="portal-form-stack">
                    @csrf
                    @method('PUT')

                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="status">Status</label>
                            <select name="status" id="status" required>
                                <option value="pending" {{ $concern->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_review" {{ $concern->status === 'in_review' ? 'selected' : '' }}>In Review</option>
                                <option value="resolved" {{ $concern->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="assigned_to_user_id">Assign To</label>
                            <select name="assigned_to_user_id" id="assigned_to_user_id">
                                <option value="">Unassigned</option>
                                @foreach ($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" @selected((int) old('assigned_to_user_id', $concern->assigned_to_user_id) === $assignee->id)>
                                        {{ $assignee->name }} ({{ $assignee->role?->role_name ?? 'User' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field field--full">
                            <label for="reply_message">Reply</label>
                            <textarea name="reply_message" id="reply_message" rows="7">{{ old('reply_message', $concern->reply_message) }}</textarea>
                        </div>
                    </div>

                    <label class="portal-helper">
                        <input type="checkbox" name="send_reply_email" value="1" {{ old('send_reply_email') ? 'checked' : '' }}>
                        Send this reply to the student by email
                    </label>

                    <div class="actions">
                        <x-button type="submit">Save Concern Update</x-button>
                    </div>
                </form>
            </x-card>
        </section>
    </div>
@endsection
