<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(AttendanceService $attendanceService): View
    {
        $attendanceSummary = $attendanceService->getSummary();
        $studentCount = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->count();

        return view('admin.dashboard.index', [
            'stats' => [
                'students' => $studentCount,
                'active_events' => $attendanceSummary['active_events'],
                'today_scans' => $attendanceSummary['today_scans'],
                'attendance_rate' => $attendanceSummary['attendance_rate'],
            ],
            'recentAnnouncements' => Announcement::query()
                ->orderByDesc('created_at')
                ->limit(3)
                ->get()
                ->map(fn (Announcement $announcement): array => [
                    'title' => $announcement->title,
                    'status' => ucfirst($announcement->visibility),
                ])
                ->all(),
            'upcomingEvents' => Event::query()
                ->whereDate('event_date', '>=', today())
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->limit(3)
                ->get()
                ->map(fn (Event $event): array => [
                    'title' => $event->event_title,
                    'date' => trim(
                        (optional($event->event_date)?->format('F d, Y') ?? (string) $event->event_date)
                        .' '
                        .substr((string) $event->event_time, 0, 5)
                    ),
                ])
                ->all(),
            'recentAttendance' => $attendanceService->getRecentAttendance(),
            'chartLabels' => Event::query()
                ->withCount('attendances')
                ->orderByDesc('attendances_count')
                ->limit(5)
                ->pluck('event_title')
                ->all(),
            'chartData' => Event::query()
                ->withCount('attendances')
                ->orderByDesc('attendances_count')
                ->limit(5)
                ->pluck('attendances_count')
                ->all(),
        ]);
    }
}
