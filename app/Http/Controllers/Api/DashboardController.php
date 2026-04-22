<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\EventAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dynamic dashboard stats
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $userRole = $user->roles->first()?->name;

        $stats = match ($userRole) {
            'admin' => $this->getAdminStats($user),
            'officer' => $this->getOfficerStats($user),
            'student' => $this->getStudentStats($user),
            default => []
        };

        return response()->json([
            'role' => $userRole,
            'stats' => $stats,
        ]);
    }

    /**
     * Admin dashboard statistics
     */
    private function getAdminStats(User $user): array
    {
        $totalEvents = Event::count();
        $totalAttendanceThisMonth = EventAttendance::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $totalStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $totalAnnouncements = Announcement::where('published', true)->count();

        return [
            'total_students' => $totalStudents,
            'total_events' => $totalEvents,
            'total_announcements' => $totalAnnouncements,
            'attendance_this_month' => $totalAttendanceThisMonth,
            'avg_attendance_rate' => $this->calculateAttendanceRate(),
        ];
    }

    /**
     * Officer dashboard statistics
     */
    private function getOfficerStats(User $user): array
    {
        $eventsThisMonth = Event::whereBetween('event_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();

        $attendanceTodayCount = EventAttendance::whereDate('created_at', Carbon::today())->count();

        return [
            'events_this_month' => $eventsThisMonth,
            'attendance_today' => $attendanceTodayCount,
            'total_announcements' => Announcement::where('published', true)->count(),
        ];
    }

    /**
     * Student dashboard statistics
     */
    private function getStudentStats(User $user): array
    {
        $attendanceCount = EventAttendance::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $upcomingEvents = Event::where('event_date', '>=', now())->count();

        $unreadConcerns = \App\Models\Concern::where('user_id', $user->id)
            ->where('seen', false)
            ->count();

        return [
            'attendance_this_month' => $attendanceCount,
            'upcoming_events' => $upcomingEvents,
            'unread_concerns' => $unreadConcerns,
            'announcements' => Announcement::where('published', true)->count(),
        ];
    }

    /**
     * Calculate overall attendance rate
     */
    private function calculateAttendanceRate(): float
    {
        $totalStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $pastMonthEvents = Event::where('event_date', '>=', Carbon::now()->subDays(30))->count();
        $totalAttendanceSlots = $totalStudents * $pastMonthEvents;

        if ($totalAttendanceSlots === 0) {
            return 0;
        }

        $actualAttendance = EventAttendance::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        return round(($actualAttendance / $totalAttendanceSlots) * 100, 1);
    }
}
