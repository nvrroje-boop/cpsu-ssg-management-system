@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>We have a new announcement from the SSG Management System.</p>

        <div class="section-title">{{ $title }}</div>

        <div class="highlight">
            <p>{!! nl2br(e($announcementMessage)) !!}</p>
        </div>

        <p>Click below to review all announcements in the student portal.</p>

        <div class="btn-container">
            <a href="{{ $announcementUrl }}" class="btn">View All Announcements</a>
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Announcement URL: <a href="{{ $announcementUrl }}" style="color: #3498db;">{{ $announcementUrl }}</a>
        </p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
        <p style="font-size: 13px; color: #7f8c8d;">
            <strong>Need help?</strong><br>
            Sign in to your student portal or contact the SSG Office for follow-up questions.
        </p>
    </div>
@endsection
