@extends('layouts.app')

@section('title', 'Announcements')
@section('page_title', 'Announcements & Notifications')
@section('page_subtitle', 'Create, track, archive, and resend campus announcements with delivery visibility across the portal.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
        $isArchivedView = $currentTab === 'archived';
    @endphp

    <div class="portal-page-stack">
        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">Announcement workspace</h1>
                <p class="portal-page-lead__text">Manage public notices and student email updates from one place, then review delivery performance before the semester gets busy.</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route($managementRoutePrefix.'.announcements.create')">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    <span>Create Announcement</span>
                </x-button>
            </div>
        </section>

        <nav class="portal-tabs" aria-label="Announcement tabs">
            <a
                href="{{ route($managementRoutePrefix.'.announcements.index', ['tab' => 'active']) }}"
                class="portal-tabs__link {{ $currentTab === 'active' ? 'portal-tabs__link--active' : '' }}"
            >
                <i class="fas fa-inbox" aria-hidden="true"></i>
                <span>Active Announcements</span>
            </a>

            @if ($archivedCount > 0)
                <a
                    href="{{ route($managementRoutePrefix.'.announcements.index', ['tab' => 'archived']) }}"
                    class="portal-tabs__link {{ $currentTab === 'archived' ? 'portal-tabs__link--active' : '' }}"
                >
                    <i class="fas fa-archive" aria-hidden="true"></i>
                    <span>Archived ({{ $archivedCount }})</span>
                </a>
            @endif
        </nav>

        <x-card title="Announcement list" subtitle="Each row shows status, target count, and email delivery progress." padding="flush">
            <div class="portal-table-wrap">
                <table class="portal-responsive-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Recipients</th>
                            <th>Delivery</th>
                            <th>Scheduled For</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            @php
                                $stats = $announcement->getStats();
                                $statusColor = [
                                    'draft' => 'secondary',
                                    'scheduled' => 'warning',
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                ][$announcement->status] ?? 'secondary';
                                $deliveryPercent = $stats['total'] > 0
                                    ? round(($stats['sent'] / $stats['total']) * 100)
                                    : 0;
                            @endphp
                            <tr>
                                <td data-label="Title">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $announcement->title }}</p>
                                        <p class="portal-table-cell-stack__meta">{{ Str::limit($announcement->message, 80) }}</p>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-{{ $statusColor }}">
                                        @if ($announcement->status === 'draft')
                                            <i class="fas fa-file-alt" aria-hidden="true"></i> Draft
                                        @elseif ($announcement->status === 'scheduled')
                                            <i class="fas fa-calendar-alt" aria-hidden="true"></i> Scheduled
                                        @elseif ($announcement->status === 'sent')
                                            <i class="fas fa-check-circle" aria-hidden="true"></i> Sent
                                        @else
                                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i> Failed
                                        @endif
                                    </span>
                                </td>
                                <td data-label="Recipients">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $stats['total'] }}</p>
                                        <p class="portal-table-cell-stack__meta">
                                            @if ($stats['queued'] > 0)
                                                {{ $stats['queued'] }} pending
                                            @else
                                                Ready for tracking
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td data-label="Delivery">
                                    @if ($stats['total'] > 0)
                                        <div class="portal-meter">
                                            <div class="portal-meter__row">
                                                <div class="portal-meter__track">
                                                    <div class="portal-meter__bar" style="width: {{ $deliveryPercent }}%;"></div>
                                                </div>
                                                <span class="portal-meter__value">{{ $deliveryPercent }}%</span>
                                            </div>
                                            <p class="portal-meter__meta">
                                                {{ $stats['sent'] }}/{{ $stats['total'] }} sent
                                                @if ($stats['failed'] > 0)
                                                    <span class="portal-meter__meta--danger">&middot; {{ $stats['failed'] }} failed</span>
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <span class="portal-table-cell-stack__meta">Not sent yet</span>
                                    @endif
                                </td>
                                <td data-label="Scheduled For">
                                    <div class="portal-table-cell-stack">
                                        @if ($announcement->send_at)
                                            <p class="portal-table-cell-stack__title">{{ $announcement->send_at->format('M d, Y h:i A') }}</p>
                                            <p class="portal-table-cell-stack__meta">
                                                @if (! $announcement->sent_at && $announcement->send_at <= now())
                                                    Sending now
                                                @else
                                                    Scheduled delivery
                                                @endif
                                            </p>
                                        @else
                                            <p class="portal-table-cell-stack__meta">Send immediately</p>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Created By">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $announcement->creator?->name ?? 'System' }}</p>
                                        <p class="portal-table-cell-stack__meta">{{ $announcement->created_at->format('M d, Y') }}</p>
                                    </div>
                                </td>
                                <td data-label="Actions">
                                    <div class="portal-table-actions">
                                        <x-button :href="route($managementRoutePrefix.'.announcements.show', $announcement->id)" variant="ghost" size="sm">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                            <span>View</span>
                                        </x-button>

                                        @unless ($isArchivedView)
                                            <x-button :href="route($managementRoutePrefix.'.announcements.edit', $announcement->id)" variant="ghost" size="sm">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                                <span>Edit</span>
                                            </x-button>
                                        @endunless

                                        @if ($stats['failed'] > 0)
                                            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.resend-failed', $announcement->id) }}" onsubmit="return confirm('Resend to {{ $stats['failed'] }} failed recipients?');">
                                                @csrf
                                                <x-button type="submit" variant="ghost" size="sm">
                                                    <i class="fas fa-redo" aria-hidden="true"></i>
                                                    <span>Retry</span>
                                                </x-button>
                                            </form>
                                        @endif

                                        @if ($isArchivedView)
                                            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.unarchive', $announcement->id) }}" onsubmit="return confirm('Restore this announcement from archive?');">
                                                @csrf
                                                <x-button type="submit" variant="ghost" size="sm">
                                                    <i class="fas fa-undo" aria-hidden="true"></i>
                                                    <span>Restore</span>
                                                </x-button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.archive', $announcement->id) }}" onsubmit="return confirm('Archive this announcement?');">
                                                @csrf
                                                <x-button type="submit" variant="ghost" size="sm">
                                                    <i class="fas fa-archive" aria-hidden="true"></i>
                                                    <span>Archive</span>
                                                </x-button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.destroy', $announcement->id) }}" onsubmit="return confirm('Delete this announcement{{ $isArchivedView ? ' permanently' : '' }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="ghost" size="sm">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                                <span>Delete</span>
                                            </x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="portal-responsive-table__empty">
                                    <div class="portal-empty">
                                        <div class="portal-empty__icon">
                                            <i class="fas fa-bullhorn" aria-hidden="true"></i>
                                        </div>
                                        <h3 class="portal-empty__title">{{ $isArchivedView ? 'No archived announcements yet' : 'No announcements yet' }}</h3>
                                        <p class="portal-empty__text">
                                            {{ $isArchivedView ? 'Archived notices will appear here once older announcements are tucked away.' : 'Create your first announcement to start the public and email communication flow.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        @if ($announcements->count())
            <div>
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
@endsection
