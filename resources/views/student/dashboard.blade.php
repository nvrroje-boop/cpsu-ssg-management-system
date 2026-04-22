{{-- resources/views/student/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    @livewire('student.dashboard-component')
@endsection
