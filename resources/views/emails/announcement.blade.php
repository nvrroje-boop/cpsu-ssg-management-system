@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>We have a new announcement for you from the SSG Management System.</p>

        <div class="section-title">{{ $announcement->title }}</div>

        <div class="highlight">
            <p>{{ $announcement->description }}</p>
        </div>

        @if($announcement->created_by_user_id)
            <div class="info-box">
                <strong>Posted by:</strong> {{ $announcement->creator->name ?? 'SSG Office' }}<br>
                <strong>Posted on:</strong> {{ $announcement->created_at->format('F d, Y \a\t h:i A') }}
            </div>
        @endif

        <p>Click the button below to view the full announcement and any additional details.</p>

        <div class="btn-container">
            <a href="{{ $announcementUrl }}" class="btn">View Announcement</a>
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Announcement URL: <a href="{{ $announcementUrl }}" style="color: #3498db;">{{ $announcementUrl }}</a>
        </p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
        <p style="font-size: 13px; color: #7f8c8d;">
            <strong>Need help?</strong><br>
            If you have any questions about this announcement, please contact the SSG Office at
            <a href="mailto:cpsuhinobaan.ssg.office@gmail.com" style="color: #3498db;">cpsuhinobaan.ssg.office@gmail.com</a>
        </p>
    </div>
@endsection
