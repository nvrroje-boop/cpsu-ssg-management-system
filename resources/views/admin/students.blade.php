{{-- resources/views/admin/students.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Students')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush

@section('content')
<section class="students">
    <h1 class="students__title">Manage Students</h1>
    <form class="students__search-form" method="GET" action="{{ route('admin.students.index') }}">
        <input type="text" name="search" placeholder="Search students by name, ID, or email" value="{{ request('search') }}">
        <button type="submit" class="btn">Search</button>
    </form>
    <form class="students__bulk-form" method="POST" action="{{ route('admin.students.bulk') }}">
        @csrf
        <div class="students__bulk-actions">
            <button type="submit" name="action" value="deactivate" class="btn btn--danger">Deactivate Selected</button>
            <button type="submit" name="action" value="export" class="btn btn--secondary">Export CSV</button>
        </div>
        <div class="students__table-wrap">
            <table class="students__table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td data-label="Select"><input type="checkbox" name="selected[]" value="{{ $student->id }}"></td>
                        <td data-label="Name">{{ $student->name }}</td>
                        <td data-label="Student ID">{{ $student->student_id }}</td>
                        <td data-label="Email">{{ $student->email }}</td>
                        <td data-label="Status">
                            <x-badge :type="$student->status">{{ ucfirst($student->status) }}</x-badge>
                        </td>
                        <td data-label="Date Joined">{{ $student->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="students__empty">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    <div class="students__pagination">
        {{ $students->links() }}
    </div>
</section>
@push('scripts')
<script src="{{ asset('js/students.js') }}" defer></script>
@endpush
@endsection
