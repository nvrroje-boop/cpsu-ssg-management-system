<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class AttendanceScanFlowTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_admin_can_record_time_in_and_time_out_using_student_qr_during_an_active_session(): void
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, ['department_id' => $department->id]);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'student_number' => '2026-03020-N',
        ]);
        $student = app(QrCodeService::class)->ensureStudentIdentityToken($student);

        $this->travelTo(now()->startOfDay()->addHours(7)->addMinutes(45));

        $event = Event::query()->create([
            'event_title' => 'Attendance Test Event',
            'event_description' => 'Attendance validation test event.',
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

        $this->actingAs($admin)
            ->post(route('admin.events.attendance.start', $event->id))
            ->assertRedirect();

        $this->actingAs($admin)
            ->postJson(route('admin.events.attendance.scan', $event->id), [
                'student_qr' => 'student='.$student->qr_token,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'action' => 'time_in']);

        $this->assertDatabaseHas('event_attendances', [
            'event_id' => $event->id,
            'user_id' => $student->id,
            'student_id' => $student->id,
            'recorded_by_user_id' => $admin->id,
            'status' => 'present',
        ]);

        $this->travelTo(now()->startOfDay()->addHours(10));

        $this->actingAs($admin)
            ->postJson(route('admin.events.attendance.scan', $event->id), [
                'student_qr' => $student->qr_token,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'action' => 'time_out']);

        $attendance = EventAttendance::query()->firstOrFail();
        $this->assertNotNull($attendance->time_in);
        $this->assertNotNull($attendance->time_out);
    }

    public function test_student_cannot_access_management_scan_endpoint(): void
    {
        $student = $this->createUserWithRole(User::ROLE_STUDENT);
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $event = Event::query()->create([
            'event_title' => 'Protected Kiosk',
            'event_description' => 'Protected event attendance endpoint.',
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

        $this->actingAs($student)
            ->post(route('admin.events.attendance.scan', $event->id), ['student_qr' => 'student=fake-token'])
            ->assertForbidden();
    }
}
