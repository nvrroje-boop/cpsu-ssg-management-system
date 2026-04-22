@extends('layouts.app')

@section('title', 'My Concerns')
@section('page_title', 'My Concerns')
@section('page_subtitle', 'Track submitted concerns and check whether a reply is already available.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-page-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">Submitted concerns</h1>
                <p class="portal-detail-header__text">Review your concern history and open a record to read replies from the SSG team.</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('student.concerns.create')">Submit New Concern</x-button>
            </div>
        </section>

        <x-card title="Concern Records" subtitle="Each submission stays visible here together with its latest status." padding="flush">
            <table class="portal-responsive-table" aria-label="Student concerns">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Reply</th>
                        <th>Created</th>
                        <th>Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($concerns as $concern)
                        <tr>
                            <td data-label="Title">{{ $concern->title }}</td>
                            <td data-label="Status">
                                <span class="badge {{ $concern->status === 'pending' ? 'badge-warning' : ($concern->status === 'in_review' ? 'badge-secondary' : 'badge-success') }}">
                                    {{ ucfirst(str_replace('_', ' ', $concern->status)) }}
                                </span>
                            </td>
                            <td data-label="Reply">{{ filled($concern->reply_message) ? 'Replied' : 'Awaiting response' }}</td>
                            <td data-label="Created">{{ $concern->created_at->format('M d, Y') }}</td>
                            <td data-label="Open">
                                <x-button :href="route('student.concerns.show', $concern)" size="sm" variant="secondary">View</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="portal-responsive-table__empty">
                                <h3>No concerns submitted</h3>
                                <p>Once you submit a concern, it will appear here with its current status.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
@endsection
