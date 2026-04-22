<?php

namespace App\Services;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\EmailLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AnnouncementDispatchService
{
    public function send(Announcement $announcement, bool $failedOnly = false): array
    {
        $students = $failedOnly
            ? $this->failedRecipients($announcement)
            : $announcement->getTargetStudents()
                ->with(['department:id,department_name', 'section:id,section_name,department_id'])
                ->orderBy('name')
                ->get();

        $stats = [
            'matched' => $students->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($students as $student) {
            if (blank($student->email)) {
                continue;
            }

            $notification = Notification::query()->firstOrNew([
                'announcement_id' => $announcement->id,
                'student_id' => $student->id,
            ]);

            $notification->fill([
                'email' => $student->email,
                'status' => 'queued',
                'error_message' => null,
                'last_attempt_at' => now(),
            ])->save();

            try {
                Mail::to($student->email)->send(
                    new AnnouncementMail(
                        title: $announcement->title,
                        announcementMessage: $announcement->message,
                        studentName: $student->name,
                    )
                );

                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'last_attempt_at' => now(),
                ]);

                $this->createSystemNotification($student, $announcement);
                $this->logEmail($student, $announcement, 'sent');
                $stats['sent']++;
            } catch (Throwable $exception) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'retry_count' => (int) $notification->retry_count + 1,
                    'last_attempt_at' => now(),
                ]);

                $this->logEmail($student, $announcement, 'failed', $exception->getMessage());
                $stats['failed']++;
            }
        }

        DB::transaction(function () use ($announcement, $stats): void {
            $announcement->update([
                'total_recipients' => max($announcement->total_recipients, $stats['matched']),
                'sent_count' => (int) $announcement->notifications()->where('status', 'sent')->count(),
                'failed_count' => (int) $announcement->notifications()->where('status', 'failed')->count(),
                'status' => $announcement->status === 'scheduled' ? 'sent' : $announcement->status,
                'sent_at' => $announcement->sent_at ?? now(),
            ]);
        });

        return $stats;
    }

    /**
     * @return Collection<int, User>
     */
    private function failedRecipients(Announcement $announcement): Collection
    {
        return User::query()
            ->whereIn('id', $announcement->notifications()
                ->where('status', 'failed')
                ->where('retry_count', '<', 3)
                ->pluck('student_id'))
            ->orderBy('name')
            ->get();
    }

    private function createSystemNotification(User $student, Announcement $announcement): void
    {
        app(SystemNotificationService::class)->createForUser(
            $student,
            'New Announcement: '.$announcement->title,
            $announcement->description ?: str($announcement->message)->limit(160)->toString(),
            'announcement',
            route('student.announcements.show', $announcement->id),
        );
    }

    private function logEmail(User $student, Announcement $announcement, string $status, ?string $error = null): void
    {
        EmailLog::query()->create([
            'user_id' => $student->id,
            'email' => $student->email,
            'subject' => 'SSG Announcement: '.$announcement->title,
            'message' => str($announcement->message)->limit(200)->toString(),
            'status' => $status,
            'error_message' => $error,
            'email_type' => 'announcement',
            'sent_at' => now(),
            'last_attempt_at' => now(),
        ]);
    }
}
