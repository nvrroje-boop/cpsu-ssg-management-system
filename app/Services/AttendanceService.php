<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use Carbon\CarbonImmutable;

class AttendanceService
{
    public function getSummary(): array
    {
        $todayScans = EventAttendance::query()
            ->whereDate('last_scanned_at', today())
            ->count();

        $activeEvents = Event::query()
            ->whereDate('event_date', '>=', today())
            ->count();

        $studentCount = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->count();

        $requiredEventCount = Event::query()
            ->where('attendance_required', true)
            ->count();

        $requiredAttendanceCount = EventAttendance::query()
            ->whereHas('event', fn ($eventQuery) => $eventQuery->where('attendance_required', true))
            ->whereIn('status', ['present', 'late'])
            ->count();

        $attendanceRate = $this->formatRate(
            $requiredAttendanceCount,
            $studentCount * $requiredEventCount,
        );

        return [
            'today_scans' => $todayScans,
            'active_events' => $activeEvents,
            'attendance_rate' => $attendanceRate,
        ];
    }

    public function getRecentAttendance(int $limit = 5): array
    {
        return EventAttendance::query()
            ->with(['student', 'event'])
            ->orderByDesc('last_scanned_at')
            ->limit($limit)
            ->get()
            ->map(function (EventAttendance $attendance): array {
                return [
                    'student' => $attendance->student?->name ?? 'Unknown student',
                    'event' => $attendance->event?->event_title ?? 'Unknown event',
                    'time' => optional($attendance->last_scanned_at ?? $attendance->time_in)?->format('M d, Y h:i A') ?? 'Not scanned',
                    'status' => ucfirst($attendance->status ?? 'present'),
                ];
            })
            ->all();
    }

    public function getSessions(int $limit = 5): array
    {
        return Event::query()
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                return [
                    'event' => $event->event_title,
                    'window' => trim(
                        (optional($event->event_date)?->format('M d, Y') ?? (string) $event->event_date)
                        .' '
                        .substr((string) $event->event_time, 0, 5)
                    ),
                    'status' => $this->sessionStatus($event),
                ];
            })
            ->all();
    }

    public function getStudentAttendanceRate(User $student): string
    {
        $requiredEvents = Event::query()
            ->visibleToUser($student)
            ->where('attendance_required', true)
            ->count();

        $attendedRequiredEvents = EventAttendance::query()
            ->where('user_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('event', fn ($eventQuery) => $eventQuery
                ->visibleToUser($student)
                ->where('attendance_required', true))
            ->count();

        return $this->formatRate($attendedRequiredEvents, $requiredEvents);
    }

    public function getStudentSemesterSummary(User $student, ?CarbonImmutable $referenceDate = null): array
    {
        $window = $this->semesterWindow($referenceDate);

        $requiredEvents = Event::query()
            ->visibleToUser($student)
            ->where('attendance_required', true)
            ->whereBetween('event_date', [$window['start']->toDateString(), $window['end']->toDateString()])
            ->count();

        $presentCount = EventAttendance::query()
            ->where('user_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('event', fn ($eventQuery) => $eventQuery
                ->visibleToUser($student)
                ->where('attendance_required', true)
                ->whereBetween('event_date', [$window['start']->toDateString(), $window['end']->toDateString()]))
            ->count();

        $absenceCount = max($requiredEvents - $presentCount, 0);

        return [
            'label' => $window['label'],
            'required_events' => $requiredEvents,
            'present_count' => $presentCount,
            'absence_count' => $absenceCount,
            'attendance_rate' => $this->formatRate($presentCount, $requiredEvents),
            'range' => $window['start']->format('M d, Y').' - '.$window['end']->format('M d, Y'),
        ];
    }

    public function getSemesterClearanceSummary(?CarbonImmutable $referenceDate = null): array
    {
        $window = $this->semesterWindow($referenceDate);

        $students = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->with(['department', 'section'])
            ->orderBy('name')
            ->get();

        $requiredEvents = Event::query()
            ->where('attendance_required', true)
            ->whereBetween('event_date', [$window['start']->toDateString(), $window['end']->toDateString()])
            ->get(['id', 'department_id', 'visibility']);

        $attendanceRows = EventAttendance::query()
            ->whereHas('event', fn ($eventQuery) => $eventQuery
                ->where('attendance_required', true)
                ->whereBetween('event_date', [$window['start']->toDateString(), $window['end']->toDateString()]))
            ->whereIn('status', ['present', 'late'])
            ->get(['user_id', 'event_id']);

        $attendanceByStudent = $attendanceRows
            ->groupBy('user_id')
            ->map(fn ($group) => $group->pluck('event_id')->unique()->values());

        $rows = $students->map(function (User $student) use ($requiredEvents, $attendanceByStudent): array {
            $eligibleEventIds = $requiredEvents
                ->filter(function (Event $event) use ($student): bool {
                    if ($event->visibility === 'public') {
                        return true;
                    }

                    return $student->department_id !== null && $event->department_id === $student->department_id;
                })
                ->pluck('id');

            $requiredCount = $eligibleEventIds->count();
            $presentCount = $attendanceByStudent
                ->get($student->id, collect())
                ->intersect($eligibleEventIds)
                ->count();
            $absenceCount = max($requiredCount - $presentCount, 0);

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number ?: 'N/A',
                'department' => $student->department?->department_name ?: 'N/A',
                'section' => $student->section?->section_name ?: 'N/A',
                'required_events' => $requiredCount,
                'present_count' => $presentCount,
                'absence_count' => $absenceCount,
                'attendance_rate' => $this->formatRate($presentCount, $requiredCount),
            ];
        })->all();

        return [
            'label' => $window['label'],
            'range' => $window['start']->format('M d, Y').' - '.$window['end']->format('M d, Y'),
            'student_count' => count($rows),
            'required_event_count' => $requiredEvents->count(),
            'total_present_count' => array_sum(array_column($rows, 'present_count')),
            'total_absence_count' => array_sum(array_column($rows, 'absence_count')),
            'students_with_absences' => count(array_filter($rows, fn (array $row): bool => $row['absence_count'] > 0)),
            'rows' => $rows,
        ];
    }

    private function formatRate(int $completed, int $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        return (string) round(($completed / $total) * 100).'%';
    }

    private function sessionStatus(Event $event): string
    {
        $eventDate = optional($event->event_date)?->format('Y-m-d') ?? $event->event_date;
        $todayDate = today()->format('Y-m-d');

        if ($eventDate > $todayDate) {
            return 'Upcoming';
        }

        if ($eventDate < $todayDate) {
            return 'Completed';
        }

        return 'Live';
    }

    private function semesterWindow(?CarbonImmutable $referenceDate = null): array
    {
        $reference = $referenceDate ?? CarbonImmutable::now();

        if ($reference->month >= 7) {
            $start = CarbonImmutable::create($reference->year, 7, 1, 0, 0, 0, $reference->timezone);
            $end = CarbonImmutable::create($reference->year, 12, 31, 23, 59, 59, $reference->timezone);

            return [
                'start' => $start,
                'end' => $end,
                'label' => 'First Semester',
            ];
        }

        return [
            'start' => CarbonImmutable::create($reference->year, 1, 1, 0, 0, 0, $reference->timezone),
            'end' => CarbonImmutable::create($reference->year, 6, 30, 23, 59, 59, $reference->timezone),
            'label' => 'Second Semester',
        ];
    }
}
