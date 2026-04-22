<?php

namespace App\Livewire\Officer;

use Livewire\Component;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Announcement;
use Carbon\Carbon;

class DashboardComponent extends Component
{
    public $upcomingEvents = [];
    public $totalAttendanceToday = 0;
    public $recentAnnouncements = [];
    public $eventsThisMonth = 0;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Upcoming events (managed by this officer)
        $this->upcomingEvents = Event::where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $e->event_date->format('M d, Y'),
                'time' => $e->event_date->format('h:i A'),
                'location' => $e->location ?? 'TBA',
                'attendanceCount' => $e->eventAttendances()->count(),
            ])->toArray();

        // Attendance today
        $today = Carbon::today();
        $this->totalAttendanceToday = EventAttendance::whereDate('created_at', $today)->count();

        // Recent announcements
        $this->recentAnnouncements = Announcement::where('published', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->toArray();

        // Events this month
        $this->eventsThisMonth = Event::whereBetween('event_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();
    }

    public function render()
    {
        return view('livewire.officer.dashboard-component');
    }
}
