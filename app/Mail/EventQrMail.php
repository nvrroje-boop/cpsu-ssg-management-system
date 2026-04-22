<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use App\Services\QrCodeService;
use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class EventQrMail extends Mailable
{
    use Queueable, SerializesModels;

    private ?array $qrDownloadAssetCache = null;

    public function __construct(
        public Event $event,
        public User $student,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Attendance QR Code - {$this->event->event_title}",
        );
    }

    public function content(): Content
    {
        $qrCodeService = app(QrCodeService::class);
        $eventQr = $qrCodeService->generateOrGetQr($this->event, $this->student);
        $downloadAsset = $this->qrDownloadAsset();
        $qrImageUrl = AppUrl::route('attendance.qr.image', ['token' => $eventQr->token]);
        $qrViewUrl = AppUrl::route('attendance.qr.show', ['token' => $eventQr->token]);
        $qrDownloadUrl = AppUrl::route('attendance.qr.download', ['token' => $eventQr->token]);

        return new Content(
            view: 'emails.event-qr',
            with: [
                'studentName' => $this->student->name,
                'eventName' => $this->event->event_title,
                'eventDescription' => $this->event->event_description,
                'eventDate' => $this->event->event_date?->format('F j, Y') ?? 'TBA',
                'eventTime' => substr((string) $this->event->event_time, 0, 5),
                'eventLocation' => $this->event->location,
                'qrImageUrl' => $qrImageUrl,
                'qrViewUrl' => $qrViewUrl,
                'qrDownloadUrl' => $qrDownloadUrl,
                'qrHasAttachment' => ($downloadAsset['binary'] ?? '') !== '',
                'eventUrl' => AppUrl::route('student.events.show', ['event' => $this->event]),
                'portalUrl' => AppUrl::route('login'),
            ],
        );
    }

    public function attachments(): array
    {
        $downloadAsset = $this->qrDownloadAsset();

        if (($downloadAsset['binary'] ?? '') === '' || ($downloadAsset['mime'] ?? '') === '') {
            return [];
        }

        return [
            Attachment::fromData(
                fn (): string => $downloadAsset['binary'],
                $this->qrAttachmentName($downloadAsset['extension'] ?? 'png'),
            )->withMime($downloadAsset['mime']),
        ];
    }

    private function qrDownloadAsset(): array
    {
        if ($this->qrDownloadAssetCache !== null) {
            return $this->qrDownloadAssetCache;
        }

        return $this->qrDownloadAssetCache = app(QrCodeService::class)
            ->getQrDownloadableImageForStudent($this->event, $this->student);
    }

    private function qrAttachmentName(string $extension): string
    {
        $studentSlug = Str::slug($this->student->name ?: 'student');
        $eventSlug = Str::slug($this->event->event_title ?: 'event');

        return "ssg-attendance-qr-{$studentSlug}-{$eventSlug}.{$extension}";
    }
}
