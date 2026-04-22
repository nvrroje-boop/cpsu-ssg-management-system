<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <p class="dashboard-subtitle">Overview of system activity and key metrics</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Total Students</h3>
                <p class="stat-value">{{ $totalStudents }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                    <path d="M12 7v5l4 2.5"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Total Events</h3>
                <p class="stat-value">{{ $totalEvents }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-gold">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Announcements</h3>
                <p class="stat-value">{{ $totalAnnouncements }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-body">
                <h3 class="stat-label">Today's Attendance</h3>
                <p class="stat-value">{{ $attendanceTodayCount }}</p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Upcoming Events</h2>
                <a href="{{ route('admin.events.index') }}" class="card-link">View All →</a>
            </div>
            <div class="card-body">
                @forelse ($recentEvents as $event)
                    <div class="list-item">
                        <div class="list-content">
                            <h4 class="list-title">{{ $event['title'] }}</h4>
                            <p class="list-meta">{{ $event['attendanceCount'] }} attendees</p>
                        </div>
                        <div class="list-date">{{ $event['date'] }}</div>
                    </div>
                @empty
                    <p class="empty-message">No upcoming events</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Recent Announcements</h2>
                <a href="{{ route('admin.announcements.index') }}" class="card-link">View All →</a>
            </div>
            <div class="card-body">
                @forelse ($recentAnnouncements as $announcement)
                    <div class="list-item">
                        <div class="list-content">
                            <h4 class="list-title">{{ $announcement['title'] }}</h4>
                            <p class="list-meta">{{ $announcement['readCount'] }} reads</p>
                        </div>
                        <div class="list-date">{{ $announcement['date'] }}</div>
                    </div>
                @empty
                    <p class="empty-message">No announcements</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Attendance Rate Chart -->
    <div class="dashboard-card dashboard-card-full">
        <div class="card-header">
            <h2 class="card-title">Attendance Rate (30 Days)</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <div class="attendance-analytics">
                    <div class="attendance-stat">
                        <span class="attendance-value">{{ $attendanceRate }}%</span>
                        <span class="attendance-label">Average Attendance</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-dashboard {
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
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
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

    .stat-icon-blue {
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
    }

    .stat-icon-green {
        background: linear-gradient(135deg, #10B981, #059669);
    }

    .stat-icon-gold {
        background: linear-gradient(135deg, #F59E0B, #D97706);
    }

    .stat-icon-purple {
        background: linear-gradient(135deg, #8B5CF6, #6D28D9);
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
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
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
        transition: color 0.2s;
    }

    .card-link:hover {
        color: var(--ssg-600);
    }

    .card-body {
        padding: 1.5rem;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .list-content {
        flex: 1;
    }

    .list-title {
        font-weight: 600;
        margin: 0 0 0.25rem;
        color: var(--text-primary);
    }

    .list-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
    }

    .list-date {
        font-size: 0.875rem;
        color: var(--text-muted);
        white-space: nowrap;
        margin-left: 1rem;
    }

    .empty-message {
        text-align: center;
        color: var(--text-muted);
        padding: 2rem 0;
        margin: 0;
    }

    .chart-container {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .attendance-analytics {
        text-align: center;
    }

    .attendance-value {
        font-size: 3rem;
        font-weight: 700;
        color: var(--ssg-700);
        display: block;
    }

    .attendance-label {
        color: var(--text-muted);
        font-size: 0.875rem;
        display: block;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .list-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .list-date {
            margin-left: 0;
        }
    }
</style>
