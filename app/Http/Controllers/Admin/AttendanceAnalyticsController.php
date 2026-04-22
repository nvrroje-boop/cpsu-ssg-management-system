<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventAttendance;
use App\Models\Event;
use App\Models\EventQr;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceAnalyticsController extends Controller
{
    /**
     * Display attendance analytics dashboard
     */
    public function index(): View
    {
        // Overall stats
        $totalEvents = Event::count();
        $totalAttendances = EventAttendance::count();
        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('role_name', 'Student');
        })->count();

        $overallAttendanceRate = $totalStudents > 0
            ? round(($totalAttendances / ($totalEvents * $totalStudents)) * 100, 2)
            : 0;

        // Recent events with attendance data
        $recentEvents = Event::query()
            ->with('attendances')
            ->orderByDesc('event_date')
            ->limit(10)
            ->get()
            ->map(function (Event $event) use ($totalStudents) {
                $attendanceCount = $event->attendances->count();
                $attendanceRate = $totalStudents > 0
                    ? round(($attendanceCount / $totalStudents) * 100, 2)
                    : 0;

                return [
                    'id' => $event->id,
                    'title' => $event->event_title,
                    'date' => $event->event_date?->format('Y-m-d') ?? 'TBA',
                    'attendances' => $attendanceCount,
                    'rate' => $attendanceRate,
                    'total_students' => $totalStudents,
                ];
            });

        // Attendance trend (last 7 days)
        $attendanceTrend = EventAttendance::query()
            ->select(DB::raw('DATE(COALESCE(last_scanned_at, time_in, scanned_at)) as date'), DB::raw('COUNT(*) as count'))
            ->whereRaw('COALESCE(last_scanned_at, time_in, scanned_at) >= ?', [now()->subDays(7)])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        // QR code statistics
        $qrStats = [
            'total_generated' => EventQr::count(),
            'valid_remaining' => EventQr::valid()->count(),
            'used' => EventQr::used()->count(),
            'expired' => EventQr::expired()->count(),
        ];

        // Department-wise attendance
        $departmentStats = User::query()
            ->whereHas('role', function ($query) {
                $query->where('role_name', 'Student');
            })
            ->where('department_id', '!=', null)
            ->select('department_id')
            ->withCount(['attendances'])
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(function (User $user) {
                return [
                    'department' => $user->department?->department_name ?? 'Unknown',
                    'attendances' => $user->attendances_count,
                ];
            });

        return view('admin.analytics.attendance', [
            'totalEvents' => $totalEvents,
            'totalAttendances' => $totalAttendances,
            'totalStudents' => $totalStudents,
            'overallAttendanceRate' => $overallAttendanceRate,
            'recentEvents' => $recentEvents,
            'attendanceTrend' => $attendanceTrend,
            'qrStats' => $qrStats,
            'departmentStats' => $departmentStats,
        ]);
    }

    /**
     * Display detailed analytics for a specific event
     */
    public function event(Event $event): View
    {
        $attendances = $event->attendances()->with('student')->get();

        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('role_name', 'Student');
        })->count();

        $attendanceRate = $totalStudents > 0
            ? round(($attendances->count() / $totalStudents) * 100, 2)
            : 0;

        // Department breakdown for this event
        $departmentBreakdown = $attendances
            ->groupBy(fn ($a) => $a->student->department?->department_name ?? 'Unknown')
            ->map(fn ($group) => $group->count())
            ->toArray();

        // Hourly distribution (if scanned_at times are available)
        $hourlyDistribution = $attendances
            ->groupBy(fn ($a) => ($a->last_scanned_at ?? $a->time_in ?? $a->scanned_at)?->format('H:00') ?? 'Unknown')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return view('admin.analytics.event-detail', [
            'event' => $event,
            'attendances' => $attendances,
            'attendanceCount' => $attendances->count(),
            'attendanceRate' => $attendanceRate,
            'totalStudents' => $totalStudents,
            'departmentBreakdown' => $departmentBreakdown,
            'hourlyDistribution' => $hourlyDistribution,
        ]);
    }
}
