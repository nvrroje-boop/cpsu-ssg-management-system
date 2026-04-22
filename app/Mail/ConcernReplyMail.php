<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConcernReplyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $studentName,
        protected string $concernTitle,
        protected string $replyMessage,
        protected string $concernsUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your SSG Concern',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.concern-reply',
            with: [
                'studentName' => $this->studentName,
                'concernTitle' => $this->concernTitle,
                'replyMessage' => $this->replyMessage,
                'concernsUrl' => $this->concernsUrl,
            ],
        );
    }
}
