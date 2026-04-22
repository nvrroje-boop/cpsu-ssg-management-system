<div class="list-container">
    <div class="list-header">
        <h2 class="list-title">Announcements</h2>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">+ New Announcement</a>
    </div>

    <!-- Search & Filters -->
    <div class="search-filter-box">
        <input
            type="text"
            class="form-input"
            placeholder="Search announcements..."
            wire:model.live.debounce.300ms="search"
        />
        <select class="form form-input" wire:model.live="statusFilter">
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
        <select class="form-input" wire:model.live="sortBy">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
        </select>
    </div>

    <!-- List -->
    <div class="list-items">
        @forelse ($announcements as $announcement)
            <div class="list-item">
                <div class="list-item-body">
                    <h4 class="list-item-title">{{ $announcement->title }}</h4>
                    <p class="list-item-text">{{ Str::limit($announcement->body, 100) }}</p>
                    <div class="list-item-meta">
                        <span class="badge {{ $announcement->published ? 'badge-success' : 'badge-default' }}">
                            {{ $announcement->published ? 'Published' : 'Draft' }}
                        </span>
                        <span class="meta-text">{{ $announcement->created_at->format('M d, Y') }}</span>
                        <span class="meta-text">{{ $announcement->readers()->count() }} reads</span>
                    </div>
                </div>
                <div class="list-item-actions">
                    <a href="{{ route('admin.announcements.edit', $announcement->id) }}" class="btn-small btn-edit">Edit</a>
                    <a href="{{ route('admin.announcements.show', $announcement->id) }}" class="btn-small btn-view">View</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No announcements found</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        {{ $announcements->links() }}
    </div>
</div>

<style>
    .list-container {
        padding: 2rem 0;
    }

    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .list-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--r-md);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-block;
    }

    .btn-primary {
        background: var(--ssg-700);
        color: white;
    }

    .search-filter-box {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .form-input {
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        font-size: 0.875rem;
    }

    .list-items {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .list-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
    }

    .list-item-body {
        flex: 1;
        min-width: 0;
    }

    .list-item-title {
        font-weight: 700;
        font-size: 1.05rem;
        margin: 0 0 0.5rem;
        color: var(--text-primary);
    }

    .list-item-text {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0 0 0.75rem;
        line-height: 1.5;
    }

    .list-item-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-block;
        padding: 0.3rem 0.75rem;
        border-radius: var(--r-pill);
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-success {
        background: var(--ssg-50);
        color: var(--ssg-700);
    }

    .badge-default {
        background: #F3F4F6;
        color: #6B7280;
    }

    .meta-text {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .list-item-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-small {
        padding: 0.5rem 1rem;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .btn-edit {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .btn-view {
        background: var(--ssg-50);
        color: var(--ssg-700);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
    }

    .pagination-wrap {
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .list-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .search-filter-box {
            grid-template-columns: 1fr;
        }

        .list-item {
            flex-direction: column;
        }
    }
</style>
