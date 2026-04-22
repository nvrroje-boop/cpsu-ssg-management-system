<div class="search-filter-wrapper">
    <div class="search-field">
        <input
            type="text"
            class="form-input form-search"
            placeholder="{{ $placeholder ?? 'Search...' }}"
            wire:model.live.debounce.300ms="search"
        />
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </svg>
    </div>

    @if ($showFilters ?? true)
        <div class="filter-group">
            @if ($showDateRange ?? false)
                <div class="filter-item">
                    <label class="filter-label">Date Range</label>
                    <input
                        type="date"
                        class="form-input form-input-sm"
                        wire:model.live="dateFrom"
                    />
                    <span class="filter-separator">to</span>
                    <input
                        type="date"
                        class="form-input form-input-sm"
                        wire:model.live="dateTo"
                    />
                </div>
            @endif

            @if ($showStatus ?? false)
                <div class="filter-item">
                    <select class="form-input form-input-sm" wire:model.live="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            @endif

            @if ($showDepartment ?? false)
                <div class="filter-item">
                    <select class="form-input form-input-sm" wire:model.live="departmentFilter">
                        <option value="">All Departments</option>
                        @foreach ($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($showSort ?? false)
                <div class="filter-item">
                    <select class="form-input form-input-sm" wire:model.live="sortBy">
                        <option value="">Sort by</option>
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                        <option value="name">Name</option>
                    </select>
                </div>
            @endif
        </div>
    @endif
</div>

<style>
    .search-filter-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .search-field {
        position: relative;
    }

    .form-search {
        width: 100%;
        padding-left: 2.5rem;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .filter-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .filter-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .form-input-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .filter-separator {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .filter-group {
            grid-template-columns: 1fr;
        }
    }
</style>
