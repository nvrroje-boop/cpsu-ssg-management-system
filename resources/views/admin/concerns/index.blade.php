@extends('layouts.app')

@section('title', 'Manage Concerns')
@section('page_title', 'Concerns')
@section('page_subtitle', 'Review submitted concerns and open any record to assign, reply, or resolve it.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-page-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">Concern queue</h1>
                <p class="portal-detail-header__text">Monitor new submissions, track current status, and open a concern to respond or reassign it.</p>
            </div>
        </section>

        <x-card title="All Concerns" subtitle="Latest concern records submitted through the student portal." padding="flush">
            <table class="portal-responsive-table" aria-label="Concern management list">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Submitter</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($concerns as $concern)
                        <tr>
                            <td data-label="Title"><strong>{{ $concern->title }}</strong></td>
                            <td data-label="Submitter">{{ $concern->submitter?->name ?? 'Unknown submitter' }}</td>
                            <td data-label="Status">
                                @php
                                    $statusClass = match($concern->status) {
                                        'pending' => 'badge-warning',
                                        'in_review' => 'badge-secondary',
                                        'resolved' => 'badge-success',
                                        default => 'badge-primary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $concern->status)) }}
                                </span>
                            </td>
                            <td data-label="Created">{{ $concern->created_at->format('M d, Y') }}</td>
                            <td data-label="Open">
                                <x-button :href="route($managementRoutePrefix.'.concerns.show', $concern)" size="sm">View</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="portal-responsive-table__empty">
                                <h3>No concerns submitted</h3>
                                <p>New student concerns will appear here once they are filed through the portal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
@endsection
