@extends('layouts.app')

@section('title', 'Student Announcements')
@section('page_title', 'Announcements')
@section('page_subtitle', 'Read the latest campus notices published for students.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-page-stack">
        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">Campus announcements</h1>
                <p class="portal-page-lead__text">
                    Stay updated on student-facing notices, deadlines, and official campus reminders published through the SSG portal.
                </p>
            </div>
            <span class="badge badge-info portal-page-lead__badge">Student feed</span>
        </section>

        <section class="portal-collection" aria-label="Announcement cards">
            @forelse ($announcements as $announcement)
                <x-card class="portal-story-card">
                    <div class="portal-story-card__meta">
                        <h2 class="portal-story-card__title">{{ $announcement['title'] }}</h2>
                        <span class="badge badge-secondary">{{ $announcement['visibility'] }}</span>
                    </div>

                    <p class="portal-story-card__text">{{ $announcement['description'] }}</p>

                    <div class="portal-story-card__footer">
                        <p class="portal-story-card__hint">Open the full notice for complete details and student guidance.</p>
                        <x-button :href="route('student.announcements.show', $announcement['id'])" size="sm">Read More</x-button>
                    </div>
                </x-card>
            @empty
                <x-card class="portal-empty">
                    <div class="portal-empty__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 10v4c0 .55.45 1 1 1h1l2 4h2l-1-4h6l4 2V7l-4 2H5c-.55 0-1 .45-1 1Z" fill="currentColor"/></svg>
                    </div>
                    <p class="portal-empty__title"><strong>No announcements yet</strong></p>
                    <p class="portal-empty__text">Published student notices will appear here once they are available.</p>
                </x-card>
            @endforelse
        </section>
    </div>
@endsection
