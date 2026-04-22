{{-- resources/views/officer/concerns.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Concerns')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/officer-concerns.css') }}">
@endpush

@section('content')
<section class="officer-concerns">
    <h1 class="officer-concerns__title">Student Concerns</h1>
    <form class="officer-concerns__filter-form" method="GET" action="{{ route('officer.concerns.index') }}">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="open" @selected(request('status')=='open')>Open</option>
            <option value="in-progress" @selected(request('status')=='in-progress')>In Progress</option>
            <option value="resolved" @selected(request('status')=='resolved')>Resolved</option>
        </select>
    </form>
    <div class="officer-concerns__table-wrap">
        <table class="officer-concerns__table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($concerns as $concern)
                <tr>
                    <td data-label="Student">{{ $concern->student_name }}</td>
                    <td data-label="Subject">{{ $concern->subject }}</td>
                    <td data-label="Date">{{ $concern->created_at->format('M d, Y') }}</td>
                    <td data-label="Status">
                        <x-badge :type="$concern->status">{{ ucfirst($concern->status) }}</x-badge>
                    </td>
                    <td data-label="View">
                        <a href="{{ route('officer.concerns.show', $concern) }}" class="btn btn--sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="officer-concerns__empty">No concerns found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="officer-concerns__pagination">
        {{ $concerns->links() }}
    </div>
</section>
@endsection
