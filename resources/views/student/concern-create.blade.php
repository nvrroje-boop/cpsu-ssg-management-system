@extends('layouts.app')

@section('title', 'Submit Concern')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-concern-create.css') }}">
@endpush

@section('content')
<section class="student-concern-create">
    <h1 class="student-concern-create__title">Submit a Concern</h1>
    <form class="student-concern-create__form" method="POST" action="{{ route('student.concern.store') }}">
        @csrf
        <div class="student-concern-create__group">
            <label for="subject" class="student-concern-create__label">Subject</label>
            <input type="text" id="subject" name="subject" class="student-concern-create__input" required>
        </div>
        <div class="student-concern-create__group">
            <label for="message" class="student-concern-create__label">Message</label>
            <textarea id="message" name="message" class="student-concern-create__textarea" rows="5" required></textarea>
        </div>
        <button type="submit" class="student-concern-create__submit-btn">Submit</button>
    </form>
</section>
@endsection
