<?php

namespace App\Livewire;

use App\Models\SystemNotification;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $showDropdown = false;
    public $notifications = [];

    #[On('refresh-notifications')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();

        if (! $user) {
            $this->unreadCount = 0;
            $this->notifications = [];

            return;
        }

        $notificationService = app(SystemNotificationService::class);

        $this->unreadCount = $notificationService->unreadCountForUser($user);

        $this->notifications = $notificationService->queryForUser($user)
            ->limit(5)
            ->get()
            ->map(fn (SystemNotification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'link' => $notification->link ?: route('notifications.index'),
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->diffForHumans() ?? 'Just now',
            ])->toArray();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = SystemNotification::find($notificationId);
        $user = Auth::user();

        if ($notification && $user) {
            app(SystemNotificationService::class)->markRead($user, $notification);
            $this->loadNotifications();
        }
    }

    public function openNotification($notificationId)
    {
        $notification = SystemNotification::find($notificationId);
        $user = Auth::user();

        if (! $notification || ! $user) {
            return $this->redirect(route('notifications.index'), navigate: false);
        }

        if (! app(SystemNotificationService::class)->markRead($user, $notification)) {
            return $this->redirect(route('notifications.index'), navigate: false);
        }

        $this->loadNotifications();

        return $this->redirect($notification->link ?: route('notifications.index'), navigate: false);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        if ($user) {
            app(SystemNotificationService::class)->markAllRead($user);
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
