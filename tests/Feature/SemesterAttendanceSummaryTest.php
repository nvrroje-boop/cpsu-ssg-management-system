<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class SemesterAttendanceSummaryTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_it_calculates_present_and_absence_totals_for_the_current_semester(): void
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);

        $firstRequired = Event::query()->create([
            'event_title' => 'Required Event One',
            'event_description' => 'Semester event one',
            'event_date' => now()->toDateString(),
            'event_time' => '09:00',
            'location' => 'Gym',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        $secondRequired = Event::query()->create([
            'event_title' => 'Required Event Two',
            'event_description' => 'Semester event two',
            'event_date' => now()->addDays(2)->toDateString(),
            'event_time' => '10:00',
            'location' => 'Auditorium',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        Event::query()->create([
            'event_title' => 'Optional Event',
            'event_description' => 'Optional event',
            'event_date' => now()->addDays(4)->toDateString(),
            'event_time' => '11:00',
            'location' => 'Library',
            'visibility' => 'public',
            'attendance_required' => false,
            'created_by_user_id' => $admin->id,
        ]);

        EventAttendance::query()->create([
            'event_id' => $firstRequired->id,
            'student_id' => $student->id,
            'token' => 'semester-summary-test-token',
            'scanned_at' => now(),
        ]);

        $summary = app(AttendanceService::class)->getStudentSemesterSummary($student);

        $this->assertSame(2, $summary['required_events']);
        $this->assertSame(1, $summary['present_count']);
        $this->assertSame(1, $summary['absence_count']);
        $this->assertSame('50%', $summary['attendance_rate']);
        $this->assertNotEmpty($summary['range']);
        $this->assertNotEmpty($summary['label']);
    }

    public function test_it_builds_a_clearance_summary_from_actual_semester_attendance(): void
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $firstStudent = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'student_number' => '2026-00001-N',
            'name' => 'First Student',
        ]);
        $secondStudent = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'student_number' => '2026-00002-N',
            'name' => 'Second Student',
        ]);

        $firstRequired = Event::query()->create([
            'event_title' => 'Required Event One',
            'event_description' => 'Semester event one',
            'event_date' => now()->toDateString(),
            'event_time' => '09:00',
            'location' => 'Gym',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        Event::query()->create([
            'event_title' => 'Required Event Two',
            'event_description' => 'Semester event two',
            'event_date' => now()->addDays(1)->toDateString(),
            'event_time' => '10:00',
            'location' => 'Auditorium',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        EventAttendance::query()->create([
            'event_id' => $firstRequired->id,
            'student_id' => $firstStudent->id,
            'token' => 'clearance-summary-token-1',
            'scanned_at' => now(),
        ]);

        $summary = app(AttendanceService::class)->getSemesterClearanceSummary();

        $this->assertSame(2, $summary['student_count']);
        $this->assertSame(2, $summary['required_event_count']);
        $this->assertSame(1, $summary['total_present_count']);
        $this->assertSame(3, $summary['total_absence_count']);
        $this->assertSame(2, $summary['students_with_absences']);
        $this->assertCount(2, $summary['rows']);

        $firstRow = collect($summary['rows'])->firstWhere('student_id', $firstStudent->id);
        $secondRow = collect($summary['rows'])->firstWhere('student_id', $secondStudent->id);

        $this->assertSame(1, $firstRow['present_count']);
        $this->assertSame(1, $firstRow['absence_count']);
        $this->assertSame('50%', $firstRow['attendance_rate']);
        $this->assertSame(0, $secondRow['present_count']);
        $this->assertSame(2, $secondRow['absence_count']);
        $this->assertSame('0%', $secondRow['attendance_rate']);
    }
}
