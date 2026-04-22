@extends('layouts.app')

@section('title', 'Scan QR Code')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-qr-scan.css') }}">
@endpush

@section('content')
<section class="student-qr-scan">
    <h1 class="student-qr-scan__title">Scan QR Code</h1>
    <div class="student-qr-scan__scanner">
        <div class="student-qr-scan__placeholder">
            <!-- Placeholder for QR scanner integration -->
            <span class="student-qr-scan__icon">📷</span>
            <p>Camera access required to scan QR code.</p>
        </div>
        <button class="student-qr-scan__start-btn">Start Scanning</button>
    </div>
    <div class="student-qr-scan__result" style="display:none;">
        <span class="student-qr-scan__result-label">Result:</span>
        <span class="student-qr-scan__result-value"></span>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/student-qr-scan.js') }}" defer></script>
@endpush
