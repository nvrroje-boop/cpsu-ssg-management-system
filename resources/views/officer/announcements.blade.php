{{-- resources/views/officer/announcements.blade.php --}}
@extends('layouts.app')

@section('title', 'My Announcements')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/officer-announcements.css') }}">
@endpush

@section('content')
<section class="officer-announcements">
    <h1 class="officer-announcements__title">My Announcements</h1>
    <a href="{{ route('officer.announcements.create') }}" class="btn officer-announcements__add-btn">Post Announcement</a>
    <div class="officer-announcements__table-wrap">
        <table class="officer-announcements__table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $announcement)
                <tr>
                    <td data-label="Title">{{ $announcement->title }}</td>
                    <td data-label="Status">
                        <x-badge :type="$announcement->status">{{ ucfirst($announcement->status) }}</x-badge>
                    </td>
                    <td data-label="Published">{{ $announcement->published_at ? $announcement->published_at->format('M d, Y') : 'Draft' }}</td>
                    <td data-label="Actions">
                        <a href="{{ route('officer.announcements.edit', $announcement) }}" class="btn btn--sm">Edit</a>
                        <form action="{{ route('officer.announcements.destroy', $announcement) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="officer-announcements__empty">No announcements found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="officer-announcements__pagination">
        {{ $announcements->links() }}
    </div>
</section>
@endsection
