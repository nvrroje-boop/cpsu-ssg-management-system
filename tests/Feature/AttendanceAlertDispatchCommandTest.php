<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class AttendanceAlertDispatchCommandTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_it_dispatches_event_reminder_once_when_event_is_ten_minutes_away(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $this->createUserWithRole(User::ROLE_STUDENT);

        $this->travelTo(now()->startOfDay()->addHours(7)->addMinutes(50));

        $event = Event::query()->create([
            'event_title' => 'Morning Assembly',
            'event_description' => 'Assembly reminder test',
            'event_date' => now()->toDateString(),
            'event_time' => '08:00',
            'location' => 'Gym',
            'visibility' => 'public',
            'attendance_required' => true,
            'attendance_time_in_starts_at' => '07:30',
            'attendance_time_in_ends_at' => '08:10',
            'attendance_time_out_starts_at' => '09:50',
            'attendance_time_out_ends_at' => '10:30',
            'attendance_late_after' => '08:00',
            'created_by_user_id' => $admin->id,
        ]);

        $this->artisan('attendance:dispatch-alerts')->assertSuccessful();
        $this->assertNotNull($event->fresh()->event_reminder_sent_at);

        $this->assertDatabaseHas('system_notifications', [
            'title' => 'Event Reminder',
            'type' => 'event',
            'target_role' => 'student',
        ]);

        $beforeCount = \App\Models\SystemNotification::query()->count();
        $this->artisan('attendance:dispatch-alerts')->assertSuccessful();
        $this->assertSame($beforeCount, \App\Models\SystemNotification::query()->count());
    }

    public function test_it_dispatches_closing_soon_and_auto_closes_attendance(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $student = $this->createUserWithRole(User::ROLE_STUDENT);

        $this->travelTo(now()->startOfDay()->addHours(10)->addMinutes(21));

        $event = Event::query()->create([
            'event_title' => 'Closing Window Test',
            'event_description' => 'Closing soon alert test',
            'event_date' => now()->toDateString(),
            'event_time' => '08:00',
            'location' => 'Auditorium',
            'visibility' => 'public',
            'attendance_required' => true,
            'attendance_time_in_starts_at' => '07:30',
            'attendance_time_in_ends_at' => '08:10',
            'attendance_time_out_starts_at' => '09:50',
            'attendance_time_out_ends_at' => '10:30',
            'attendance_late_after' => '08:00',
            'attendance_active' => true,
            'attendance_started_at' => now()->subHours(2),
            'attendance_started_by_user_id' => $admin->id,
            'attendance_token' => hash('sha256', 'closing-window-test'),
            'attendance_token_expires_at' => now()->addMinutes(20),
            'created_by_user_id' => $admin->id,
        ]);

        EventAttendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'student_id' => $student->id,
            'time_in' => now()->subHours(2),
            'status' => 'present',
            'attendance_method' => 'kiosk',
            'recorded_by_user_id' => $admin->id,
            'scanned_by_user_id' => $admin->id,
            'last_scanned_at' => now()->subHours(2),
            'scanned_at' => now()->subHours(2),
        ]);

        $this->artisan('attendance:dispatch-alerts')->assertSuccessful();

        $event->refresh();
        $this->assertNotNull($event->attendance_closing_notified_at);
        $this->assertTrue($event->attendance_active);

        $this->assertDatabaseHas('system_notifications', [
            'title' => 'Attendance closing soon',
            'type' => 'attendance',
            'target_role' => 'student',
        ]);

        $this->travelTo(now()->startOfDay()->addHours(10)->addMinutes(35));
        $this->artisan('attendance:dispatch-alerts')->assertSuccessful();

        $event->refresh();
        $this->assertFalse($event->attendance_active);
        $this->assertNotNull($event->attendance_closed_notified_at);

        $this->assertDatabaseHas('event_attendances', [
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'incomplete',
        ]);
    }
}
