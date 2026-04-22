@extends('emails.layout')

@section('content')
    <div class="greeting">Hello {{ $studentName }},</div>

    <div class="content">
        <p>Your concern has been reviewed by the SSG team.</p>

        <div class="section-title">{{ $concernTitle }}</div>

        <div class="highlight">
            <p>{!! nl2br(e($replyMessage)) !!}</p>
        </div>

        <div class="btn-container">
            <a href="{{ $concernsUrl }}" class="btn">View Concern Updates</a>
        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #7f8c8d;">
            Concern portal: <a href="{{ $concernsUrl }}" style="color: #3498db;">{{ $concernsUrl }}</a>
        </p>
    </div>
@endsection
