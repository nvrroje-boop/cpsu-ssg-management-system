<div class="notification-bell-wrapper" wire:poll.5s="refreshNotifications">
    <button
        class="notification-bell"
        @click="@this.toggleDropdown"
        aria-label="Notifications"
        aria-haspopup="true"
    >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if ($unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($showDropdown)
        <div class="notification-dropdown" wire:click.outside="$toggle('showDropdown')">
            <div class="notification-header">
                <h3 class="notification-title">Notifications</h3>
                @if ($unreadCount > 0)
                    <button
                        class="mark-all-btn"
                        wire:click="markAllAsRead"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>

            <div class="notification-list">
                @forelse ($notifications as $notification)
                    <div
                        class="notification-item {{ !$notification['read'] ? 'unread' : '' }}"
                        wire:key="notif-{{ $notification['id'] }}"
                        wire:click="openNotification({{ $notification['id'] }})"
                        role="button"
                        tabindex="0"
                    >
                        <div class="notification-content">
                            <h4 class="notification-item-title">{{ $notification['title'] }}</h4>
                            <p class="notification-item-message">{{ $notification['message'] }}</p>
                            <span class="notification-time">{{ $notification['created_at'] }}</span>
                        </div>
                        @if (!$notification['read'])
                            <button
                                type="button"
                                class="notification-read-btn"
                                wire:click.stop="markAsRead({{ $notification['id'] }})"
                                aria-label="Mark as read"
                            >
                                &bull;
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="notification-empty">
                        <p>No notifications</p>
                    </div>
                @endforelse
            </div>

            <div class="notification-footer">
                <a href="{{ route('notifications.index') }}" class="see-all-link">See all notifications &rarr;</a>
            </div>
        </div>
    @endif
</div>

<style>
    .notification-bell-wrapper {
        position: relative;
    }

    .notification-bell {
        position: relative;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        border-radius: var(--r-md);
        cursor: pointer;
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .notification-bell:hover {
        background: var(--surface-2);
    }

    .notification-badge {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.25rem;
        height: 1.25rem;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .notification-dropdown {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        width: 360px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-lg);
        z-index: 100;
        max-height: 500px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .notification-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
    }

    .mark-all-btn {
        background: none;
        border: none;
        color: var(--ssg-700);
        font-size: 0.75rem;
        cursor: pointer;
        font-weight: 600;
        transition: color 0.2s;
    }

    .mark-all-btn:hover {
        color: var(--ssg-600);
    }

    .notification-list {
        flex: 1;
        overflow-y: auto;
        max-height: 350px;
    }

    .notification-item {
        width: 100%;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        border-left: none;
        border-right: none;
        border-top: none;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        text-align: left;
        background: transparent;
        transition: background 0.2s;
        cursor: pointer;
    }

    .notification-item:hover {
        background: var(--surface-2);
    }

    .notification-item.unread {
        background: rgba(212, 160, 23, 0.05);
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-item-title {
        font-weight: 600;
        margin: 0 0 0.25rem;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .notification-item-message {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 0.25rem 0;
        line-height: 1.4;
    }

    .notification-time {
        color: #999;
        font-size: 0.75rem;
    }

    .notification-read-btn {
        background: var(--gold-500);
        border: none;
        border-radius: 50%;
        width: 0.75rem;
        height: 0.75rem;
        color: white;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        flex-shrink: 0;
        margin-top: 0.25rem;
    }

    .notification-empty {
        padding: 2rem;
        text-align: center;
        color: var(--text-muted);
    }

    .notification-footer {
        padding: 1rem;
        border-top: 1px solid var(--border);
        text-align: center;
    }

    .see-all-link {
        color: var(--ssg-700);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: color 0.2s;
    }

    .see-all-link:hover {
        color: var(--ssg-600);
    }

    @media (max-width: 480px) {
        .notification-dropdown {
            right: -1rem;
            width: calc(100vw - 2rem);
            max-width: 100%;
        }
    }
</style>
