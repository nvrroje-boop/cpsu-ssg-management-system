<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Services\AnnouncementDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAnnouncementEmails implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $announcementId,
        public bool $failedOnly = false,
    ) {
    }

    public function handle(AnnouncementDispatchService $dispatchService): void
    {
        $announcement = Announcement::query()->find($this->announcementId);

        if ($announcement === null) {
            return;
        }

        $stats = $dispatchService->send($announcement, $this->failedOnly);

        Log::info('Announcement emails queued', [
            'announcement_id' => $announcement->id,
            'matched' => $stats['matched'],
            'sent' => $stats['sent'],
            'failed' => $stats['failed'],
            'failed_only' => $this->failedOnly,
        ]);
    }
}
