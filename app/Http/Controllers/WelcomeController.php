<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()
            ->published()
            ->where('visibility', 'public')
            ->orderByDesc('created_at')
            ->get();

        $events = Event::query()
            ->where('visibility', 'public')
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();

        $studentCount = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->count();
        $requiredEventCount = Event::query()
            ->where('attendance_required', true)
            ->count();
        $requiredAttendanceCount = EventAttendance::query()
            ->whereHas('event', fn ($eventQuery) => $eventQuery->where('attendance_required', true))
            ->count();
        $attendanceRate = $studentCount > 0 && $requiredEventCount > 0
            ? round(($requiredAttendanceCount / ($studentCount * $requiredEventCount)) * 100).'%'
            : '0%';

        $stats = [
            'students' => $studentCount,
            'officers' => User::query()->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('role_name', [User::ROLE_OFFICER, User::ROLE_SSG_OFFICER]))->count(),
            'announcements' => $announcements->count(),
            'attendance_rate' => $attendanceRate,
            'events' => $events->count(),
        ];

        return view('welcome', compact('announcements', 'events', 'stats'));
    }
}
