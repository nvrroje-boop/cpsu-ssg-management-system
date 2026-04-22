<div class="student-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Student Dashboard</h1>
        <p class="dashboard-subtitle">Your announcements, events, and submissions</p>
    </div>

    @if ($nextEvent)
        <div class="featured-event">
            <div class="featured-content">
                <span class="featured-badge">Next Event</span>
                <h3 class="featured-title">{{ $nextEvent['title'] }}</h3>
                <p class="featured-date">
                    📅 {{ Carbon\Carbon::parse($nextEvent['event_date'])->format('M d, Y · h:i A') }}
                </p>
                <a href="{{ route('student.events.show', $nextEvent['id']) }}" class="btn btn-primary">View Details</a>
            </div>
        </div>
    @endif

    <div class="dashboard-grid">
        <!-- Announcements -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Latest Announcements</h2>
                <a href="{{ route('student.announcements.index') }}" class="card-link">See All</a>
            </div>
            <div class="card-body">
                @forelse ($recentAnnouncements as $announcement)
                    <div class="announcement-card">
                        <div class="announcement-badge {{ $announcement['read'] ? 'read' : 'unread' }}">
                            {{ $announcement['read'] ? '✓' : '●' }}
                        </div>
                        <div class="announcement-content">
                            <h4 class="announcement-title">{{ $announcement['title'] }}</h4>
                            <p class="announcement-excerpt">{{ $announcement['excerpt'] }}</p>
                            <span class="announcement-date">{{ $announcement['date'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="empty-message">No announcements</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Upcoming Events</h2>
                <a href="{{ route('student.events.index') }}" class="card-link">See All</a>
            </div>
            <div class="card-body">
                @forelse ($upcomingEvents as $event)
                    <div class="event-card">
                        <div class="event-header">
                            <h4 class="event-title">{{ $event['title'] }}</h4>
                            <span class="event-badge {{ $event['attended'] ? 'attended' : 'pending' }}">
                                {{ $event['attended'] ? '✓ Attended' : 'Not Yet' }}
                            </span>
                        </div>
                        <p class="event-time">{{ $event['date'] }} at {{ $event['time'] }}</p>
                    </div>
                @empty
                    <p class="empty-message">No upcoming events</p>
                @endforelse
            </div>
        </div>

        <!-- Stats -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Your Stats</h2>
            </div>
            <div class="card-body stats-body">
                <div class="stat-block">
                    <span class="stat-number">{{ $attendanceCount }}</span>
                    <span class="stat-name">Events Attended</span>
                    <span class="stat-period">(Last 30 Days)</span>
                </div>
                <div class="stat-block">
                    <span class="stat-number">{{ $unreadConcernsCount }}</span>
                    <span class="stat-name">Unread Concerns</span>
                    <a href="{{ route('student.concerns.index') }}" class="stat-link">View</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .student-dashboard {
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

    .featured-event {
        background: linear-gradient(135deg, var(--ssg-700), var(--ssg-600));
        border-radius: var(--r-lg);
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .featured-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: var(--r-pill);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .featured-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .featured-date {
        color: rgba(255, 255, 255, 0.9);
        margin: 0.5rem 0 1.5rem;
    }

    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: var(--gold-500);
        color: var(--text-on-gold);
        border-radius: var(--r-md);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: var(--gold-500);
        color: var(--text-on-gold);
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .dashboard-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
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
    }

    .card-link {
        color: var(--ssg-700);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .card-body {
        padding: 1.5rem;
    }

    .announcement-card {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-radius: var(--r-md);
        background: var(--surface-2);
        margin-bottom: 0.75rem;
    }

    .announcement-badge {
        width: 2rem;
        height: 2rem;
        min-width: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .announcement-badge.unread {
        background: var(--gold-50);
        color: var(--gold-700);
    }

    .announcement-badge.read {
        background: var(--ssg-50);
        color: var(--ssg-700);
    }

    .announcement-content {
        flex: 1;
        min-width: 0;
    }

    .announcement-title {
        font-weight: 600;
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
    }

    .announcement-excerpt {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 0.25rem 0;
        line-height: 1.4;
    }

    .announcement-date {
        font-size: 0.75rem;
        color: #999;
    }

    .event-card {
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        margin-bottom: 0.75rem;
    }

    .event-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .event-title {
        font-weight: 600;
        margin: 0;
        flex: 1;
    }

    .event-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: var(--r-pill);
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .event-badge.attended {
        background: var(--ssg-50);
        color: var(--ssg-700);
    }

    .event-badge.pending {
        background: #FEE2E2;
        color: #DC2626;
    }

    .event-time {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin: 0;
    }

    .stats-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        text-align: center;
    }

    .stat-block {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--ssg-700);
    }

    .stat-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .stat-period {
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .stat-link {
        color: var(--ssg-700);
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    .empty-message {
        text-align: center;
        color: var(--text-muted);
        padding: 2rem 0;
        margin: 0;
    }

    @media (max-width: 768px) {
        .featured-title {
            font-size: 1.5rem;
        }

        .stats-body {
            grid-template-columns: 1fr;
        }
    }
</style>
