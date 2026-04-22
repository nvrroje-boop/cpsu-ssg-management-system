<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\QrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateEventQrBatch implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public Event $event
    ) {
        $this->onQueue('default');
    }

    public function handle(QrCodeService $qrCodeService): void
    {
        try {
            $count = $qrCodeService->batchGenerateQrsForEvent($this->event);

            Log::info("Generated QR codes for event {$this->event->id}: {$count} QR codes created.");

            // After generating QRs, dispatch email job
            SendEventQrEmails::dispatch($this->event)->onQueue('default');
        } catch (\Throwable $e) {
            Log::error("Failed to generate QR batch for event {$this->event->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
