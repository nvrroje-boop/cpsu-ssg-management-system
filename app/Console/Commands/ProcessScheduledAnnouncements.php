<?php

namespace App\Console\Commands;

use App\Jobs\SendAnnouncementEmails;
use App\Models\Announcement;
use Illuminate\Console\Command;

class ProcessScheduledAnnouncements extends Command
{
    protected $signature = 'announcements:process';

    protected $description = 'Publish and queue scheduled announcements that are due';

    public function handle(): int
    {
        $announcements = Announcement::query()
            ->where('status', 'scheduled')
            ->where('send_at', '<=', now())
            ->whereNull('sent_at')
            ->get();

        foreach ($announcements as $announcement) {
            $announcement->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            SendAnnouncementEmails::dispatch($announcement->id);
        }

        $this->info("Processed {$announcements->count()} scheduled announcement(s).");

        return self::SUCCESS;
    }
}
