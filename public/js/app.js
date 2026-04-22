document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const dropdownButtons = document.querySelectorAll('[data-dropdown-target]');
    const notificationsUrl = body.dataset.notificationsUrl;
    const notificationsReadUrlTemplate = body.dataset.notificationsReadUrlTemplate;
    const notificationsReadAllUrl = body.dataset.notificationsReadAllUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    const closeAllDropdowns = (exceptId = null) => {
        document.querySelectorAll('.dropdown-menu.show').forEach((menu) => {
            if (menu.id !== exceptId) {
                menu.classList.remove('show');
            }
        });
    };

    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        sidebarOverlay?.classList.remove('show');
    };

    const syncDesktopState = () => {
        if (desktopQuery.matches) {
            closeSidebar();
        }
    };

    const dismissAlerts = () => {
        document.querySelectorAll('[data-alert]').forEach((alert) => {
            const closeButton = alert.querySelector('[data-alert-close]');
            const dismiss = () => alert.remove();

            closeButton?.addEventListener('click', dismiss);
            window.setTimeout(dismiss, 5000);
        });
    };

    const escapeHtml = (text) => {
        const value = String(text ?? '');
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };

        return value.replace(/[&<>"']/g, (character) => map[character]);
    };

    const renderNotifications = (data) => {
        const notificationCount = document.getElementById('notification-count');
        const notificationHeader = document.getElementById('notification-header');
        const notificationList = document.getElementById('notification-list');
        const notificationMarkAll = document.getElementById('notificationMarkAll');

        if (!notificationList || !notificationHeader || !notificationCount || !notificationMarkAll) {
            return;
        }

        notificationCount.textContent = data.unread;
        notificationCount.style.display = data.unread > 0 ? 'flex' : 'none';
        notificationHeader.textContent = `Notifications (${data.unread})`;
        notificationMarkAll.style.display = data.unread > 0 ? 'inline-flex' : 'none';

        if (!data.notifications || data.notifications.length === 0) {
            notificationList.innerHTML = '<div class="dropdown-item portal-dropdown__item--muted">No notifications yet.</div>';
            return;
        }

        notificationList.innerHTML = data.notifications.map((notification) => `
            <button
                type="button"
                class="dropdown-item portal-notification-item ${notification.read ? '' : 'portal-notification-item--unread'}"
                data-notification-id="${notification.id}"
                data-notification-link="${encodeURIComponent(notification.link || '')}"
            >
                <div class="portal-notification-item__body">
                    <strong>${escapeHtml(notification.title)}</strong>
                    <div class="portal-notification__message">${escapeHtml(notification.message || '')}</div>
                    <div class="portal-notification__time">${escapeHtml(notification.time)}</div>
                </div>
                ${notification.read ? '' : '<span class="portal-notification-item__dot" aria-hidden="true"></span>'}
            </button>
        `).join('');
    };

    const postNotificationAction = async (url) => {
        if (!url) {
            return;
        }

        await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
    };

    const markNotificationRead = async (id) => {
        if (!notificationsReadUrlTemplate) {
            return;
        }

        const url = notificationsReadUrlTemplate.replace('/0/read', `/${id}/read`);
        await postNotificationAction(url);
    };

    const loadNotifications = () => {
        if (!notificationsUrl) {
            return;
        }

        fetch(notificationsUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.json())
            .then(renderNotifications)
            .catch(() => {});
    };

    document.getElementById('notificationMarkAll')?.addEventListener('click', async (event) => {
        event.preventDefault();
        await postNotificationAction(notificationsReadAllUrl);
        loadNotifications();
    });

    document.getElementById('notification-list')?.addEventListener('click', async (event) => {
        const item = event.target.closest('[data-notification-id]');

        if (!item) {
            return;
        }

        const id = item.dataset.notificationId;
        const link = decodeURIComponent(item.dataset.notificationLink || '');

        await markNotificationRead(id);
        loadNotifications();

        if (link) {
            window.location.href = link;
        }
    });

    sidebarToggle?.addEventListener('click', () => {
        if (desktopQuery.matches) {
            return;
        }

        sidebar?.classList.toggle('open');
        sidebarOverlay?.classList.toggle('show');
    });

    sidebarOverlay?.addEventListener('click', closeSidebar);

    dropdownButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const target = document.getElementById(button.dataset.dropdownTarget);

            if (!target) {
                return;
            }

            const willOpen = !target.classList.contains('show');
            closeAllDropdowns(button.dataset.dropdownTarget);
            target.classList.toggle('show', willOpen);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.dropdown-wrap')) {
            closeAllDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllDropdowns();
            closeSidebar();
        }
    });

    desktopQuery.addEventListener('change', syncDesktopState);

    dismissAlerts();
    syncDesktopState();
    loadNotifications();
    window.setInterval(loadNotifications, 5000);
});
