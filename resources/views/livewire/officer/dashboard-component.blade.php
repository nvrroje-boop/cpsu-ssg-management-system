<div class="officer-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Officer Dashboard</h1>
        <p class="dashboard-subtitle">Event management and attendance overview</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Events This Month</h3>
                <p class="stat-value">{{ $eventsThisMonth }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Attendance Today</h3>
                <p class="stat-value">{{ $totalAttendanceToday }}</p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card dashboard-card-full">
            <div class="card-header">
                <h2 class="card-title">Upcoming Events</h2>
                <a href="{{ route('officer.events.index') }}" class="card-link">View All →</a>
            </div>
            <div class="card-body">
                @forelse ($upcomingEvents as $event)
                    <div class="event-row">
                        <div class="event-info">
                            <h4 class="event-title">{{ $event['title'] }}</h4>
                            <div class="event-meta">
                                <span>📍 {{ $event['location'] }}</span>
                                <span>👥 {{ $event['attendanceCount'] }} attending</span>
                            </div>
                        </div>
                        <div class="event-time">
                            <div class="event-date">{{ $event['date'] }}</div>
                            <div class="event-hour">{{ $event['time'] }}</div>
                        </div>
                        <a href="{{ route('officer.events.show', $event['id']) }}" class="btn-small">Manage</a>
                    </div>
                @empty
                    <p class="empty-message">No upcoming events</p>
                @endforelse
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Recent Announcements</h2>
            </div>
            <div class="card-body">
                @forelse ($recentAnnouncements as $announcement)
                    <div class="announcement-item">
                        <h4 class="announcement-title">{{ $announcement['title'] }}</h4>
                        <p class="announcement-body">{{ Str::limit($announcement['body'], 80) }}</p>
                        <span class="announcement-date">{{ $announcement['created_at']->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="empty-message">No announcements</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .officer-dashboard {
        padding: 2rem 0;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        box-shadow: var(--shadow-md);
    }

    .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .stat-icon-green {
        background: linear-gradient(135deg, #10B981, #059669);
    }

    .stat-icon-blue {
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
    }

    .stat-body {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    .dashboard-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .dashboard-card-full {
        grid-column: 1 / -1;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
    }

    .card-link {
        color: var(--ssg-700);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
    }

    .card-body {
        padding: 1.5rem;
    }

    .event-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        margin-bottom: 0.75rem;
    }

    .event-info {
        min-width: 0;
    }

    .event-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .event-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .event-time {
        text-align: right;
    }

    .event-date {
        font-weight: 600;
        color: var(--ssg-700);
    }

    .event-hour {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .btn-small {
        padding: 0.5rem 1rem;
        background: var(--ssg-700);
        color: white;
        border-radius: var(--r-md);
        text-decoration: none;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .announcement-item {
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
    }

    .announcement-item:last-child {
        border-bottom: none;
    }

    .announcement-title {
        font-weight: 600;
        margin: 0 0 0.5rem;
        font-size: 0.95rem;
    }

    .announcement-body {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin: 0 0 0.5rem;
        line-height: 1.5;
    }

    .announcement-date {
        font-size: 0.75rem;
        color: #999;
    }

    .empty-message {
        text-align: center;
        color: var(--text-muted);
        padding: 2rem 0;
        margin: 0;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .event-row {
            grid-template-columns: 1fr;
        }

        .event-time {
            text-align: left;
        }
    }
</style>
