<?php

namespace App\Services;

use App\Models\Event;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SystemNotificationService
{
    public function queryForUser(User $user): Builder
    {
        return SystemNotification::query()
            ->visibleToUser($user)
            ->orderByDesc('created_at');
    }

    public function unreadCountForUser(User $user): int
    {
        return (clone $this->queryForUser($user))
            ->whereNull('read_at')
            ->count();
    }

    public function latestForUser(User $user, int $limit = 5): Collection
    {
        return (clone $this->queryForUser($user))
            ->limit($limit)
            ->get();
    }

    public function createForUser(
        User $user,
        string $title,
        string $message,
        string $type = 'system',
        ?string $link = null,
        ?Event $event = null,
    ): SystemNotification {
        return SystemNotification::query()->create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_role' => $user->notificationRoleKey(),
            'event_id' => $event?->id,
            'link' => $link,
        ]);
    }

    public function createForUsers(
        iterable $users,
        string $title,
        string $message,
        string $type = 'system',
        ?string $link = null,
        ?Event $event = null,
    ): void {
        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->createForUser($user, $title, $message, $type, $link, $event);
        }
    }

    public function broadcastToRole(
        string $role,
        string $title,
        string $message,
        string $type = 'system',
        ?string $link = null,
        ?Event $event = null,
    ): SystemNotification {
        return SystemNotification::query()->create([
            'user_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_role' => $role,
            'event_id' => $event?->id,
            'link' => $link,
        ]);
    }

    public function markRead(User $user, SystemNotification $notification): bool
    {
        if (! $this->canView($user, $notification)) {
            return false;
        }

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return true;
    }

    public function markAllRead(User $user): void
    {
        (clone $this->queryForUser($user))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function canView(User $user, SystemNotification $notification): bool
    {
        if ((int) $notification->user_id === (int) $user->id) {
            return true;
        }

        return $notification->user_id === null
            && ($notification->target_role === null || $notification->target_role === $user->notificationRoleKey());
    }
}
