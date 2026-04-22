<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use Illuminate\Console\Command;

class EmailStatsCommand extends Command
{
    protected $signature = 'email:stats {--days=30}';

    protected $description = 'View email sending statistics and performance';

    public function handle()
    {
        $days = $this->option('days');
        $startDate = now()->subDays($days);

        $this->info("📊 Email Statistics (Last {$days} days)\n");

        // Overall stats
        $totalSent = EmailLog::where('sent_at', '>=', $startDate)->count();
        $totalQueued = EmailLog::where('status', 'queued')->count();
        $totalFailed = EmailLog::where('status', 'failed')->count();
        $uniqueRecipients = EmailLog::where('sent_at', '>=', $startDate)
            ->distinct('user_id')
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Emails Sent', $totalSent],
                ['Unique Recipients', $uniqueRecipients],
                ['Currently Queued', $totalQueued],
                ['Failed Emails', $totalFailed],
                ['Retry Attempts', EmailLog::where('retry_count', '>', 0)->count()],
            ]
        );

        // By type
        $this->info("\n📧 Breakdown by Email Type:");
        $byType = EmailLog::where('sent_at', '>=', $startDate)
            ->selectRaw('email_type, count(*) as count')
            ->groupBy('email_type')
            ->get();

        if ($byType->isEmpty()) {
            $this->line('  No email type data available');
        } else {
            $typeData = $byType->map(fn ($row) => [
                $row->email_type ?? 'Unknown',
                $row->count,
            ])->toArray();
            $this->table(['Type', 'Count'], $typeData);
        }

        // By status
        $this->info("\n✅ Status Breakdown:");
        $byStatus = EmailLog::where('sent_at', '>=', $startDate)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        if ($byStatus->isEmpty()) {
            $this->line('  No status data available');
        } else {
            $statusData = $byStatus->map(fn ($row) => [
                ucfirst($row->status),
                $row->count,
            ])->toArray();
            $this->table(['Status', 'Count'], $statusData);
        }

        // Recent failures
        $this->info("\n⚠️  Recent Failed Emails:");
        $failures = EmailLog::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['email', 'subject', 'error_message', 'retry_count']);

        if ($failures->isEmpty()) {
            $this->line('  ✅ No failed emails!');
        } else {
            $failureData = $failures->map(fn ($fail) => [
                $fail->email,
                substr($fail->subject, 0, 30) . '...',
                substr($fail->error_message ?? 'N/A', 0, 25) . '...',
                $fail->retry_count,
            ])->toArray();
            $this->table(['Email', 'Subject', 'Error', 'Retries'], $failureData);
        }

        $this->info("\n✨ Statistics generated!");

        return Command::SUCCESS;
    }
}
