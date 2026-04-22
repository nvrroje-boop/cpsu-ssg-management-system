<?php

namespace App\Mail;

use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $title,
        protected string $announcementMessage,
        protected string $studentName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SSG Announcement: '.$this->title,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement-notification',
            with: [
                'title' => $this->title,
                'announcementMessage' => $this->announcementMessage,
                'studentName' => $this->studentName,
                'announcementUrl' => AppUrl::route('student.announcements.index'),
            ],
        );
    }
}
