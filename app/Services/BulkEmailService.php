<?php

namespace App\Services;

use App\Mail\AnnouncementMail;
use App\Mail\EventMail;
use App\Models\Announcement;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bulk Email Service
 * Handles efficient bulk email sending with queue support
 * Prevents memory overload by chunking database queries
 */
class BulkEmailService
{
    private const CHUNK_SIZE = 50;

    /**
     * Send announcement emails to all students
     *
     * @param Announcement $announcement
     * @param array|null $studentIds Filter to specific student IDs (optional)
     * @return array ['success' => int, 'failed' => int, 'total' => int]
     */
    public function sendAnnouncementBulk(Announcement $announcement, ?array $studentIds = null): array
    {
        Log::info('Starting bulk announcement email send', [
            'announcement_id' => $announcement->id,
            'announcement_title' => $announcement->title,
        ]);

        $stats = ['success' => 0, 'failed' => 0, 'total' => 0];

        try {
            // Build query
            $query = User::where('role_id', function ($q) {
                $q->selectRaw('id')
                    ->from('roles')
                    ->where('role_name', User::ROLE_STUDENT);
            })
                ->whereNotNull('email')
                ->where('email', '!=', '');

            // Filter specific student IDs if provided
            if (!empty($studentIds)) {
                $query->whereIn('id', $studentIds);
            }

            // Get total count
            $stats['total'] = $query->count();

            // Process in chunks to avoid memory overload
            $query->chunk(self::CHUNK_SIZE, function ($students) use ($announcement, &$stats) {
                foreach ($students as $student) {
                    try {
                        Mail::to($student->email)
                            ->queue(
                                (new AnnouncementMail(
                                    title: $announcement->title,
                                    message: $announcement->message,
                                    studentName: $student->name,
                                ))->afterCommit()
                            );

                        EmailLog::create([
                            'user_id' => $student->id,
                            'email' => $student->email,
                            'subject' => 'New SSG Announcement: ' . $announcement->title,
                            'message' => substr((string) ($announcement->message ?? $announcement->description), 0, 200),
                            'status' => 'queued',
                            'email_type' => 'announcement',
                            'sent_at' => now(),
                        ]);

                        $stats['success']++;

                        Log::debug('Announcement email queued', [
                            'student_id' => $student->id,
                            'student_email' => $student->email,
                        ]);
                    } catch (Exception $e) {
                        $stats['failed']++;

                        Log::error('Failed to queue announcement email', [
                            'student_id' => $student->id,
                            'student_email' => $student->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Bulk announcement email send completed', [
                'announcement_id' => $announcement->id,
                'stats' => $stats,
            ]);

            return $stats;
        } catch (Exception $e) {
            Log::error('Bulk announcement email service error', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Send event emails to all students
     *
     * @param Event $event
     * @param array|null $studentIds Filter to specific student IDs (optional)
     * @return array ['success' => int, 'failed' => int, 'total' => int]
     */
    public function sendEventBulk(Event $event, ?array $studentIds = null): array
    {
        Log::info('Starting bulk event email send', [
            'event_id' => $event->id,
            'event_title' => $event->event_title,
        ]);

        $stats = ['success' => 0, 'failed' => 0, 'total' => 0];

        try {
            // Build query
            $query = User::where('role_id', function ($q) {
                $q->selectRaw('id')
                    ->from('roles')
                    ->where('role_name', User::ROLE_STUDENT);
            })
                ->whereNotNull('email')
                ->where('email', '!=', '');

            // Filter specific student IDs if provided
            if (!empty($studentIds)) {
                $query->whereIn('id', $studentIds);
            }

            // Get total count
            $stats['total'] = $query->count();

            // Process in chunks to avoid memory overload
            $query->chunk(self::CHUNK_SIZE, function ($students) use ($event, &$stats) {
                foreach ($students as $student) {
                    try {
                        Mail::to($student->email)
                            ->queue((new EventMail($event, $student->name))->afterCommit());

                        EmailLog::create([
                            'user_id' => $student->id,
                            'email' => $student->email,
                            'subject' => 'Upcoming SSG Event: ' . $event->event_title,
                            'message' => substr((string) $event->event_description, 0, 200),
                            'status' => 'queued',
                            'email_type' => 'event',
                            'sent_at' => now(),
                        ]);

                        $stats['success']++;

                        Log::debug('Event email queued', [
                            'student_id' => $student->id,
                            'student_email' => $student->email,
                        ]);
                    } catch (Exception $e) {
                        $stats['failed']++;

                        Log::error('Failed to queue event email', [
                            'student_id' => $student->id,
                            'student_email' => $student->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Bulk event email send completed', [
                'event_id' => $event->id,
                'stats' => $stats,
            ]);

            return $stats;
        } catch (Exception $e) {
            Log::error('Bulk event email service error', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Send email to specific student
     *
     * @param User $student
     * @param string $mailableClass Mailable class path
     * @param mixed $data
     * @return bool
     */
    public function sendToStudent(User $student, string $mailableClass, mixed $data): bool
    {
        if (empty($student->email)) {
            Log::warning('Cannot send email - student has no email', [
                'student_id' => $student->id,
            ]);

            return false;
        }

        try {
            Mail::to($student->email)->queue(new $mailableClass($data, $student->name));

            Log::info('Email queued for student', [
                'student_id' => $student->id,
                'mailable' => $mailableClass,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to queue email for student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get email statistics
     *
     * @return array
     */
    public function getEmailStats(): array
    {
        return [
            'total_emails_sent' => EmailLog::count(),
            'emails_this_month' => EmailLog::whereMonth('sent_at', now()->month)
                ->whereYear('sent_at', now()->year)
                ->count(),
            'unique_recipients' => EmailLog::distinct('user_id')->count(),
            'announcements_sent' => EmailLog::where('subject', 'like', '%Announcement%')->count(),
            'events_sent' => EmailLog::where('subject', 'like', '%Event%')->count(),
        ];
    }
}
