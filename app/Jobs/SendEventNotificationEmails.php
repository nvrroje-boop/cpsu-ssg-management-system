<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\EventNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEventNotificationEmails implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $eventId,
    ) {
    }

    public function handle(EventNotificationService $notificationService): void
    {
        $event = Event::query()->find($this->eventId);

        if ($event === null) {
            return;
        }
        $notificationService->notify($event);
    }
}
