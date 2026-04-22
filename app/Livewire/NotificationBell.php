<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;

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
        if (!$user) {
            $this->unreadCount = 0;
            $this->notifications = [];
            return;
        }

        $this->unreadCount = SystemNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $this->notifications = SystemNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
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
        if ($notification) {
            $notification->update(['read_at' => now()]);
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            SystemNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
