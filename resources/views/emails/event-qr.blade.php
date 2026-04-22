@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>Your attendance QR code for <strong>{{ $eventName }}</strong> is ready.</p>

        @if (! empty($qrImageUrl ?? ''))
            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ $qrViewUrl }}" style="display: inline-block;">
                    <img src="{{ $qrImageUrl }}" alt="Attendance QR Code" style="width: 260px; height: 260px; max-width: 100%;">
                </a>
            </div>
        @endif

        <div class="info-box" style="margin-top: 16px;">
            <strong>Tap to open:</strong> If the image preview is blocked by Gmail, use the buttons below to view or download the QR image in your browser.
        </div>

        <div class="info-box">
            <strong>Date:</strong> {{ $eventDate }}<br>
            <strong>Time:</strong> {{ $eventTime }}<br>
            <strong>Location:</strong> {{ $eventLocation }}
            @if ($eventDescription)
                <br><strong>Details:</strong> {{ $eventDescription }}
            @endif
        </div>

        <p>Present this QR code to an Admin or SSG Officer during the event. They will scan it from your email to validate your attendance. The QR code is unique to your account and can only be used once.</p>

        <div class="warning-box">
            <strong>Security note:</strong> The QR embeds an `APP_URL`-aware attendance link, so it stays compatible with your current Ngrok address during mobile check-in.
        </div>

        <div class="btn-container">
            <a href="{{ $qrViewUrl }}" class="btn" style="margin-bottom: 12px;">View QR Image</a>
        </div>

        <div class="btn-container">
            <a href="{{ $qrDownloadUrl }}" class="btn" style="margin-bottom: 12px;">Download QR Image</a>
        </div>

        @if (! empty($qrHasAttachment ?? false))
            <p style="margin-top: 0; font-size: 13px; color: #7f8c8d; text-align: center;">
                A QR image attachment is also included for mail apps that support direct downloads.
            </p>
        @endif

        <div class="btn-container">
            <a href="{{ $eventUrl }}" class="btn">View Event Details</a>
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Portal login: <a href="{{ $portalUrl }}" style="color: #3498db;">{{ $portalUrl }}</a>
        </p>
    </div>
@endsection
