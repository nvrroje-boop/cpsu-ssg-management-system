<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentCredentialsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $studentName,
        protected string $studentEmail,
        protected string $temporaryPassword,
        protected string $loginUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SSG Management System Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-credentials',
            with: [
                'studentName' => $this->studentName,
                'studentEmail' => $this->studentEmail,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }
}
