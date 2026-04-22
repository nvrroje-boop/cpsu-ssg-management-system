<?php

namespace App\Mail;

use App\Models\Event;
use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Event $event,
        protected string $studentName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Upcoming SSG Event: '.$this->event->event_title,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event',
            with: [
                'event' => $this->event,
                'studentName' => $this->studentName,
                'eventUrl' => AppUrl::route('student.events.show', ['event' => $this->event]),
            ],
        );
    }
}
