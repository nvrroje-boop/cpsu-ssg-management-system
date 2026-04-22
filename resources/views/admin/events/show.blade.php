@extends('layouts.app')

@section('title', 'Event Details')
@section('page_title', 'Event Information')
@section('page_subtitle', 'Attendance kiosk, secure event QR, and live event controls.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
<style>
    .attendance-qr-preview {
        max-width: 240px;
        margin: 0 auto;
        padding: 1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(46, 139, 74, 0.08), rgba(245, 208, 96, 0.1));
    }

    .attendance-scanner-box {
        min-height: 280px;
        border: 1px dashed rgba(15, 45, 28, 0.18);
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.72);
    }

    .attendance-scan-result {
        margin-top: 1rem;
    }
</style>
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
        $isActive = (bool) $event->attendance_active;
    @endphp

    <div class="portal-detail-stack">
        <section class="portal-detail-header">
            <div>
                <h1 class="portal-detail-header__title">{{ $event->event_title }}</h1>
                <p class="portal-detail-header__text">Created by {{ $event->creator?->name ?? 'System' }} on {{ $event->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route($managementRoutePrefix.'.events.index')" variant="secondary">Back to Events</x-button>
                <x-button :href="route($managementRoutePrefix.'.events.edit', $event->id)">Edit Event</x-button>
            </div>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Event Details" subtitle="Schedule, audience, and attendance configuration.">
                <div class="portal-kv">
                    <div class="portal-kv__row"><p class="portal-kv__label">Location</p><p class="portal-kv__value">{{ $event->location }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Date</p><p class="portal-kv__value">{{ optional($event->event_date)->format('Y-m-d') ?? $event->event_date }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Event Time</p><p class="portal-kv__value">{{ substr((string) $event->event_time, 0, 5) }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Visibility</p><p class="portal-kv__value"><span class="badge badge-info">{{ ucfirst($event->visibility) }}</span></p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Department</p><p class="portal-kv__value">{{ $event->department?->department_name ?? 'All departments' }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Time-In Window</p><p class="portal-kv__value">{{ $timeInWindow }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Time-Out Window</p><p class="portal-kv__value">{{ $timeOutWindow }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Late After</p><p class="portal-kv__value">{{ substr((string) $event->attendance_late_after, 0, 5) }}</p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Attendance Status</p><p class="portal-kv__value"><span class="badge {{ $isActive ? 'badge-success' : 'badge-warning' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span></p></div>
                    <div class="portal-kv__row"><p class="portal-kv__label">Description</p><p class="portal-kv__value">{{ $event->event_description }}</p></div>
                </div>
            </x-card>

            <x-card title="Attendance Summary" subtitle="Live counts for the selected event.">
                <div class="portal-stat-grid">
                    <div class="portal-stat-tile portal-stat-tile--info">
                        <p class="portal-stat-tile__label">Eligible Students</p>
                        <h2 class="portal-stat-tile__value">{{ $attendanceSummary['eligible'] }}</h2>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--success">
                        <p class="portal-stat-tile__label">Present</p>
                        <h2 class="portal-stat-tile__value">{{ $attendanceSummary['present'] }}</h2>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--warning">
                        <p class="portal-stat-tile__label">Late</p>
                        <h2 class="portal-stat-tile__value">{{ $attendanceSummary['late'] }}</h2>
                    </div>
                    <div class="portal-stat-tile portal-stat-tile--danger">
                        <p class="portal-stat-tile__label">Incomplete</p>
                        <h2 class="portal-stat-tile__value">{{ $attendanceSummary['incomplete'] }}</h2>
                    </div>
                </div>

                <div class="portal-note-box" style="margin-top: 1rem;">
                    <h2 class="portal-note-box__title">Current Attendance Phase</h2>
                    <p class="portal-note-box__text">{{ $attendancePhase['message'] }}</p>
                </div>
            </x-card>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Kiosk Controls" subtitle="Start, stop, and extend attendance without leaving this page.">
                <div class="portal-form-stack">
                    <div class="portal-inline-actions">
                        <form method="POST" action="{{ route($managementRoutePrefix.'.events.attendance.start', $event->id) }}">
                            @csrf
                            <x-button type="submit">Start Attendance</x-button>
                        </form>

                        <form method="POST" action="{{ route($managementRoutePrefix.'.events.attendance.stop', $event->id) }}">
                            @csrf
                            <x-button type="submit" variant="secondary">Stop Attendance</x-button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route($managementRoutePrefix.'.events.attendance.extend', $event->id) }}" class="portal-form-grid">
                        @csrf
                        <div class="field">
                            <label for="window">Extend Window</label>
                            <select id="window" name="window">
                                <option value="time_in">Time-In</option>
                                <option value="time_out">Time-Out</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="minutes">Minutes</label>
                            <input id="minutes" name="minutes" type="number" min="1" max="120" value="10" required>
                        </div>
                        <div class="actions" style="margin-top: 0;">
                            <x-button type="submit" variant="accent">Extend Time</x-button>
                        </div>
                    </form>

                    <div class="portal-note-box">
                        <h2 class="portal-note-box__title">Security Notes</h2>
                        <p class="portal-note-box__text">Kiosk scanning uses the selected event session plus the student's ID QR. The system rejects early scans, invalid QR data, duplicate scans, and repeated scans within 5 seconds.</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Secure Event QR" subtitle="Use this for optional student self-scan while the session is active.">
                @if ($eventQrImage !== '')
                    <div class="attendance-qr-preview">
                        <img src="{{ $eventQrImage }}" alt="Event attendance QR code">
                    </div>
                    <p class="portal-helper" style="margin-top: 1rem;">Expires at {{ optional($event->attendance_token_expires_at)?->format('M d, Y h:i A') ?? 'Not set' }}</p>
                    <p class="portal-helper">QR link: {{ $eventQrLink }}</p>
                @else
                    <div class="portal-empty">
                        <p class="portal-empty__title"><strong>Event QR inactive</strong></p>
                        <p class="portal-empty__text">Start attendance to rotate a fresh signed event QR for self-scan.</p>
                    </div>
                @endif
            </x-card>
        </section>

        <section class="portal-detail-grid portal-detail-grid--two">
            <x-card title="Kiosk Scanner" subtitle="Scan the student QR from the kiosk device.">
                <div id="attendance-reader" class="attendance-scanner-box"></div>
                <div id="attendance-scan-result" class="attendance-scan-result"></div>
                <div class="portal-inline-actions" style="margin-top: 1rem;">
                    <x-button type="button" id="startAttendanceScanner">Start Camera</x-button>
                    <x-button type="button" id="stopAttendanceScanner" variant="secondary">Stop Camera</x-button>
                </div>

                <form id="manualStudentQrForm" class="portal-form-stack" style="margin-top: 1rem;">
                    <div class="field">
                        <label for="student_qr">Manual Student QR Payload</label>
                        <input id="student_qr" name="student_qr" type="text" placeholder="Paste student={token} or the raw student token">
                    </div>
                    <div class="actions">
                        <x-button type="submit" variant="accent">Record Attendance</x-button>
                    </div>
                </form>
            </x-card>

            <x-card title="Manual Override" subtitle="Add, edit, or mark attendance without scanning.">
                <form method="POST" action="{{ route($managementRoutePrefix.'.events.attendance.manual', $event->id) }}" class="portal-form-stack">
                    @csrf
                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="user_id">Student</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Select student</option>
                                @foreach ($eligibleStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }}{{ $student->student_number ? ' - '.$student->student_number : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="incomplete">Incomplete</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="time_in">Time In</label>
                            <input id="time_in" name="time_in" type="datetime-local">
                        </div>
                        <div class="field">
                            <label for="time_out">Time Out</label>
                            <input id="time_out" name="time_out" type="datetime-local">
                        </div>
                    </div>
                    <div class="actions">
                        <x-button type="submit" variant="accent">Save Attendance</x-button>
                    </div>
                </form>
            </x-card>
        </section>

        <x-card title="Attendance Records" subtitle="Latest scans and manual overrides for this event." padding="flush">
            <table class="portal-responsive-table" aria-label="Attendance records">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Department</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceRecords as $record)
                        <tr>
                            <td data-label="Student">{{ $record->student?->name ?? 'Unknown student' }}</td>
                            <td data-label="Department">{{ $record->student?->department?->department_name ?? 'N/A' }}</td>
                            <td data-label="Time In">{{ $record->time_in?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td data-label="Time Out">{{ $record->time_out?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td data-label="Status"><span class="badge {{ $record->status === 'late' ? 'badge-warning' : ($record->status === 'incomplete' ? 'badge-danger' : 'badge-success') }}">{{ ucfirst($record->status) }}</span></td>
                            <td data-label="Recorded By">{{ $record->recordedBy?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="portal-responsive-table__empty">
                                <h3>No attendance recorded yet</h3>
                                <p>Student scans and manual overrides will appear here after attendance starts.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
@endsection

@push('page-js')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const readerId = 'attendance-reader';
        const result = document.getElementById('attendance-scan-result');
        const manualForm = document.getElementById('manualStudentQrForm');
        const manualInput = document.getElementById('student_qr');
        const startButton = document.getElementById('startAttendanceScanner');
        const stopButton = document.getElementById('stopAttendanceScanner');
        const scanUrl = @json(route($managementRoutePrefix.'.events.attendance.scan', $event->id));
        let scanner = null;
        let active = false;
        let pending = false;

        const renderResult = (message, success = false) => {
            result.innerHTML = `<div class="portal-alert ${success ? 'portal-alert--success' : 'portal-alert--error'}"><div class="portal-alert__content">${message}</div></div>`;
        };

        const submitScan = async (studentQr) => {
            if (!studentQr || pending) {
                return;
            }

            pending = true;

            try {
                const response = await fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ student_qr: studentQr }),
                });

                const data = await response.json();
                renderResult(data.message || 'Attendance processed.', response.ok);

                if (response.ok) {
                    manualInput.value = '';
                    window.setTimeout(() => window.location.reload(), 900);
                }
            } catch (error) {
                renderResult('Attendance could not be processed. Check your connection and try again.');
            } finally {
                pending = false;
            }
        };

        const stopScanner = async () => {
            if (!scanner || !active) {
                return;
            }

            try {
                await scanner.stop();
                await scanner.clear();
            } catch (error) {
            }

            active = false;
        };

        startButton?.addEventListener('click', async () => {
            if (active || typeof Html5Qrcode === 'undefined') {
                return;
            }

            scanner = new Html5Qrcode(readerId);

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 240, aspectRatio: 1 },
                    async (decodedText) => {
                        await stopScanner();
                        await submitScan(decodedText);
                    }
                );

                active = true;
                renderResult('Scanner ready. Point the camera at the student QR.', true);
            } catch (error) {
                renderResult('Camera access is unavailable. Use manual QR entry instead.');
            }
        });

        stopButton?.addEventListener('click', async () => {
            await stopScanner();
            renderResult('Scanner stopped.');
        });

        manualForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            await submitScan(manualInput.value);
        });
    });
</script>
@endpush
