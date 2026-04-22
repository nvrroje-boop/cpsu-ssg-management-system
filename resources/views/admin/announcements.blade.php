{{-- resources/views/admin/announcements.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Announcements')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
@endpush

@section('content')
<section class="announcements">
    <h1 class="announcements__title">Manage Announcements</h1>
    <a href="{{ route('admin.announcements.create') }}" class="btn announcements__add-btn">Create Announcement</a>
    <div class="announcements__table-wrap">
        <table class="announcements__table">
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
                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn--sm">Edit</a>
                        <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="announcements__empty">No announcements found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="announcements__pagination">
        {{ $announcements->links() }}
    </div>
</section>
@endsection
