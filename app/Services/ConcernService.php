<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;

class ConcernService
{
    public function titleOptionsForStudent(User $student): Collection
    {
        $announcementOptions = Announcement::query()
            ->published()
            ->visibleToUser($student)
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at')
            ->get(['id', 'title'])
            ->map(fn (Announcement $announcement): array => [
                'value' => 'announcement:'.$announcement->id,
                'label' => $announcement->title,
                'group' => 'Announcements',
            ]);

        $eventOptions = Event::query()
            ->visibleToUser($student)
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->get(['id', 'event_title'])
            ->map(fn (Event $event): array => [
                'value' => 'event:'.$event->id,
                'label' => $event->event_title,
                'group' => 'Events',
            ]);

        return $announcementOptions->concat($eventOptions)->values();
    }

    public function resolveStudentSource(User $student, string $sourceReference): Announcement|Event|null
    {
        [$sourceType, $sourceId] = array_pad(explode(':', $sourceReference, 2), 2, null);
        $sourceId = (int) $sourceId;

        if ($sourceId <= 0) {
            return null;
        }

        return match ($sourceType) {
            'announcement' => Announcement::query()
                ->published()
                ->visibleToUser($student)
                ->find($sourceId),
            'event' => Event::query()
                ->visibleToUser($student)
                ->find($sourceId),
            default => null,
        };
    }
}
