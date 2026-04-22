<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\EmailLog;
use App\Models\Event;
use App\Services\AttendanceService;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    public function index(AttendanceService $attendanceService): View
    {
        $highestAttendanceEvent = Event::query()
            ->withCount('attendances')
            ->orderByDesc('attendances_count')
            ->first();

        $mostEngagedDepartment = Department::query()
            ->with(['users.eventAttendances'])
            ->get()
            ->sortByDesc(fn (Department $department) => $department->users
                ->sum(fn ($user) => $user->eventAttendances->count()))
            ->first();

        $currentMonth = now()->format('F Y');
        $monthlyEmailCount = EmailLog::query()
            ->whereYear('sent_at', now()->year)
            ->whereMonth('sent_at', now()->month)
            ->count();
        $clearanceSummary = $attendanceService->getSemesterClearanceSummary();

        return view('admin.reports.index', [
            'reports' => [
                ['name' => 'Attendance Summary', 'period' => $currentMonth, 'status' => 'Ready'],
                ['name' => 'Announcement Inventory', 'period' => 'All Time', 'status' => Announcement::query()->exists() ? 'Ready' : 'No Data'],
                ['name' => 'Email Delivery Log', 'period' => $currentMonth, 'status' => $monthlyEmailCount > 0 ? 'Ready' : 'No Data'],
                ['name' => 'Semester Clearance Summary', 'period' => $clearanceSummary['label'], 'status' => $clearanceSummary['student_count'] > 0 ? 'Ready' : 'No Data'],
            ],
            'highlights' => [
                'Highest attendance event' => $highestAttendanceEvent?->event_title ?? 'No attendance data yet',
                'Most engaged department' => $mostEngagedDepartment?->department_name ?? 'No department activity yet',
                'Emails logged this month' => (string) $monthlyEmailCount,
                'Students with absences this semester' => (string) $clearanceSummary['students_with_absences'],
            ],
            'clearanceSummary' => $clearanceSummary,
        ]);
    }
}
