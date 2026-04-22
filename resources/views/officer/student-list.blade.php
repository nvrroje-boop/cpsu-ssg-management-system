@extends('layouts.app')

@section('title', 'Student List')

@section('content')
<div class="student-list">
    <h1 class="student-list__title">Student List</h1>
    <div class="student-list__filter-bar">
        <input type="text" class="student-list__search" placeholder="Search by name or ID...">
        <select class="student-list__filter">
            <option value="">All Years</option>
            <!-- @foreach($years as $year) -->
            <option value="{{ $year }}">Year {{ $year }}</option>
            <!-- @endforeach -->
        </select>
    </div>
    <div class="student-list__table-wrapper">
        <table class="student-list__table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- @foreach($students as $student) -->
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->year }}</td>
                    <td>{{ $student->section }}</td>
                    <td>
                        <span class="student-list__badge student-list__badge--{{ $student->status }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                </tr>
                <!-- @endforeach -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-list.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/student-list.js') }}" defer></script>
@endpush
