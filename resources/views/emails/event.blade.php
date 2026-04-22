@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>You're invited to an upcoming SSG event.</p>

        <div class="section-title">{{ $event->event_title }}</div>

        <div class="highlight">
            <p>{{ $event->event_description }}</p>
        </div>

        <div class="info-box">
            <strong>Location:</strong> {{ $event->location }}<br>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}<br>
            @if($event->event_time)
                <strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
            @endif
        </div>

        @if($event->created_by_user_id)
            <div class="info-box" style="margin-top: 20px;">
                <strong>Organized by:</strong> {{ $event->creator->name ?? 'SSG Office' }}<br>
                <strong>Posted on:</strong> {{ $event->created_at->format('F d, Y \a\t h:i A') }}
            </div>
        @endif

        <p style="margin-top: 20px;">If attendance is required, a unique QR email will be sent to you separately. Present that QR code during event check-in.</p>

        <div class="btn-container">
            <a href="{{ $eventUrl }}" class="btn">View Event Details</a>
        </div>

        <div class="warning-box" style="margin-top: 20px;">
            <strong>Tip:</strong> Keep your event QR email available on your phone during attendance checking.
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Event URL: <a href="{{ $eventUrl }}" style="color: #3498db;">{{ $eventUrl }}</a>
        </p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
        <p style="font-size: 13px; color: #7f8c8d;">
            <strong>Questions about this event?</strong><br>
            Contact the SSG Office at
            <a href="mailto:cpsuhinobaan.ssg.office@gmail.com" style="color: #3498db;">cpsuhinobaan.ssg.office@gmail.com</a>
        </p>
    </div>
@endsection
