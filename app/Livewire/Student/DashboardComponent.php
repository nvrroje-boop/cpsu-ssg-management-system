<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Concern;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardComponent extends Component
{
    public $upcomingEvents = [];
    public $recentAnnouncements = [];
    public $attendanceCount = 0;
    public $unreadConcernsCount = 0;
    public $nextEvent = null;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Upcoming events for student
        $this->upcomingEvents = Event::where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $e->event_date->format('M d'),
                'time' => $e->event_date->format('h:i A'),
                'attended' => EventAttendance::where('user_id', $user->id)
                    ->where('event_id', $e->id)
                    ->exists(),
            ])->toArray();

        // Next event (closest upcoming)
        $this->nextEvent = Event::where('event_date', '>=', now())
            ->orderBy('event_date')
            ->first()
            ?->toArray() ?? null;

        // Recent announcements
        $this->recentAnnouncements = Announcement::where('published', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'excerpt' => substr($a->message ?? $a->description ?? '', 0, 100) . '...',
                'date' => $a->created_at->format('M d'),
                'read' => $a->notifications ? $a->notifications->count() > 0 : false,
            ])->toArray();

        // Attendance count (past 30 days)
        $this->attendanceCount = EventAttendance::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Unread concerns count
        $this->unreadConcernsCount = Concern::where('user_id', $user->id)
            ->where('seen', false)
            ->count();
    }

    public function render()
    {
        return view('livewire.student.dashboard-component');
    }
}
