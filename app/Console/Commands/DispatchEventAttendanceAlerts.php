<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\EventAttendanceWorkflowService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DispatchEventAttendanceAlerts extends Command
{
    protected $signature = 'attendance:dispatch-alerts';

    protected $description = 'Dispatch event reminders, attendance closing alerts, and automatic attendance closure notifications';

    public function handle(EventAttendanceWorkflowService $workflow): int
    {
        $now = now();
        $events = Event::query()
            ->where('attendance_required', true)
            ->whereDate('event_date', '>=', $now->copy()->subDay()->toDateString())
            ->whereDate('event_date', '<=', $now->copy()->addDay()->toDateString())
            ->get();

        $reminderCount = 0;
        $closingSoonCount = 0;
        $closedCount = 0;

        foreach ($events as $event) {
            $eventStart = $this->eventMoment($event, (string) $event->event_time);
            $closingEnd = $this->eventMoment(
                $event,
                (string) ($event->attendance_time_out_ends_at ?: $event->attendance_time_in_ends_at ?: $event->event_time)
            );

            if (
                $eventStart !== null
                && $event->event_reminder_sent_at === null
                && $now->betweenIncluded($eventStart->copy()->subMinutes(10), $eventStart)
            ) {
                $workflow->dispatchAudienceNotification(
                    $event,
                    'Event Reminder',
                    $event->event_title.' starts in 10 minutes.',
                    'event',
                    route('student.events.show', $event->id),
                );

                $event->forceFill(['event_reminder_sent_at' => $now])->save();
                $reminderCount++;
            }

            if (! $event->attendance_active || $closingEnd === null) {
                continue;
            }

            if (
                $event->attendance_closing_notified_at === null
                && $now->betweenIncluded($closingEnd->copy()->subMinutes(10), $closingEnd->copy()->subSecond())
            ) {
                $workflow->dispatchAudienceNotification(
                    $event,
                    'Attendance closing soon',
                    'Attendance for '.$event->event_title.' will close at '.$closingEnd->format('h:i A').'.',
                    'attendance',
                    route('student.events.show', $event->id),
                );

                $event->forceFill(['attendance_closing_notified_at' => $now])->save();
                $closingSoonCount++;
            }

            if ($event->attendance_closed_notified_at === null && $now->gte($closingEnd)) {
                $workflow->stopSession($event, null);
                $closedCount++;
            }
        }

        $this->info("Attendance alerts dispatched. reminders={$reminderCount}, closing_soon={$closingSoonCount}, closed={$closedCount}");

        return self::SUCCESS;
    }

    private function eventMoment(Event $event, string $time): ?Carbon
    {
        if (blank($event->event_date) || blank($time)) {
            return null;
        }

        return Carbon::parse(
            (optional($event->event_date)->format('Y-m-d') ?? (string) $event->event_date)
            .' '
            .substr($time, 0, 5)
        );
    }
}
