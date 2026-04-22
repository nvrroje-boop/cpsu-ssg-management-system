@extends('layouts.app', ['portal' => auth()->user()?->isAdmin() ? 'admin' : (auth()->user()?->isOfficer() ? 'officer' : 'student')])

@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_subtitle', 'Recent alerts, reminders, and attendance updates for your account.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-detail-stack">
        <x-card title="Notification Center" subtitle="Unread count: {{ $unreadCount }}">
            <div class="portal-form-stack">
                @forelse ($notifications as $notification)
                    <a href="{{ $notification->link ?: '#' }}" class="portal-note-box {{ $notification->read_at === null ? 'portal-note-box--accent' : '' }}">
                        <div class="portal-inline-actions" style="justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h2 class="portal-note-box__title">{{ $notification->title }}</h2>
                                <p class="portal-note-box__text">{{ $notification->message }}</p>
                            </div>
                            <span class="badge {{ $notification->read_at === null ? 'badge-warning' : 'badge-info' }}">
                                {{ $notification->read_at === null ? 'Unread' : 'Read' }}
                            </span>
                        </div>
                        <p class="portal-helper" style="margin-top: 0.75rem;">
                            {{ strtoupper($notification->type) }} • {{ $notification->created_at?->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <div class="portal-empty">
                        <p class="portal-empty__title"><strong>No notifications yet</strong></p>
                        <p class="portal-empty__text">System alerts, event reminders, and attendance updates will appear here.</p>
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
