<?php

namespace App\Jobs;

use App\Mail\EventQrMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventQrEmails implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public Event $event
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            $students = User::query()
                ->whereHas('eventQrs', function ($query) {
                    $query->where('event_id', $this->event->id)
                        ->whereNull('used_at')
                        ->where('expires_at', '>', now());
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            $successCount = 0;
            $failedRecipients = [];

            Log::info("Sending QR emails for event {$this->event->id} to {$students->count()} students.");

            foreach ($students as $student) {
                try {
                    Mail::to($student->email)->send(new EventQrMail($this->event, $student));
                    $successCount++;
                } catch (\Throwable $e) {
                    $failedRecipients[] = $student->email;

                    Log::warning("Failed to send QR email to {$student->email} for event {$this->event->id}: ".$e->getMessage());
                }
            }

            Log::info("QR email dispatch finished for event {$this->event->id}. Success: {$successCount}. Failed: ".count($failedRecipients).'.', [
                'failed_recipients' => $failedRecipients,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send QR emails for event {$this->event->id}: ".$e->getMessage());
            throw $e;
        }
    }
}
