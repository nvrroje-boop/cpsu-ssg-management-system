@extends('layouts.app')

@section('title', 'Announcement Details')
@section('page_title', 'Announcement Information')
@section('page_subtitle', 'Detailed information about the selected announcement.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $announcement->title }}</h1>
                <p class="portal-detail-header__text">{{ $announcement->created_at->format('F d, Y') }}</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('student.announcements.index')" variant="secondary">Back to Announcements</x-button>
            </div>
        </section>

        <x-card title="Announcement Overview" subtitle="Read the full notice exactly as published in the student portal.">
            <div class="portal-form-stack">
                <div class="portal-chip-list">
                    <span class="badge badge-info">{{ ucfirst($announcement->visibility) }}</span>
                </div>

                @if (filled($announcement->description))
                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">Summary</h2>
                        <p class="portal-note-box__text">{{ $announcement->description }}</p>
                    </div>
                @endif

                <div>
                    <p class="portal-kv__label">Message</p>
                    <div class="portal-rich-text">{{ $announcement->message }}</div>
                </div>
            </div>
        </x-card>
    </div>
@endsection
