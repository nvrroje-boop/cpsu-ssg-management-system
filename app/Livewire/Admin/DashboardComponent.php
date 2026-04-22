<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
    public $totalStudents = 0;
    public $totalOfficers = 0;
    public $totalEvents = 0;
    public $totalAnnouncements = 0;
    public $attendanceTodayCount = 0;
    public $recentEvents = [];
    public $recentAnnouncements = [];
    public $attendanceRate = 0;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Load statistics
        $this->totalStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $this->totalOfficers = User::whereHas('roles', fn($q) => $q->where('name', 'officer'))->count();
        $this->totalEvents = Event::count();
        $this->totalAnnouncements = Announcement::where('published', true)->count();

        // Attendance today
        $today = Carbon::today();
        $this->attendanceTodayCount = EventAttendance::whereDate('created_at', $today)->count();

        // Recent events (next 5)
        $this->recentEvents = Event::where('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $e->event_date->format('M d'),
                'attendanceCount' => $e->eventAttendances()->count(),
            ])->toArray();

        // Recent announcements
        $this->recentAnnouncements = Announcement::where('published', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'date' => $a->created_at->format('M d'),
                'readCount' => $a->notifications ? $a->notifications->count() : 0,
            ])->toArray();

        // Attendance rate calculation
        $totalAttendanceSlots = Event::where('event_date', '>=', Carbon::now()->subDays(30))->count() * $this->totalStudents;
        $this->attendanceRate = $totalAttendanceSlots > 0
            ? round((EventAttendance::where('created_at', '>=', Carbon::now()->subDays(30))->count() / $totalAttendanceSlots) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.admin.dashboard-component');
    }
}
