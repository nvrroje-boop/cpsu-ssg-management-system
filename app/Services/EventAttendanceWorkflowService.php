<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventAttendanceWorkflowService
{
    private const DOUBLE_SCAN_WINDOW_SECONDS = 5;

    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly SystemNotificationService $notificationService,
    ) {
    }

    public function applyScheduleDefaults(array $validated): array
    {
        $eventTime = substr((string) ($validated['event_time'] ?? '08:00'), 0, 5) ?: '08:00';
        $base = Carbon::createFromFormat('H:i', $eventTime);

        return array_merge($validated, [
            'attendance_time_in_starts_at' => $this->normalizeTimeInput($validated['attendance_time_in_starts_at'] ?? $base->copy()->subMinutes(30)->format('H:i')),
            'attendance_time_in_ends_at' => $this->normalizeTimeInput($validated['attendance_time_in_ends_at'] ?? $base->copy()->addMinutes(10)->format('H:i')),
            'attendance_time_out_starts_at' => $this->normalizeTimeInput($validated['attendance_time_out_starts_at'] ?? $base->copy()->addMinutes(110)->format('H:i')),
            'attendance_time_out_ends_at' => $this->normalizeTimeInput($validated['attendance_time_out_ends_at'] ?? $base->copy()->addMinutes(150)->format('H:i')),
            'attendance_late_after' => $this->normalizeTimeInput($validated['attendance_late_after'] ?? $eventTime),
        ]);
    }

    public function startSession(Event $event, User $actor): Event
    {
        $refreshed = $this->qrCodeService->refreshEventAttendanceToken($event, $this->latestWindowEnd($event));

        $refreshed->forceFill([
            'attendance_active' => true,
            'attendance_started_at' => now(),
            'attendance_stopped_at' => null,
            'attendance_started_by_user_id' => $actor->id,
            'attendance_open_notified_at' => now(),
            'attendance_closing_notified_at' => null,
            'attendance_closed_notified_at' => null,
        ])->save();

        $this->notifyEventAudience(
            $refreshed->fresh(),
            'Attendance is now open',
            'Attendance is now OPEN for '.$refreshed->event_title.'.',
            'attendance',
            route('student.events.show', $refreshed->id),
        );

        return $refreshed->fresh();
    }

    public function stopSession(Event $event, ?User $actor = null): Event
    {
        $event->forceFill([
            'attendance_active' => false,
            'attendance_stopped_at' => now(),
            'attendance_token_expires_at' => now(),
            'attendance_closed_notified_at' => now(),
        ])->save();

        EventAttendance::query()
            ->where('event_id', $event->id)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->where('status', '!=', 'absent')
            ->update([
                'status' => 'incomplete',
                'recorded_by_user_id' => $actor?->id,
            ]);

        $this->notifyEventAudience(
            $event->fresh(),
            'Attendance closed',
            'Attendance for '.$event->event_title.' is now closed.',
            'attendance',
            route('student.events.show', $event->id),
        );

        return $event->fresh();
    }

    public function dispatchAudienceNotification(Event $event, string $title, string $message, string $type, string $studentLink): void
    {
        $this->notifyEventAudience($event, $title, $message, $type, $studentLink);
    }

    public function extendSession(Event $event, int $minutes, ?string $window = null): Event
    {
        $minutes = max(1, min(120, $minutes));
        $targetWindow = $window ?: $this->activeWindowKey($event);
        $column = $targetWindow === 'time_out'
            ? 'attendance_time_out_ends_at'
            : 'attendance_time_in_ends_at';

        $current = $this->normalizeTimeInput((string) $event->{$column}) ?: '00:00';
        $extended = Carbon::createFromFormat('H:i', substr($current, 0, 5))
            ->addMinutes($minutes)
            ->format('H:i');

        $event->forceFill([
            $column => $extended,
            'attendance_token_expires_at' => $this->latestWindowEnd($event, [
                $column => $extended,
            ]),
            'attendance_closing_notified_at' => null,
        ])->save();

        return $event->fresh();
    }

    public function recordKioskScan(Event $event, string $studentPayload, User $actor): array
    {
        $student = $this->resolveStudentFromPayload($studentPayload);

        if ($student === null) {
            return $this->failure('Student QR is invalid or unreadable.', 404);
        }

        return $this->record($event, $student, $actor, 'kiosk');
    }

    public function recordSelfScan(Event $event, User $student, ?string $token, mixed $expires): array
    {
        if (! $this->qrCodeService->validateEventAttendanceToken($event, $token, $expires)) {
            return $this->failure('The event QR token is invalid or expired.', 422);
        }

        return $this->record($event, $student, $student, 'self_scan');
    }

    public function upsertManualAttendance(Event $event, User $student, User $actor, array $payload): EventAttendance
    {
        $attendance = EventAttendance::query()->firstOrNew([
            'event_id' => $event->id,
            'user_id' => $student->id,
        ]);

        $timeIn = $this->parseDateTimeInput($payload['time_in'] ?? null);
        $timeOut = $this->parseDateTimeInput($payload['time_out'] ?? null);
        $status = $payload['status'] ?? ($timeIn !== null ? $this->statusForTimeIn($event, $timeIn) : 'absent');

        $attendance->fill([
            'student_id' => $student->id,
            'user_id' => $student->id,
            'time_in' => $status === 'absent' ? null : $timeIn,
            'time_out' => $status === 'absent' ? null : $timeOut,
            'status' => $status,
            'attendance_method' => 'manual',
            'recorded_by_user_id' => $actor->id,
            'scanned_by_user_id' => $actor->id,
            'last_scanned_at' => now(),
            'scanned_at' => $timeIn,
        ]);

        $attendance->save();

        $message = $status === 'absent'
            ? 'Your attendance for '.$event->event_title.' was updated by an administrator.'
            : 'Your attendance for '.$event->event_title.' has been recorded.';

        $this->notificationService->createForUser(
            $student,
            'Attendance updated',
            $message,
            'attendance',
            route('student.events.show', $event->id),
            $event,
        );

        return $attendance->fresh(['student']);
    }

    public function eligibleStudentsQuery(Event $event): Builder
    {
        return User::query()
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->when(
                $event->visibility === 'private' && $event->department_id !== null,
                fn (Builder $query) => $query->where('department_id', $event->department_id),
            )
            ->with(['department', 'section'])
            ->orderBy('name');
    }

    public function eventSummary(Event $event): array
    {
        $eligibleStudents = $this->eligibleStudentsQuery($event)->get(['id']);
        $records = EventAttendance::query()
            ->where('event_id', $event->id)
            ->get(['user_id', 'status']);

        return [
            'eligible' => $eligibleStudents->count(),
            'recorded' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'incomplete' => $records->where('status', 'incomplete')->count(),
            'absent' => max($eligibleStudents->count() - $records->where('status', '!=', 'absent')->count(), 0),
        ];
    }

    public function eventAttendances(Event $event, int $limit = 25): Collection
    {
        return EventAttendance::query()
            ->with(['student.department', 'student.section', 'recordedBy'])
            ->where('event_id', $event->id)
            ->orderByDesc(DB::raw('COALESCE(last_scanned_at, time_in, created_at)'))
            ->limit($limit)
            ->get();
    }

    public function currentPhase(Event $event, ?Carbon $now = null): array
    {
        $moment = $now ?? now();
        $timeInStart = $this->eventMoment($event, (string) $event->attendance_time_in_starts_at);
        $timeInEnd = $this->eventMoment($event, (string) $event->attendance_time_in_ends_at);
        $timeOutStart = $this->eventMoment($event, (string) $event->attendance_time_out_starts_at);
        $timeOutEnd = $this->eventMoment($event, (string) $event->attendance_time_out_ends_at);

        if ($timeInStart === null || $timeInEnd === null || $timeOutStart === null || $timeOutEnd === null) {
            return ['phase' => 'closed', 'message' => 'Attendance schedule is incomplete.'];
        }

        if ($moment->lt($timeInStart)) {
            return ['phase' => 'too_early', 'message' => 'Too early for attendance.'];
        }

        if ($moment->betweenIncluded($timeInStart, $timeInEnd)) {
            return ['phase' => 'time_in', 'message' => 'Time-in window is open.'];
        }

        if ($moment->betweenIncluded($timeOutStart, $timeOutEnd)) {
            return ['phase' => 'time_out', 'message' => 'Time-out window is open.'];
        }

        return ['phase' => 'closed', 'message' => 'Attendance is closed.'];
    }

    public function formatWindow(Event $event, string $window): string
    {
        $startColumn = $window === 'time_out' ? 'attendance_time_out_starts_at' : 'attendance_time_in_starts_at';
        $endColumn = $window === 'time_out' ? 'attendance_time_out_ends_at' : 'attendance_time_in_ends_at';

        return substr((string) $event->{$startColumn}, 0, 5).' - '.substr((string) $event->{$endColumn}, 0, 5);
    }

    private function record(Event $event, User $student, User $actor, string $method): array
    {
        if (! $event->attendance_active) {
            return $this->failure('Attendance is not active for this event.', 422);
        }

        if (! $this->isEligibleStudent($event, $student)) {
            return $this->failure('This student is not eligible for the selected event.', 403);
        }

        $phase = $this->currentPhase($event);
        $attendance = EventAttendance::query()
            ->where('event_id', $event->id)
            ->where('user_id', $student->id)
            ->first();

        $now = now();

        if (
            $attendance !== null
            && $attendance->last_scanned_at !== null
            && $attendance->last_scanned_at->gt($now->copy()->subSeconds(self::DOUBLE_SCAN_WINDOW_SECONDS))
        ) {
            return $this->failure('Scan ignored. Please wait a few seconds before scanning again.', 429, [
                'duplicate' => true,
                'student' => $student->name,
            ]);
        }

        if ($phase['phase'] === 'too_early') {
            return $this->failure('Too early. Attendance has not opened yet.', 422);
        }

        if ($phase['phase'] === 'closed') {
            return $this->failure('Attendance closed for this event.', 422);
        }

        if ($phase['phase'] === 'time_in') {
            if ($attendance !== null && $attendance->time_out !== null) {
                return $this->failure('Attendance already completed for this student.', 409);
            }

            if ($attendance !== null && $attendance->time_in !== null) {
                return $this->failure('Time-in is already recorded. Wait for the time-out window.', 409);
            }

            $status = $this->statusForTimeIn($event, $now);

            $attendance = EventAttendance::query()->create([
                'event_id' => $event->id,
                'user_id' => $student->id,
                'student_id' => $student->id,
                'time_in' => $now,
                'status' => $status,
                'attendance_method' => $method,
                'recorded_by_user_id' => $actor->id,
                'scanned_by_user_id' => $actor->id,
                'last_scanned_at' => $now,
                'scanned_at' => $now,
            ]);

            $this->notifyStudentAttendance($student, $event);

            return $this->success(
                sprintf('%s recorded for TIME-IN. Status: %s.', $student->name, strtoupper($status)),
                $attendance,
                'time_in'
            );
        }

        if ($attendance === null || $attendance->time_in === null) {
            return $this->failure('No time-in record found for this student.', 409);
        }

        if ($attendance->time_out !== null) {
            return $this->failure('Attendance already completed for this student.', 409);
        }

        $attendance->fill([
            'time_out' => $now,
            'attendance_method' => $method,
            'recorded_by_user_id' => $actor->id,
            'scanned_by_user_id' => $actor->id,
            'last_scanned_at' => $now,
        ])->save();

        $this->notifyStudentAttendance($student, $event);

        return $this->success(
            sprintf('%s recorded for TIME-OUT.', $student->name),
            $attendance->fresh(),
            'time_out'
        );
    }

    private function notifyStudentAttendance(User $student, Event $event): void
    {
        $this->notificationService->createForUser(
            $student,
            'Attendance recorded',
            'Your attendance has been recorded for '.$event->event_title.'.',
            'attendance',
            route('student.events.show', $event->id),
            $event,
        );
    }

    private function notifyEventAudience(Event $event, string $title, string $message, string $type, string $link): void
    {
        if ($event->visibility === 'public' && $event->department_id === null) {
            $this->notificationService->broadcastToRole('student', $title, $message, $type, $link, $event);
        } else {
            $students = $this->eligibleStudentsQuery($event)->get();
            $this->notificationService->createForUsers($students, $title, $message, $type, $link, $event);
        }

        $this->notificationService->broadcastToRole(
            'admin',
            $title,
            $message,
            $type,
            route('admin.events.show', $event->id),
            $event,
        );

        $this->notificationService->broadcastToRole(
            'ssg',
            $title,
            $message,
            $type,
            route('officer.events.show', $event->id),
            $event,
        );
    }

    private function resolveStudentFromPayload(string $payload): ?User
    {
        $token = $this->extractStudentToken($payload);

        if (blank($token)) {
            return null;
        }

        return User::query()
            ->where('qr_token', $token)
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->first();
    }

    private function extractStudentToken(string $payload): string
    {
        $value = trim($payload);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, 'student=')) {
            parse_str(parse_url($value, PHP_URL_QUERY) ?: $value, $parts);
            return (string) ($parts['student'] ?? '');
        }

        return $value;
    }

    private function isEligibleStudent(Event $event, User $student): bool
    {
        if (! $student->isStudentPortalUser()) {
            return false;
        }

        if ($event->visibility !== 'private' || $event->department_id === null) {
            return true;
        }

        return (int) $event->department_id === (int) $student->department_id;
    }

    private function statusForTimeIn(Event $event, Carbon $moment): string
    {
        $lateAfter = $this->eventMoment($event, (string) $event->attendance_late_after);

        return $lateAfter !== null && $moment->gt($lateAfter) ? 'late' : 'present';
    }

    private function normalizeTimeInput(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    private function parseDateTimeInput(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse((string) $value);
    }

    private function eventMoment(Event $event, string $time): ?Carbon
    {
        if (blank($event->event_date) || blank($time)) {
            return null;
        }

        return Carbon::parse(
            (optional($event->event_date)->format('Y-m-d') ?? (string) $event->event_date)
            .' '
            .substr($time, 0, 5)
        );
    }

    private function latestWindowEnd(Event $event, array $overrides = []): Carbon
    {
        $endTime = $overrides['attendance_time_out_ends_at'] ?? $event->attendance_time_out_ends_at ?? $event->event_time ?? '23:59';
        $moment = $this->eventMoment($event, (string) $endTime);

        return ($moment ?? now()->addHours(2))->copy()->addMinutes(5);
    }

    private function activeWindowKey(Event $event): string
    {
        return $this->currentPhase($event)['phase'] === 'time_out' ? 'time_out' : 'time_in';
    }

    private function success(string $message, EventAttendance $attendance, string $action): array
    {
        return [
            'success' => true,
            'status' => 200,
            'message' => $message,
            'action' => $action,
            'attendance' => $attendance->fresh(['student']),
        ];
    }

    private function failure(string $message, int $status, array $extra = []): array
    {
        return array_merge([
            'success' => false,
            'status' => $status,
            'message' => $message,
        ], $extra);
    }
}
