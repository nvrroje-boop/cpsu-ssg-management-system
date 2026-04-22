@extends('layouts.app')

@section('title', 'Announcement Details')
@section('page_title', 'Announcement & Delivery Status')
@section('page_subtitle', 'Review the message, target filters, and live delivery performance before resending or archiving.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
        $deliveryPercent = $stats['total'] > 0 ? round(($stats['sent'] / $stats['total']) * 100) : 0;
        $statusColor = [
            'draft' => 'secondary',
            'scheduled' => 'warning',
            'sent' => 'success',
            'failed' => 'danger',
        ][$announcement->status] ?? 'secondary';
    @endphp

    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $announcement->title }}</h1>
                <p class="portal-detail-header__text">
                    <span class="badge badge-{{ $statusColor }}">{{ ucfirst($announcement->status) }}</span>
                    Created {{ $announcement->created_at->format('M d, Y h:i A') }}
                </p>
            </div>
            <div class="portal-inline-actions">
                @if ($announcement->status === 'draft')
                    <x-button type="button" id="showSendModalButton">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        <span>Send Announcement</span>
                    </x-button>
                @endif
                <x-button :href="route($managementRoutePrefix.'.announcements.index')" variant="secondary">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Delivery summary" subtitle="Monitor total targets, sent records, pending queue, and failures in one place.">
                @if ($stats['total'] > 0)
                    <div class="portal-form-stack">
                        <div class="portal-meter">
                            <div class="portal-meter__row">
                                <div class="portal-meter__track">
                                    <div class="portal-meter__bar" style="width: {{ $deliveryPercent }}%;"></div>
                                </div>
                                <span class="portal-meter__value">{{ $deliveryPercent }}%</span>
                            </div>
                            <p class="portal-meter__meta">Overall progress across all targeted recipients.</p>
                        </div>

                        <div class="portal-stat-grid">
                            <div class="portal-stat-tile portal-stat-tile--info">
                                <p class="portal-stat-tile__label">Total Recipients</p>
                                <h2 class="portal-stat-tile__value">{{ $stats['total'] }}</h2>
                            </div>
                            <div class="portal-stat-tile portal-stat-tile--success">
                                <p class="portal-stat-tile__label">Successfully Sent</p>
                                <h2 class="portal-stat-tile__value">{{ $stats['sent'] }}</h2>
                            </div>
                            <div class="portal-stat-tile portal-stat-tile--warning">
                                <p class="portal-stat-tile__label">Queued</p>
                                <h2 class="portal-stat-tile__value">{{ $stats['queued'] }}</h2>
                            </div>
                            <div class="portal-stat-tile portal-stat-tile--danger">
                                <p class="portal-stat-tile__label">Failed</p>
                                <h2 class="portal-stat-tile__value">{{ $stats['failed'] }}</h2>
                            </div>
                        </div>

                        @if ($stats['failed'] > 0)
                            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.resend-failed', $announcement->id) }}" onsubmit="return confirm('Resend to {{ $stats['failed'] }} failed recipients?')">
                                @csrf
                                <x-button type="submit" variant="accent">
                                    <i class="fas fa-redo" aria-hidden="true"></i>
                                    <span>Retry Failed Deliveries</span>
                                </x-button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">No deliveries yet</h2>
                        <p class="portal-note-box__text">This announcement is still a draft. Open the send dialog when you are ready to deliver it immediately or schedule it for later.</p>
                    </div>
                @endif
            </x-card>

            <x-card title="Announcement content" subtitle="Message body, targeting filters, and schedule details used for delivery.">
                <div class="portal-form-stack">
                    <div class="portal-kv">
                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Message</p>
                            <div class="portal-kv__value portal-rich-text">{{ $announcement->message }}</div>
                        </div>

                        @if (! empty($filterSummary))
                            <div class="portal-kv__row">
                                <p class="portal-kv__label">Target Filters</p>
                                <div class="portal-chip-list">
                                    @foreach ($filterSummary as $label => $value)
                                        <span class="badge badge-secondary">{{ $label }}: {{ $value }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="portal-kv__row">
                            <p class="portal-kv__label">Scheduling</p>
                            <div class="portal-kv__value">
                                @if ($announcement->send_at)
                                    <div>Scheduled: <strong>{{ $announcement->send_at->format('M d, Y h:i A') }}</strong></div>
                                    @if ($announcement->sent_at)
                                        <div>Sent: <strong>{{ $announcement->sent_at->format('M d, Y h:i A') }}</strong></div>
                                    @endif
                                @else
                                    Sent immediately when dispatched
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </section>

        <x-card title="Delivery details" subtitle="Track each student notification, retry count, and any recorded delivery error." padding="flush">
            <div class="portal-card__body portal-card__body--compact">
                <div class="portal-toolbar">
                    <div class="portal-toolbar__group portal-toolbar__group--end">
                        <div class="portal-select-inline">
                            <label for="notificationStatusFilter">Status Filter</label>
                            <form method="GET">
                                <select id="notificationStatusFilter" name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="queued" @selected(request('status') === 'queued')>Queued</option>
                                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                    <option value="bounced" @selected(request('status') === 'bounced')>Bounced</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portal-table-wrap">
                <table class="portal-responsive-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Attempts</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            @php
                                $notificationStatusColor = [
                                    'queued' => 'warning',
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'bounced' => 'danger',
                                ][$notification->status] ?? 'secondary';
                                $statusIcon = [
                                    'queued' => 'fas fa-hourglass-half',
                                    'sent' => 'fas fa-check-circle',
                                    'failed' => 'fas fa-exclamation-circle',
                                    'bounced' => 'fas fa-ban',
                                ][$notification->status] ?? 'fas fa-question-circle';
                            @endphp
                            <tr>
                                <td data-label="Student">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $notification->student?->name ?? 'Unknown' }}</p>
                                        <p class="portal-table-cell-stack__meta">{{ $notification->student?->student_number ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td data-label="Email"><span class="portal-code">{{ $notification->email }}</span></td>
                                <td data-label="Status">
                                    <span class="badge badge-{{ $notificationStatusColor }}">
                                        <i class="{{ $statusIcon }}" aria-hidden="true"></i> {{ ucfirst($notification->status) }}
                                    </span>
                                </td>
                                <td data-label="Sent At">
                                    <span class="portal-table-cell-stack__meta">
                                        {{ $notification->sent_at ? $notification->sent_at->format('M d, Y h:i A') : 'Not sent yet' }}
                                    </span>
                                </td>
                                <td data-label="Attempts">
                                    <div class="portal-table-cell-stack">
                                        <p class="portal-table-cell-stack__title">{{ $notification->retry_count }}/3</p>
                                        <p class="portal-table-cell-stack__meta">
                                            Last try: {{ $notification->last_attempt_at?->format('M d, h:i A') ?? 'N/A' }}
                                        </p>
                                    </div>
                                </td>
                                <td data-label="Notes">
                                    <span class="portal-table-cell-stack__meta">
                                        {{ $notification->error_message ? Str::limit($notification->error_message, 80) : 'No issues recorded' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="portal-responsive-table__empty">
                                    <div class="portal-empty">
                                        <div class="portal-empty__icon">
                                            <i class="fas fa-inbox" aria-hidden="true"></i>
                                        </div>
                                        <h3 class="portal-empty__title">No deliveries to show</h3>
                                        <p class="portal-empty__text">Once this announcement is queued or sent, individual delivery records will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        @if ($notifications->hasPages())
            <div>
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <div id="sendModal" class="portal-modal" aria-hidden="true">
        <div class="portal-modal__dialog">
            <x-card title="Send announcement" subtitle="Choose whether to send the draft immediately or schedule a later release.">
                <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.send', $announcement->id) }}" class="portal-form-stack">
                    @csrf

                    <div class="portal-radio-list">
                        <label class="portal-radio-option">
                            <input type="radio" name="send_type" value="now" checked>
                            <span class="portal-radio-option__content">
                                <span class="portal-radio-option__title">Send immediately</span>
                                <span class="portal-radio-option__text">Queue the announcement for delivery right away.</span>
                            </span>
                        </label>

                        <label class="portal-radio-option">
                            <input type="radio" name="send_type" value="scheduled">
                            <span class="portal-radio-option__content">
                                <span class="portal-radio-option__title">Schedule for later</span>
                                <span class="portal-radio-option__text">Pick a date and time for a future release.</span>
                            </span>
                        </label>
                    </div>

                    <div class="field" id="scheduleInputDiv" hidden>
                        <label for="send_at">Send Date & Time</label>
                        <input type="datetime-local" id="send_at" name="send_at">
                    </div>

                    <div class="portal-note-box portal-note-box--accent">
                        <h2 class="portal-note-box__title">Targeted students</h2>
                        <p class="portal-note-box__text">{{ $stats['target_preview_count'] ?? $stats['total'] }} recipients are currently matched by this announcement.</p>
                    </div>

                    <div class="portal-modal__footer">
                        <x-button type="button" variant="secondary" id="hideSendModalButton">Cancel</x-button>
                        <x-button type="submit">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            <span>Confirm & Send</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('sendModal');
        const showButton = document.getElementById('showSendModalButton');
        const hideButton = document.getElementById('hideSendModalButton');
        const scheduleInput = document.getElementById('scheduleInputDiv');
        const radioButtons = Array.from(document.querySelectorAll('input[name="send_type"]'));

        const syncScheduleInput = () => {
            const selected = document.querySelector('input[name="send_type"]:checked');
            const isScheduled = selected?.value === 'scheduled';
            scheduleInput.hidden = !isScheduled;
        };

        const openModal = () => {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            syncScheduleInput();
        };

        const closeModal = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };

        showButton?.addEventListener('click', openModal);
        hideButton?.addEventListener('click', closeModal);
        radioButtons.forEach((radio) => radio.addEventListener('change', syncScheduleInput));

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                closeModal();
            }
        });

        syncScheduleInput();
    });
</script>
@endpush
