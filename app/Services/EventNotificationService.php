<?php

namespace App\Services;

use App\Mail\EventMail;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EventNotificationService
{
    public function notify(Event $event): array
    {
        $recipients = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->when(
                $event->visibility === 'private' && $event->department_id !== null,
                fn ($query) => $query->where('department_id', $event->department_id),
            )
            ->orderBy('name')
            ->get();

        $stats = [
            'matched' => $recipients->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($recipients as $recipient) {
            app(SystemNotificationService::class)->createForUser(
                $recipient,
                'New Event: '.$event->event_title,
                str($event->event_description)->limit(160)->toString(),
                'event',
                route('student.events.show', $event->id),
                $event,
            );

            if (blank($recipient->email)) {
                continue;
            }

            try {
                Mail::to($recipient->email)->send(new EventMail($event, $recipient->name));

                EmailLog::query()->create([
                    'user_id' => $recipient->id,
                    'email' => $recipient->email,
                    'subject' => 'SSG Event: '.$event->event_title,
                    'message' => str($event->event_description)->limit(200)->toString(),
                    'status' => 'sent',
                    'email_type' => 'event',
                    'sent_at' => now(),
                    'last_attempt_at' => now(),
                ]);

                $stats['sent']++;
            } catch (Throwable $exception) {
                EmailLog::query()->create([
                    'user_id' => $recipient->id,
                    'email' => $recipient->email,
                    'subject' => 'SSG Event: '.$event->event_title,
                    'message' => str($event->event_description)->limit(200)->toString(),
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'email_type' => 'event',
                    'sent_at' => now(),
                    'last_attempt_at' => now(),
                ]);

                $stats['failed']++;
            }
        }

        return $stats;
    }
}
