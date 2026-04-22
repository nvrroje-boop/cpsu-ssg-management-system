@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>Your SSG Management System account is ready.</p>

        <div class="info-box">
            <strong>Email:</strong> {{ $studentEmail }}<br>
            <strong>Temporary Password:</strong> {{ $temporaryPassword }}
        </div>

        <p>Use the button below to sign in. Attendance QR codes are sent separately per event, so there is no reusable plaintext QR token attached to your account email.</p>

        <div class="btn-container">
            <a href="{{ $loginUrl }}" class="btn">Open Login Page</a>
        </div>

        <div class="warning-box">
            <strong>Password policy:</strong> Only an Admin can reset or change portal passwords. Contact the SSG administrator if you lose access.
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Login URL: <a href="{{ $loginUrl }}" style="color: #3498db;">{{ $loginUrl }}</a>
        </p>
    </div>
@endsection

