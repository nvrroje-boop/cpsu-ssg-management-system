<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventQr;
use App\Models\User;
use App\Support\AppUrl;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate or retrieve existing QR for a student at an event
     */
    public function generateOrGetQr(Event $event, User $student, bool $refresh = false): EventQr
    {
        // If not refreshing, try to get existing valid QR
        if (!$refresh) {
            $existingQr = EventQr::query()
                ->where('event_id', $event->id)
                ->where('user_id', $student->id)
                ->valid()
                ->first();

            if ($existingQr) {
                return $existingQr;
            }
        }

        // Generate new token
        $token = hash_hmac('sha256', Str::uuid(), config('app.key'));

        $expiresAt = $this->calculateExpiryForEvent($event);

        // Create or update EventQr record
        $eventQr = EventQr::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $student->id,
            ],
            [
                'token' => $token,
                'expires_at' => $expiresAt,
                'used_at' => null,
            ]
        );

        return $eventQr;
    }

    /**
     * Generate QR code image (PNG or SVG)
     */
    public function generateQrCodeImage(string $data): string
    {
        try {
            $qrCode = new QrCode(
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 220,
                margin: 10,
            );
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return 'data:image/png;base64,' . base64_encode($result->getString());
        } catch (\Throwable $exception) {
            // Fallback to SVG if PNG fails
            try {
                $qrCode = new QrCode(
                    data: $data,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 220,
                    margin: 10,
                );
                $writer = new SvgWriter();
                $result = $writer->write($qrCode);

                return 'data:image/svg+xml;base64,' . base64_encode($result->getString());
            } catch (\Throwable $e) {
                // Return placeholder if all else fails
                return '';
            }
        }
    }

    public function generateQrCodePngBinary(string $data): string
    {
        try {
            $qrCode = new QrCode(
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 260,
                margin: 12,
            );

            $writer = new PngWriter();

            return $writer->write($qrCode)->getString();
        } catch (\Throwable $exception) {
            return '';
        }
    }

    public function generateQrCodeSvgBinary(string $data): string
    {
        try {
            $qrCode = new QrCode(
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 260,
                margin: 12,
            );

            $writer = new SvgWriter();

            return $writer->write($qrCode)->getString();
        } catch (\Throwable $exception) {
            return '';
        }
    }

    public function getQrDownloadableImageForStudent(Event $event, User $student): array
    {
        $eventQr = $this->generateOrGetQr($event, $student);
        
        return $this->getQrDownloadableImageForEventQr($eventQr);
    }

    public function getQrDownloadableImageForEventQr(EventQr $eventQr): array
    {
        $scanUrl = $this->attendanceScanUrl($eventQr->token);
        $pngBinary = $this->generateQrCodePngBinary($scanUrl);

        if ($pngBinary !== '') {
            return [
                'binary' => $pngBinary,
                'mime' => 'image/png',
                'extension' => 'png',
            ];
        }

        $svgBinary = $this->generateQrCodeSvgBinary($scanUrl);

        if ($svgBinary !== '') {
            return [
                'binary' => $svgBinary,
                'mime' => 'image/svg+xml',
                'extension' => 'svg',
            ];
        }

        return [
            'binary' => '',
            'mime' => '',
            'extension' => '',
        ];
    }

    public function getQrByToken(string $token): ?EventQr
    {
        return EventQr::query()
            ->where('token', $token)
            ->with(['event', 'user'])
            ->first();
    }

    /**
     * Get QR image for student at event
     */
    public function getQrImageForStudent(Event $event, User $student): string
    {
        $eventQr = $this->generateOrGetQr($event, $student);
        $scanUrl = $this->attendanceScanUrl($eventQr->token);

        return $this->generateQrCodeImage($scanUrl);
    }

    public function getQrPngBinaryForStudent(Event $event, User $student): string
    {
        $eventQr = $this->generateOrGetQr($event, $student);
        $scanUrl = $this->attendanceScanUrl($eventQr->token);

        return $this->generateQrCodePngBinary($scanUrl);
    }

    public function attendanceScanUrl(string $token): string
    {
        return AppUrl::route('attendance.scan', ['token' => $token]);
    }

    public function ensureStudentIdentityToken(User $user): User
    {
        if (filled($user->qr_token)) {
            return $user;
        }

        $user->forceFill([
            'qr_token' => (string) Str::uuid(),
        ])->save();

        return $user->fresh();
    }

    public function studentIdentityPayload(User $user): string
    {
        $securedUser = $this->ensureStudentIdentityToken($user);

        return 'student='.$securedUser->qr_token;
    }

    public function getStudentIdentityQrImage(User $user): string
    {
        return $this->generateQrCodeImage($this->studentIdentityPayload($user));
    }

    public function refreshEventAttendanceToken(Event $event, ?Carbon $expiresAt = null): Event
    {
        $expiry = ($expiresAt ?? now()->addHours(2))->copy()->seconds(0);
        $token = hash_hmac(
            'sha256',
            $event->id.'|'.$expiry->timestamp,
            (string) config('app.key')
        );

        $event->forceFill([
            'attendance_token' => $token,
            'attendance_token_expires_at' => $expiry,
        ])->save();

        return $event->fresh();
    }

    public function eventAttendanceLink(Event $event): string
    {
        return AppUrl::route('student.events.self-scan', [
            'event' => $event->id,
            'token' => $event->attendance_token,
            'expires' => optional($event->attendance_token_expires_at)->timestamp,
        ]);
    }

    public function getEventAttendanceQrImage(Event $event): string
    {
        if (blank($event->attendance_token) || $event->attendance_token_expires_at === null) {
            return '';
        }

        return $this->generateQrCodeImage($this->eventAttendanceLink($event));
    }

    public function validateEventAttendanceToken(Event $event, ?string $token, mixed $expires): bool
    {
        if (
            blank($token)
            || blank($expires)
            || blank($event->attendance_token)
            || $event->attendance_token_expires_at === null
        ) {
            return false;
        }

        $expiryTimestamp = (int) $expires;

        if ($expiryTimestamp <= 0 || $event->attendance_token_expires_at->timestamp !== $expiryTimestamp) {
            return false;
        }

        if ($event->attendance_token_expires_at->isPast()) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $event->id.'|'.$expiryTimestamp,
            (string) config('app.key')
        );

        return hash_equals($expected, (string) $token) && hash_equals((string) $event->attendance_token, (string) $token);
    }

    /**
     * Batch generate QR codes for all students in an event
     */
    public function batchGenerateQrsForEvent(Event $event): int
    {
        // Get all students eligible for this event
        $students = User::query()
            ->where('role_id', function ($query) {
                $query->select('id')->from('roles')->where('role_name', 'Student');
            })
            ->when($event->department_id, function ($query) {
                $query->where('department_id', $event->department_id);
            })
            ->get();

        $count = 0;
        foreach ($students as $student) {
            $this->generateOrGetQr($event, $student);
            $count++;
        }

        return $count;
    }

    /**
     * Get valid QR by token (secure lookup)
     */
    public function validateQrToken(string $token): ?EventQr
    {
        return EventQr::query()
            ->valid()
            ->where('token', $token)
            ->with(['event', 'user'])
            ->first();
    }

    private function calculateExpiryForEvent(Event $event): Carbon
    {
        $fallback = now()->addHours(4);

        if (blank($event->event_date)) {
            return $fallback;
        }

        try {
            $date = Carbon::parse((string) $event->event_date);
            $time = filled($event->event_time) ? substr((string) $event->event_time, 0, 5) : '23:59';
            $eventMoment = Carbon::parse($date->format('Y-m-d').' '.$time);
            $expiry = $eventMoment->copy()->addHours(6);

            if ($expiry->isFuture()) {
                return $expiry;
            }
        } catch (\Throwable $exception) {
            return $fallback;
        }

        return $fallback;
    }

    /**
     * Legacy: Ensure event-level attendance token (backwards compatibility)
     */
    public function ensureAttendanceToken(Event $event, bool $refresh = false): Event
    {
        if (
            $refresh
            || blank($event->attendance_token)
            || $event->attendance_token_expires_at === null
            || $event->attendance_token_expires_at->isPast()
        ) {
            $event->forceFill([
                'attendance_token' => hash_hmac('sha256', (string) Str::uuid(), (string) config('app.key')),
                'attendance_token_expires_at' => now()->addHours(2),
            ])->save();
        }

        return $event->fresh();
    }

    /**
     * Legacy: Generate event-level QR code
     */
    public function generateQrCode(string $data): string
    {
        return $this->generateQrCodeImage($data);
    }

    /**
     * Legacy: Generate event QR code
     */
    public function generateEventQrCode(Event $event, bool $refresh = false): string
    {
        $securedEvent = $this->ensureAttendanceToken($event, $refresh);

        return $this->generateQrCode($securedEvent->attendance_token);
    }
}
