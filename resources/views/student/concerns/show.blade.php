@extends('layouts.app')

@section('title', 'Concern Details')
@section('page_title', 'Concern Details')
@section('page_subtitle', 'Read the concern you submitted together with the latest reply from the SSG team.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $concern->title }}</h1>
                <p class="portal-detail-header__text">Submitted {{ $concern->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('student.concerns.index')" variant="secondary">Back</x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Concern Summary" subtitle="Your original submission details and current workflow state.">
                <div class="portal-kv">
                    <div class="portal-kv__row">
                        <p class="portal-kv__label">Status</p>
                        <p class="portal-kv__value">{{ ucfirst(str_replace('_', ' ', $concern->status)) }}</p>
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
            </x-card>

            <x-card title="SSG Reply" subtitle="Responses appear here once an officer or admin updates your concern.">
                @if (filled($concern->reply_message))
                    <div class="portal-form-stack">
                        <div class="portal-rich-text">{{ $concern->reply_message }}</div>
                        <div class="portal-note-box">
                            <h2 class="portal-note-box__title">{{ $concern->replier?->name ?? 'SSG Team' }}</h2>
                            <p class="portal-note-box__text">{{ optional($concern->replied_at)->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                        </div>
                    </div>
                @else
                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">No reply yet</h2>
                        <p class="portal-note-box__text">The SSG team will update this concern once it has been reviewed.</p>
                    </div>
                @endif
            </x-card>
        </section>
    </div>
@endsection
