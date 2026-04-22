{{-- resources/views/officer/qr-display.blade.php --}}
@extends('layouts.app')

@section('title', 'Event QR Code')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/qr-display.css') }}">
@endpush

@section('content')
<section class="qr-display">
    <h1 class="qr-display__title">Event QR Code</h1>
    <div class="qr-display__event-info">
        <div class="qr-display__event-name">{{ $event->event_title }}</div>
        <div class="qr-display__event-date">{{ $event->event_date ? $event->event_date->format('M d, Y') : '' }} {{ $event->event_time ? substr((string) $event->event_time, 0, 5) : '' }}</div>
    </div>
    <div class="qr-display__code-wrap">
        <div id="qrCode" class="qr-display__code"></div>
    </div>
    <div class="qr-display__countdown">
        Expires in <span id="qrCountdown">--:--</span>
    </div>
    <button class="btn qr-display__print" onclick="window.print()">Print QR</button>
</section>
@push('scripts')
<script src="{{ asset('js/qr-display.js') }}" defer></script>
@endpush
@endsection
