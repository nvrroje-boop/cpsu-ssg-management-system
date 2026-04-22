@extends('layouts.app')

@php
    $isOfficerPortal = request()->routeIs('officer.*');
    $portalLabel = $isOfficerPortal ? 'Officer' : 'Admin';
    $summaryCards = [
        [
            'label' => "Today's scans",
            'value' => $summary['today_scans'],
            'caption' => 'QR validations recorded today.',
            'tone' => 'green',
            'icon' => 'M9 16.17 5.12 12.29 3.7 13.7 9 19l12-12-1.41-1.41L9 16.17ZM5 5h4v4H5V5Zm10 0h4v4h-4V5ZM5 15h4v4H5v-4Z',
        ],
        [
            'label' => 'Active events',
            'value' => $summary['active_events'],
            'caption' => 'Sessions currently ready for attendance.',
            'tone' => 'info',
            'icon' => 'M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h3V2Zm13 8H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8Z',
        ],
        [
            'label' => 'Attendance rate',
            'value' => $summary['attendance_rate'],
            'caption' => 'Participation completion across tracked attendance.',
            'tone' => 'amber',
            'icon' => 'M5 9.2h3V19H5V9.2Zm5.5-4.2h3V19h-3V5Zm5.5 7h3V19h-3v-7Z',
        ],
    ];
@endphp

@section('title', 'Attendance')
@section('page_title', 'Attendance Monitoring')
@section('page_subtitle', 'Scan student QR emails, validate attendance tokens, and review recent check-ins.')

@push('page-css')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-page-stack">
        <section class="dashboard__metrics" aria-label="Attendance summary metrics">
            @foreach ($summaryCards as $card)
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--{{ $card['tone'] }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="{{ $card['icon'] }}" fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <p class="dashboard-metric__label">{{ $card['label'] }}</p>
                        <h2 class="dashboard-metric__value">{{ $card['value'] }}</h2>
                        <p class="dashboard-metric__caption">{{ $card['caption'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">{{ $portalLabel }} attendance workspace</h1>
                <p class="portal-page-lead__text">
                    Use the device camera to scan student QR emails, or paste a QR URL manually if camera access is unavailable on the current device.
                </p>
            </div>
            <span class="badge badge-info portal-page-lead__badge">Live QR validation</span>
        </section>

        <section class="scanner-layout">
            <x-card title="QR Scanner" subtitle="Attendance is recorded once per student and event token.">
                <div id="attendance-reader" class="scanner-reader"></div>
                <div id="attendance-scan-result" class="scanner-result"></div>
                <p class="scanner-help">
                    On mobile, allow camera access when prompted. If the browser blocks the camera, use manual token validation instead.
                </p>
                <div class="scanner-actions">
                    <x-button id="startAttendanceScan" type="button">Start Camera</x-button>
                    <x-button id="stopAttendanceScan" type="button" variant="secondary">Stop Camera</x-button>
                </div>
            </x-card>

            <x-card title="Manual Token Validation" subtitle="Paste a full QR URL or the raw token when scanning is not available.">
                <form id="manualAttendanceForm" class="form-grid">
                    <div class="field">
                        <label for="attendanceToken">QR URL or Token</label>
                        <input id="attendanceToken" name="attendance_token" type="text" placeholder="https://your-app.ngrok.../attendance/scan/{token}">
                    </div>
                    <div class="actions">
                        <x-button type="submit">Validate Attendance</x-button>
                    </div>
                </form>
                <p class="scanner-help">Ngrok-compatible links are generated automatically, so the same QR works on phones during live events.</p>
            </x-card>
        </section>

        <section class="scanner-layout">
            <x-card title="Session Windows" subtitle="Current attendance windows and event readiness.">
                <div class="portal-session-list">
                    @forelse ($sessions as $session)
                        <article class="portal-session-list__item">
                            <h3 class="portal-session-list__title">{{ $session['event'] }}</h3>
                            <p class="portal-session-list__text">{{ $session['window'] }}</p>
                            <span class="badge badge-info">{{ $session['status'] }}</span>
                        </article>
                    @empty
                        <div class="portal-empty">
                            <p class="portal-empty__title"><strong>No active sessions</strong></p>
                            <p class="portal-empty__text">Event attendance windows will appear here when sessions are available.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Recent Scans" subtitle="Latest successful attendance validations." padding="flush">
                <table class="portal-responsive-table" aria-label="Recent attendance scans">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Event</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentAttendance as $entry)
                            <tr>
                                <td data-label="Student">{{ $entry['student'] }}</td>
                                <td data-label="Event">{{ $entry['event'] }}</td>
                                <td data-label="Time">{{ $entry['time'] }}</td>
                                <td data-label="Status"><span class="badge badge-success">{{ $entry['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="portal-responsive-table__empty">
                                    <h3>No recent scans</h3>
                                    <p>Successful validations will appear here after QR attendance is recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </section>
    </div>
@endsection

@push('page-js')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const readerId = 'attendance-reader';
        const result = document.getElementById('attendance-scan-result');
        const startButton = document.getElementById('startAttendanceScan');
        const stopButton = document.getElementById('stopAttendanceScan');
        const manualForm = document.getElementById('manualAttendanceForm');
        const manualInput = document.getElementById('attendanceToken');
        const scanUrlTemplate = @json(route('attendance.scan', ['token' => '__TOKEN__']));
        let scanner = null;
        let active = false;
        let requestInFlight = false;

        const renderResult = (message, success = false) => {
            result.innerHTML = `<div class="portal-alert ${success ? 'portal-alert--success' : 'portal-alert--error'}"><div class="portal-alert__content">${message}</div></div>`;
        };

        const setButtons = (running) => {
            if (startButton) {
                startButton.disabled = running;
                startButton.textContent = running ? 'Camera Running' : 'Start Camera';
            }

            if (stopButton) {
                stopButton.disabled = !running;
            }
        };

        const extractToken = (value) => {
            const trimmed = String(value || '').trim();

            if (trimmed === '') {
                return '';
            }

            try {
                const url = new URL(trimmed);
                const segments = url.pathname.split('/').filter(Boolean);
                return segments[segments.length - 1] || '';
            } catch (error) {
                return trimmed.split('/').filter(Boolean).pop() || trimmed;
            }
        };

        const submitToken = async (rawValue) => {
            if (requestInFlight) {
                return;
            }

            const token = extractToken(rawValue);

            if (!token) {
                renderResult('Enter a valid QR token or scan URL.');
                return;
            }

            const requestUrl = scanUrlTemplate.replace('__TOKEN__', encodeURIComponent(token));
            requestInFlight = true;
            renderResult('Validating attendance token...', true);

            try {
                const response = await fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ token })
                });

                const data = await response.json();
                renderResult(data.message || 'Attendance processed.', data.success === true);

                if (data.success === true) {
                    manualInput.value = '';
                }
            } catch (error) {
                renderResult('Attendance validation failed. Check your connection and try again.');
            } finally {
                requestInFlight = false;
            }
        };

        const stopScanner = async () => {
            if (!scanner || !active) {
                setButtons(false);
                return;
            }

            try {
                await scanner.stop();
            } catch (error) {
            }

            try {
                await scanner.clear();
            } catch (error) {
            }

            active = false;
            setButtons(false);
        };

        startButton?.addEventListener('click', async () => {
            if (active || requestInFlight) {
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                renderResult('QR scanner library could not be loaded on this device. Use manual token validation instead.');
                return;
            }

            scanner = new Html5Qrcode(readerId);

            try {
                setButtons(true);
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 240, aspectRatio: 1 },
                    async (decodedText) => {
                        await stopScanner();
                        await submitToken(decodedText);
                    }
                );

                active = true;
                renderResult('Scanner is ready. Point the camera at the student QR email.', true);
            } catch (error) {
                setButtons(false);
                renderResult('Camera access is unavailable on this device. Use manual token validation instead.');
            }
        });

        stopButton?.addEventListener('click', async () => {
            await stopScanner();
            renderResult('Scanner stopped.');
        });

        manualForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            await submitToken(manualInput?.value || '');
        });

        setButtons(false);
    });
</script>
@endpush
