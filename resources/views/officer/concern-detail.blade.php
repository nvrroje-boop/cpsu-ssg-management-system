{{-- resources/views/officer/concern-detail.blade.php --}}
@extends('layouts.app')

@section('title', 'Concern Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/officer-concerns.css') }}">
@endpush

@section('content')
<section class="officer-concern-detail">
    <h1 class="officer-concern-detail__title">Concern: {{ $concern->subject }}</h1>
    <div class="officer-concern-detail__meta">
        <span>From: {{ $concern->student_name }}</span>
        <span>Date: {{ $concern->created_at->format('M d, Y') }}</span>
        <x-badge :type="$concern->status">{{ ucfirst($concern->status) }}</x-badge>
    </div>
    <div class="officer-concern-detail__thread">
        @foreach($concern->thread as $message)
            <div class="thread__item thread__item--{{ $message->sender_type }}">
                <div class="thread__meta">
                    <span class="thread__sender">{{ $message->sender_name }}</span>
                    <span class="thread__date">{{ $message->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="thread__body">{{ $message->body }}</div>
            </div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('officer.concerns.reply', $concern) }}" class="officer-concern-detail__reply-form">
        @csrf
        <label for="reply">Reply</label>
        <textarea id="reply" name="reply" rows="3" required></textarea>
        @error('reply')<div class="form__error">{{ $message }}</div>@enderror
        <label for="status">Update Status</label>
        <select name="status" id="status">
            <option value="open" @selected($concern->status=='open')>Open</option>
            <option value="in-progress" @selected($concern->status=='in-progress')>In Progress</option>
            <option value="resolved" @selected($concern->status=='resolved')>Resolved</option>
        </select>
        <button type="submit" class="btn">Send Reply</button>
    </form>
</section>
@endsection
